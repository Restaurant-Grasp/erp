<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $order->po_no }} - Print</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }

            .no-print {
                display: none !important;
            }

            .items-table {
                page-break-inside: auto;
            }

            .items-table thead {
                display: table-header-group;
            }

            .items-table tbody tr {
                page-break-inside: avoid;
            }

            /* Print-specific background colors */
            .table-header-dark {
                background-color: #000 !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .vendor-header-bg {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .total-row {
                background-color: #f5f5f5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .grand-total {
                background-color: #d0d0d0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Base styles */
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            margin: 10px;
            color: #000;
        }

        /* Company Header */
        .company-header {
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
        }

        .company-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .company-header td {
            vertical-align: top;
            border: none;
            padding: 0;
        }

        .company-info {
            font-weight: bold;
            width: 70%;
            padding-right: 10px;
            font-size: 15px;
            line-height: 1.3;
        }

        .logo-cell {
            width: 30%;
            text-align: right;
            vertical-align: top;
        }

        .logo-cell img {
            max-width: 241px;
            width: auto;
            height: auto;
            margin-right: 115px;
        }

        .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #000;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .registration {
            font-size: 10px;
            color: #000;
            margin-bottom: 3px;
        }

        .contact-info {
            margin-bottom: 3px;
            line-height: 1.2;
        }

        .tagline {
            font-style: italic;
            color: #000;
            margin-top: 3px;
        }

        /* Vendor Details Section */
        .vendor-details {
            width: 100%;
            margin-bottom: 10px;
        }

        .vendor-details table {
            width: 100%;
            border-collapse: collapse;
        }

        .vendor-details td {
            padding: 5px;
            vertical-align: top;
            font-size: 9px;
        }

        .vendor-details .label {
            font-weight: bold;
            width: 20%;
            background-color: #f0f0f0;
            text-transform: uppercase;
        }

        .vendor-details .colon {
            width: 2%;
            text-align: center;
            background-color: #f0f0f0;
        }

        .vendor-details .value {
            width: 28%;
            text-transform: uppercase;
            background-color: #f0f0f0;
        }

        /* Purchase Order Header */
        .po-header {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            margin: 10px 0 8px 0;
            text-transform: uppercase;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 1px solid #000;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-size: 9px;
            vertical-align: top;
        }

        .table-header-dark {
            background-color: #000;
            color: #fff;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
            text-transform: uppercase;
            padding: 8px 6px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .grand-total {
            font-weight: bold;
            background-color: #d0d0d0;
            color: #000;
            font-size: 10px;
        }

        /* Print button styling */
        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
            margin: 5px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        /* Footer */
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 8px;
            color: #000;
            border-top: 1px solid #000;
            padding-top: 6px;
        }

        /* Specific item styling */
        .item-name {
            font-weight: bold;
            text-transform: uppercase;
        }

        .item-description {
            font-size: 8px;
            color: #666;
            margin-top: 2px;
        }

        /* Additional styling for better print compatibility */
        .vendor-header-bg {
            background-color: #f0f0f0;
        }

        /* Approval status styling */
        .approval-status {
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 3px;
            color: #fff;
            font-size: 8px;
            text-transform: uppercase;
			line-height: 21px;
        }

        .status-approved {
            background-color: #28a745;
        }

        .status-pending {
            background-color: #ffc107;
            color: #000;
        }

        .status-rejected {
            background-color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="print-button no-print">
        <button class="btn" onclick="window.print()">Print Purchase Order</button>
        <button class="btn" onclick="window.close()">Close</button>
    </div>

    <!-- Company Header -->
    <div class="company-header">
        <table>
            <tr>
                <td class="company-info">
                    <div class="company-name">
                        {{ $companyInfo['name'] !== 'Company Name Not Set' ? $companyInfo['name'] : 'GRASP SOFTWARE SOLUTIONS SDN BHD' }}
                    </div>
                    @if($companyInfo['registration_number'] && $companyInfo['registration_number'] !== 'Registration Number Not Set')
                    <div class="registration">({{ $companyInfo['registration_number'] }})</div>
                    @endif
                    <div class="contact-info">
                        @if($companyInfo['address'] && $companyInfo['address'] !== 'Address Not Set')
                        {!! $companyInfo['address'] !!}<br>
                        @else
                        No. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya,<br>
                        Selangor Darul Ehsan<br>
                        @endif
                        @if($companyInfo['website'] && $companyInfo['website'] !== 'Website Not Set')
                        {{ $companyInfo['website'] }}
                        @endif
                        @if($companyInfo['email'] && $companyInfo['email'] !== 'Email Not Set')
                        {{ $companyInfo['email'] }}<br>
                        @endif
                        @if($companyInfo['phone'] && $companyInfo['phone'] !== 'Phone Not Set')
                        {{ $companyInfo['phone'] }}
                        @endif
                    </div>
                </td>
                <td class="logo-cell">
                    @if($companyInfo['logo'])
                    <img src="{{ asset($companyInfo['logo']) }}" alt="Company Logo">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Vendor Details in Table Format -->
    <div class="vendor-details">
        <table>
            <tr>
                <td class="label vendor-header-bg">Vendor Name</td>
                <td class="colon vendor-header-bg">:</td>
                <td class="value vendor-header-bg">{{ strtoupper($order->vendor->company_name) }}</td>
                <td></td>
                <td class="label vendor-header-bg">PO Date</td>
                <td class="colon vendor-header-bg">:</td>
                <td class="value vendor-header-bg">{{ $order->po_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label vendor-header-bg">Vendor Code</td>
                <td class="colon vendor-header-bg">:</td>
                <td class="value vendor-header-bg">{{ strtoupper($order->vendor->vendor_code) }}</td>
                <td></td>
                <td class="label vendor-header-bg">PO Number</td>
                <td class="colon vendor-header-bg">:</td>
                <td class="value vendor-header-bg">{{ $order->po_no }}</td>
            </tr>
            <tr>
                <td class="label vendor-header-bg">Address</td>
                <td class="colon vendor-header-bg">:</td>
                <td class="value vendor-header-bg">
                    {{ strtoupper($order->vendor->address_line1 ?: '') }}
                    @if($order->vendor->address_line2), {{ strtoupper($order->vendor->address_line2) }}@endif
                    @if($order->vendor->city || $order->vendor->state || $order->vendor->postcode)
                    <br>{{ strtoupper($order->vendor->city ?: '') }}
                    @if($order->vendor->state), {{ strtoupper($order->vendor->state) }}@endif
                    @if($order->vendor->postcode) {{ $order->vendor->postcode }}@endif
                    @endif
                    @if($order->vendor->country)
                    <br>{{ strtoupper($order->vendor->country) }}
                    @endif
                </td>
                <td></td>
                @if($order->reference_no)
                <td class="label vendor-header-bg">Reference No</td>
                <td class="colon vendor-header-bg">:</td>
                <td class="value vendor-header-bg">{{ $order->reference_no }}</td>
                @else
                <td class="label vendor-header-bg"></td>
                <td class="colon vendor-header-bg"></td>
                <td class="value vendor-header-bg"></td>
                @endif
            </tr>
            <tr>
                <td class="label vendor-header-bg">Contact Person</td>
                <td class="colon vendor-header-bg">:</td>
                <td class="value vendor-header-bg">{{ strtoupper($order->vendor->contact_person ?: '') }}</td>
                <td></td>
                @if($order->delivery_date)
                <td class="label vendor-header-bg">Delivery Date</td>
                <td class="colon vendor-header-bg">:</td>
                <td class="value vendor-header-bg">{{ $order->delivery_date->format('d/m/Y') }}</td>
                @else
                <td class="label vendor-header-bg"></td>
                <td class="colon vendor-header-bg"></td>
                <td class="value vendor-header-bg"></td>
                @endif
            </tr>
            <tr>
                <td class="label vendor-header-bg">Email / Phone</td>
                <td class="colon vendor-header-bg">:</td>
                <td class="value vendor-header-bg">
                    @if($order->vendor->email){{ strtoupper($order->vendor->email) }}@endif
                    @if($order->vendor->email && $order->vendor->phone) / @endif
                    @if($order->vendor->phone){{ $order->vendor->phone }}@endif
                </td>
                <td></td>
                <td class="label vendor-header-bg">Approval Status</td>
                <td class="colon vendor-header-bg">:</td>
                <td class="value vendor-header-bg">
                    <span class="approval-status status-{{ $order->approval_status }}">
                        {{ strtoupper($order->approval_status) }}
                    </span>
                    @if($order->approval_status === 'approved' && $order->approvedBy)
                    <br><small>By: {{ $order->approvedBy->name }} on {{ $order->approved_date->format('d/m/Y') }}</small>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Purchase Order Header -->
    <div class="po-header">Purchase Order Items</div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="table-header-dark">S.NO</th>
                <th class="table-header-dark">Item Description</th>
                <th class="table-header-dark">Qty</th>
                <th class="table-header-dark">UOM</th>
                <th class="table-header-dark">Unit Price ({{ $order->currency }})</th>
                <th class="table-header-dark">Discount</th>
                <th class="table-header-dark">Tax</th>
                <th class="table-header-dark">Total ({{ $order->currency }})</th>
            </tr>
        </thead>
        <tbody>
            @if($order->items && $order->items->count() > 0)
            @foreach($order->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <div class="item-name">{{ strtoupper($item->item_name) }}</div>
                    @if($item->description)
                    <div class="item-description">{{ $item->description }}</div>
                    @endif
                    <small style="color: #666; font-size: 7px; text-transform: uppercase;">
                        Type: {{ ucfirst($item->item_type) }}
                    </small>
                </td>
                <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-center">{{ $item->uom ? $item->uom->name : '-' }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">
                    @if($item->discount_amount > 0)
                    {{ number_format($item->discount_amount, 2) }}
                    @if($item->discount_type === 'percentage')
                    <br><small>({{ $item->discount_value }}%)</small>
                    @endif
                    @else
                    -
                    @endif
                </td>
                <td class="text-right">
                    @if($item->tax_amount > 0)
                    {{ number_format($item->tax_amount, 2) }}
                    @if($item->tax_rate > 0)
                    <br><small>({{ $item->tax_rate }}%)</small>
                    @endif
                    @else
                    -
                    @endif
                </td>
                <td class="text-right">{{ number_format($item->total_amount, 2) }}</td>
            </tr>
            @endforeach
            @else
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px; color: #666;">
                    No items added to this purchase order
                </td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="7" class="text-right">SUB-TOTAL</td>
                <td class="text-right">{{ number_format($order->subtotal ?? 0, 2) }}</td>
            </tr>
            @if(($order->discount_amount ?? 0) > 0)
            <tr class="total-row">
                <td colspan="7" class="text-right">
                    DISCOUNT
                    @if($order->discount_type === 'percentage')
                    ({{ $order->discount_value }}%)
                    @endif
                </td>
                <td class="text-right">-{{ number_format($order->discount_amount, 2) }}</td>
            </tr>
            @endif
            @if(($order->tax_amount ?? 0) > 0)
            <tr class="total-row">
                <td colspan="7" class="text-right">TAX</td>
                <td class="text-right">{{ number_format($order->tax_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td colspan="7" class="text-right">GRAND TOTAL</td>
                <td class="text-right">{{ number_format($order->total_amount ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Additional PO Information -->
    @if($order->terms_conditions || $order->notes)
    <div style="margin-top: 15px;">
        @if($order->terms_conditions)
        <div style="margin-bottom: 10px;">
            <div style="font-weight: bold; font-size: 11px; text-transform: uppercase; margin-bottom: 5px;">
                Terms & Conditions:
            </div>
            <div style="font-size: 8px; line-height: 1.3; text-align: justify;">
                {!! nl2br(e($order->terms_conditions)) !!}
            </div>
        </div>
        @endif

        @if($order->notes)
        <div style="margin-bottom: 10px;">
            <div style="font-weight: bold; font-size: 11px; text-transform: uppercase; margin-bottom: 5px;">
                Internal Notes:
            </div>
            <div style="font-size: 8px; line-height: 1.3; text-align: justify;">
                {!! nl2br(e($order->notes)) !!}
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer-generated purchase order and does not require signature.</p>
        <p>Generated on: {{ date('d-m-Y H:i:s') }} | Created by: {{ $order->createdBy->name }}</p>
        @if($order->currency !== 'MYR')
        <p>Currency: {{ $order->currency }} | Exchange Rate: {{ $order->exchange_rate }}</p>
        @endif
    </div>

    <script>
        // Auto-print on load (optional)
        window.onload = function() {
            // window.print();
        }
    </script>
</body>

</html>