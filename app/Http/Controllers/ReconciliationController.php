<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reconciliation;
use App\Models\ReconciliationAdjustment;
use App\Models\Entry;
use App\Models\EntryItem;
use App\Models\Ledger;
use App\Models\AcYear;
use DB;
use Auth;

class ReconciliationController extends Controller
{
    /**
     * Display list of reconciliations
     */
    public function index()
    {
        $reconciliations = Reconciliation::with(['ledger', 'reconciledBy'])
            ->orderBy('month', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('accounts.reconciliation.index', compact('reconciliations'));
    }
    
    /**
     * Show form to create new reconciliation
     */
    public function create()
    {
        $bankLedgers = Ledger::where('type', 1)
            ->where('reconciliation', 1)
            ->orderBy('name')
            ->get();
            
        // Get current accounting year
        $currentYear = AcYear::where('status', 1)->first();
        
        return view('accounts.reconciliation.create', compact('bankLedgers', 'currentYear'));
    }
    
    /**
     * Start reconciliation process
     */
    public function start(Request $request)
    {
        $request->validate([
            'ledger_id' => 'required|exists:ledgers,id',
            'month' => 'required|date_format:Y-m',
            'statement_closing_balance' => 'required|numeric'
        ]);
        
        // Check if reconciliation already exists
        $existing = Reconciliation::where('ledger_id', $request->ledger_id)
            ->where('month', $request->month)
            ->first();
            
        if ($existing) {
            if ($existing->status == 'locked') {
                return back()->with('error', 'This reconciliation is already locked and cannot be modified.');
            }
            return redirect()->route('accounts.reconciliation.process', $existing->id);
        }
        
        DB::beginTransaction();
        
        try {
            // Calculate opening balance (previous month's closing balance or 0)
            $previousMonth = date('Y-m', strtotime($request->month . '-01 -1 month'));
            $previousRecon = Reconciliation::where('ledger_id', $request->ledger_id)
                ->where('month', $previousMonth)
                ->where('status', '!=', 'draft')
                ->first();
                
            $openingBalance = $previousRecon ? $previousRecon->statement_closing_balance : 0;
            
            // Create new reconciliation
            $reconciliation = new Reconciliation();
            $reconciliation->ledger_id = $request->ledger_id;
            $reconciliation->month = $request->month;
            $reconciliation->statement_closing_balance = $request->statement_closing_balance;
            $reconciliation->opening_balance = $openingBalance;
            $reconciliation->reconciled_balance = 0;
            $reconciliation->difference = $request->statement_closing_balance;
            $reconciliation->status = 'draft';
            $reconciliation->created_by = Auth::id();
            $reconciliation->save();
            
            DB::commit();
            
            return redirect()->route('accounts.reconciliation.process', $reconciliation->id);
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error starting reconciliation: ' . $e->getMessage());
        }
    }
    
    /**
     * Show reconciliation process screen
     */
    public function process($id)
    {
        $reconciliation = Reconciliation::with('ledger')->findOrFail($id);
        
        if ($reconciliation->status == 'locked') {
            return redirect()->route('accounts.reconciliation.view', $id)
                ->with('info', 'This reconciliation is locked. Viewing in read-only mode.');
        }
        
        // Get month start and end dates
        $monthStart = $reconciliation->month . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));
        
        // Get all transactions for the ledger in the selected month
        $transactions = EntryItem::with(['entry', 'entry.entryItems'])
            ->where('ledger_id', $reconciliation->ledger_id)
            ->whereHas('entry', function($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('date', [$monthStart, $monthEnd]);
            })
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Get pending transactions from previous months
        $pendingTransactions = EntryItem::with(['entry', 'entry.entryItems'])
            ->where('ledger_id', $reconciliation->ledger_id)
            ->where('is_reconciled', 0)
            ->whereHas('entry', function($query) use ($monthStart) {
                $query->where('date', '<', $monthStart);
            })
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Calculate current reconciled balance
        $reconciledBalance = $this->calculateReconciledBalance($reconciliation);
        
        return view('accounts.reconciliation.process', compact(
            'reconciliation', 
            'transactions', 
            'pendingTransactions',
            'reconciledBalance'
        ));
    }
    
    /**
     * Update reconciliation status (mark items)
     */
    public function updateItems(Request $request, $id)
    {
        $reconciliation = Reconciliation::findOrFail($id);
        
        if ($reconciliation->status == 'locked') {
            return response()->json(['error' => 'Reconciliation is locked'], 403);
        }
        
        $request->validate([
            'items' => 'array',
            'items.*' => 'exists:entryitems,id'
        ]);
        
        DB::beginTransaction();
        
        try {
            // First, unmark all items for this reconciliation
            EntryItem::where('reconciliation_id', $reconciliation->id)
                ->update([
                    'is_reconciled' => 0,
                    'reconciliation_id' => null,
                    'reconciliation_date' => null
                ]);
            
            // Mark selected items as reconciled
            if (!empty($request->items)) {
                EntryItem::whereIn('id', $request->items)
                    ->update([
                        'is_reconciled' => 1,
                        'reconciliation_id' => $reconciliation->id,
                        'reconciliation_date' => now()
                    ]);
            }
            
            // Recalculate reconciled balance
            $reconciledBalance = $this->calculateReconciledBalance($reconciliation);
            
            // Update reconciliation
            $reconciliation->reconciled_balance = $reconciledBalance;
            $reconciliation->difference = $reconciliation->statement_closing_balance - $reconciledBalance;
            $reconciliation->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'reconciled_balance' => $reconciledBalance,
                'difference' => $reconciliation->difference
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Add investigation note to transaction
     */
    public function addInvestigationNote(Request $request, $id)
    {
        $request->validate([
            'item_id' => 'required|exists:entryitems,id',
            'note' => 'required|string|max:500'
        ]);
        
        $reconciliation = Reconciliation::findOrFail($id);
        
        if ($reconciliation->status == 'locked') {
            return response()->json(['error' => 'Reconciliation is locked'], 403);
        }
        
        $item = EntryItem::findOrFail($request->item_id);
        $item->investigation_note = $request->note;
        $item->save();
        
        // Create adjustment record
        $adjustment = new ReconciliationAdjustment();
        $adjustment->reconciliation_id = $reconciliation->id;
        $adjustment->adjustment_type = 'investigation_tag';
        $adjustment->amount = $item->amount;
        $adjustment->description = 'Investigation: ' . $request->note;
        $adjustment->created_by = Auth::id();
        $adjustment->save();
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Create manual adjustment entry
     */
    public function createAdjustment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|in:debit,credit',
            'adjustment_ledger_id' => 'required|exists:ledgers,id',
            'description' => 'required|string|max:500'
        ]);
        
        $reconciliation = Reconciliation::findOrFail($id);
        
        if ($reconciliation->status == 'locked') {
            return back()->with('error', 'Reconciliation is locked');
        }
        
        DB::beginTransaction();
        
        try {
            // Determine entry type based on adjustment type
            // Debit increases balance (Receipt), Credit decreases balance (Payment)
            $entryTypeId = $request->type == 'debit' ? 1 : 2;
            
            // Generate entry code based on type
            $prefix = $request->type == 'debit' ? 'REC-ADJ' : 'PAY-ADJ';
            $entryCode = $prefix . '-' . date('Ymd') . '-' . $reconciliation->id;
            
            // Create adjustment entry
            $entry = new Entry();
            $entry->entrytype_id = $entryTypeId;
            $entry->number = $entryCode;
            $entry->entry_code = $entryCode;
            $entry->date = date('Y-m-d');
            $entry->dr_total = abs($request->amount);
            $entry->cr_total = abs($request->amount);
            $entry->narration = 'Reconciliation Adjustment: ' . $request->description;
            $entry->payment = 'ADJUSTMENT';
            $entry->created_by = Auth::id();
            $entry->save();
            
            // Create entry items based on type
            if ($request->type == 'debit') {
                // Receipt entry - Debit bank, Credit adjustment ledger
                $item1 = new EntryItem();
                $item1->entry_id = $entry->id;
                $item1->ledger_id = $reconciliation->ledger_id;
                $item1->amount = abs($request->amount);
                $item1->dc = 'D';
                $item1->details = 'Reconciliation adjustment - Increase';
                $item1->is_reconciled = 1;
                $item1->reconciliation_id = $reconciliation->id;
                $item1->reconciliation_date = now();
                $item1->save();
                
                $item2 = new EntryItem();
                $item2->entry_id = $entry->id;
                $item2->ledger_id = $request->adjustment_ledger_id;
                $item2->amount = abs($request->amount);
                $item2->dc = 'C';
                $item2->details = 'Reconciliation adjustment';
                $item2->save();
            } else {
                // Payment entry - Debit adjustment ledger, Credit bank
                $item1 = new EntryItem();
                $item1->entry_id = $entry->id;
                $item1->ledger_id = $request->adjustment_ledger_id;
                $item1->amount = abs($request->amount);
                $item1->dc = 'D';
                $item1->details = 'Reconciliation adjustment';
                $item1->save();
                
                $item2 = new EntryItem();
                $item2->entry_id = $entry->id;
                $item2->ledger_id = $reconciliation->ledger_id;
                $item2->amount = abs($request->amount);
                $item2->dc = 'C';
                $item2->details = 'Reconciliation adjustment - Decrease';
                $item2->is_reconciled = 1;
                $item2->reconciliation_id = $reconciliation->id;
                $item2->reconciliation_date = now();
                $item2->save();
            }
            
            // Create adjustment record
            $adjustment = new ReconciliationAdjustment();
            $adjustment->reconciliation_id = $reconciliation->id;
            $adjustment->adjustment_type = 'manual_entry';
            $adjustment->entry_id = $entry->id;
            $adjustment->amount = $request->amount;
            $adjustment->description = $request->description;
            $adjustment->created_by = Auth::id();
            $adjustment->save();
            
            // Recalculate balance
            $reconciledBalance = $this->calculateReconciledBalance($reconciliation);
            $reconciliation->reconciled_balance = $reconciledBalance;
            $reconciliation->difference = $reconciliation->statement_closing_balance - $reconciledBalance;
            $reconciliation->save();
            
            DB::commit();
            
            return back()->with('success', 'Adjustment entry created successfully');
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error creating adjustment: ' . $e->getMessage());
        }
    }
    
    /**
     * Finalize reconciliation
     */
    public function finalize(Request $request, $id)
    {
        $reconciliation = Reconciliation::findOrFail($id);
        
        if ($reconciliation->status == 'locked') {
            return back()->with('error', 'Reconciliation is already locked');
        }
        
        $request->validate([
            'notes' => 'nullable|string'
        ]);
        
        // Check if difference is zero or acceptable
        if (abs($reconciliation->difference) > 0.01) {
            return back()->with('error', 'Cannot finalize reconciliation with unresolved difference of ' . 
                number_format(abs($reconciliation->difference), 2));
        }
        
        DB::beginTransaction();
        
        try {
            $reconciliation->status = 'completed';
            $reconciliation->reconciled_date = now();
            $reconciliation->reconciled_by = Auth::id();
            $reconciliation->notes = $request->notes;
            $reconciliation->save();
            
            DB::commit();
            
            return redirect()->route('accounts.reconciliation.index')
                ->with('success', 'Reconciliation completed successfully');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error finalizing reconciliation: ' . $e->getMessage());
        }
    }
    
    /**
     * Lock reconciliation
     */
    public function lock($id)
    {
        $reconciliation = Reconciliation::findOrFail($id);
        
        if ($reconciliation->status != 'completed') {
            return back()->with('error', 'Only completed reconciliations can be locked');
        }
        
        $reconciliation->status = 'locked';
        $reconciliation->save();
        
        return back()->with('success', 'Reconciliation locked successfully');
    }
    
    /**
     * Update bank statement balance
     */
    public function updateBalance(Request $request, $id)
    {
        $reconciliation = Reconciliation::findOrFail($id);
        
        // Check if reconciliation is editable
        if ($reconciliation->status != 'draft') {
            return response()->json(['error' => 'Cannot edit balance after reconciliation is completed'], 403);
        }
        
        $request->validate([
            'statement_closing_balance' => 'required|numeric'
        ]);
        
        $reconciliation->statement_closing_balance = $request->statement_closing_balance;
        $reconciliation->difference = $reconciliation->statement_closing_balance - $reconciliation->reconciled_balance;
        $reconciliation->save();
        
        return response()->json([
            'success' => true,
            'difference' => $reconciliation->difference
        ]);
    }
    
    /**
     * Delete reconciliation
     */
    public function destroy($id)
    {
        $reconciliation = Reconciliation::findOrFail($id);
        
        // Check if reconciliation can be deleted
        if ($reconciliation->status == 'locked') {
            return back()->with('error', 'Cannot delete locked reconciliation');
        }
        
        DB::beginTransaction();
        
        try {
            // Remove reconciliation references from entry items
            EntryItem::where('reconciliation_id', $reconciliation->id)
                ->update([
                    'is_reconciled' => 0,
                    'reconciliation_id' => null,
                    'reconciliation_date' => null
                ]);
            
            // Delete adjustments (but keep the entries for audit trail)
            ReconciliationAdjustment::where('reconciliation_id', $reconciliation->id)->delete();
            
            // Delete reconciliation
            $reconciliation->delete();
            
            DB::commit();
            
            return redirect()->route('accounts.reconciliation.index')
                ->with('success', 'Reconciliation deleted successfully');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error deleting reconciliation: ' . $e->getMessage());
        }
    }
    
    /**
     * View reconciliation details
     */
    public function view($id)
    {
        $reconciliation = Reconciliation::with(['ledger', 'adjustments', 'reconciledBy'])
            ->findOrFail($id);
            
        // Get reconciled items
        $reconciledItems = EntryItem::with(['entry', 'ledger'])
            ->where('reconciliation_id', $reconciliation->id)
            ->where('is_reconciled', 1)
            ->orderBy('created_at')
            ->get();
            
        return view('accounts.reconciliation.view', compact('reconciliation', 'reconciledItems'));
    }
    
    /**
     * Generate reconciliation report
     */
    public function report($id)
    {
        $reconciliation = Reconciliation::with(['ledger', 'adjustments', 'reconciledBy'])
            ->findOrFail($id);
            
        // Get all transactions
        $monthStart = $reconciliation->month . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));
        
        $allTransactions = EntryItem::with(['entry'])
            ->where('ledger_id', $reconciliation->ledger_id)
            ->whereHas('entry', function($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('date', [$monthStart, $monthEnd]);
            })
            ->orderBy('created_at')
            ->get();
            
        $reconciledItems = $allTransactions->where('is_reconciled', 1);
        $unreconciledItems = $allTransactions->where('is_reconciled', 0);
        
        return view('accounts.reconciliation.report', compact(
            'reconciliation', 
            'reconciledItems', 
            'unreconciledItems'
        ));
    }
    
    /**
     * Calculate reconciled balance
     */
    private function calculateReconciledBalance($reconciliation)
    {
        $balance = $reconciliation->opening_balance;
        
        // Get all reconciled items
        $items = EntryItem::where('reconciliation_id', $reconciliation->id)
            ->where('is_reconciled', 1)
            ->get();
            
        foreach ($items as $item) {
            if ($item->dc == 'D') {
                $balance += $item->amount;
            } else {
                $balance -= $item->amount;
            }
        }
        
        return $balance;
    }
}