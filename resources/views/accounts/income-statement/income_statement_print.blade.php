<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Income Statement - Print</title>
    <style>
        @media print {
            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .section-header {
            background-color: #e9ecef;
            font-weight: bold;
        }

        .group-total {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .footer {
            background-color: #343a40;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 10px;
        }

        .indent-1 {
            padding-left: 20px;
        }

        .company-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;

            gap: 20px;
            flex-wrap: wrap;
            text-align: left;
        }

        .company-header .logo img {
            width: 120px;
            height: 80px;
        }

        .company-header .company-info {
            font-size: 13px;
            line-height: 1.6;
            max-width: 580px;
        }

        .company-header .company-info strong {
            font-size: 24px;
            color: #e16c2f;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="company-header">
        <div class="logo">
            <img src="{{ asset('public/assets/logo.jpeg') }}" alt="RSK Logo">
        </div>
        <div class="company-info">
            <strong>RSK Canvas Trading</strong><br>
            No. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya,<br>
            Selangor Darul Ehsan.<br>
            <span>Tel: +603-7781 7434 / +603-7785 7434</span><br>
            E-mail: sales@rsk.com.my
        </div>
    </div>
    <div class="header">
        <h3>INCOME STATEMENT</h3>
        <p>From {{ date('d-m-Y', strtotime($fromDate)) }} to {{ date('d-m-Y', strtotime($toDate)) }}</p>
        @if($selectedFund)
        <p>Fund: {{ $selectedFund->code }} - {{ $selectedFund->name }}</p>
        @endif
        <p>Financial Year: {{ date('d-m-Y', strtotime($activeYear->from_year_month)) }} to
            {{ date('d-m-Y', strtotime($activeYear->to_year_month)) }}
        </p>
    </div>

    @if($displayType == 'full')
    @include('accounts.income-statement.partials.full_view')
    @else
    @include('accounts.income-statement.partials.monthly_view')
    @endif

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.close()">Close</button>
    </div>
</body>

</html>