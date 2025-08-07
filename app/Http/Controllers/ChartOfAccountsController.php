<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\AcYear;
use App\Models\EntryItem;
use App\Models\AcYearLedgerBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ChartOfAccountsController extends Controller
{
    /**
     * Apply middleware for permissions
     */
    public function __construct()
    {
        $this->middleware('permission:chart_of_accounts.view')->only(['index', 'getTreeData', 'getSummaryTotals']);
        $this->middleware('permission:chart_of_accounts.create_group')->only(['createGroup', 'storeGroup']);
        $this->middleware('permission:chart_of_accounts.create_ledger')->only(['createLedger', 'storeLedger']);
        $this->middleware('permission:chart_of_accounts.edit_group')->only(['editGroup', 'updateGroup']);
        $this->middleware('permission:chart_of_accounts.edit_ledger')->only(['editLedger', 'updateLedger']);
        $this->middleware('permission:chart_of_accounts.delete_group')->only(['deleteGroup']);
        $this->middleware('permission:chart_of_accounts.delete_ledger')->only(['deleteLedger']);
        $this->middleware('permission:chart_of_accounts.view_ledger_details')->only(['viewLedger', 'getLedgerDetails', 'getGroupDetails']);
    }

    /**
     * Display Chart of Accounts
     */
    public function index()
    {
        $groups = Group::where('parent_id', 0)
            ->with(['children', 'ledgers'])
            ->orderBy('code', 'asc')
            ->get();
            
        $activeYear = AcYear::where('status', 1)
            ->where('user_id', Auth::id())
            ->first();
     
        // Get user permissions for the view
        $user = Auth::user();
        $permissions = [
            'can_create_group' => $user->can('chart_of_accounts.create_group'),
            'can_create_ledger' => $user->can('chart_of_accounts.create_ledger'),
            'can_edit_group' => $user->can('chart_of_accounts.edit_group'),
            'can_edit_ledger' => $user->can('chart_of_accounts.edit_ledger'),
            'can_delete_group' => $user->can('chart_of_accounts.delete_group'),
            'can_delete_ledger' => $user->can('chart_of_accounts.delete_ledger'),
            'can_view_details' => $user->can('chart_of_accounts.view_ledger_details'),
        ];
    
        return view('accounts.chart_of_accounts.index', compact('groups', 'activeYear', 'permissions'));
    }
    
    /**
     * Get groups and ledgers for tree view
     */
    public function getTreeData()
    {
        $groups = Group::with(['children', 'ledgers.openingBalance' => function($query) {
            $query->whereHas('acYear', function($q) {
                $q->where('status', 1)->where('user_id', Auth::id());
            });
        }])->where('parent_id', 0)->get();
        
        return response()->json($this->buildTree($groups));
    }
    
    /**
     * Build tree structure for groups and ledgers
     */
    private function buildTree($groups)
    {
        $tree = [];
        
        foreach ($groups as $group) {
            $node = [
                'id' => 'g_' . $group->id,
                'text' => $group->name . ' (' . $group->code . ')',
                'type' => 'group',
                'data' => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'code' => $group->code,
                    'fixed' => $group->fixed
                ],
                'children' => []
            ];
            
            // Add child groups
            if ($group->children->count() > 0) {
                $node['children'] = array_merge($node['children'], $this->buildTree($group->children));
            }
            
            // Add ledgers
            foreach ($group->ledgers as $ledger) {
                $balance = $ledger->openingBalance->first();
                $ledgerCode = $ledger->left_code && $ledger->right_code ? 
                    ' [' . $ledger->left_code . '/' . $ledger->right_code . ']' : '';
                $node['children'][] = [
                    'id' => 'l_' . $ledger->id,
                    'text' => $ledger->name . $ledgerCode . ($balance ? ' (RM' . number_format($balance->dr_amount - $balance->cr_amount, 2) . ')' : ''),
                    'type' => 'ledger',
                    'icon' => 'fa fa-file-text-o',
                    'data' => [
                        'id' => $ledger->id,
                        'name' => $ledger->name,
                        'group_id' => $ledger->group_id,
                        'type' => $ledger->type,
                        'left_code' => $ledger->left_code,
                        'right_code' => $ledger->right_code,
                        'balance' => $balance
                    ]
                ];
            }
            
            $tree[] = $node;
        }
        
        return $tree;
    }
    
    /**
     * Show form for creating new group
     */
    public function createGroup()
    {
        $parentGroups = $this->getHierarchicalGroups();
        return view('accounts.chart_of_accounts.create_group', compact('parentGroups'));
    }
    
    /**
     * Store new group
     */
    public function storeGroup(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:200',
                'code' => 'required|string|size:4|unique:groups,code',
                'parent_id' => 'required|exists:groups,id' // Parent is required - no top level groups
            ]);
            
            // Validate code is numeric and within range
            if (!is_numeric($request->code) || $request->code < 1000 || $request->code > 9999) {
                return back()->withInput()->with('error', 'Group code must be a 4-digit number between 1000 and 9999');
            }
            
            // Get parent group to validate code range
            $parentGroup = Group::findOrFail($request->parent_id);
            
            // Find the base group (top-level parent)
            $baseGroup = $this->findBaseGroup($parentGroup);
            
            // Validate code is within parent's thousand series
            if (!$this->isCodeInValidRange($request->code, $baseGroup->code)) {
                $rangeStart = $baseGroup->code;
                $rangeEnd = (intval($baseGroup->code) + 999);
                return back()->withInput()->with('error', "Group code must be within {$rangeStart}-{$rangeEnd} range for groups under {$baseGroup->name}");
            }
            
            DB::beginTransaction();
            
            $group = new Group();
            $group->name = $request->name;
            $group->code = $request->code;
            $group->parent_id = $request->parent_id;
            $group->fixed = 0; // User-created groups are not fixed
            $group->added_by = Auth::id();
            $group->save();
            
            DB::commit();
            
            return redirect()->route('chart_of_accounts.index')
                ->with('success', 'Group created successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error creating group: ' . $e->getMessage());
        }
    }
    
    /**
     * Show form for editing group
     */
    public function editGroup($id)
    {
        $group = Group::findOrFail($id);
        
        // Check if group is fixed
        if ($group->fixed == 1) {
            return redirect()->route('chart_of_accounts.index')
                ->with('error', 'System groups cannot be edited!');
        }
        
        $parentGroups = $this->getHierarchicalGroups($group->id);
        return view('accounts.chart_of_accounts.edit_group', compact('group', 'parentGroups'));
    }
    
    /**
     * Update group
     */
    public function updateGroup(Request $request, $id)
    {
        $group = Group::findOrFail($id);
        
        // Check if group is fixed
        if ($group->fixed == 1) {
            return redirect()->route('chart_of_accounts.index')
                ->with('error', 'System groups cannot be edited!');
        }
        
        $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|size:4|unique:groups,code,' . $id,
            'parent_id' => 'required|exists:groups,id' // Parent is required
        ]);
        
        // Validate code is numeric and within range
        if (!is_numeric($request->code) || $request->code < 1000 || $request->code > 9999) {
            return back()->withInput()->with('error', 'Group code must be a 4-digit number between 1000 and 9999');
        }
        
        // Prevent setting itself as parent
        if ($request->parent_id == $id) {
            return back()->with('error', 'A group cannot be its own parent!');
        }
        
        // Check for circular reference
        if ($this->wouldCreateCircularReference($id, $request->parent_id)) {
            return back()->with('error', 'This would create a circular reference!');
        }
        
        // Get parent group to validate code range
        $parentGroup = Group::findOrFail($request->parent_id);
        
        // Find the base group (top-level parent)
        $baseGroup = $this->findBaseGroup($parentGroup);
        
        // Validate code is within parent's thousand series
        if (!$this->isCodeInValidRange($request->code, $baseGroup->code)) {
            $rangeStart = $baseGroup->code;
            $rangeEnd = (intval($baseGroup->code) + 999);
            return back()->withInput()->with('error', "Group code must be within {$rangeStart}-{$rangeEnd} range for groups under {$baseGroup->name}");
        }
        
        DB::beginTransaction();
        
        try {
            $group->name = $request->name;
            $group->code = $request->code;
            $group->parent_id = $request->parent_id;
            $group->save();
            
            DB::commit();
            
            return redirect()->route('chart_of_accounts.index')
                ->with('success', 'Group updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error updating group: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete group
     */
    public function deleteGroup($id)
    {
        $group = Group::findOrFail($id);
        
        // Check if group is fixed
        if ($group->fixed == 1) {
            return response()->json([
                'success' => false,
                'message' => 'System groups cannot be deleted!'
            ]);
        }
        
        // Check if group has ledgers
        $hasLedgers = Ledger::where('group_id', $id)->exists();
        if ($hasLedgers) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete group with existing ledgers!'
            ]);
        }
        
        // Check if group has child groups
        $hasChildren = Group::where('parent_id', $id)->exists();
        if ($hasChildren) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete group with sub-groups!'
            ]);
        }
        
        DB::beginTransaction();
        
        try {
            $group->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Group deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting group: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get group details
     */
    public function getGroupDetails($id)
    {
        $group = Group::with(['parent', 'children', 'ledgers'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'group' => $group
        ]);
    }
    
    /**
     * Check if setting a parent would create circular reference
     */
    private function wouldCreateCircularReference($groupId, $parentId)
    {
        $parent = Group::find($parentId);
        while ($parent) {
            if ($parent->id == $groupId) {
                return true;
            }
            $parent = $parent->parent;
        }
        return false;
    }
    
    /**
     * Show form for creating new ledger
     */
    public function createLedger()
    {
        $groups = $this->getHierarchicalGroups();
        $activeYear = AcYear::where('status', 1)->where('user_id', Auth::id())->first();
        
        return view('accounts.chart_of_accounts.create_ledger', compact('groups', 'activeYear'));
    }
    
    /**
     * Get groups in hierarchical structure for dropdown
     */
    private function getHierarchicalGroups($excludeId = null, $parentId = 0, $prefix = '')
    {
        $query = Group::where('parent_id', $parentId)->orderBy('code', 'asc');
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $groups = $query->get();
        $result = [];
        
        foreach ($groups as $group) {
            // Skip if this is a descendant of excludeId
            if ($excludeId && $this->isDescendantOf($group->id, $excludeId)) {
                continue;
            }
            
            $group->display_name = $prefix . $group->name . ' (' . $group->code . ')';
            $result[] = $group;
            
            // Get children with increased prefix
            $children = $this->getHierarchicalGroups($excludeId, $group->id, $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;');
            $result = array_merge($result, $children);
        }
        
        return $result;
    }
    
    /**
     * Find the base (top-level) group for a given group
     */
    private function findBaseGroup($group)
    {
        while ($group->parent_id != 0) {
            $group = $group->parent;
        }
        return $group;
    }
    
    /**
     * Check if code is within valid range for the base group
     */
    private function isCodeInValidRange($code, $baseCode)
    {
        $codeInt = intval($code);
        $baseInt = intval($baseCode);
        
        // Code must be within the thousand series of the base code
        // For base 1000: valid range is 1001-1999
        // For base 2000: valid range is 2001-2999, etc.
        $rangeStart = $baseInt;
        $rangeEnd = $baseInt + 999;
        
        // The sub-group code cannot be the same as base code
        if ($codeInt == $baseInt) {
            return false;
        }
        
        return $codeInt >= $rangeStart && $codeInt <= $rangeEnd;
    }
    
    /**
     * Check if a group is descendant of another
     */
    private function isDescendantOf($groupId, $ancestorId)
    {
        $group = Group::find($groupId);
        while ($group && $group->parent_id != 0) {
            if ($group->parent_id == $ancestorId) {
                return true;
            }
            $group = $group->parent;
        }
        return false;
    }
    
    /**
     * Store new ledger
     */
    public function storeLedger(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:300',
                'group_id' => 'required|exists:groups,id',
                'right_code' => 'required|string|max:4',
                'opening_balance' => 'nullable|numeric|min:0',
                'balance_type' => 'required_with:opening_balance|in:dr,cr'
            ]);
            
            // Get group code for left_code
            $group = Group::findOrFail($request->group_id);
            $leftCode = $group->code;
            
            // Format right_code with leading zeros
            $rightCode = str_pad($request->right_code, 4, '0', STR_PAD_LEFT);
            
            // Check for duplicate combination
            $exists = Ledger::where('left_code', $leftCode)
                ->where('right_code', $rightCode)
                ->exists();
                
            if ($exists) {
                return back()->withInput()->with('error', 'Ledger Code duplicate not allowed');
            }
            
            DB::beginTransaction();
            
            // Create ledger
            $ledger = new Ledger();
            $ledger->name = $request->name;
            $ledger->group_id = $request->group_id;
            $ledger->left_code = $leftCode;
            $ledger->right_code = $rightCode;
            $ledger->type = $request->has('is_bank') ? 1 : 0;
            $ledger->reconciliation = $request->has('reconciliation') ? 1 : 0;
            $ledger->pa = $request->has('pa') ? 1 : 0;
            $ledger->hb = $request->has('hb') ? 1 : 0;
            $ledger->aging = $request->has('aging') ? 1 : 0;
            $ledger->credit_aging = $request->has('credit_aging') ? 1 : 0;
            $ledger->iv = $request->has('iv') ? 1 : 0;
            $ledger->notes = $request->notes;
            $ledger->save();
            
            // Add opening balance if provided
            if ($request->opening_balance > 0) {
                $activeYear = AcYear::where('status', 1)->where('user_id', Auth::id())->first();
                
                if ($activeYear) {
                    $balance = new AcYearLedgerBalance();
                    $balance->ac_year_id = $activeYear->id;
                    $balance->ledger_id = $ledger->id;
                    
                    if ($request->balance_type == 'dr') {
                        $balance->dr_amount = $request->opening_balance;
                        $balance->cr_amount = 0;
                    } else {
                        $balance->dr_amount = 0;
                        $balance->cr_amount = $request->opening_balance;
                    }
                    
                    // For inventory ledgers
                    if ($request->has('iv') && $request->quantity) {
                        $balance->quantity = $request->quantity;
                        $balance->unit_price = $request->unit_price ?? 0;
                        $balance->uom_id = $request->uom_id;
                    }
                    
                    $balance->save();
                }
            }
            
            DB::commit();
            

            return redirect()->route('chart_of_accounts.index')
                ->with('success', 'Ledger created successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error creating ledger: ' . $e->getMessage());
        }
    }
    
    /**
     * Show form for editing ledger
     */
    public function editLedger($id)
    {
        $ledger = Ledger::findOrFail($id);
        $groups = $this->getHierarchicalGroups();
        $activeYear = AcYear::where('status', 1)->where('user_id', Auth::id())->first();
        
        $openingBalance = null;
        if ($activeYear) {
            $openingBalance = AcYearLedgerBalance::where('ac_year_id', $activeYear->id)
                ->where('ledger_id', $id)
                ->first();
        }
        
        return view('accounts.chart_of_accounts.edit_ledger', 
            compact('ledger', 'groups', 'activeYear', 'openingBalance'));
    }
    
    /**
     * Update ledger
     */
    public function updateLedger(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:300',
            'group_id' => 'required|exists:groups,id',
            'right_code' => 'required|string|max:4',
            'opening_balance' => 'nullable|numeric|min:0',
            'balance_type' => 'required_with:opening_balance|in:dr,cr'
        ]);
        
        $ledger = Ledger::findOrFail($id);
        
        // Get group code for left_code
        $group = Group::findOrFail($request->group_id);
        $leftCode = $group->code;
        
        // Format right_code with leading zeros
        $rightCode = str_pad($request->right_code, 4, '0', STR_PAD_LEFT);
        
        // Check for duplicate combination (excluding current ledger)
        $exists = Ledger::where('left_code', $leftCode)
            ->where('right_code', $rightCode)
            ->where('id', '!=', $id)
            ->exists();
            
        if ($exists) {
            return back()->withInput()->with('error', 'Ledger Code duplicate not allowed');
        }
        
        DB::beginTransaction();
        
        try {
            // Update ledger
            $ledger->name = $request->name;
            $ledger->group_id = $request->group_id;
            $ledger->left_code = $leftCode;
            $ledger->right_code = $rightCode;
            $ledger->type = $request->has('is_bank') ? 1 : 0;
            $ledger->reconciliation = $request->has('reconciliation') ? 1 : 0;
            $ledger->pa = $request->has('pa') ? 1 : 0;
            $ledger->hb = $request->has('hb') ? 1 : 0;
            $ledger->aging = $request->has('aging') ? 1 : 0;
            $ledger->credit_aging = $request->has('credit_aging') ? 1 : 0;
            $ledger->iv = $request->has('iv') ? 1 : 0;
            $ledger->notes = $request->notes;
            $ledger->save();
            
            // Update opening balance
            $activeYear = AcYear::where('status', 1)->where('user_id', Auth::id())->first();
            
            if ($activeYear) {
                $balance = AcYearLedgerBalance::where('ac_year_id', $activeYear->id)
                    ->where('ledger_id', $id)
                    ->first();
                    
                if ($request->opening_balance > 0) {
                    if (!$balance) {
                        $balance = new AcYearLedgerBalance();
                        $balance->ac_year_id = $activeYear->id;
                        $balance->ledger_id = $ledger->id;
                    }
                    
                    if ($request->balance_type == 'dr') {
                        $balance->dr_amount = $request->opening_balance;
                        $balance->cr_amount = 0;
                    } else {
                        $balance->dr_amount = 0;
                        $balance->cr_amount = $request->opening_balance;
                    }
                    
                    // For inventory ledgers
                    if ($request->has('iv') && $request->quantity) {
                        $balance->quantity = $request->quantity;
                        $balance->unit_price = $request->unit_price ?? 0;
                        $balance->uom_id = $request->uom_id;
                    }
                    
                    $balance->save();
                } elseif ($balance) {
                    // Remove opening balance if set to 0
                    $balance->delete();
                }
            }
            
            DB::commit();
            
            return redirect()->route('chart_of_accounts.index')
                ->with('success', 'Ledger updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error updating ledger: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete ledger
     */
    public function deleteLedger($id)
    {
        $ledger = Ledger::findOrFail($id);
        
        // Check if ledger has transactions
        $hasTransactions = DB::table('entryitems')->where('ledger_id', $id)->exists();
        
        if ($hasTransactions) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete ledger with existing transactions!'
            ]);
        }
        
        DB::beginTransaction();
        
        try {
            // Delete opening balances
            AcYearLedgerBalance::where('ledger_id', $id)->delete();
            
            // Delete ledger
            $ledger->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Ledger deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting ledger: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get ledger details
     */
    public function getLedgerDetails($id)
    {
        $ledger = Ledger::with(['group', 'openingBalance' => function($query) {
            $query->whereHas('acYear', function($q) {
                $q->where('status', 1)->where('user_id', Auth::id());
            });
        }])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'ledger' => $ledger
        ]);
    }
 public function getSummaryTotals()
{
    try {
        $activeYear = AcYear::where('status', 1)
            ->where('user_id', Auth::id())
            ->first();
            
        if (!$activeYear) {
            return response()->json([
                'success' => false,
                'message' => 'No active accounting year found'
            ]);
        }
        
        $asOnDate = date('Y-m-d'); // Current date
        
        // Initialize totals
        $totals = [
            'assets' => 0,
            'liabilities' => 0,
            'income' => 0,
            'expenses' => 0
        ];
        
        // Get all base groups (parent_id = 0)
        $baseGroups = Group::where('parent_id', 0)->get();
        
        foreach ($baseGroups as $baseGroup) {
            $groupCode = intval($baseGroup->code);
            
            // Get all ledger IDs under this base group (including sub-groups)
            $ledgerIds = $this->getAllLedgerIdsUnderGroup($baseGroup->id);
            
            if (empty($ledgerIds)) {
                continue;
            }
            
            // Calculate current balance for these ledgers
            $currentBalance = $this->calculateCurrentBalanceForSummary($ledgerIds, $activeYear->id, $asOnDate);
            
            // Categorize based on group code ranges
            if ($groupCode >= 1000 && $groupCode <= 1999) {
                // Assets (1000-1999) - Debit balance is positive
                $totals['assets'] += $currentBalance;
            } elseif ($groupCode >= 2000 && $groupCode <= 2999) {
                // Liabilities (2000-2999) - Credit balance shows as positive
                $totals['liabilities'] += abs($currentBalance);
            } elseif ($groupCode >= 3000 && $groupCode <= 3999) {
                // Equity (3000-3999) - Credit balance shows as positive
                // Skip P&L accumulation ledgers (pa=1) as they're calculated separately
                $nonPABalance = $this->calculateNonPABalance($ledgerIds, $activeYear->id, $asOnDate);
                $totals['liabilities'] += abs($nonPABalance); // Group with liabilities for display
            } elseif ($groupCode >= 4000 && $groupCode <= 4999 || $groupCode >= 8000 && $groupCode <= 8999) {
                // Income/Revenue (4000-4999) + Other Income (8000-8999)
                // Credit balance is positive for income
                $totals['income'] += abs($currentBalance);
            } elseif ($groupCode >= 5000 && $groupCode <= 6999 || $groupCode >= 9000 && $groupCode <= 9999) {
                // Direct Cost (5000) + Expenses (6000) + Taxation (9000)
                // Debit balance is positive for expenses
                $totals['expenses'] += abs($currentBalance);
            }
        }
        
        // Calculate Current Year P&L from P&L accounts and add to equity
        $currentYearPL = $this->calculateCurrentYearPL($activeYear, $asOnDate);
        
        // Add current year P&L to liabilities (which includes equity for display)
        if ($currentYearPL != 0) {
            $totals['liabilities'] += abs($currentYearPL);
        }
        
        return response()->json([
            'success' => true,
            'totals' => [
                'assets' => number_format($totals['assets'], 2),
                'liabilities' => number_format($totals['liabilities'], 2),
                'income' => number_format($totals['income'], 2),
                'expenses' => number_format($totals['expenses'], 2)
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error calculating summary totals: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error calculating totals'
        ]);
    }
}

/**
 * CORRECTED: Calculate current balance for summary (handles signs properly)
 */
private function calculateCurrentBalanceForSummary($ledgerIds, $acYearId, $asOnDate)
{
    if (empty($ledgerIds)) {
        return 0;
    }
    
    // Get opening balances
    $openingBalances = DB::table('ac_year_ledger_balance')
        ->where('ac_year_id', $acYearId)
        ->whereIn('ledger_id', $ledgerIds)
        ->selectRaw('SUM(dr_amount - cr_amount) as opening_balance')
        ->first();
        
    $openingBalance = $openingBalances ? $openingBalances->opening_balance : 0;
    
    // Get transaction balances up to the date
    $transactionBalances = DB::table('entryitems')
        ->join('entries', 'entryitems.entry_id', '=', 'entries.id')
        ->whereIn('entryitems.ledger_id', $ledgerIds)
        ->whereRaw('DATE(entries.date) <= ?', [$asOnDate])
        ->selectRaw('SUM(CASE WHEN entryitems.dc = "D" THEN entryitems.amount ELSE -entryitems.amount END) as transaction_balance')
        ->first();
        
    $transactionBalance = $transactionBalances ? $transactionBalances->transaction_balance : 0;
    
    return $openingBalance + $transactionBalance;
}

/**
 * NEW: Calculate balance excluding P&L accumulation ledgers
 */
private function calculateNonPABalance($ledgerIds, $acYearId, $asOnDate)
{
    if (empty($ledgerIds)) {
        return 0;
    }
    
    // Get ledgers that are NOT P&L accumulation (pa != 1)
    $nonPALedgerIds = DB::table('ledgers')
        ->whereIn('id', $ledgerIds)
        ->where('pa', '!=', 1)
        ->pluck('id')
        ->toArray();
    
    if (empty($nonPALedgerIds)) {
        return 0;
    }
    
    return $this->calculateCurrentBalanceForSummary($nonPALedgerIds, $acYearId, $asOnDate);
}

/**
 * NEW: Calculate current year P&L from income and expense accounts
 */
private function calculateCurrentYearPL($activeYear, $asOnDate)
{
    // Get income from Revenue (4000) + Other Income (8000)
    $incomeGroups = Group::where(function($query) {
            $query->where('code', 'LIKE', '4%')
                  ->orWhere('code', 'LIKE', '8%');
        })
        ->where('parent_id', 0) // Only base groups
        ->get();
    
    $totalIncome = 0;
    foreach ($incomeGroups as $group) {
        $ledgerIds = $this->getAllLedgerIdsUnderGroup($group->id);
        if (!empty($ledgerIds)) {
            // For income, credit increases balance (so we want the credit side)
            $balance = $this->calculateIncomeBalance($ledgerIds, $activeYear->id, $asOnDate);
            $totalIncome += $balance;
        }
    }
    
    // Get expenses from Direct Cost (5000) + Expenses (6000) + Taxation (9000)
    $expenseGroups = Group::where(function($query) {
            $query->where('code', 'LIKE', '5%')
                  ->orWhere('code', 'LIKE', '6%')
                  ->orWhere('code', 'LIKE', '9%');
        })
        ->where('parent_id', 0) // Only base groups
        ->get();
    
    $totalExpenses = 0;
    foreach ($expenseGroups as $group) {
        $ledgerIds = $this->getAllLedgerIdsUnderGroup($group->id);
        if (!empty($ledgerIds)) {
            // For expenses, debit increases balance (so we want the debit side)
            $balance = $this->calculateExpenseBalance($ledgerIds, $activeYear->id, $asOnDate);
            $totalExpenses += $balance;
        }
    }
    
    // Profit = Income - Expenses
    return $totalIncome - $totalExpenses;
}

/**
 * NEW: Calculate income balance (credit side positive)
 */
private function calculateIncomeBalance($ledgerIds, $acYearId, $asOnDate)
{
    if (empty($ledgerIds)) {
        return 0;
    }
    
    // Opening balances
    $openingBalances = DB::table('ac_year_ledger_balance')
        ->where('ac_year_id', $acYearId)
        ->whereIn('ledger_id', $ledgerIds)
        ->selectRaw('SUM(cr_amount - dr_amount) as opening_credit_balance')
        ->first();
        
    $openingBalance = $openingBalances ? $openingBalances->opening_credit_balance : 0;
    
    // Transaction balances (credit side positive for income)
    $transactionBalances = DB::table('entryitems')
        ->join('entries', 'entryitems.entry_id', '=', 'entries.id')
        ->whereIn('entryitems.ledger_id', $ledgerIds)
        ->whereRaw('DATE(entries.date) <= ?', [$asOnDate])
        ->selectRaw('SUM(CASE WHEN entryitems.dc = "C" THEN entryitems.amount ELSE -entryitems.amount END) as transaction_credit_balance')
        ->first();
        
    $transactionBalance = $transactionBalances ? $transactionBalances->transaction_credit_balance : 0;
    
    return $openingBalance + $transactionBalance;
}

/**
 * NEW: Calculate expense balance (debit side positive)
 */
private function calculateExpenseBalance($ledgerIds, $acYearId, $asOnDate)
{
    if (empty($ledgerIds)) {
        return 0;
    }
    
    // Opening balances (debit side positive for expenses)
    $openingBalances = DB::table('ac_year_ledger_balance')
        ->where('ac_year_id', $acYearId)
        ->whereIn('ledger_id', $ledgerIds)
        ->selectRaw('SUM(dr_amount - cr_amount) as opening_debit_balance')
        ->first();
        
    $openingBalance = $openingBalances ? $openingBalances->opening_debit_balance : 0;
    
    // Transaction balances (debit side positive for expenses)
    $transactionBalances = DB::table('entryitems')
        ->join('entries', 'entryitems.entry_id', '=', 'entries.id')
        ->whereIn('entryitems.ledger_id', $ledgerIds)
        ->whereRaw('DATE(entries.date) <= ?', [$asOnDate])
        ->selectRaw('SUM(CASE WHEN entryitems.dc = "D" THEN entryitems.amount ELSE -entryitems.amount END) as transaction_debit_balance')
        ->first();
        
    $transactionBalance = $transactionBalances ? $transactionBalances->transaction_debit_balance : 0;
    
    return $openingBalance + $transactionBalance;
}

/**
 * EXISTING: Get all ledger IDs under a group (including sub-groups recursively)
 * This method should already exist in your controller
 */
private function getAllLedgerIdsUnderGroup($groupId)
{
    $ledgerIds = [];
    
    // Get direct ledgers under this group
    $directLedgers = Ledger::where('group_id', $groupId)->pluck('id')->toArray();
    $ledgerIds = array_merge($ledgerIds, $directLedgers);
    
    // Get sub-groups and their ledgers recursively
    $subGroups = Group::where('parent_id', $groupId)->get();
    foreach ($subGroups as $subGroup) {
        $subLedgerIds = $this->getAllLedgerIdsUnderGroup($subGroup->id);
        $ledgerIds = array_merge($ledgerIds, $subLedgerIds);
    }
    
    return $ledgerIds;
}
public function viewLedger($id)
{
    try {
        // Get the ledger with relationships
        $ledger = Ledger::with(['group', 'openingBalances'])->findOrFail($id);
        
        // Get active accounting year
        $activeYear = AcYear::where('status', 1)->where('user_id', Auth::id())->first();
        
        if (!$activeYear) {
            return redirect()->back()->with('error', 'No active accounting year found.');
        }
        
        // Get opening balance for current year
        $openingBalance = AcYearLedgerBalance::where('ac_year_id', $activeYear->id)
            ->where('ledger_id', $ledger->id)
            ->first();
        
        // Get recent transactions (last 10)
        $recentTransactions = EntryItem::with(['entry:id,date,entry_code,narration,entrytype_id'])
            ->where('ledger_id', $ledger->id)
            ->whereHas('entry', function($query) use ($activeYear) {
                $query->whereBetween('date', [$activeYear->from_year_month, $activeYear->to_year_month]);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('accounts.chart_of_accounts.view_ledger', compact(
            'ledger', 
            'openingBalance', 
            'recentTransactions',
            'activeYear'
        ));
        
    } catch (\Exception $e) {
        Log::error('Error viewing ledger: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error loading ledger details.');
    }
}
}