<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quotation_no }} - Print</title>
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

            .customer-header-bg {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .payment-header-bg {
                background-color: #e0e0e0 !important;
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

        /* Customer Details Section */
        .customer-details {
            width: 100%;
            margin-bottom: 10px;
        }

        .customer-details table {
            width: 100%;
            border-collapse: collapse;
        }

        .customer-details td {
            padding: 5px;
            vertical-align: top;
            font-size: 9px;
        
        }

        .customer-details .label {
            font-weight: bold;
            width: 20%;
            background-color: #f0f0f0;
            text-transform: uppercase;
        }

        .customer-details .colon {
            width: 2%;
            text-align: center;
            background-color: #f0f0f0;
        }

        .customer-details .value {
            width: 28%;
            text-transform: uppercase;
             background-color: #f0f0f0;
        }

        /* Product Header */
        .product-header {
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

        /* Payment Terms */
        .payment-section {
            margin-bottom: 15px;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .payment-table th,
        .payment-table td {

            padding: 6px;
            text-align: left;
            font-size: 9px;
        }

        .payment-header-bg {

            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            font-size: 11px;
            padding: 8px 6px;
        }

        .payment-subheader {

            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            font-size: 9px;
        }

        /* Terms & Conditions */
        .terms-header {
            font-weight: bold;
            margin-top: 5px;
            font-size: 11px;
            text-transform: uppercase;
        }

        .terms-text {
            margin-top: 5px;
            font-size: 8px;
            line-height: 1.3;
            text-align: justify;
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

        .item-features {
            margin-top: 3px;
            padding-left: 8px;
        }

        .item-features li {
            font-size: 8px;
            margin-bottom: 1px;
            text-transform: uppercase;
        }

        /* Additional styling for better print compatibility */
        .customer-header-bg {
            background-color: #f0f0f0;
        }
    </style>
</head>

<body>
    <div class="print-button no-print">
        <button class="btn" onclick="window.print()">Print Quotation</button>
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
                    <img src="{{ asset($companyInfo['logo']) }}" alt="GSS Logo">
                    @endif

                </td>
            </tr>
        </table>
    </div>

    <!-- Customer Details in Table Format -->
    <div class="customer-details">
        <table>
            <tr>
                <td class="label customer-header-bg">Customer / Lead Name</td>
                <td class="colon customer-header-bg">:</td>
                <td class="value customer-header-bg">
                    @if($quotation->customer)
                    {{ strtoupper($quotation->customer->company_name) }}
                    @elseif($quotation->lead)
                    {{ strtoupper($quotation->lead->company_name ?: $quotation->lead->contact_person) }}
                    @endif
                </td>
                <td></td>
                <td class="label customer-header-bg">Date</td>
                <td class="colon customer-header-bg">:</td>
                <td class="value customer-header-bg">{{ $quotation->quotation_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label customer-header-bg">Address</td>
                <td class="colon customer-header-bg">:</td>
                <td class="value customer-header-bg">
                    @if($quotation->customer)
                    {{ strtoupper($quotation->customer->address_line1 ?: '') }}
                    @if($quotation->customer->address_line2), {{ strtoupper($quotation->customer->address_line2) }}@endif
                    @if($quotation->customer->city || $quotation->customer->state || $quotation->customer->postcode)
                    <br>{{ strtoupper($quotation->customer->city ?: '') }}
                    @if($quotation->customer->state), {{ strtoupper($quotation->customer->state) }}@endif
                    @if($quotation->customer->postcode) {{ $quotation->customer->postcode }}@endif
                    @endif
                    @if($quotation->customer->country)
                    <br>{{ strtoupper($quotation->customer->country) }}
                    @endif
                    @elseif($quotation->lead)
                    {{ strtoupper($quotation->lead->address ?: '') }}
                    @if($quotation->lead->city || $quotation->lead->state)
                    <br>{{ strtoupper($quotation->lead->city ?: '') }}
                    @if($quotation->lead->state), {{ strtoupper($quotation->lead->state) }}@endif
                    @endif
                    @if($quotation->lead->country)
                    <br>{{ strtoupper($quotation->lead->country) }}
                    @endif
                    @endif
                </td>
                 <td></td>
                <td class="label customer-header-bg">Quotation No</td>
                <td class="colon customer-header-bg">:</td>
                <td class="value customer-header-bg">{{ $quotation->quotation_no }}</td>
            </tr>
            <tr>
                <td class="label customer-header-bg">Company / Organization</td>
                <td class="colon customer-header-bg">:</td>
                <td class="value customer-header-bg">
                    @if($quotation->customer)
                    {{ strtoupper($quotation->customer->company_name) }}
                    @elseif($quotation->lead)
                    {{ strtoupper($quotation->lead->company_name ?: $quotation->lead->contact_person) }}
                    @endif
                </td>
                 <td></td>
                <td class="label customer-header-bg"></td>
                <td class="colon customer-header-bg"></td>
                <td class="value customer-header-bg"></td>
            </tr>
        </table>
    </div>

    <!-- Product Header -->
    <div class="product-header">Product: {{ strtoupper($quotation->subject ?: '') }}</div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="table-header-dark">S.NO</th>
                <th class="table-header-dark">Particulars</th>
                <th class="table-header-dark">Amount ({{ $quotation->currency }})</th>
            </tr>
        </thead>
        <tbody>
            @if($quotation->items && $quotation->items->count() > 0)
            @foreach($quotation->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <div class="item-name">{{ strtoupper($item->item_name) }}</div>
                    @if($item->description)
                    <div class="item-description">{{ $item->description }}</div>
                    @endif

                    @php
                    // You can add specific item details here based on item type
                    $details = [];
                    if($item->item_type === 'product' && $item->product) {
                    // Add product-specific details
                    } elseif($item->item_type === 'service' && $item->service) {
                    // Add service-specific details
                    }
                    @endphp

                    @if(!empty($details))
                    <ul class="item-features">
                        @foreach($details as $detail)
                        <li>{{ $detail }}</li>
                        @endforeach
                    </ul>
                    @endif
                </td>
                <td class="text-right">{{ number_format($item->total_amount, 2) }}</td>
            </tr>
            @endforeach
            @else
            <tr>
                <td colspan="3" class="text-center" style="padding: 20px; color: #666;">
                    No items added to this quotation
                </td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-right">SUB-TOTAL</td>
                <td class="text-right">{{ number_format($quotation->subtotal ?? 0, 2) }}</td>
            </tr>
            @if(($quotation->discount_amount ?? 0) > 0)
            <tr class="total-row">
                <td colspan="2" class="text-right">
                    DISCOUNT
                    @if($quotation->discount_type === 'percentage')
                    ({{ $quotation->discount_value }}%)
                    @endif
                </td>
                <td class="text-right">-{{ number_format($quotation->discount_amount, 2) }}</td>
            </tr>
            @endif
            @if(($quotation->tax_amount ?? 0) > 0)
            <tr class="total-row">
                <td colspan="2" class="text-right">SST (8%)</td>
                <td class="text-right">{{ number_format($quotation->tax_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td colspan="2" class="text-right">GRAND TOTAL</td>
                <td class="text-right">{{ number_format($quotation->total_amount ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Payment Terms -->
    @php
    $paymentTerms = [
    ['no' => 1, 'item' => 'Deposit', 'date' => $quotation->quotation_date->copy()->addDays(5)->format('d/m/Y'), 'description' => 'Hardware + Software', 'amount' => ($quotation->total_amount ?? 0) * 0.25],
    ['no' => 2, 'item' => 'Upon Installation', 'date' => $quotation->quotation_date->copy()->addDays(30)->format('d/m/Y'), 'description' => '', 'amount' => ($quotation->total_amount ?? 0) * 0.25],
    ['no' => 3, 'item' => 'Third Payment', 'date' => $quotation->quotation_date->copy()->addDays(60)->format('d/m/Y'), 'description' => '', 'amount' => ($quotation->total_amount ?? 0) * 0.25],
    ['no' => 4, 'item' => 'Final Payment', 'date' => $quotation->quotation_date->copy()->addDays(90)->format('d/m/Y'), 'description' => '', 'amount' => ($quotation->total_amount ?? 0) * 0.25],
    ];
    @endphp

    <div class="payment-section">
        <table class="payment-table">
            <thead>
                <tr>
                    <th colspan="5" class="payment-header-bg">Payment Terms</th>
                </tr>
                <tr>
                    <th class="payment-subheader">No</th>
                    <th class="payment-subheader">Item</th>
                    <th class="payment-subheader">Date</th>
                    <th class="payment-subheader">Description</th>
                    <th class="payment-subheader">Grand Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paymentTerms as $term)
                <tr>
                    <td class="text-center">{{ $term['no'] }}</td>
                    <td>{{ $term['item'] }}</td>
                    <td>{{ $term['date'] }}</td>
                    <td>{{ $term['description'] }}</td>
                    <td class="text-right">{{ $quotation->currency }} {{ number_format($term['amount'], 2) }}</td>
                </tr>
                @endforeach
                @if($termsConditions)
                <tr>
                    <td colspan="5" style="border-top: 1px solid #000; padding-top: 10px;">
                        <div class="terms-header">Terms & Conditions:</div>
                        <div class="terms-text">{{ $termsConditions }}</div>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer-generated quotation and does not require signature.</p>
        <p>Generated on: {{ date('d-m-Y H:i:s') }}</p>
    </div>

    <script>
        // Auto-print on load (optional)
        window.onload = function() {
            // window.print();
        }
    </script>
</body>

</html>