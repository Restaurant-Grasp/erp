<!DOCTYPE html>
<html>
<head>
    <title>Balance Sheet</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .indent-1 { padding-left: 20px; }
        .indent-2 { padding-left: 40px; }
        .indent-3 { padding-left: 60px; }
        .group-total { background-color: #f8f9fa; font-weight: bold; }
        .footer-total { background-color: #e9ecef; font-weight: bold; }
    </style>
</head>
<body>
<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 20px; border: 1px solid #ffffff;">
    <tr>
        <td width="100" style="vertical-align: top; border: 1px solid #ffffff;">
            <img src="{{ asset('public/assets/logo.jpeg') }}" alt="RSK Logo" width="100" height="70" style="display: block;">
        </td>
        <td style="font-size: 13px; line-height: 1.6; border: 1px solid #ffffff;">
            <strong style="font-size: 20px; color: #e16c2f;">RSK Canvas Trading</strong><br>
            No. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya,<br>
            Selangor Darul Ehsan.<br>
            <span>Tel: +603-7781 7434 / +603-7785 7434</span><br>
            E-mail: sales@rsk.com.my
        </td>
    </tr>
</table>


<div style="text-align: center;">
    <h2>Balance Sheet</h2>
    <p>As on: {{ date('d-m-Y', strtotime($asOnDate)) }}</p>
    <p>Financial Year: {{ date('d-m-Y', strtotime($activeYear->from_year_month)) }} to {{ date('d-m-Y', strtotime($activeYear->to_year_month)) }}</p>
</div>

    <table>
        <thead>
            <tr>
                <th width="60%">Account Name</th>
                <th width="20%" class="text-right">Current Year</th>
                <th width="20%" class="text-right">Previous Year</th>
            </tr>
        </thead>
        <tbody>
            @foreach($balanceSheetData as $group)
                <!-- Render group data recursively -->
                @include('accounts.reports.partials.balance_sheet_pdf_group', ['group' => $group, 'level' => 0])
            @endforeach
        </tbody>
        <tfoot>
            <tr class="footer-total">
                <td>TOTAL LIABILITIES & EQUITY</td>
                <td class="text-right">
                    @php $total = $totalLiabilities['current'] + $totalEquity['current']; @endphp
                    {{ $total < 0 ? '(' . number_format(abs($total), 2) . ')' : number_format($total, 2) }}
                </td>
                <td class="text-right">
                    @php $totalPrev = $totalLiabilities['previous'] + $totalEquity['previous']; @endphp
                    {{ $totalPrev < 0 ? '(' . number_format(abs($totalPrev), 2) . ')' : number_format($totalPrev, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>