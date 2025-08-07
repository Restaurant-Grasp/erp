<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entry;
use App\Models\EntryItem;
use App\Models\Ledger;
use App\Models\AcYear;
use App\Models\Group;
use App\Models\AcYearLedgerBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GeneralLedgerExport;
use App\Exports\TrialBalanceExport;
use App\Exports\BalanceSheetExport;

class ReportsController extends Controller
{
    /**
     * Display General Ledger Report
     */
    public function generalLedger(Request $request)
    {
        // Get active accounting year
        $activeYear = AcYear::where('status', 1)->where('user_id', Auth::id())->first();
        
        if (!$activeYear) {
            return redirect()->back()->with('error', 'No active accounting year found.');
        }
        
        // Get all ledgers for dropdown
        $ledgers = Ledger::orderBy('left_code')->orderBy('right_code')->get();
        
        // Set default dates (first and last day of current month)
        $fromDate = $request->from_date ?? date('Y-m-01');
        $toDate = $request->to_date ?? date('Y-m-t');
        
        // Get selected ledgers (now supports multiple)
        $selectedLedgerIds = $request->ledger_ids ?? [];
        if (!is_array($selectedLedgerIds)) {
            $selectedLedgerIds = [$selectedLedgerIds];
        }
        $selectedLedgerIds = array_filter($selectedLedgerIds);
        
        $ledgerReports = [];
        
        if (!empty($selectedLedgerIds)) {
            foreach ($selectedLedgerIds as $ledgerId) {
                $ledger = Ledger::find($ledgerId);
                
                if ($ledger) {
                    // Get opening balance
                    $openingBalance = $this->calculateOpeningBalance($ledgerId, $fromDate, $activeYear);
                    
                    // Build query for transactions
                    $query = EntryItem::with(['entry', 'entry.fund', 'entry.creator'])
                        ->where('ledger_id', $ledgerId)
                        ->whereHas('entry', function($q) use ($fromDate, $toDate, $request) {
                            // Use whereDate for proper date comparison
                            $q->whereRaw('DATE(date) >= ?', [$fromDate])
                              ->whereRaw('DATE(date) <= ?', [$toDate]);
                            
                            // Apply invoice type filter
                            if ($request->filled('invoice_type') && $request->invoice_type !== 'all') {
                                if ($request->invoice_type == 'manual') {
                                    $q->whereNull('inv_type');
                                } else {
                                    $q->where('inv_type', $request->invoice_type);
                                }
                            }
                        })
                        ->join('entries', 'entryitems.entry_id', '=', 'entries.id')
                        ->orderBy('entries.date', 'asc')
                        ->orderBy('entries.id', 'asc')
                        ->select('entryitems.*');
                    
                    $transactions = $query->get();
                    
                    // Calculate running balance
                    $runningBalance = [
                        'debit' => $openingBalance['debit'],
                        'credit' => $openingBalance['credit']
                    ];
                    
                    foreach ($transactions as $transaction) {
                        if ($transaction->dc == 'D') {
                            $runningBalance['debit'] += $transaction->amount;
                        } else {
                            $runningBalance['credit'] += $transaction->amount;
                        }
                        
                        // Calculate net balance for display
                        $netBalance = $runningBalance['debit'] - $runningBalance['credit'];
                        $transaction->running_balance = abs($netBalance);
                        $transaction->balance_type = $netBalance >= 0 ? 'Dr' : 'Cr';
                    }
                    
                    $closingBalance = $runningBalance;
                    
                    $ledgerReports[] = [
                        'ledger' => $ledger,
                        'transactions' => $transactions,
                        'openingBalance' => $openingBalance,
                        'closingBalance' => $closingBalance
                    ];
                }
            }
        }
        
        // Export handling
        if ($request->has('export')) {
            return $this->exportGeneralLedger($request, $ledgerReports, $fromDate, $toDate);
        }
        
        return view('accounts.reports.general_ledger', compact(
            'ledgers', 'selectedLedgerIds', 'ledgerReports', 'fromDate', 'toDate',
            'activeYear'
        ));
    }
    
    /**
     * Calculate opening balance for a ledger
     */
    private function calculateOpeningBalance($ledgerId, $fromDate, $activeYear)
    {
        // Get year opening balance
        $yearOpeningBalance = AcYearLedgerBalance::where('ac_year_id', $activeYear->id)
            ->where('ledger_id', $ledgerId)
            ->first();
        
        $openingDr = $yearOpeningBalance ? $yearOpeningBalance->dr_amount : 0;
        $openingCr = $yearOpeningBalance ? $yearOpeningBalance->cr_amount : 0;
        
        // Add transactions before from_date but within the accounting year
        if ($fromDate > $activeYear->from_year_month) {
            $priorTransactions = DB::table('entryitems')
                ->join('entries', 'entryitems.entry_id', '=', 'entries.id')
                ->where('entryitems.ledger_id', $ledgerId)
                ->whereRaw('DATE(entries.date) >= ?', [$activeYear->from_year_month])
                ->whereRaw('DATE(entries.date) < ?', [$fromDate])
                ->select(
                    DB::raw('SUM(CASE WHEN entryitems.dc = "D" THEN entryitems.amount ELSE 0 END) as total_dr'),
                    DB::raw('SUM(CASE WHEN entryitems.dc = "C" THEN entryitems.amount ELSE 0 END) as total_cr')
                )
                ->first();
            
            $openingDr += $priorTransactions->total_dr ?? 0;
            $openingCr += $priorTransactions->total_cr ?? 0;
        }
        
        return [
            'debit' => $openingDr,
            'credit' => $openingCr
        ];
    }
    
    /**
     * Export General Ledger
     */
    private function exportGeneralLedger($request, $ledgerReports, $fromDate, $toDate)
    {
        $exportType = $request->export;
        $data = [
            'ledgerReports' => $ledgerReports,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'invoiceType' => $request->invoice_type ?? 'all'
        ];
        
        if ($exportType == 'pdf') {
            $pdf = PDF::loadView('accounts.reports.general_ledger_pdf', $data);
            return $pdf->download('general_ledger_' . date('YmdHis') . '.pdf');
        } elseif ($exportType == 'excel') {
            return Excel::download(new GeneralLedgerExport($data), 
                'general_ledger_' . date('YmdHis') . '.xlsx');
        } elseif ($exportType == 'print') {
            return view('accounts.reports.general_ledger_print', $data);
        }
    }
    
    /**
     * Get ledgers for AJAX search
     */
    public function searchLedgers(Request $request)
    {
        $search = $request->search;
        
        $ledgers = Ledger::where(function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('left_code', 'like', '%' . $search . '%')
                    ->orWhere('right_code', 'like', '%' . $search . '%');
            })
            ->orderBy('left_code')
            ->orderBy('right_code')
            ->limit(20)
            ->get();
        
        $results = [];
        foreach ($ledgers as $ledger) {
            $results[] = [
                'id' => $ledger->id,
                'text' => $ledger->left_code . '/' . $ledger->right_code . ' - ' . $ledger->name
            ];
        }
        
        return response()->json(['results' => $results]);
    }
	
	/**
	 * Display Trial Balance Report
	 */
public function trialBalance(Request $request)
{
    // Get active accounting year
    $activeYear = AcYear::where('status', 1)->where('user_id', Auth::id())->first();
    
    if (!$activeYear) {
        return redirect()->back()->with('error', 'No active accounting year found.');
    }
    
    // Set default dates
    $fromDate = $request->from_date ?? $activeYear->from_year_month;
    $toDate = $request->to_date ?? date('Y-m-d');
    
    // Get all parent groups (parent_id = 0)
    $parentGroups = Group::where('parent_id', 0)
        ->with(['children.children', 'ledgers'])
        ->orderBy('code', 'asc')
        ->get();
    
    // Process groups and calculate balances
    $trialBalanceData = [];
    $grandTotalOpeningDebit = 0;
    $grandTotalOpeningCredit = 0;
    $grandTotalClosingDebit = 0;
    $grandTotalClosingCredit = 0;
    
    foreach ($parentGroups as $parentGroup) {
        $groupData = $this->processGroupForTrialBalance($parentGroup, $activeYear, $fromDate, $toDate);
        
        // Always include parent groups (even if they have zero totals)
        // This maintains the trial balance structure
        $trialBalanceData[] = $groupData;
        
        $grandTotalOpeningDebit += $groupData['totalOpeningDebit'];
        $grandTotalOpeningCredit += $groupData['totalOpeningCredit'];
        $grandTotalClosingDebit += $groupData['totalClosingDebit'];
        $grandTotalClosingCredit += $groupData['totalClosingCredit'];
    }
    
    // Check if trial balance is balanced (closing balances should be equal)
    $isBalanced = abs($grandTotalClosingDebit - $grandTotalClosingCredit) < 0.01;
    
    // Export handling
    if ($request->has('export')) {
        return $this->exportTrialBalance($request, $trialBalanceData, $fromDate, $toDate, 
            $grandTotalOpeningDebit, $grandTotalOpeningCredit,
            $grandTotalClosingDebit, $grandTotalClosingCredit, $isBalanced);
    }
    
    return view('accounts.reports.trial_balance', compact(
        'trialBalanceData', 'fromDate', 'toDate', 'activeYear',
        'grandTotalOpeningDebit', 'grandTotalOpeningCredit',
        'grandTotalClosingDebit', 'grandTotalClosingCredit', 'isBalanced'
    ));
}
private function hasTrialBalanceData($groupData)
{
    // Check if group has any ledgers
    if (!empty($groupData['ledgers'])) {
        return true;
    }
    
    // Check if group has any children with data
    if (!empty($groupData['children'])) {
        return true;
    }
    
    // Check if group itself has non-zero totals
    if ($groupData['totalOpeningDebit'] != 0 || 
        $groupData['totalOpeningCredit'] != 0 || 
        $groupData['totalClosingDebit'] != 0 || 
        $groupData['totalClosingCredit'] != 0) {
        return true;
    }
    
    return false;
}
	/**
	 * Process group and its children for trial balance
	 */
private function processGroupForTrialBalance($group, $activeYear, $fromDate, $toDate, $level = 0)
{
    $groupData = [
        'id' => $group->id,
        'code' => $group->code,
        'name' => $group->name,
        'level' => $level,
        'isGroup' => true,
        'totalOpeningDebit' => 0,
        'totalOpeningCredit' => 0,
        'totalClosingDebit' => 0,
        'totalClosingCredit' => 0,
        'children' => [],
        'ledgers' => []
    ];
    
    // Process direct ledgers under this group
    foreach ($group->ledgers as $ledger) {
        $ledgerBalances = $this->calculateLedgerBalances($ledger, $activeYear, $fromDate, $toDate);
        
        // Skip ledgers where all values are zero
        if ($ledgerBalances['openingDebit'] == 0 && 
            $ledgerBalances['openingCredit'] == 0 && 
            $ledgerBalances['closingDebit'] == 0 && 
            $ledgerBalances['closingCredit'] == 0) {
            continue; // Skip this ledger
        }
        
        $groupData['ledgers'][] = [
            'id' => $ledger->id,
            'code' => $ledger->left_code . '/' . $ledger->right_code,
            'name' => $ledger->name,
            'openingDebit' => $ledgerBalances['openingDebit'],
            'openingCredit' => $ledgerBalances['openingCredit'],
            'closingDebit' => $ledgerBalances['closingDebit'],
            'closingCredit' => $ledgerBalances['closingCredit']
        ];
        
        $groupData['totalOpeningDebit'] += $ledgerBalances['openingDebit'];
        $groupData['totalOpeningCredit'] += $ledgerBalances['openingCredit'];
        $groupData['totalClosingDebit'] += $ledgerBalances['closingDebit'];
        $groupData['totalClosingCredit'] += $ledgerBalances['closingCredit'];
    }
    
    // Process child groups recursively
    foreach ($group->children as $childGroup) {
        $childData = $this->processGroupForTrialBalance($childGroup, $activeYear, $fromDate, $toDate, $level + 1);
        
        // Always include child groups (even if zero) to maintain hierarchy
        $groupData['children'][] = $childData;
        
        $groupData['totalOpeningDebit'] += $childData['totalOpeningDebit'];
        $groupData['totalOpeningCredit'] += $childData['totalOpeningCredit'];
        $groupData['totalClosingDebit'] += $childData['totalClosingDebit'];
        $groupData['totalClosingCredit'] += $childData['totalClosingCredit'];
    }
    
    return $groupData;
}
	/**
	 * Calculate ledger opening and closing balances
	 */
	private function calculateLedgerBalances($ledger, $activeYear, $fromDate, $toDate)
	{
		// Get year opening balance
		$yearOpeningBalance = AcYearLedgerBalance::where('ac_year_id', $activeYear->id)
			->where('ledger_id', $ledger->id)
			->first();
		
		$openingDr = $yearOpeningBalance ? $yearOpeningBalance->dr_amount : 0;
		$openingCr = $yearOpeningBalance ? $yearOpeningBalance->cr_amount : 0;
		
		// Calculate opening balance as on from_date
		if ($fromDate > $activeYear->from_year_month) {
			$priorTransactions = DB::table('entryitems')
				->join('entries', 'entryitems.entry_id', '=', 'entries.id')
				->where('entryitems.ledger_id', $ledger->id)
				->whereRaw('DATE(entries.date) >= ?', [$activeYear->from_year_month])
				->whereRaw('DATE(entries.date) < ?', [$fromDate])
				->select(
					DB::raw('SUM(CASE WHEN entryitems.dc = "D" THEN entryitems.amount ELSE 0 END) as total_dr'),
					DB::raw('SUM(CASE WHEN entryitems.dc = "C" THEN entryitems.amount ELSE 0 END) as total_cr')
				)
				->first();
			
			$openingDr += $priorTransactions->total_dr ?? 0;
			$openingCr += $priorTransactions->total_cr ?? 0;
		}
		
		// Calculate transactions within the period
		$periodTransactions = DB::table('entryitems')
			->join('entries', 'entryitems.entry_id', '=', 'entries.id')
			->where('entryitems.ledger_id', $ledger->id)
			->whereRaw('DATE(entries.date) >= ?', [$fromDate])
			->whereRaw('DATE(entries.date) <= ?', [$toDate])
			->select(
				DB::raw('SUM(CASE WHEN entryitems.dc = "D" THEN entryitems.amount ELSE 0 END) as total_dr'),
				DB::raw('SUM(CASE WHEN entryitems.dc = "C" THEN entryitems.amount ELSE 0 END) as total_cr')
			)
			->first();
		
		$periodDr = $periodTransactions->total_dr ?? 0;
		$periodCr = $periodTransactions->total_cr ?? 0;
		
		// Calculate closing balance
		$closingDr = $openingDr + $periodDr;
		$closingCr = $openingCr + $periodCr;
		
		// Net calculation for display
		$openingNet = $openingDr - $openingCr;
		$closingNet = $closingDr - $closingCr;
		
		return [
			'openingDebit' => $openingNet > 0 ? abs($openingNet) : 0,
			'openingCredit' => $openingNet < 0 ? abs($openingNet) : 0,
			'closingDebit' => $closingNet > 0 ? abs($closingNet) : 0,
			'closingCredit' => $closingNet < 0 ? abs($closingNet) : 0
		];
	}

	/**
	 * Export Trial Balance
	 */
	private function exportTrialBalance($request, $trialBalanceData, $fromDate, $toDate, 
		$grandTotalOpeningDebit, $grandTotalOpeningCredit,
		$grandTotalClosingDebit, $grandTotalClosingCredit, $isBalanced)
	{
		$exportType = $request->export;
		$data = [
			'trialBalanceData' => $trialBalanceData,
			'fromDate' => $fromDate,
			'toDate' => $toDate,
			'grandTotalOpeningDebit' => $grandTotalOpeningDebit,
			'grandTotalOpeningCredit' => $grandTotalOpeningCredit,
			'grandTotalClosingDebit' => $grandTotalClosingDebit,
			'grandTotalClosingCredit' => $grandTotalClosingCredit,
			'isBalanced' => $isBalanced
		];
		
		if ($exportType == 'pdf') {
			$pdf = Pdf::loadView('accounts.reports.trial_balance_pdf', $data);
			return $pdf->download('trial_balance_' . date('YmdHis') . '.pdf');
		} elseif ($exportType == 'excel') {
			return Excel::download(new TrialBalanceExport($data), 
				'trial_balance_' . date('YmdHis') . '.xlsx');
		} elseif ($exportType == 'print') {
			return view('accounts.reports.trial_balance_print', $data);
		}
	}
	
	/**
	 * Display Balance Sheet Report
	 */
	public function balanceSheet(Request $request)
	{
		// Get active accounting year
		$activeYear = AcYear::where('status', 1)->where('user_id', Auth::id())->first();
		
		if (!$activeYear) {
			return redirect()->back()->with('error', 'No active accounting year found.');
		}
		
		// Set default date to current date
		$asOnDate = $request->date ?? date('Y-m-d');
		
		// Get balance sheet groups (1000-Assets, 2000-Liabilities, 3000-Equity)
		$balanceSheetGroups = Group::whereIn('code', ['1000', '2000', '3000'])
			->where('parent_id', 0)
			->with(['children.children', 'ledgers'])
			->orderBy('code', 'asc')
			->get();
		
		// Process groups for balance sheet
		$balanceSheetData = [];
		$totalAssets = ['current' => 0, 'previous' => 0];
		$totalLiabilities = ['current' => 0, 'previous' => 0];
		$totalEquity = ['current' => 0, 'previous' => 0];
		
		foreach ($balanceSheetGroups as $group) {
			$groupData = $this->processGroupForBalanceSheet($group, $activeYear, $asOnDate);
			
			// Skip if group has no data to display
			if (!$this->hasDisplayableData($groupData)) {
				continue;
			}
			
			$balanceSheetData[] = $groupData;
			
			// Accumulate totals
			if ($group->code == '1000') { // Assets
				$totalAssets['current'] = $groupData['currentBalance'];
				$totalAssets['previous'] = $groupData['previousBalance'];
			} elseif ($group->code == '2000') { // Liabilities
				$totalLiabilities['current'] = $groupData['currentBalance'];
				$totalLiabilities['previous'] = $groupData['previousBalance'];
			} elseif ($group->code == '3000') { // Equity
				$totalEquity['current'] = $groupData['currentBalance'];
				$totalEquity['previous'] = $groupData['previousBalance'];
			}
		}
		
		// Calculate Current Year Profit & Loss
		$currentProfitLoss = $this->calculateCurrentProfitLoss($activeYear, $asOnDate);
		
		// Add Current P&L to Equity section if not zero
		if ($currentProfitLoss != 0) {
			// Find equity group in balanceSheetData and add P&L
			foreach ($balanceSheetData as &$group) {
				if ($group['code'] == '3000') {
					$group['profitLoss'] = [
						'name' => 'Current Profit & Loss',
						'current' => $currentProfitLoss,
						'previous' => 0
					];
					// Add P&L to group balance (whether positive or negative)
					$group['currentBalance'] -= $currentProfitLoss;
					$totalEquity['current'] -= $currentProfitLoss;
					break;
				}
			}
		}
		
		// Export handling
		if ($request->has('export')) {
			return $this->exportBalanceSheet($request, $balanceSheetData, $asOnDate, 
				$totalAssets, $totalLiabilities, $totalEquity, $activeYear);
		}
		
		return view('accounts.reports.balance_sheet', compact(
			'balanceSheetData', 'asOnDate', 'activeYear',
			'totalAssets', 'totalLiabilities', 'totalEquity'
		));
	}

	/**
	 * Process group for balance sheet with hierarchical structure
	 */
	private function processGroupForBalanceSheet($group, $activeYear, $asOnDate, $level = 0)
	{
		$groupData = [
			'id' => $group->id,
			'code' => $group->code,
			'name' => $group->name,
			'level' => $level,
			'isGroup' => true,
			'currentBalance' => 0,
			'previousBalance' => 0,
			'children' => [],
			'ledgers' => []
		];
		
		// Process direct ledgers
		foreach ($group->ledgers as $ledger) {
			$balances = $this->calculateLedgerBalanceSheet($ledger, $activeYear, $asOnDate);
			
			// Skip if both current closing and current opening are zero
			if ($balances['currentClosing'] == 0 && $balances['currentOpening'] == 0) {
				continue;
			}
			
			$ledgerData = [
				'id' => $ledger->id,
				'code' => $ledger->left_code . '/' . $ledger->right_code,
				'name' => $ledger->name,
				'currentBalance' => $balances['currentClosing'],
				'previousBalance' => $balances['currentOpening'],
				'isPaLedger' => $ledger->pa == 1
			];
			
			$groupData['ledgers'][] = $ledgerData;
			$groupData['currentBalance'] += $balances['currentClosing'];
			$groupData['previousBalance'] += $balances['currentOpening'];
		}
		
		// Process child groups recursively
		foreach ($group->children as $childGroup) {
			$childData = $this->processGroupForBalanceSheet($childGroup, $activeYear, $asOnDate, $level + 1);
			
			// Only add if has displayable data
			if ($this->hasDisplayableData($childData)) {
				$groupData['children'][] = $childData;
				$groupData['currentBalance'] += $childData['currentBalance'];
				$groupData['previousBalance'] += $childData['previousBalance'];
			}
		}
		
		return $groupData;
	}

	/**
	 * Calculate ledger balances for balance sheet
	 */
	private function calculateLedgerBalanceSheet($ledger, $activeYear, $asOnDate)
	{
		// Get year opening balance (this becomes "previous year" column)
		$yearOpeningBalance = AcYearLedgerBalance::where('ac_year_id', $activeYear->id)
			->where('ledger_id', $ledger->id)
			->first();
		
		$openingDr = $yearOpeningBalance ? $yearOpeningBalance->dr_amount : 0;
		$openingCr = $yearOpeningBalance ? $yearOpeningBalance->cr_amount : 0;
		$currentOpening = $openingDr - $openingCr;
		
		// Calculate closing balance as of the given date
		$transactions = DB::table('entryitems')
			->join('entries', 'entryitems.entry_id', '=', 'entries.id')
			->where('entryitems.ledger_id', $ledger->id)
			->whereRaw('DATE(entries.date) >= ?', [$activeYear->from_year_month])
			->whereRaw('DATE(entries.date) <= ?', [$asOnDate])
			->select(
				DB::raw('SUM(CASE WHEN entryitems.dc = "D" THEN entryitems.amount ELSE 0 END) as total_dr'),
				DB::raw('SUM(CASE WHEN entryitems.dc = "C" THEN entryitems.amount ELSE 0 END) as total_cr')
			)
			->first();
		
		$periodDr = $transactions->total_dr ?? 0;
		$periodCr = $transactions->total_cr ?? 0;
		
		$closingDr = $openingDr + $periodDr;
		$closingCr = $openingCr + $periodCr;
		$currentClosing = $closingDr - $closingCr;
		
		return [
			'currentOpening' => $currentOpening,
			'currentClosing' => $currentClosing
		];
	}

	/**
	 * Calculate current year profit & loss
	 */
	private function calculateCurrentProfitLoss($activeYear, $asOnDate)
	{
		// Revenue (4000) + Other Income (8000)
		$income = DB::table('entryitems')
			->join('entries', 'entryitems.entry_id', '=', 'entries.id')
			->join('ledgers', 'entryitems.ledger_id', '=', 'ledgers.id')
			->join('groups', 'ledgers.group_id', '=', 'groups.id')
			->whereIn('groups.code', function($query) {
				$query->select('code')
					->from('groups')
					->where(function($q) {
						$q->where('code', 'LIKE', '4%')
						  ->orWhere('code', 'LIKE', '8%');
					});
			})
			->whereRaw('DATE(entries.date) >= ?', [$activeYear->from_year_month])
			->whereRaw('DATE(entries.date) <= ?', [$asOnDate])
			->select(
				DB::raw('SUM(CASE WHEN entryitems.dc = "C" THEN entryitems.amount ELSE -entryitems.amount END) as total')
			)
			->first();
		
		// Direct Cost (5000) + Expenses (6000) + Taxation (9000)
		$expense = DB::table('entryitems')
			->join('entries', 'entryitems.entry_id', '=', 'entries.id')
			->join('ledgers', 'entryitems.ledger_id', '=', 'ledgers.id')
			->join('groups', 'ledgers.group_id', '=', 'groups.id')
			->whereIn('groups.code', function($query) {
				$query->select('code')
					->from('groups')
					->where(function($q) {
						$q->where('code', 'LIKE', '5%')
						  ->orWhere('code', 'LIKE', '6%')
						  ->orWhere('code', 'LIKE', '9%');
					});
			})
			->whereRaw('DATE(entries.date) >= ?', [$activeYear->from_year_month])
			->whereRaw('DATE(entries.date) <= ?', [$asOnDate])
			->select(
				DB::raw('SUM(CASE WHEN entryitems.dc = "D" THEN entryitems.amount ELSE -entryitems.amount END) as total')
			)
			->first();
		
		$totalIncome = $income->total ?? 0;
		$totalExpense = $expense->total ?? 0;
		
		return $totalIncome - $totalExpense;
	}

	/**
	 * Check if group has any displayable data
	 */
	private function hasDisplayableData($groupData)
	{
		// Check if group itself has non-zero balances
		if ($groupData['currentBalance'] != 0 || $groupData['previousBalance'] != 0) {
			return true;
		}
		
		// Check if any ledger has non-zero balances
		if (!empty($groupData['ledgers'])) {
			return true;
		}
		
		// Check if any child has displayable data
		if (!empty($groupData['children'])) {
			return true;
		}
		
		return false;
	}

	/**
	 * Export Balance Sheet
	 */
	private function exportBalanceSheet($request, $balanceSheetData, $asOnDate, 
		$totalAssets, $totalLiabilities, $totalEquity, $activeYear)
	{
		$exportType = $request->export;
		$data = [
			'balanceSheetData' => $balanceSheetData,
			'asOnDate' => $asOnDate,
			'totalAssets' => $totalAssets,
			'totalLiabilities' => $totalLiabilities,
			'totalEquity' => $totalEquity,
			'activeYear' => $activeYear
		];
		
		if ($exportType == 'pdf') {
			$pdf = Pdf::loadView('accounts.reports.balance_sheet_pdf', $data);
			return $pdf->download('balance_sheet_' . date('YmdHis') . '.pdf');
		} elseif ($exportType == 'excel') {
			return Excel::download(new BalanceSheetExport($data), 
				'balance_sheet_' . date('YmdHis') . '.xlsx');
		} elseif ($exportType == 'print') {
			return view('accounts.reports.balance_sheet_print', $data);
		}
	}
}