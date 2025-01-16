<style>
    /* Table Styling */
    .table .custom-bg {
        background-color: #f1f1f1 !important;
        -webkit-print-color-adjust: exact;
    }

    .main_table {
        width: 100%;
        border-collapse: collapse;
        background-color: #fff;
        font-size: 14px;
        text-align: right;
    }

    .main_table thead {
        background-color: #f2f2f2;
    }

    .main_table th {
        border: 1px solid #ddd;
        padding: 10px;
        background-color: #007bff;
        color: #fff;
        font-weight: bold;
    }

    .main_table td {
        border: 1px solid #ddd;
        padding: 8px;
    }

    .main_table tbody tr:nth-child(odd) {
        background-color: #f9f9f9;
    }

    .main_table tbody tr:nth-child(even) {
        background-color: #fff;
    }

    .main_table tbody tr:hover {
        background-color: #f1f1f1;
    }

    .main_table img {
        border-radius: 5px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }

    .main_table .custom-bg {
        color: #fff;
    }

    .main_table tfoot td {
        text-align: center;
        font-weight: bold;
        background-color: #f2f2f2;
    }

    /* Responsive Styling */
    @media (max-width: 768px) {
        .main_table {
            font-size: 12px;
        }

        .main_table td,
        .main_table th {
            padding: 6px;
        }
    }

    /* General Table Adjustments */
    .table {
        margin-bottom: 5px;
    }

    .table-slim,
    .table-slim td,
    .table-slim th {
        border: 0.5px solid #d5d5d5 !important;
        padding: 2px 4px !important;
    }

    /* Footer Styling */
    .page-footer,
    .page-footer-space {
        max-height: 60px !important;
        margin-top: 30px !important;
    }

    .page-footer {
        position: fixed !important;
        bottom: 0px !important;
        margin-top: 10px !important;
        width: 50%;
    }

    .footer-text {
        width: 100%;
        margin: auto;
        padding-top: 20px;
    }

    /* Page Adjustments */
    @page {
        margin: 10px 0px 0px 0px !important;
    }

    table {
        font-size: 13px;
    }

    table:not(.main_table) {
        font-size: 13px;
        page-break-inside: avoid;
        page-break-after: avoid;
    }

    .invoice-container {
        width: 93%;
        margin: auto;
        text-align: center;
        line-height: 22px;
        font-size: 14px;
    }

    .flex {
        display: flex;
        justify-content: space-between;
        padding: 0;
        margin: 5px 0px !important;
    }

    body {
        width: 100%;
        margin: auto;
    }

    /* Print-specific Styling */
    @media print {
        .invoice-container thead {
            display: table-header-group !important;
        }

        tfoot {
            display: table-footer-group !important;
        }

        body {
            margin: 0 !important;
            width: 100%;
        }

        .page-footer {
            width: 100% !important;
        }

        .page-footer-space {
            max-height: 60px;
        }
    }
</style>


<body>
    <table class="invoice-container">

        <tbody class="invoice-content">
            <tr>
                <td class="invoice-content-cell">
                    <div class="main">
                        <div class="row" style="color: #000000 !important;">
                            <!-- Logo -->
                            @if (empty($receipt_details->letter_head))
                            @if (!empty($receipt_details->logo))
                            <img style="max-height: 100px; width: auto;" src="{{ $receipt_details->logo }}"
                                class="img img-responsive center-block">
                            @endif

                            <!-- Header text -->
                            @if (!empty($receipt_details->header_text))
                            <div class="col-xs-12">
                                {!! $receipt_details->header_text !!}
                            </div>
                            @endif

                            <!-- business information here -->
                            <div class="col-xs-12 text-center">
                                <h2 class="text-center">
                                    <!-- Shop & Location Name  -->
                                    @if (!empty($receipt_details->display_name))
                                    {{ $receipt_details->display_name }}
                                    @endif
                                </h2>

                                <!-- Address -->
                                <p>
                                    @if (!empty($receipt_details->address))
                                    <small class="text-center">
                                        {!! $receipt_details->address !!}
                                    </small>
                                    @endif
                                    @if (!empty($receipt_details->contact))
                                    <br />{!! $receipt_details->contact !!}
                                    @endif
                                    @if (!empty($receipt_details->contact) && !empty($receipt_details->website))
                                    ,
                                    @endif
                                    @if (!empty($receipt_details->website))
                                    {{-- {{ $receipt_details->website }} --}}
                                    @endif
                                    @if (!empty($receipt_details->location_custom_fields))
                                    <br>{{ $receipt_details->location_custom_fields }}
                                    @endif
                                </p>
                                <p>
                                    @if (!empty($receipt_details->sub_heading_line1))
                                    {{ $receipt_details->sub_heading_line1 }}
                                    @endif
                                    @if (!empty($receipt_details->sub_heading_line2))
                                    <br>{{ $receipt_details->sub_heading_line2 }}
                                    @endif
                                    @if (!empty($receipt_details->sub_heading_line3))
                                    <br>{{ $receipt_details->sub_heading_line3 }}
                                    @endif
                                    @if (!empty($receipt_details->sub_heading_line4))
                                    <br>{{ $receipt_details->sub_heading_line4 }}
                                    @endif
                                    @if (!empty($receipt_details->sub_heading_line5))
                                    <br>{{ $receipt_details->sub_heading_line5 }}
                                    @endif
                                </p>
                                <p>
                                    @if (!empty($receipt_details->tax_info1))
                                    <b>{{ $receipt_details->tax_label1 }}</b>
                                    {{ $receipt_details->tax_info1 }}
                                    @endif

                                    @if (!empty($receipt_details->tax_info2))
                                    <b>{{ $receipt_details->tax_label2 }}</b>
                                    {{ $receipt_details->tax_info2 }}
                                    @endif
                                </p>
                                @endif


                                <!-- Title of receipt -->
                                @if (!empty($receipt_details->invoice_heading))
                                <h3 class="text-center">
                                    {!! $receipt_details->invoice_heading !!}
                                </h3>
                                @endif
                                @if (!empty($receipt_details->letter_head))
                                <div class="col-xs-12 text-center">
                                    <img style="width: 100%;margin-bottom: 10px;"
                                        src="{{ $receipt_details->letter_head }}">
                                </div>
                                @endif
                            </div>

                            <div class="text-end" style="position: absolute; top: 20px; right: 10px; display: flex; flex-direction: column; align-items: flex-end; 
                                padding: 10px; background-color: #f9f9f9; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
                                @if ($receipt_details->show_barcode)
                                {{-- Barcode --}}
                                <img class="center-block"
                                    src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, [39, 48, 54], true) }}"
                                    style="max-width: 150px; margin-bottom: 10px; border-radius: 5px;">
                                @endif

                                @if ($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
                                {{-- QR Code --}}
                                <img class="center-block"
                                    src="data:image/png;base64,{{ DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54]) }}"
                                    style="max-width: 200px; margin-top: 10px; border-radius: 5px;">
                                @endif

                                {{-- Invoice Details --}}
                            </div>

                            <div class="text-start"
                                style="position: absolute; top: 20px; left: 20px; display: flex; flex-direction: column;
                            align-items: flex-start; padding: 15px; background-color: #f9f9f9;
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); border-radius: 10px; max-width: 300px; font-family: Arial, sans-serif; font-size: 12px;">

                                <table style="width: 100%;">
                                    <!-- Invoice No -->
                                    <tr>
                                        <td
                                            style="font-weight: bold; color: #333; text-transform: uppercase; padding-right: 10px; text-align: start;">
                                            @lang('رقم الفاتورة'):
                                        </td>
                                        <td style="color: #555; word-break: break-word; text-align: center;">
                                            {{ $receipt_details->invoice_no }}
                                        </td>
                                    </tr>

                                    <!-- Date -->
                                    <tr>
                                        <td
                                            style="font-weight: bold; color: #333; text-transform: uppercase; padding-right: 10px; text-align: start;">
                                            @lang('التاريخ'):
                                        </td>
                                        <td style="color: #555; word-break: break-word; text-align: center;">
                                            {{ $receipt_details->invoice_date }}
                                        </td>
                                    </tr>
                                </table>
                            </div>

                        </div>



                        <div class="row col-xs-12 text-center" style="margin-top: 120px;">
                            <!-- Adjust top margin to avoid overlap with QR code/barcode -->
                            <div
                                style="width: 100%; font-size: 14px; line-height: 1.5; border: 1px solid #ddd; padding: 20px; margin-top: 20px; background-color: #f9f9f9;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tbody>
                                        @if (!empty($receipt_details->customer_info))
                                        @if (!empty($receipt_details->customer_name))
                                        <tr style="border: 1px solid #ddd;">
                                            <td style="padding: 10px; font-weight: bold; width: 30%;">
                                                @lang(' Name') / الاسم :
                                            </td>
                                            <td style="padding: 10px;">{!! $receipt_details->customer_name !!}</td>
                                        </tr>
                                        @endif

                                        @if (!empty($receipt_details->customer_mobile))
                                        <tr style="border: 1px solid #ddd;">
                                            <td style="padding: 10px; font-weight: bold; width: 30%;">
                                                @lang(' Mobile') / الجوال :
                                            </td>
                                            <td style="padding: 10px;">{!! $receipt_details->customer_mobile !!}</td>
                                        </tr>
                                        @endif

                                        @if (!empty($receipt_details->customer_tax_number))
                                        <tr style="border: 1px solid #ddd;">
                                            <td style="padding: 10px; font-weight: bold; width: 30%;">
                                                @if (!empty($receipt_details->customer_tax_label))
                                                {{ $receipt_details->customer_tax_label }}:
                                                @else
                                                @lang('Customer Tax Number') / رقم ضريبة العميل:
                                                @endif
                                            </td>
                                            <td style="padding: 10px;">{!! $receipt_details->customer_tax_number !!}
                                            </td>
                                        </tr>
                                        @endif





                                        @endif
                                        @if (!empty($receipt_details->client_id_label))
                                        <tr style="border: 1px solid #ddd;">
                                            <td style="padding: 10px; font-weight: bold;">
                                                @lang('Client ID') / رقم العميل:
                                            </td>
                                            <td style="padding: 10px;">{{ $receipt_details->client_id }}</td>
                                        </tr>
                                        @endif
                                        @if (!empty($receipt_details->customer_tax_label))
                                        <tr style="border: 1px solid #ddd;">
                                            <td style="padding: 10px; font-weight: bold;">
                                                @lang('Customer Tax') / الضريبة على العميل:
                                            </td>
                                            <td style="padding: 10px;">{{ $receipt_details->customer_tax_number }}</td>
                                        </tr>
                                        @endif
                                        @if (!empty($receipt_details->customer_custom_fields))
                                        <tr style="border: 1px solid #ddd;">
                                            <td style="padding: 10px; font-weight: bold;">
                                                @lang('Additional Information') / معلومات إضافية:
                                            </td>
                                            <td style="padding: 10px;">{!! $receipt_details->customer_custom_fields !!}
                                            </td>
                                        </tr>
                                        @endif
                                        @if (!empty($receipt_details->sales_person_label))
                                        <tr style="border: 1px solid #ddd;">
                                            <td style="padding: 10px; font-weight: bold;">
                                                @lang('Sales Person') / مندوب المبيعات:
                                            </td>
                                            <td style="padding: 10px;">{{ $receipt_details->sales_person }}</td>
                                        </tr>
                                        @endif
                                        @if (!empty($receipt_details->commission_agent_label))
                                        <tr style="border: 1px solid #ddd;">
                                            <td style="padding: 10px; font-weight: bold;">
                                                @lang('Commission Agent') / وكيل العمولة:
                                            </td>
                                            <td style="padding: 10px;">{{ $receipt_details->commission_agent }}</td>
                                        </tr>
                                        @endif
                                        @if (!empty($receipt_details->customer_rp_label))
                                        <tr style="border: 1px solid #ddd;">
                                            <td style="padding: 10px; font-weight: bold;">
                                                @lang('Customer RP') / رصيد العميل:
                                            </td>
                                            <td style="padding: 10px;">{{ $receipt_details->customer_total_rp }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>

                            </div>
                        </div>

                        <div class="row" style="color: #000000 !important; margin-bottom:10px;">
                            @includeIf('sale_pos.receipts.partial.common_repair_invoice')
                        </div>
                        <div style="margin-bottom: 10px">
                            <div class="row" style="color: #000000 !important;">
                                <div class="col-xs-12" style="margin-top: 25px;">
                                    <br />
                                    @php
                                    $p_width = 45;
                                    @endphp
                                    @if (!empty($receipt_details->item_discount_label))
                                    @php
                                    $p_width -= 10;
                                    @endphp
                                    @endif
                                    @if (!empty($receipt_details->discounted_unit_price_label))
                                    @php
                                    $p_width -= 10;
                                    @endphp
                                    @endif
                                    <table class="table table-responsive table-slim table-bordered main_table">
                                        <thead>
                                            <tr>
                                                <th class="text-center custom-bg" style="vertical-align: top;"
                                                    width="{{ $p_width }}%">
                                                    الملاحظات / Notes
                                                </th>
                                                <th class="text-center custom-bg" style="vertical-align: top;"
                                                    width="15%">
                                                    {{ $receipt_details->table_product_label }} / Product
                                                </th>
                                                <th class="text-center custom-bg" style="vertical-align: top;"
                                                    width="15%">
                                                    {{ $receipt_details->table_qty_label }} / Quantity
                                                </th>
                                                <th class="text-center custom-bg" style="vertical-align: top;"
                                                    width="15%">
                                                    {{ $receipt_details->table_unit_price_label }} / Unit Price
                                                </th>
                                                @if (!empty($receipt_details->discounted_unit_price_label))
                                                <th class="text-center custom-bg" style="vertical-align: top;"
                                                    width="10%">
                                                    {{ $receipt_details->discounted_unit_price_label }} / Discounted
                                                    Unit Price
                                                </th>
                                                @endif
                                                @if (!empty($receipt_details->item_discount_label))
                                                <th class="text-center custom-bg" style="vertical-align: top;"
                                                    width="10%">
                                                    {{ $receipt_details->item_discount_label }} / Item Discount
                                                </th>
                                                @endif
                                                <th class="text-center custom-bg" style="vertical-align: top;"
                                                    width="15%">
                                                    {{ $receipt_details->table_subtotal_label }} / Subtotal
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($receipt_details->lines as $line)
                                            <tr style="border-bottom: 1px solid #ddd;">
                                                <td class="text-end" style="padding: 10px; vertical-align: top;">
                                                    @if (!empty($line['product_description']))
                                                    <small>{!! $line['product_description'] !!}</small>
                                                    @else
                                                    <small></small>
                                                    @endif
                                                </td>
                                                <td class="text-end" style="padding: 10px; vertical-align: top;">
                                                    @if (!empty($line['image']))
                                                    <img src="{{ $line['image'] }}" alt="Image" width="50"
                                                        style="float: left; margin-right: 8px;">
                                                    @endif
                                                    <div>
                                                        {{ $line['name'] }} {{ $line['product_variation'] }} {{
                                                        $line['variation'] }}
                                                        @if (!empty($line['sub_sku']))
                                                        , {{ $line['sub_sku'] }}
                                                        @endif
                                                        @if (!empty($line['brand']))
                                                        , {{ $line['brand'] }}
                                                        @endif
                                                        @if (!empty($line['cat_code']))
                                                        , {{ $line['cat_code'] }}
                                                        @endif
                                                        @if (!empty($line['product_custom_fields']))
                                                        , {{ $line['product_custom_fields'] }}
                                                        @endif
                                                        @if (!empty($line['product_description']))
                                                        <small>{!! $line['product_description'] !!}</small>
                                                        @endif
                                                        @if (!empty($line['lot_number']))
                                                        <br> {{ $line['lot_number_label'] }}: {{ $line['lot_number']
                                                        }}
                                                        @endif
                                                        @if (!empty($line['product_expiry']))
                                                        , {{ $line['product_expiry_label'] }}: {{
                                                        $line['product_expiry'] }}
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-end" style="padding: 10px; vertical-align: top;">{{
                                                    $line['quantity'] }}
                                                    {{ $line['units'] }}
                                                </td>
                                                <td class="text-end" style="padding: 10px; vertical-align: top;">{{
                                                    $line['unit_price_before_discount'] }}</td>
                                                @if (!empty($receipt_details->discounted_unit_price_label))
                                                <td class="text-end" style="padding: 10px; vertical-align: top;">{{
                                                    $line['unit_price_inc_tax'] }}</td>
                                                @endif
                                                @if (!empty($receipt_details->item_discount_label))
                                                <td class="text-end" style="padding: 10px; vertical-align: top;">
                                                    {{ $line['total_line_discount'] ?? '0.00' }}
                                                    @if (!empty($line['line_discount_percent']))
                                                    ({{ $line['line_discount_percent'] }}%)
                                                    @endif
                                                </td>
                                                @endif
                                                <td class="text-end" style="padding: 10px; vertical-align: top;">{{
                                                    $line['line_total']
                                                    }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" style="padding: 10px;">&nbsp;</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                {{-- <td colspan="4" style="padding: 10px; border: 1px solid #d5d5d5;">
                                                </td> --}}
                                            </tr>
                                        </tfoot>
                                    </table>

                                </div>
                            </div>
                        </div>

                        {{-- <div class="row" style="color: #000000 !important;margin-bottom: 10px;">
                            <div class="col-md-12" style="height: 10px; padding-top:30px;">
                                <hr />
                            </div>
                        </div> --}}
                        <div class="row" style="color: #000000 !important;">
                            <!-- Payments Table -->
                            <div class="col-xs-6">
                                <table class="table table-slim table-bordered">
                                    @if (!empty($receipt_details->payments))
                                    @foreach ($receipt_details->payments as $payment)
                                    <tr>
                                        <td class="custom-bg">{{ $payment['method'] }}</td>
                                        <td class="text-end">{{ $payment['amount'] }}</td>
                                        <td class="text-end">{{ $payment['date'] }}</td>
                                    </tr>
                                    @endforeach
                                    @endif

                                    <!-- Total Paid -->
                                    @if (!empty($receipt_details->total_paid))
                                    <tr>
                                        <th class="custom-bg">{{ $receipt_details->total_paid_label }}</th>
                                        <td class="text-end">{{ $receipt_details->total_paid }}</td>
                                    </tr>
                                    @endif

                                    <!-- Total Due -->
                                    @if (!empty($receipt_details->total_due) &&
                                    !empty($receipt_details->total_due_label))
                                    <tr>
                                        <th class="custom-bg">{{ $receipt_details->total_due_label }}</th>
                                        <td class="text-end">{{ $receipt_details->total_due }}</td>
                                    </tr>
                                    @endif

                                    <!-- All Due -->
                                    @if (!empty($receipt_details->all_due))
                                    <tr>
                                        <th class="custom-bg">{{ $receipt_details->all_bal_label }}</th>
                                        <td class="text-end">{{ $receipt_details->all_due }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            <!-- Receipt Summary Table -->
                            <div class="col-xs-6">
                                <div class="table-responsive">
                                    <table class="table table-slim table-bordered">
                                        <tbody>
                                            <!-- Total Quantity -->
                                            @if (!empty($receipt_details->total_quantity_label))
                                            <tr>
                                                <th style="width:70%" class="custom-bg">{{
                                                    $receipt_details->total_quantity_label }}</th>
                                                <td class="text-end custom-bg">{{ $receipt_details->total_quantity
                                                    }}</td>
                                            </tr>
                                            @endif

                                            <!-- Total Items -->
                                            @if (!empty($receipt_details->total_items_label))
                                            <tr>
                                                <th style="width:70%" class="custom-bg">{{
                                                    $receipt_details->total_items_label }}</th>
                                                <td class="text-end">{{ $receipt_details->total_items }}</td>
                                            </tr>
                                            @endif

                                            <!-- Subtotal -->
                                            <tr>
                                                <th style="width:70%" class="custom-bg">{{
                                                    $receipt_details->subtotal_label }}</th>
                                                <td class="text-end">{{ $receipt_details->subtotal }}</td>
                                            </tr>

                                            <!-- Exempt -->
                                            @if (!empty($receipt_details->total_exempt_uf))
                                            <tr>
                                                <th style="width:70%" class="custom-bg">@lang('lang_v1.exempt')</th>
                                                <td class="text-end">{{ $receipt_details->total_exempt }}</td>
                                            </tr>
                                            @endif

                                            <!-- Shipping Charges -->
                                            @if (!empty($receipt_details->shipping_charges))
                                            <tr>
                                                <th style="width:70%" class="custom-bg">{{
                                                    $receipt_details->shipping_charges_label }}</th>
                                                <td class="text-end">{{ $receipt_details->shipping_charges }}</td>
                                            </tr>
                                            @endif

                                            <!-- Packing Charge -->
                                            @if (!empty($receipt_details->packing_charge))
                                            <tr>
                                                <th style="width:70%" class="custom-bg">{{
                                                    $receipt_details->packing_charge_label }}</th>
                                                <td class="text-end">{{ $receipt_details->packing_charge }}</td>
                                            </tr>
                                            @endif

                                            <!-- Discount -->
                                            @if (!empty($receipt_details->discount))
                                            <tr>
                                                <th class="custom-bg">{{ $receipt_details->discount_label }}</th>
                                                <td class="text-end">(-) {{ $receipt_details->discount }}</td>
                                            </tr>
                                            @endif

                                            <!-- Line Discount -->
                                            @if (!empty($receipt_details->total_line_discount))
                                            <tr>
                                                <th class="custom-bg">{{ $receipt_details->line_discount_label }}
                                                </th>
                                                <td class="text-end">(-) {{ $receipt_details->total_line_discount }}
                                                </td>
                                            </tr>
                                            @endif

                                            <!-- Additional Expenses -->
                                            @if (!empty($receipt_details->additional_expenses))
                                            @foreach ($receipt_details->additional_expenses as $key => $val)
                                            <tr>
                                                <td>{{ $key }}:</td>
                                                <td class="text-end">(+)
                                                    {{ $val }}
                                                </td>
                                            </tr>
                                            @endforeach
                                            @endif

                                            <!-- Reward Points -->
                                            @if (!empty($receipt_details->reward_point_label))
                                            <tr>
                                                <th class="custom-bg">{{ $receipt_details->reward_point_label }}
                                                </th>
                                                <td class="text-end">(-) {{ $receipt_details->reward_point_amount }}
                                                </td>
                                            </tr>
                                            @endif

                                            <!-- Tax -->
                                            @if (!empty($receipt_details->tax))
                                            <tr>
                                                <th class="custom-bg">{{ $receipt_details->tax_label }}</th>
                                                <td class="text-end">(+)
                                                    {{ $receipt_details->tax }}
                                                </td>
                                            </tr>
                                            @endif

                                            <!-- Round Off -->
                                            @if ($receipt_details->round_off_amount > 0)
                                            <tr>
                                                <th class="custom-bg">{{ $receipt_details->round_off_label }}</th>
                                                <td class="text-end">{{ $receipt_details->round_off }}</td>
                                            </tr>
                                            @endif

                                            <!-- Total -->
                                            <tr>
                                                <th class="custom-bg">{{ $receipt_details->total_label }}</th>
                                                <td class="text-end">{{ $receipt_details->total }}
                                                    @if (!empty($receipt_details->total_in_words))
                                                    <br>
                                                    <small>({{ $receipt_details->total_in_words }})</small>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tax Summary -->
                            <div class="border-bottom col-md-12">
                                @if (empty($receipt_details->hide_price) &&
                                !empty($receipt_details->tax_summary_label))
                                @if (!empty($receipt_details->taxes))
                                <table class="table table-slim table-bordered">
                                    <tr>
                                        <th colspan="2" class="text-center custom-bg">{{
                                            $receipt_details->tax_summary_label }}</th>
                                    </tr>
                                    @foreach ($receipt_details->taxes as $key => $val)
                                    <tr>
                                        <td class="text-center"><b>{{ $key }}</b></td>
                                        <td class="text-center">{{ $val }}</td>
                                    </tr>
                                    @endforeach
                                </table>
                                @endif
                                @endif
                            </div>

                            <!-- Additional Notes -->
                            @if (!empty($receipt_details->additional_notes))
                            <div class="col-xs-12">
                                <p>{!! nl2br($receipt_details->additional_notes) !!}</p>
                            </div>
                            @endif
                        </div>

                        <div class="row footer-text" style="color: #000000 !important;">
                            @if (!empty($receipt_details->footer_text))
                            <div class="@if ($receipt_details->show_barcode || $receipt_details->show_qr_code) col-xs-8 @else col-xs-12 @endif"
                                style="width: 100%;">
                                {!! $receipt_details->footer_text !!}
                            </div>
                            @endif
                        </div>

                        <div class="row footer-text" style="color: #000000 !important;">
                            @if (!empty($receipt_details->footer_text))
                            <div class="@if ($receipt_details->show_barcode || $receipt_details->show_qr_code) col-xs-8 @else col-xs-12 @endif"
                                style="width: 100%;">
                                {!! $receipt_details->footer_text !!}
                            </div>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td>
                    <!--place holder for the fixed-position footer-->
                    <div class="page-footer-space"></div>
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- <div style="margin: 20px auto; width: 90%; text-align: center; font-family: Arial, sans-serif;">
        <h4 style="margin-bottom: 20px; font-weight: bold;">يرجي تحويل المبلغ المستحق علي بيانات الحساب البنكي التالي
        </h4>
        <h4 style="margin-bottom: 20px; font-weight: bold;">Please remit the amount due to our bank account as per the
            below details</h4>
        <table class="table table-responsive table-slim table-bordered main_table"
            style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;">
            <thead>
                <tr>
                    <!-- Table Header -->
                    <th class="custom-bg text-start"
                        style="padding: 10px; background-color: #f1f1f1; font-weight: bold;">
                        <span class="d-block text-end">اسم البنك</span>
                    </th>
                    <th class="custom-bg text-center"
                        style="padding: 10px; background-color: #f1f1f1; font-weight: bold;">
                        <span class="d-block text-center">{{ $receipt_details->bank_name ?? 'Demo Bank' }}</span>
                    </th>
                    <th class="custom-bg text-center"
                        style="padding: 10px; background-color: #f1f1f1; font-weight: bold;">
                        <span>{{ $receipt_details->bank_name ?? 'Bank Name' }}</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <!-- Bank Name -->
                    <th class="custom-bg text-start" style="padding: 10px; background-color: #f1f1f1;">
                        <span class="d-block text-end">اسم البنك</span>
                    </th>
                    <td class="custom-bg text-center" style="padding: 10px; background-color: #ffffff;">
                        <span class="d-block text-center">
                            @if (app()->getLocale() == 'ar')
                            {{ $receipt_details->bank_name ?? 'Demo Bank' }}
                            @else
                            {{ $receipt_details->bank_name ?? 'Demo Bank' }}
                            @endif
                        </span>
                    </td>
                    <td class="custom-bg text-center" style="padding: 10px; background-color: #f1f1f1;">
                        <span>{{ $receipt_details->bank_name ?? 'Bank Name' }}</span>
                    </td>
                </tr>

                <tr>
                    <!-- Account Number -->
                    <th class="custom-bg text-start" style="padding: 10px; background-color: #f1f1f1;">
                        <span class="d-block text-end">رقم الحساب</span>
                    </th>
                    <td class="custom-bg text-center" style="padding: 10px; background-color: #ffffff;">
                        <span class="d-block text-center">{{ $receipt_details->account_number ?? '1234567890' }}</span>
                    </td>
                    <td class="custom-bg text-center" style="padding: 10px; background-color: #f1f1f1;">
                        <span>{{ $receipt_details->account_number ?? 'Account Number' }}</span>
                    </td>
                </tr>

                <tr>
                    <!-- IBAN -->
                    <th class="custom-bg text-start" style="padding: 10px; background-color: #f1f1f1;">
                        <span class="d-block text-end">رقم الآيبان</span>
                    </th>
                    <td class="custom-bg text-center" style="padding: 10px; background-color: #ffffff;">
                        <span class="d-block text-center">{{ $receipt_details->iban ?? 'DE89370400440532013000'
                            }}</span>
                    </td>
                    <td class="custom-bg text-center" style="padding: 10px; background-color: #f1f1f1;">
                        <span>{{ $receipt_details->iban ?? 'IBAN' }}</span>
                    </td>
                </tr>

                <tr>
                    <!-- SWIFT Code -->
                    <th class="custom-bg text-start" style="padding: 10px; background-color: #f1f1f1;">
                        <span class="d-block text-end">رمز السويفت</span>
                    </th>
                    <td class="custom-bg text-center" style="padding: 10px; background-color: #ffffff;">
                        <span class="d-block text-center">{{ $receipt_details->swift_code ?? 'DEUTDEFF' }}</span>
                    </td>
                    <td class="custom-bg text-center" style="padding: 10px; background-color: #f1f1f1;">
                        <span>{{ $receipt_details->swift_code ?? 'SWIFT Code' }}</span>
                    </td>
                </tr>
            </tbody>
        </table>

    </div> --}}
    @if (!empty($receipt_details->letter_footer))
    <div class="page-footer">
        <img id="footer-image" width="100%" src="{{ $receipt_details->letter_footer }}" alt="footer">
    </div>
    @endif
</body>