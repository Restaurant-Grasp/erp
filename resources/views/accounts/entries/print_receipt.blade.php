<!DOCTYPE html>
<html>
<head>
    <title>Receipt Voucher - {{ $entry->entry_code }}</title>
    <style>
        @media print {
            #backButon {
                visibility: hidden;
            }
        }
        .btn {
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.42857143;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            cursor: pointer;
            background-image: none;
            border: 1px solid transparent;
            border-radius: 4px;
            color: #fff;
            background-color: #5bc0de;
            border-color: #46b8da;
        }

        table { page-break-inside:auto }
        tr    { page-break-inside:avoid; page-break-after:auto }
        thead { display:table-header-group }
        tfoot { display:table-footer-group }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            font-weight: bold;
            color: rgba(0, 0, 0, 0.1);
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="watermark">RECEIPT</div>
    
    <table width="750" border="0" align="center" id="backButon">
        <tr>
            <td width="550"></td>
            <td width="100" style="vertical-align:top;">
                <a href="{{ route('accounts.receipt.list') }}">
                    <button class="btn btn-primary">Back</button>
                </a>
            </td>
            <td width="100">
                <button class="btn btn-info" onclick="window.print()">Print</button>
            </td>
        </tr>
    </table>
    <br><br>

    <table width="750" border="0" align="center" style="margin-top:-50px;">
        <tr>
            <td width="20"></td>
            <td width="120"><img src="{{ asset('public/assets/logo.jpeg') }}" width="120" height="80" alt="logo" /></td>
            <td width="580" align="left" style="font-size:13px;">
                <strong style="font-size: 28px; color:#e16c2f;">RSK Canvas Trading</strong><span style="font-size:12px;">&nbsp;202103164044(MA0269595-D)</span>
                <br>No. 8 Lot 2921, Jalann PJS 3/1, Taman medan, 46000 Petaling Jaya, 
                <br>Selangor Darul Ehsan.
                <br><span>Tel : +603-7781 7434 / +603-7785 7434</span>
                <br>E-mail : sales@rsk.com.my
            </td>
            <td width="50"></td>
        </tr>
    </table>

    <table width="750" style="border-top:2px solid #c2c2c2;padding: 7px 0px;" align="center">
        <tr>
            <td width="200"></td>
            <td width="350" style="font-size:26px;text-align:center;font-weight: bold;text-transform: uppercase;">Receipt Voucher</td>
            <td width="200"></td>
        </tr>
    </table>

    <table width="750" border="0" align="center" cellpadding="3">
        <tr style="font-size:14px;">
            <td width="150"><b>Receipt No:</b></td>
            <td width="250">{{ $entry->entry_code }}</td>
            <td width="150"><b>Date:</b></td>
            <td width="200">{{ date('d/M/Y', strtotime($entry->date)) }}</td>
        </tr>
        <tr style="font-size:14px;">
            <td><b>Received From:</b></td>
            <td colspan="3"><b>{{ $entry->paid_to }}</b></td>
        </tr>
        <tr style="font-size:14px;">
            <td><b>Receipt Mode:</b></td>
            <td>{{ $entry->payment }}</td>
   
        </tr>
        @if($entry->payment == 'CHEQUE')
        <tr style="font-size:14px;">
            <td><b>Cheque No:</b></td>
            <td>{{ $entry->cheque_no }}</td>
            <td><b>Cheque Date:</b></td>
            <td>{{ $entry->cheque_date ? \Carbon\Carbon::parse($entry->cheque_date)->format('d/M/Y') : '' }}</td>
        </tr>
        @endif
        @if($entry->payment == 'ONLINE')
        <tr style="font-size:14px;">
            <td><b>Transaction No:</b></td>
            <td>{{ $entry->cheque_no }}</td>
            <td><b>Transaction Date:</b></td>
            <td>{{ $entry->cheque_date ? \Carbon\Carbon::parse($entry->cheque_date)->format('d/M/Y') : '' }}</td>
        </tr>
        @endif
    </table>

    <table width="750" align="center" style="padding-top:20px;border-top:2px solid black;border-collapse:collapse;">
        <thead>
            <tr style="font-size: 14px;">   
                <td width="50" height="30" align="center" style="border-bottom:2px solid black;padding:5px;"><b>S.No</b></td>
                <td width="400" style="border-bottom:2px solid black;padding:5px;" align="left"><b>Account</b></td>
                <td width="150" style="border-bottom:2px solid black;padding:5px;" align="left"><b>Details</b></td>
                <td width="150" style="border-bottom:2px solid black;padding:5px;" align="center"><b>Amount (RM)</b></td>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($creditItems as $item)
            <tr style="height:30px;">
                <td align="center" style="padding:3px;font-size:14px;">{{ $i++ }}</td>
                <td align="left" style="padding:3px;font-size:14px;">{{ $item->ledger->name }}</td>
                <td align="left" style="padding:3px;font-size:14px;">{{ $item->details }}</td>
                <td align="right" style="padding:3px;font-size:14px;">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
			@if($discountItem)
            <tr style="height:30px;">
                <td align="center" style="padding:3px;font-size:14px;">{{ $i++ }}</td>
                <td align="left" style="padding:3px;font-size:14px;">
                    <b>[Discount]</b> {{ $discountItem->ledger->name }}
                </td>
                <td align="left" style="padding:3px;font-size:14px;">{{ $discountItem->details }}</td>
                <td align="right" style="padding:3px;font-size:14px;color:red;">{{ number_format($discountItem->amount, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <table width="750" align="center" style="padding-top:20px;border-top:2px solid black;border-collapse:collapse;margin-bottom:5px;">
        <tr style="font-size: 14px;">
            <td><b>Amount in Words:</b> {{ $total_value }}</td>
        </tr>
    </table>

    <table width="750" border="0" align="center" style="border-collapse:collapse;">
        <tr style="font-size: 14px;">
            <td align="right" width="600"><span style="font-size:14px;font-weight:bold;">Total Amount Received:</span></td>
			@php
			$dr_total = $entry->dr_total;
			if($discountItem && !empty($discountItem->amount)) $dr_total -= $discountItem->amount;
			@endphp
            <td align="right" style="border: 2px solid #000;width:150px;padding:5px;"><strong>RM {{ number_format($dr_total, 2) }}</strong></td>
        </tr>
    </table>

    @if(!empty($entry->narration))
    <table width="750" align="center" style="margin-top:20px;">
        <tr>
            <td><b>Narration:</b></td>
        </tr>
        <tr>
            <td style="border:1px solid #ccc;padding:10px;">{{ $entry->narration }}</td>
        </tr>
    </table>
    @endif

    <table width="750" align="center" style="margin-top:50px;">
        <tr>
            <td width="375">
                <p style="border-top:1px solid #000;width:200px;text-align:center;margin-top:50px;">
                    Receiver's Signature
                </p>
            </td>
            <td width="375" align="right">
                <p style="border-top:1px solid #000;width:200px;text-align:center;margin-top:50px;margin-left:auto;">
                    Authorized Signature
                </p>
            </td>
        </tr>
    </table>

    <script type="text/javascript">
        // Uncomment to auto-print
        // window.print(); 
    </script>
</body>
</html>