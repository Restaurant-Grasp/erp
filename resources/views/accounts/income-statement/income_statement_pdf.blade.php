<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Income Statement</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 5px 0; }
        .info { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 5px; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .section-header { background-color: #e9ecef; font-weight: bold; }
        .group-total { background-color: #f8f9fa; font-weight: bold; }
        .footer { background-color: #343a40; color: white; font-weight: bold; text-align: center; }
        .indent-1 { padding-left: 20px; }
        .indent-2 { padding-left: 40px; }
    </style>
</head>
<body>
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 20px; border: 1px solid #ffffff;">
    <tr>
        <td width="50" style="vertical-align: top; border: 1px solid #ffffff;">
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
    <div class="header">
        <h2>INCOME STATEMENT</h2>
        <p>From {{ date('d-m-Y', strtotime($fromDate)) }} to {{ date('d-m-Y', strtotime($toDate)) }}</p>
        @if($selectedFund)
        <p>Fund: {{ $selectedFund->code }} - {{ $selectedFund->name }}</p>
        @endif
    </div>
    
    @if($displayType == 'full')
        @include('accounts.income-statement.exports.pdf_full_content')
    @else
        @include('accounts.income-statement.exports.pdf_monthly_content')
    @endif
</body>
</html>