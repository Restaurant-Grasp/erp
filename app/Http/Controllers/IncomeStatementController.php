<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entry;
use App\Models\EntryItem;
use App\Models\Ledger;
use App\Models\AcYear;
use App\Models\Group;
use App\Models\Fund;
use App\Models\AcYearLedgerBalance;
use DB;
use Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IncomeStatementExport;

class IncomeStatementController extends Controller
{
    /**
     * Display Income Statement Report
     */
    public function index(Request $request)
    {
        // Get active accounting year
        $activeYear = AcYear::where('status', 1)->where('user_id', Auth::id())->first();
        
        if (!$activeYear) {
            return redirect()->back()->with('error', 'No active accounting year found.');
        }
        
        // Get all funds for dropdown
        $funds = Fund::orderBy('code')->get();
        
        // Set default dates (first and last day of current month)
        $fromDate = $request->from_date ?? date('Y-m-01');
        $toDate = $request->to_date ?? date('Y-m-t');
        
        // Validate from date is within accounting year
        if ($fromDate < $activeYear->from_year_month) {
            $fromDate = $activeYear->from_year_month;
        }
        
        // Get selected fund
        $selectedFundId = $request->fund_id ?? 'all';
        
        // Get display type
        $displayType = $request->display_type ?? 'full';
        
        // Validate monthly view doesn't exceed 12 months
        if ($displayType == 'monthly') {
            $monthsDiff = Carbon::parse($fromDate)->diffInMonths(Carbon::parse($toDate));
            if ($monthsDiff > 11) { // 0-11 = 12 months
                return redirect()->back()->with('error', 'Monthly view cannot exceed 12 months.');
            }
        }
        
        // Get Income Statement Data
        $incomeStatementData = $this->getIncomeStatementData($fromDate, $toDate, $selectedFundId, $displayType);
        
        // Export handling
        if ($request->has('export')) {
            return $this->exportIncomeStatement($request, $incomeStatementData, $fromDate, $toDate, 
                $selectedFundId, $displayType, $activeYear, $funds);
        }
        
        return view('accounts.income-statement.index', compact(
            'funds', 'selectedFundId', 'fromDate', 'toDate', 'displayType',
            'incomeStatementData', 'activeYear'
        ));
    }
    
    /**
     * Get Income Statement Data
     */
    private function getIncomeStatementData($fromDate, $toDate, $fundId, $displayType)
    {
        if ($displayType == 'monthly') {
            return $this->getMonthlyData($fromDate, $toDate, $fundId);
        } else {
            return $this->getFullData($fromDate, $toDate, $fundId);
        }
    }
    
    /**
     * Get Full View Data
     */
    private function getFullData($fromDate, $toDate, $fundId)
    {
        $data = [
            'revenue' => $this->getGroupData('4000', $fromDate, $toDate, $fundId),
            'directCost' => $this->getGroupData('5000', $fromDate, $toDate, $fundId),
            'incomes' => $this->getGroupData('8000', $fromDate, $toDate, $fundId),
            'expenses' => $this->getGroupData('6000', $fromDate, $toDate, $fundId),
            'taxation' => $this->getGroupData('9000', $fromDate, $toDate, $fundId),
        ];
        
        // Calculate totals
        $data['totalRevenue'] = $this->calculateGroupTotal($data['revenue']);
        $data['totalDirectCost'] = $this->calculateGroupTotal($data['directCost']);
        $data['grossSurplus'] = $data['totalRevenue'] - $data['totalDirectCost'];
        $data['totalIncomes'] = $this->calculateGroupTotal($data['incomes']);
        $data['totalExpenses'] = $this->calculateGroupTotal($data['expenses']);
        $data['surplusBeforeTax'] = $data['grossSurplus'] + $data['totalIncomes'] - $data['totalExpenses'];
        $data['totalTaxation'] = $this->calculateGroupTotal($data['taxation']);
        $data['surplusAfterTax'] = $data['surplusBeforeTax'] - $data['totalTaxation'];
        
        return $data;
    }
    
    /**
     * Get Monthly View Data
     */
    private function getMonthlyData($fromDate, $toDate, $fundId)
    {
        // Generate month list
        $months = [];
        $current = Carbon::parse($fromDate)->startOfMonth();
        $end = Carbon::parse($toDate)->endOfMonth();
        
        while ($current <= $end) {
            $months[] = [
                'month' => $current->format('M, Y'),
                'start' => $current->copy()->startOfMonth()->format('Y-m-d'),
                'end' => $current->copy()->endOfMonth()->format('Y-m-d')
            ];
            $current->addMonth();
        }
        
        $data = [
            'months' => $months,
            'revenue' => $this->getGroupMonthlyData('4000', $months, $fundId),
            'directCost' => $this->getGroupMonthlyData('5000', $months, $fundId),
            'incomes' => $this->getGroupMonthlyData('8000', $months, $fundId),
            'expenses' => $this->getGroupMonthlyData('6000', $months, $fundId),
            'taxation' => $this->getGroupMonthlyData('9000', $months, $fundId),
        ];
        
        // Calculate monthly totals
        foreach ($months as $index => $month) {
            $monthKey = 'month_' . $index;
            
            $data['totalRevenue'][$monthKey] = $this->calculateMonthlyGroupTotal($data['revenue'], $monthKey);
            $data['totalDirectCost'][$monthKey] = $this->calculateMonthlyGroupTotal($data['directCost'], $monthKey);
            $data['grossSurplus'][$monthKey] = $data['totalRevenue'][$monthKey] - $data['totalDirectCost'][$monthKey];
            $data['totalIncomes'][$monthKey] = $this->calculateMonthlyGroupTotal($data['incomes'], $monthKey);
            $data['totalExpenses'][$monthKey] = $this->calculateMonthlyGroupTotal($data['expenses'], $monthKey);
            $data['surplusBeforeTax'][$monthKey] = $data['grossSurplus'][$monthKey] + 
                $data['totalIncomes'][$monthKey] - $data['totalExpenses'][$monthKey];
            $data['totalTaxation'][$monthKey] = $this->calculateMonthlyGroupTotal($data['taxation'], $monthKey);
            $data['surplusAfterTax'][$monthKey] = $data['surplusBeforeTax'][$monthKey] - 
                $data['totalTaxation'][$monthKey];
        }
        
        // Calculate row totals
        $data['totalRevenue']['total'] = array_sum(array_filter($data['totalRevenue'], 'is_numeric'));
        $data['totalDirectCost']['total'] = array_sum(array_filter($data['totalDirectCost'], 'is_numeric'));
        $data['grossSurplus']['total'] = array_sum(array_filter($data['grossSurplus'], 'is_numeric'));
        $data['totalIncomes']['total'] = array_sum(array_filter($data['totalIncomes'], 'is_numeric'));
        $data['totalExpenses']['total'] = array_sum(array_filter($data['totalExpenses'], 'is_numeric'));
        $data['surplusBeforeTax']['total'] = array_sum(array_filter($data['surplusBeforeTax'], 'is_numeric'));
        $data['totalTaxation']['total'] = array_sum(array_filter($data['totalTaxation'], 'is_numeric'));
        $data['surplusAfterTax']['total'] = array_sum(array_filter($data['surplusAfterTax'], 'is_numeric'));
        
        return $data;
    }
    
    /**
     * Get Group Data for Full View
     */
    private function getGroupData($groupCode, $fromDate, $toDate, $fundId)
    {
        $group = Group::where('code', $groupCode)->first();
        
        if (!$group) {
            return [];
        }
        
        return $this->processGroupHierarchy($group, $fromDate, $toDate, $fundId);
    }
    
    /**
     * Process Group Hierarchy
     */
    private function processGroupHierarchy($group, $fromDate, $toDate, $fundId)
    {
        $data = [];
        
        // Get ledgers under this group
        $ledgers = $group->ledgers;
        
        foreach ($ledgers as $ledger) {
            $balance = $this->getLedgerBalance($ledger->id, $fromDate, $toDate, $fundId);
            
            // Skip zero balance ledgers
            if ($balance == 0) {
                continue;
            }
            
            $data['ledgers'][] = [
                'id' => $ledger->id,
                'code' => '(' . $ledger->full_code . ')',
                'name' => $ledger->name,
                'balance' => $balance
            ];
        }
        
        // Process child groups
        foreach ($group->children as $childGroup) {
            $childData = $this->processGroupHierarchy($childGroup, $fromDate, $toDate, $fundId);
            
            if (!empty($childData)) {
                $data['groups'][] = [
                    'id' => $childGroup->id,
                    'code' => $childGroup->code,
                    'name' => $childGroup->name,
                    'data' => $childData
                ];
            }
        }
        
        return $data;
    }
    
    /**
     * Get Ledger Balance
     */
    private function getLedgerBalance($ledgerId, $fromDate, $toDate, $fundId)
    {
        $query = DB::table('entryitems')
            ->join('entries', 'entryitems.entry_id', '=', 'entries.id')
            ->where('entryitems.ledger_id', $ledgerId)
            ->whereRaw('DATE(entries.date) >= ?', [$fromDate])
            ->whereRaw('DATE(entries.date) <= ?', [$toDate]);
        
        if ($fundId !== 'all') {
            $query->where('entries.fund_id', $fundId);
        }
        
        $result = $query->select(
            DB::raw('SUM(CASE WHEN entryitems.dc = "C" THEN entryitems.amount ELSE -entryitems.amount END) as balance')
        )->first();
        
        return $result->balance ?? 0;
    }
    
    /**
     * Get Group Monthly Data
     */
    private function getGroupMonthlyData($groupCode, $months, $fundId)
    {
        $group = Group::where('code', $groupCode)->first();
        
        if (!$group) {
            return [];
        }
        
        return $this->processGroupMonthlyHierarchy($group, $months, $fundId);
    }
    
    /**
     * Process Group Monthly Hierarchy
     */
    private function processGroupMonthlyHierarchy($group, $months, $fundId)
    {
        $data = [];
        
        // Get ledgers
        $ledgers = $group->ledgers;
        
        foreach ($ledgers as $ledger) {
            $monthlyBalances = [];
            $hasNonZeroBalance = false;
            
            foreach ($months as $index => $month) {
                $balance = $this->getLedgerBalance($ledger->id, $month['start'], $month['end'], $fundId);
                $monthlyBalances['month_' . $index] = $balance;
                
                if ($balance != 0) {
                    $hasNonZeroBalance = true;
                }
            }
            
            // Skip if all months are zero
            if (!$hasNonZeroBalance) {
                continue;
            }
            
            $monthlyBalances['total'] = array_sum($monthlyBalances);
            
            $data['ledgers'][] = array_merge([
                'id' => $ledger->id,
                'code' => '(' . $ledger->full_code . ')',
                'name' => $ledger->name
            ], $monthlyBalances);
        }
        
        // Process child groups
        foreach ($group->children as $childGroup) {
            $childData = $this->processGroupMonthlyHierarchy($childGroup, $months, $fundId);
            
            if (!empty($childData)) {
                $data['groups'][] = [
                    'id' => $childGroup->id,
                    'code' => $childGroup->code,
                    'name' => $childGroup->name,
                    'data' => $childData
                ];
            }
        }
        
        return $data;
    }
    
    /**
     * Calculate Group Total
     */
    private function calculateGroupTotal($groupData)
    {
        $total = 0;
        
        if (isset($groupData['ledgers'])) {
            foreach ($groupData['ledgers'] as $ledger) {
                $total += $ledger['balance'];
            }
        }
        
        if (isset($groupData['groups'])) {
            foreach ($groupData['groups'] as $group) {
                $total += $this->calculateGroupTotal($group['data']);
            }
        }
        
        return $total;
    }
    
    /**
     * Calculate Monthly Group Total
     */
    private function calculateMonthlyGroupTotal($groupData, $monthKey)
    {
        $total = 0;
        
        if (isset($groupData['ledgers'])) {
            foreach ($groupData['ledgers'] as $ledger) {
                $total += $ledger[$monthKey] ?? 0;
            }
        }
        
        if (isset($groupData['groups'])) {
            foreach ($groupData['groups'] as $group) {
                $total += $this->calculateMonthlyGroupTotal($group['data'], $monthKey);
            }
        }
        
        return $total;
    }
    
    /**
     * Export Income Statement
     */
    private function exportIncomeStatement($request, $incomeStatementData, $fromDate, $toDate, 
        $selectedFundId, $displayType, $activeYear, $funds)
    {
        $exportType = $request->export;
        $selectedFund = $selectedFundId == 'all' ? null : Fund::find($selectedFundId);
        
        $data = [
            'incomeStatementData' => $incomeStatementData,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'displayType' => $displayType,
            'activeYear' => $activeYear,
            'selectedFund' => $selectedFund
        ];
        
        if ($exportType == 'pdf') {
            $pdf = PDF::loadview('accounts.income-statement.income_statement_pdf', $data);
            return $pdf->download('income_statement_' . date('YmdHis') . '.pdf');
        } elseif ($exportType == 'excel') {
            return Excel::download(new IncomeStatementExport($data), 
                'income_statement_' . date('YmdHis') . '.xlsx');
        } elseif ($exportType == 'print') {
            return view('accounts.income-statement.income_statement_print', $data);
        }
    }
}