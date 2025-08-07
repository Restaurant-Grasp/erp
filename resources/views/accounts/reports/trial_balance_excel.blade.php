<table>
                {{-- Company Address Section --}}
        <tr>
            <th colspan="6" style="text-align: center; font-size: 14px; font-weight: bold;">
                RSK Canvas Trading
            </th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: center; font-size: 11px;">
                No. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya, Selangor Darul Ehsan. Tel: +603-7781 7434 / +603-7785 7434, E-mail: sales@rsk.com.my
            </th>
        </tr>
        <tr>
            <th colspan="6"></th>
        </tr>
    <tr>
        <td colspan="6" style="text-align: center; font-size: 16px; font-weight: bold;">TRIAL BALANCE</td>
    </tr>
    <tr>
        <td colspan="6" style="text-align: center;">As on: {{ date('d-m-Y', strtotime($toDate)) }} | Period: {{ date('d-m-Y', strtotime($fromDate)) }} to {{ date('d-m-Y', strtotime($toDate)) }}</td>
    </tr>
    <tr>
        <td colspan="6"></td>
    </tr>
    <tr>
        <th>Account Code</th>
        <th>Account Name</th>
        <th>Opening Debit</th>
        <th>Opening Credit</th>
        <th>Closing Debit</th>
        <th>Closing Credit</th>
    </tr>
    
    @foreach($trialBalanceData as $parentGroup)
    <tr>
        <td>{{ $parentGroup['code'] }}</td>
        <td><strong>{{ $parentGroup['name'] }}</strong></td>
        <td>0</td>
        <td>0</td>
        <td>{{ $parentGroup['totalClosingDebit'] }}</td>
        <td>{{ $parentGroup['totalClosingCredit'] }}</td>
    </tr>
    
    @include('accounts.reports.partials.trial_balance_excel_group', [
        'group' => $parentGroup, 
        'level' => 0
    ])
    @endforeach
    
    <tr>
        <td colspan="2"><strong>Grand Total</strong></td>
        <td>{{ $grandTotalOpeningDebit }}</td>
        <td>{{ $grandTotalOpeningCredit }}</td>
        <td>{{ $grandTotalClosingDebit }}</td>
        <td>{{ $grandTotalClosingCredit }}</td>
    </tr>
</table>