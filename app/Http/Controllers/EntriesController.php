<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entry;
use App\Models\EntryItem;
use App\Models\Ledger;
use App\Models\Group;
use App\Models\Fund;
use App\Models\AcYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EntriesController extends Controller
{
    
    /**
     * Receipt List
     */
    public function receiptList()
    {
        $receipts = Entry::where('entrytype_id', 1)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('accounts.entries.receipt_list', compact('receipts'));
    }
    
    /**
     * Payment List
     */
    public function paymentList()
    {
        $payments = Entry::where('entrytype_id', 2)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('accounts.entries.payment_list', compact('payments'));
    }
    
    /**
     * Journal List
     */
    public function journalList()
    {
        $journals = Entry::where('entrytype_id', 4)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('accounts.entries.journal_list', compact('journals'));
    }
    
    /**
     * Add Receipt
     */
    public function addReceipt()
    {
        $bankLedgers = Ledger::where('type', 1)->orderBy('name')->get();
        $creditLedgers = Ledger::orderBy('left_code', 'asc')->orderBy('right_code', 'asc')->get();
        $funds = Fund::orderBy('name')->get();
        
        // Generate entry code
        $lastEntry = Entry::where('entrytype_id', 1)
            ->whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->orderBy('id', 'desc')
            ->first();
        $lastEntryNumber = 0;
		if(!empty($lastEntry)) $lastEntryNumber = (int) substr($lastEntry->entry_code, -5);
        $entryCode = $this->generateEntryCode('REC', $lastEntryNumber);
        
        return view('accounts.entries.add_receipt', compact('bankLedgers', 'creditLedgers', 'funds', 'entryCode'));
    }
    
    /**
     * Add Payment
     */
    public function addPayment()
    {
        $bankLedgers = Ledger::where('type', 1)->orderBy('name')->get();
        $debitLedgers = Ledger::orderBy('left_code', 'asc')->orderBy('right_code', 'asc')->get();
        $funds = Fund::orderBy('name')->get();
        
        // Generate entry code
        $lastEntry = Entry::where('entrytype_id', 2)
            ->whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->orderBy('id', 'desc')
            ->first();
        $lastEntryNumber = 0;
		if(!empty($lastEntry)) $lastEntryNumber = (int) substr($lastEntry->entry_code, -5);
        $entryCode = $this->generateEntryCode('PAY', $lastEntryNumber);
        
        return view('accounts.entries.add_payment', compact('bankLedgers', 'debitLedgers', 'funds', 'entryCode'));
    }
    
    /**
     * Add Journal
     */
    public function addJournal()
    {
        $ledgers = Ledger::where('type', 0)->orderBy('name')->get();
        $funds = Fund::orderBy('name')->get();
        
        // Generate entry code
        $lastEntry = Entry::where('entrytype_id', 4)
            ->whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->orderBy('id', 'desc')
            ->first();
        $lastEntryNumber = 0;
		if(!empty($lastEntry)) $lastEntryNumber = (int) substr($lastEntry->entry_code, -5);
        $entryCode = $this->generateEntryCode('JOR', $lastEntryNumber);
        
        return view('accounts.entries.add_journal', compact('ledgers', 'funds', 'entryCode'));
    }
    
    /**
     * Store Receipt
     */
    public function storeReceipt(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'debit_account' => 'required|exists:ledgers,id',
            'fund_id' => 'required|exists:funds,id',
            'payment_mode' => 'required|in:CASH,CHEQUE,ONLINE',
            'received_from' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.ledger_id' => 'required|exists:ledgers,id',
            'items.*.amount' => 'required|numeric|min:0.01'
        ]);
        
        DB::beginTransaction();
        
        try {
            // Calculate totals
            $totalAmount = array_sum(array_column($request->items, 'amount'));
            
            $lastEntry = Entry::where('entrytype_id', 1)
            ->whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->orderBy('id', 'desc')
            ->first();
			$lastEntryNumber = 0;
			if(!empty($lastEntry)) $lastEntryNumber = (int) substr($lastEntry->entry_code, -5);
			$entryCode = $this->generateEntryCode('REC', $lastEntryNumber);
            // Create entry
            $entry = new Entry();
            $entry->entrytype_id = 1; // Receipt
            $entry->number = $entryCode;
            $entry->entry_code = $entryCode;
            $entry->date = $request->date;
            $entry->dr_total = $totalAmount;
            $entry->cr_total = $totalAmount;
            $entry->narration = $request->narration;
            $entry->fund_id = $request->fund_id;
            $entry->payment = $request->payment_mode;
            $entry->paid_to = $request->received_from;
            $entry->created_by = Auth::id();
            
            if ($request->payment_mode == 'CHEQUE') {
                $entry->cheque_no = !empty($request->cheque_no) ? $request->cheque_no : NULL;
                $entry->cheque_date = !empty($request->cheque_date) ? $request->cheque_date : NULL;
            }elseif ($request->payment_mode == 'ONLINE') {
                $entry->cheque_no = !empty($request->transaction_no) ? $request->transaction_no : NULL;
                $entry->cheque_date = !empty($request->transaction_date) ? $request->transaction_date : NULL;
            }
            
            $entry->save();
            $drTotal = $totalAmount;
			if ($request->has('discount_amount') && $request->discount_amount > 0) $drTotal = $totalAmount - $request->discount_amount;
            // Create debit entry (Bank/Cash)
            $debitItem = new EntryItem();
            $debitItem->entry_id = $entry->id;
            $debitItem->ledger_id = $request->debit_account;
            $debitItem->amount = $drTotal;
            $debitItem->dc = 'D';
            $debitItem->save();
            
            // Create discount entry if applicable
            if ($request->has('discount_amount') && $request->discount_amount > 0) {
                $discountItem = new EntryItem();
                $discountItem->entry_id = $entry->id;
                $discountItem->ledger_id = $request->discount_ledger;
                $discountItem->amount = $request->discount_amount;
                $discountItem->dc = 'D';
                $discountItem->is_discount = 1;
                $discountItem->save();
            }
            
            // Create credit entries
            foreach ($request->items as $item) {
                $creditItem = new EntryItem();
                $creditItem->entry_id = $entry->id;
                $creditItem->ledger_id = $item['ledger_id'];
                $creditItem->amount = $item['amount'];
                $creditItem->dc = 'C';
                $creditItem->details = $item['details'] ?? '';
                $creditItem->save();
            }
            
            DB::commit();
            
            return redirect()->route('accounts.receipt.list')
                ->with('success', 'Receipt created successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error creating receipt: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Store Payment
     */
    public function storePayment(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'credit_account' => 'required|exists:ledgers,id',
            'fund_id' => 'required|exists:funds,id',
            'payment_mode' => 'required|in:CASH,CHEQUE,ONLINE',
            'entry_code' => 'required|unique:entries,entry_code',
            'paid_to' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.ledger_id' => 'required|exists:ledgers,id',
            'items.*.amount' => 'required|numeric|min:0.01'
        ]);
        
        DB::beginTransaction();
        
        try {
            // Calculate totals
            $totalAmount = array_sum(array_column($request->items, 'amount'));
            $lastEntry = Entry::where('entrytype_id', 2)
				->whereMonth('date', date('m'))
				->whereYear('date', date('Y'))
				->orderBy('id', 'desc')
				->first();
			$lastEntryNumber = 0;
			if(!empty($lastEntry)) $lastEntryNumber = (int) substr($lastEntry->entry_code, -5);
			$entryCode = $this->generateEntryCode('PAY', $lastEntryNumber);
            // Create entry
            $entry = new Entry();
            $entry->entrytype_id = 2; // Payment
            $entry->number = $entryCode;
            $entry->entry_code = $entryCode;
            $entry->date = $request->date;
            $entry->dr_total = $totalAmount;
            $entry->cr_total = $totalAmount;
            $entry->narration = $request->narration;
            $entry->fund_id = $request->fund_id;
            $entry->payment = $request->payment_mode;
            $entry->paid_to = $request->paid_to;
            $entry->created_by = Auth::id();
            
            if ($request->payment_mode == 'CHEQUE') {
                $entry->cheque_no = !empty($request->cheque_no) ? $request->cheque_no : NULL;
                $entry->cheque_date = !empty($request->cheque_date) ? $request->cheque_date : NULL;
            }elseif ($request->payment_mode == 'ONLINE') {
                $entry->cheque_no = !empty($request->transaction_no) ? $request->transaction_no : NULL;
                $entry->cheque_date = !empty($request->transaction_date) ? $request->transaction_date : NULL;
            }
            
            $entry->save();
            $drTotal = $totalAmount;
			if ($request->has('discount_amount') && $request->discount_amount > 0) $drTotal = $totalAmount - $request->discount_amount;
            // Create credit entry (Bank/Cash)
            $creditItem = new EntryItem();
            $creditItem->entry_id = $entry->id;
            $creditItem->ledger_id = $request->credit_account;
            $creditItem->amount = $drTotal;
            $creditItem->dc = 'C';
            $creditItem->save();
			
			 // Create discount entry if applicable
            if ($request->has('discount_amount') && $request->discount_amount > 0) {
                $discountItem = new EntryItem();
                $discountItem->entry_id = $entry->id;
                $discountItem->ledger_id = $request->discount_ledger;
                $discountItem->amount = $request->discount_amount;
                $discountItem->dc = 'C';
                $discountItem->is_discount = 1;
                $discountItem->save();
            }
            
            // Create debit entries
            foreach ($request->items as $item) {
                $debitItem = new EntryItem();
                $debitItem->entry_id = $entry->id;
                $debitItem->ledger_id = $item['ledger_id'];
                $debitItem->amount = $item['amount'];
                $debitItem->dc = 'D';
                $debitItem->details = $item['details'] ?? '';
                $debitItem->save();
            }
            
            DB::commit();
            
            return redirect()->route('accounts.payment.list')
                ->with('success', 'Payment created successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error creating payment: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Store Journal
     */
    public function storeJournal(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'fund_id' => 'required|exists:funds,id',
            'entry_code' => 'required|unique:entries,entry_code',
            'journal_items' => 'required|array|min:2',
            'journal_items.*.ledger_id' => 'required|exists:ledgers,id',
            'journal_items.*.dr_amount' => 'nullable|numeric|min:0',
            'journal_items.*.cr_amount' => 'nullable|numeric|min:0'
        ]);
        
        // Calculate totals
        $drTotal = 0;
        $crTotal = 0;
        
        foreach ($request->journal_items as $item) {
            $drTotal += $item['dr_amount'] ?? 0;
            $crTotal += $item['cr_amount'] ?? 0;
        }
        
        // Validate balanced entry
        if (abs($drTotal - $crTotal) > 0.01) {
            return back()->with('error', 'Journal entry must be balanced. Debit and Credit totals must be equal.')
                ->withInput();
        }
        
        DB::beginTransaction();
        
        try {
			$lastEntry = Entry::where('entrytype_id', 4)
				->whereMonth('date', date('m'))
				->whereYear('date', date('Y'))
				->orderBy('id', 'desc')
				->first();
			$lastEntryNumber = 0;
			if(!empty($lastEntry)) $lastEntryNumber = (int) substr($lastEntry->entry_code, -5);
			$entryCode = $this->generateEntryCode('JOR', $lastEntryNumber);
            // Create entry
            $entry = new Entry();
            $entry->entrytype_id = 4; // Journal
            $entry->number = $entryCode;
            $entry->entry_code = $entryCode;
            $entry->date = $request->date;
            $entry->dr_total = $drTotal;
            $entry->cr_total = $crTotal;
            $entry->narration = $request->narration;
            $entry->fund_id = $request->fund_id;
            $entry->created_by = Auth::id();
            $entry->save();
            
            // Create entry items
            foreach ($request->journal_items as $item) {
                if (($item['dr_amount'] ?? 0) > 0) {
                    $entryItem = new EntryItem();
                    $entryItem->entry_id = $entry->id;
                    $entryItem->ledger_id = $item['ledger_id'];
                    $entryItem->amount = $item['dr_amount'];
                    $entryItem->dc = 'D';
                    $entryItem->save();
                }
                
                if (($item['cr_amount'] ?? 0) > 0) {
                    $entryItem = new EntryItem();
                    $entryItem->entry_id = $entry->id;
                    $entryItem->ledger_id = $item['ledger_id'];
                    $entryItem->amount = $item['cr_amount'];
                    $entryItem->dc = 'C';
                    $entryItem->save();
                }
            }
            
            DB::commit();
            
            return redirect()->route('accounts.journal.list')
                ->with('success', 'Journal entry created successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error creating journal: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Edit Receipt
     */
    public function editReceipt($id)
    {
        $entry = Entry::with('entryItems')->findOrFail($id);
        
        if ($entry->entrytype_id != 1) {
            abort(404);
        }
        
        $bankLedgers = Ledger::where('type', 1)->orderBy('name')->get();
        $creditLedgers = Ledger::orderBy('left_code', 'asc')->orderBy('right_code', 'asc')->get();
        $funds = Fund::orderBy('name')->get();
        
        // Get debit account (bank)
        $debitAccount = $entry->entryItems->where('dc', 'D')->where('is_discount', 0)->first();
        $discountItem = $entry->entryItems->where('dc', 'D')->where('is_discount', 1)->first();
        $creditItems = $entry->entryItems->where('dc', 'C');
        return view('accounts.entries.edit_receipt', compact('entry', 'bankLedgers', 'creditLedgers', 'funds', 
            'debitAccount', 'discountItem', 'creditItems'));
    }
    
    /**
     * Update Receipt
     */
    public function updateReceipt(Request $request, $id)
    {
        $entry = Entry::findOrFail($id);
        
        $request->validate([
            'date' => 'required|date',
            'debit_account' => 'required|exists:ledgers,id',
            'fund_id' => 'required|exists:funds,id',
            'payment_mode' => 'required|in:CASH,CHEQUE,ONLINE',
            'entry_code' => 'required|unique:entries,entry_code,' . $id,
            'received_from' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.ledger_id' => 'required|exists:ledgers,id',
            'items.*.amount' => 'required|numeric|min:0.01'
        ]);
        
        DB::beginTransaction();
        
        try {
			// Calculate totals
            $totalAmount = array_sum(array_column($request->items, 'amount'));
            // Update entry
            // $entry->number = $request->entry_code;
            // $entry->entry_code = $request->entry_code;
            $entry->date = $request->date;
            $entry->dr_total = $totalAmount;
            $entry->cr_total = $totalAmount;
            $entry->narration = $request->narration;
            $entry->fund_id = $request->fund_id;
            $entry->payment = $request->payment_mode;
            $entry->paid_to = $request->received_from;
            
            if ($request->payment_mode == 'CHEQUE') {
                $entry->cheque_no = $request->cheque_no;
                $entry->cheque_date = $request->cheque_date;
            } elseif ($request->payment_mode == 'ONLINE') {
                $entry->cheque_no = !empty($request->transaction_no) ? $request->transaction_no : NULL;
                $entry->cheque_date = !empty($request->transaction_date) ? $request->transaction_date : NULL;
            } else {
                $entry->cheque_no = null;
                $entry->cheque_date = null;
            }
            
            $entry->save();
            
            // Delete existing entry items
            EntryItem::where('entry_id', $entry->id)->delete();
			
			$drTotal = $totalAmount;
			if ($request->has('discount_amount') && $request->discount_amount > 0) $drTotal = $totalAmount - $request->discount_amount;
            
            // Create new debit entry (Bank/Cash)
            $debitItem = new EntryItem();
            $debitItem->entry_id = $entry->id;
            $debitItem->ledger_id = $request->debit_account;
            $debitItem->amount = $drTotal;
            $debitItem->dc = 'D';
            $debitItem->save();
            
            // Create discount entry if applicable
            if ($request->has('discount_amount') && $request->discount_amount > 0) {
                $discountItem = new EntryItem();
                $discountItem->entry_id = $entry->id;
                $discountItem->ledger_id = $request->discount_ledger;
                $discountItem->amount = $request->discount_amount;
                $discountItem->dc = 'D';
                $discountItem->is_discount = 1;
                $discountItem->save();
            }
            
            // Create credit entries
            foreach ($request->items as $item) {
                $creditItem = new EntryItem();
                $creditItem->entry_id = $entry->id;
                $creditItem->ledger_id = $item['ledger_id'];
                $creditItem->amount = $item['amount'];
                $creditItem->dc = 'C';
                $creditItem->details = $item['details'] ?? '';
                $creditItem->save();
            }
            
            DB::commit();
            
            return redirect()->route('accounts.receipt.list')
                ->with('success', 'Receipt updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error updating receipt: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * View Receipt
     */
    public function viewReceipt($id)
    {
        $entry = Entry::with(['entryItems.ledger', 'fund', 'creator'])->findOrFail($id);
        
        if ($entry->entrytype_id != 1) {
            abort(404);
        }
        
        $debitAccount = $entry->entryItems->where('dc', 'D')->where('is_discount', 0)->first();
        $discountItem = $entry->entryItems->where('dc', 'D')->where('is_discount', 1)->first();
        $creditItems = $entry->entryItems->where('dc', 'C');
        
        return view('accounts.entries.view_receipt', compact('entry', 'debitAccount', 'discountItem', 'creditItems'));
    }
    
    /**
     * Copy Receipt
     */
    public function copyReceipt($id)
    {
        $sourceEntry = Entry::with('entryItems')->findOrFail($id);
        
        if ($sourceEntry->entrytype_id != 1) {
            abort(404);
        }
        
        $bankLedgers = Ledger::where('type', 1)->orderBy('name')->get();
        $creditLedgers = Ledger::orderBy('left_code', 'asc')->orderBy('right_code', 'asc')->get();
        $funds = Fund::orderBy('name')->get();
        
        // Generate new entry code
        $lastEntry = Entry::where('entrytype_id', 1)
			->whereMonth('date', date('m'))
			->whereYear('date', date('Y'))
			->orderBy('id', 'desc')
			->first();
		$lastEntryNumber = 0;
		if(!empty($lastEntry)) $lastEntryNumber = (int) substr($lastEntry->entry_code, -5);
		$entryCode = $this->generateEntryCode('REC', $lastEntryNumber);
        
        // Get source data
        $debitAccount = $sourceEntry->entryItems->where('dc', 'D')->where('is_discount', 0)->first();
        $discountItem = $sourceEntry->entryItems->where('dc', 'D')->where('is_discount', 1)->first();
        $creditItems = $sourceEntry->entryItems->where('dc', 'C');
        
        return view('accounts.entries.copy_receipt', compact('sourceEntry', 'bankLedgers', 'creditLedgers', 'funds', 
            'entryCode', 'debitAccount', 'discountItem', 'creditItems'));
    }
    
    /**
     * Edit Payment
     */
    public function editPayment($id)
    {
        $entry = Entry::with('entryItems')->findOrFail($id);
        
        if ($entry->entrytype_id != 2) {
            abort(404);
        }
        
        $bankLedgers = Ledger::where('type', 1)->orderBy('name')->get();
        $debitLedgers = Ledger::orderBy('left_code', 'asc')->orderBy('right_code', 'asc')->get();
        $funds = Fund::orderBy('name')->get();
        
        // Get credit account (bank)
        $creditAccount = $entry->entryItems->where('dc', 'C')->first();
		$discountItem = $entry->entryItems->where('dc', 'C')->where('is_discount', 1)->first();
        $debitItems = $entry->entryItems->where('dc', 'D');
        
        return view('accounts.entries.edit_payment', compact('entry', 'bankLedgers', 'debitLedgers', 'funds', 
            'creditAccount', 'discountItem', 'debitItems'));
    }
    
    /**
     * Update Payment
     */
    public function updatePayment(Request $request, $id)
    {
        $entry = Entry::findOrFail($id);
        
        $request->validate([
            'date' => 'required|date',
            'credit_account' => 'required|exists:ledgers,id',
            'fund_id' => 'required|exists:funds,id',
            'payment_mode' => 'required|in:CASH,CHEQUE,ONLINE',
            'entry_code' => 'required|unique:entries,entry_code,' . $id,
            'paid_to' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.ledger_id' => 'required|exists:ledgers,id',
            'items.*.amount' => 'required|numeric|min:0.01'
        ]);
        
        DB::beginTransaction();
        
        try {
            // Calculate totals
            $totalAmount = array_sum(array_column($request->items, 'amount'));
            
            // Update entry
            // $entry->number = $request->entry_code;
            // $entry->entry_code = $request->entry_code;
            $entry->date = $request->date;
            $entry->dr_total = $totalAmount;
            $entry->cr_total = $totalAmount;
            $entry->narration = $request->narration;
            $entry->fund_id = $request->fund_id;
            $entry->payment = $request->payment_mode;
            $entry->paid_to = $request->paid_to;
            
            if ($request->payment_mode == 'CHEQUE') {
                $entry->cheque_no = $request->cheque_no;
                $entry->cheque_date = $request->cheque_date;
            } elseif ($request->payment_mode == 'ONLINE') {
                $entry->cheque_no = !empty($request->transaction_no) ? $request->transaction_no : NULL;
                $entry->cheque_date = !empty($request->transaction_date) ? $request->transaction_date : NULL;
            } else {
                $entry->cheque_no = null;
                $entry->cheque_date = null;
            }
            
            $entry->save();
            
            // Delete existing entry items
            EntryItem::where('entry_id', $entry->id)->delete();
			
			$drTotal = $totalAmount;
			if ($request->has('discount_amount') && $request->discount_amount > 0) $drTotal = $totalAmount - $request->discount_amount;
            
            // Create new credit entry (Bank/Cash)
            $creditItem = new EntryItem();
            $creditItem->entry_id = $entry->id;
            $creditItem->ledger_id = $request->credit_account;
            $creditItem->amount = $drTotal;
            $creditItem->dc = 'C';
            $creditItem->save();
			
			 // Create discount entry if applicable
            if ($request->has('discount_amount') && $request->discount_amount > 0) {
                $discountItem = new EntryItem();
                $discountItem->entry_id = $entry->id;
                $discountItem->ledger_id = $request->discount_ledger;
                $discountItem->amount = $request->discount_amount;
                $discountItem->dc = 'C';
                $discountItem->is_discount = 1;
                $discountItem->save();
            }
            
            // Create debit entries
            foreach ($request->items as $item) {
                $debitItem = new EntryItem();
                $debitItem->entry_id = $entry->id;
                $debitItem->ledger_id = $item['ledger_id'];
                $debitItem->amount = $item['amount'];
                $debitItem->dc = 'D';
                $debitItem->details = $item['details'] ?? '';
                $debitItem->save();
            }
            
            DB::commit();
            
            return redirect()->route('accounts.payment.list')
                ->with('success', 'Payment updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error updating payment: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * View Payment
     */
    public function viewPayment($id)
    {
        $entry = Entry::with(['entryItems.ledger', 'fund', 'creator'])->findOrFail($id);
        
        if ($entry->entrytype_id != 2) {
            abort(404);
        }
        
        $creditAccount = $entry->entryItems->where('dc', 'C')->where('is_discount', 0)->first();
		$discountItem = $entry->entryItems->where('dc', 'C')->where('is_discount', 1)->first();
        $debitItems = $entry->entryItems->where('dc', 'D');
        
        return view('accounts.entries.view_payment', compact('entry', 'creditAccount', 'debitItems', 'discountItem'));
    }
    
    /**
     * Copy Payment
     */
    public function copyPayment($id)
    {
        $sourceEntry = Entry::with('entryItems')->findOrFail($id);
        
        if ($sourceEntry->entrytype_id != 2) {
            abort(404);
        }
        
        $bankLedgers = Ledger::where('type', 1)->orderBy('name')->get();
        $debitLedgers = Ledger::orderBy('left_code', 'asc')->orderBy('right_code', 'asc')->get();
        $funds = Fund::orderBy('name')->get();
        
        // Generate new entry code
        $lastEntry = Entry::where('entrytype_id', 2)
            ->whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->orderBy('id', 'desc')
            ->first();
			$lastEntryNumber = 0;
		if(!empty($lastEntry)) $lastEntryNumber = (int) substr($lastEntry->entry_code, -5);
		$entryCode = $this->generateEntryCode('PAY', $lastEntryNumber);
        
        // Get source data
        $creditAccount = $sourceEntry->entryItems->where('dc', 'C')->first();
		$discountItem = $sourceEntry->entryItems->where('dc', 'C')->where('is_discount', 1)->first();
        $debitItems = $sourceEntry->entryItems->where('dc', 'D');
        
        return view('accounts.entries.copy_payment', compact('sourceEntry', 'bankLedgers', 'debitLedgers', 'funds', 
            'entryCode', 'creditAccount', 'discountItem', 'debitItems'));
    }
    
    /**
     * Edit Journal
     */
    public function editJournal($id)
    {
        $entry = Entry::with('entryItems')->findOrFail($id);
        
        if ($entry->entrytype_id != 4) {
            abort(404);
        }
        
        $ledgers = Ledger::where('type', 0)->orderBy('name')->get();
        $funds = Fund::orderBy('name')->get();
        
        return view('accounts.entries.edit_journal', compact('entry', 'ledgers', 'funds'));
    }
    
    /**
     * Update Journal
     */
    public function updateJournal(Request $request, $id)
    {
        $entry = Entry::findOrFail($id);
        
        $request->validate([
            'date' => 'required|date',
            'fund_id' => 'required|exists:funds,id',
            'entry_code' => 'required|unique:entries,entry_code,' . $id,
            'journal_items' => 'required|array|min:2',
            'journal_items.*.ledger_id' => 'required|exists:ledgers,id',
            'journal_items.*.dr_amount' => 'nullable|numeric|min:0',
            'journal_items.*.cr_amount' => 'nullable|numeric|min:0'
        ]);
        
        // Calculate totals
        $drTotal = 0;
        $crTotal = 0;
        
        foreach ($request->journal_items as $item) {
            $drTotal += $item['dr_amount'] ?? 0;
            $crTotal += $item['cr_amount'] ?? 0;
        }
        
        // Validate balanced entry
        if (abs($drTotal - $crTotal) > 0.01) {
            return back()->with('error', 'Journal entry must be balanced. Debit and Credit totals must be equal.')
                ->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            // Update entry
            // $entry->number = $request->entry_code;
            // $entry->entry_code = $request->entry_code;
            $entry->date = $request->date;
            $entry->dr_total = $drTotal;
            $entry->cr_total = $crTotal;
            $entry->narration = $request->narration;
            $entry->fund_id = $request->fund_id;
            $entry->save();
            
            // Delete existing entry items
            EntryItem::where('entry_id', $entry->id)->delete();
            
            // Create new entry items
            foreach ($request->journal_items as $item) {
                if (($item['dr_amount'] ?? 0) > 0) {
                    $entryItem = new EntryItem();
                    $entryItem->entry_id = $entry->id;
                    $entryItem->ledger_id = $item['ledger_id'];
                    $entryItem->amount = $item['dr_amount'];
                    $entryItem->dc = 'D';
                    $entryItem->save();
                }
                
                if (($item['cr_amount'] ?? 0) > 0) {
                    $entryItem = new EntryItem();
                    $entryItem->entry_id = $entry->id;
                    $entryItem->ledger_id = $item['ledger_id'];
                    $entryItem->amount = $item['cr_amount'];
                    $entryItem->dc = 'C';
                    $entryItem->save();
                }
            }
            
            DB::commit();
            
            return redirect()->route('accounts.journal.list')
                ->with('success', 'Journal entry updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error updating journal: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * View Journal
     */
    public function viewJournal($id)
    {
        $entry = Entry::with(['entryItems.ledger', 'fund', 'creator'])->findOrFail($id);
        
        if ($entry->entrytype_id != 4) {
            abort(404);
        }
        
        return view('accounts.entries.view_journal', compact('entry'));
    }
    
    /**
     * Copy Journal
     */
    public function copyJournal($id)
    {
        $sourceEntry = Entry::with('entryItems')->findOrFail($id);
        
        if ($sourceEntry->entrytype_id != 4) {
            abort(404);
        }
        
        $ledgers = Ledger::where('type', 0)->orderBy('name')->get();
        $funds = Fund::orderBy('name')->get();
        
        // Generate new entry code
        $lastEntry = Entry::where('entrytype_id', 4)
            ->whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->orderBy('id', 'desc')
            ->first();
			$lastEntryNumber = 0;
		if(!empty($lastEntry)) $lastEntryNumber = (int) substr($lastEntry->entry_code, -5);
		$entryCode = $this->generateEntryCode('JOR', $lastEntryNumber);
        
        return view('accounts.entries.copy_journal', compact('sourceEntry', 'ledgers', 'funds', 'entryCode'));
    }
    
    /**
     * Generate Entry Code
     */
    private function generateEntryCode($prefix, $lastEntryNumber)
    {
        $date = date('ymd');
        $sequence = 1;
        
        $sequence += $lastEntryNumber;
        
        return $prefix . $date . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Convert amount to words
     */
    private function amountToWords($amount)
    {
        // Implementation for converting numbers to words
        // This is a simplified version - you may want to use a library
        return strtoupper(number_format($amount, 2));
    }
	
	/**
	 * Print Receipt
	 */
	public function printReceipt($id)
	{
		$entry = Entry::with(['entryItems.ledger', 'fund', 'creator'])->findOrFail($id);
		
		if ($entry->entrytype_id != 1) {
			abort(404);
		}
		
		$debitAccount = $entry->entryItems->where('dc', 'D')->where('is_discount', 0)->first();
		$discountItem = $entry->entryItems->where('dc', 'D')->where('is_discount', 1)->first();
		$creditItems = $entry->entryItems->where('dc', 'C');

		$dr_total = $entry->dr_total;
        if($discountItem && !empty($discountItem->amount)) $dr_total -= $discountItem->amount;
		// Convert amount to words
		$total_value = $this->amountInWords($dr_total);
		
		return view('accounts.entries.print_receipt', compact('entry', 'debitAccount', 'discountItem', 'creditItems', 'total_value'));
	}

	/**
	 * Print Payment
	 */
	public function printPayment($id)
	{
		$entry = Entry::with(['entryItems.ledger', 'fund', 'creator'])->findOrFail($id);
		
		if ($entry->entrytype_id != 2) {
			abort(404);
		}
		
		$creditAccount = $entry->entryItems->where('dc', 'C')->where('is_discount', 0)->first();
		$discountItem = $entry->entryItems->where('dc', 'C')->where('is_discount', 1)->first();
		$debitItems = $entry->entryItems->where('dc', 'D');
		
		// Convert amount to words
        $dr_total = $entry->dr_total;
		if($discountItem && !empty($discountItem->amount)) $dr_total -= $discountItem->amount;
		$total_value = $this->amountInWords($dr_total);
		
		return view('accounts.entries.print_payment', compact('entry', 'creditAccount', 'discountItem', 'debitItems', 'total_value'));
	}

	/**
	 * Print Journal
	 */
	public function printJournal($id)
	{
		$entry = Entry::with(['entryItems.ledger', 'fund', 'creator'])->findOrFail($id);
		
		if ($entry->entrytype_id != 4) {
			abort(404);
		}
		
		// Convert amount to words
		$total_value = $this->amountInWords($entry->dr_total);
		
		return view('accounts.entries.print_journal', compact('entry', 'total_value'));
	}

	/**
	 * Convert amount to words
	 */
	private function convertAmountToWords($amount)
	{
		if ($amount == 0) return 'ZERO';
        return 'RINGGIT ' . number_format(floor($amount)) . ' AND ' . str_pad(round(($amount - floor($amount)) * 100), 2, '0', STR_PAD_LEFT) . ' SEN';
	}
private function amountInWords($amount)
{
    if ($amount == 0) {
        return 'RINGGIT ZERO ONLY';
    }

    $ones = ['', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE'];
    $teens = ['TEN', 'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN', 'SEVENTEEN', 'EIGHTEEN', 'NINETEEN'];
    $tens = ['', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY'];
    $thousands = ['', 'THOUSAND', 'MILLION', 'BILLION'];

    // Converts numbers less than 1000
    $convertHundreds = function ($num) use ($ones, $teens, $tens) {
        $str = '';
        if ($num >= 100) {
            $str .= $ones[intval($num / 100)] . ' HUNDRED ';
            $num %= 100;
        }

        if ($num >= 10 && $num < 20) {
            $str .= $teens[$num - 10] . ' ';
        } else {
            if ($num >= 20) {
                $str .= $tens[intval($num / 10)] . ' ';
            }
            if ($num % 10 > 0) {
                $str .= $ones[$num % 10] . ' ';
            }
        }

        return trim($str);
    };

    // Converts full integer part
    $convertWhole = function ($num) use ($convertHundreds, $thousands) {
        $words = '';
        $i = 0;

        while ($num > 0) {
            $rem = $num % 1000;
            if ($rem > 0) {
                $words = $convertHundreds($rem) . ' ' . $thousands[$i] . ' ' . $words;
            }
            $num = intval($num / 1000);
            $i++;
        }

        return trim($words);
    };

    $ringgit = floor($amount);
    $sen = round(($amount - $ringgit) * 100);

    $ringgitWords = $ringgit > 0 ? 'RINGGIT ' . $convertWhole($ringgit) : 'RINGGIT ZERO';

    if ($sen > 0) {
        $senWords = $convertHundreds($sen) . ' SEN';
        return $ringgitWords . ' AND ' . $senWords . ' ONLY';
    } else {
        return $ringgitWords . ' ONLY';
    }
}
}