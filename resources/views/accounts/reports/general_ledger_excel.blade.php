
<table>
               {{-- Company Address Section --}}
        <tr>
            <th colspan="7" style="text-align: center; font-size: 14px; font-weight: bold;">
                RSK Canvas Trading
            </th>
        </tr>
        <tr>
         <th colspan="7" style="text-align: center;">
    No. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya, Selangor Darul Ehsan. Tel: +603-7781 7434 / +603-7785 7434, E-mail: sales@rsk.com.my
</th>
        </tr>
        <tr>
            <th colspan="7"></th>
        </tr>
    <tr>
        <td colspan="7" style="text-align: center; font-size: 16px; font-weight: bold;">GENERAL LEDGER REPORT</td>
    </tr>
    <tr>
        <td colspan="7" style="text-align: center;">From: {{ date('d-m-Y', strtotime($fromDate)) }} To: {{ date('d-m-Y', strtotime($toDate)) }}</td>
    </tr>
    <tr>
        <td colspan="7" style="text-align: center;">Total Ledgers: {{ count($ledgerReports) }}</td>
    </tr>
    <tr>
        <td colspan="7"></td>
    </tr>
    
    @foreach($ledgerReports as $report)
    <tr>
        <td colspan="7" style="font-weight: bold; font-size: 14px; background-color: #e0e0e0;">
            {{ $report['ledger']->name }} ({{ $report['ledger']->left_code }}/{{ $report['ledger']->right_code }})
        </td>
    </tr>
    <tr>
        <th>Date</th>
        <th>Voucher No</th>
        <th>Type</th>
        <th>Particulars</th>
        <th>Debit</th>
        <th>Credit</th>
        <th>Balance</th>
    </tr>
    
    <!-- Opening Balance -->
    <tr>
        <td colspan="4">Opening Balance</td>
        <td>{{ $report['openingBalance']['debit'] }}</td>
        <td>{{ $report['openingBalance']['credit'] }}</td>
        <td>
            @php
                $openingNet = $report['openingBalance']['debit'] - $report['openingBalance']['credit'];
            @endphp
   {{ $openingNet >= 0 
    ? number_format(abs($openingNet), 2, '.', '') 
    : '(' . number_format(abs($openingNet), 2, '.', '') . ')' 
}}
        </td>
    </tr>
    
    <!-- Transactions -->
    @foreach($report['transactions'] as $transaction)
    <tr>
        <td>{{ date('d-m-Y', strtotime($transaction->entry->date)) }}</td>
        <td>{{ $transaction->entry->entry_code }}</td>
        <td>
            {{ $transaction->entry->entry_type_name }}
            @if($transaction->entry->inv_type)
                ({{ $transaction->entry->inv_type == 1 ? 'Sales' : 'Purchase' }})
            @endif
        </td>
        <td>
            @php
                $oppositeEntries = $transaction->entry->entryItems
                    ->where('id', '!=', $transaction->id)
                    ->where('dc', '!=', $transaction->dc);
                $particulars = [];
                foreach($oppositeEntries as $opposite) {
                    $particulars[] = @$opposite->ledger->name;
                }
            @endphp
            {{ implode(', ', $particulars) }}
            @if($transaction->entry->narration)
                - {{ $transaction->entry->narration }}
            @endif
        </td>
        <td>{{ $transaction->dc == 'D' ? $transaction->amount : 0 }}</td>
        <td>{{ $transaction->dc == 'C' ? $transaction->amount : 0 }}</td>
    <td>
    {{ $transaction->balance_type == 'Dr'
        ? number_format($transaction->running_balance, 2, '.', '')
        : '(' . number_format($transaction->running_balance, 2, '.', '') . ')' }}
</td>
    </tr>
    @endforeach
    
    <!-- Closing Balance -->
    <tr>
        <td colspan="4">Closing Balance</td>
        <td>{{ $report['closingBalance']['debit'] }}</td>
        <td>{{ $report['closingBalance']['credit'] }}</td>
        <td>
            @php
                $closingNet = $report['closingBalance']['debit'] - $report['closingBalance']['credit'];
            @endphp
            {{ $closingNet >= 0 
    ? number_format(abs($closingNet), 2, '.', '') 
    : '(' . number_format(abs($closingNet), 2, '.', '') . ')' 
}}
          
        </td>
    </tr>
    
    <!-- Empty row between ledgers -->
    <tr>
        <td colspan="7"></td>
    </tr>
    @endforeach
</table>