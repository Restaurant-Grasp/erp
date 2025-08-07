<!DOCTYPE html>
<html>
<head>
    <title>Journal Voucher - {{ $entry->entry_code }}</title>
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
    <div class="watermark">JOURNAL</div>
    
    <table width="750" border="0" align="center" id="backButon">
        <tr>
            <td width="550"></td>
            <td width="100" style="vertical-align:top;">
                <a href="{{ route('accounts.journal.list') }}">
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
            <td width="350" style="font-size:26px;text-align:center;font-weight: bold;text-transform: uppercase;">Journal Voucher</td>
            <td width="200"></td>
        </tr>
    </table>

    <table width="750" border="0" align="center" cellpadding="3">
        <tr style="font-size:14px;">
            <td width="150"><b>Journal No:</b></td>
            <td width="250">{{ $entry->entry_code }}</td>
            <td width="150"><b>Date:</b></td>
            <td width="200">{{ date('d/M/Y', strtotime($entry->date)) }}</td>
        </tr>
        <tr style="font-size:14px;">
            <!-- <td><b>Fund:</b></td>
            <td>{{ $entry->fund->name }}</td> -->
            <td><b>Status:</b></td>
            <td>
                @if($entry->dr_total == $entry->cr_total)
                    <span style="color:green;font-weight:bold;">Balanced</span>
                @else
                    <span style="color:red;font-weight:bold;">Unbalanced</span>
                @endif
            </td>
        </tr>
    </table>

    <table width="750" align="center" style="padding-top:20px;border-top:2px solid black;border-collapse:collapse;">
        <thead>
            <tr style="font-size: 14px;">   
                <td width="50" height="30" align="center" style="border-bottom:2px solid black;padding:5px;"><b>S.No</b></td>
                <td width="400" style="border-bottom:2px solid black;padding:5px;" align="left"><b>Account</b></td>
                <td width="150" style="border-bottom:2px solid black;padding:5px;" align="center"><b>Debit (RM)</b></td>
                <td width="150" style="border-bottom:2px solid black;padding:5px;" align="center"><b>Credit (RM)</b></td>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($entry->entryItems as $item)
            <tr style="height:30px;">
                <td align="center" style="padding:3px;font-size:14px;">{{ $i++ }}</td>
                <td align="left" style="padding:3px;font-size:14px;">
                    {{ $item->ledger->name }}
                    <br><small>({{ $item->ledger->left_code }}/{{ $item->ledger->right_code }})</small>
                </td>
                <td align="right" style="padding:3px;font-size:14px;">
                    @if($item->dc == 'D')
                        {{ number_format($item->amount, 2) }}
                    @else
                        -
                    @endif
                </td>
                <td align="right" style="padding:3px;font-size:14px;">
                    @if($item->dc == 'C')
                        {{ number_format($item->amount, 2) }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="border-top:2px solid black;">
                <td colspan="2" align="right" style="padding:5px;font-size:14px;font-weight:bold;">Total:</td>
                <td align="right" style="padding:5px;font-size:14px;font-weight:bold;">RM {{ number_format($entry->dr_total, 2) }}</td>
                <td align="right" style="padding:5px;font-size:14px;font-weight:bold;">RM {{ number_format($entry->cr_total, 2) }}</td>
            </tr>
            @if($entry->dr_total != $entry->cr_total)
            <tr>
                <td colspan="2" align="right" style="padding:5px;font-size:14px;font-weight:bold;color:red;">Difference:</td>
                <td colspan="2" align="center" style="padding:5px;font-size:14px;font-weight:bold;color:red;">
                    RM {{ number_format(abs($entry->dr_total - $entry->cr_total), 2) }}
                </td>
            </tr>
            @endif
        </tfoot>
    </table>

    <table width="750" align="center" style="padding-top:20px;border-top:2px solid black;border-collapse:collapse;margin-bottom:5px;">
        <tr style="font-size: 14px;">
            <td><b>Amount in Words:</b> {{ $total_value }}</td>
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
            <td width="250">
                <p style="border-top:1px solid #000;width:200px;text-align:center;margin-top:50px;">
                    Prepared By
                </p>
            </td>
            <td width="250" align="center">
                <p style="border-top:1px solid #000;width:200px;text-align:center;margin-top:50px;">
                    Checked By
                </p>
            </td>
            <td width="250" align="right">
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