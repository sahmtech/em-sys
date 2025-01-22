<style>
    body {
        margin: 0;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        /* الصفحة تغطي الشاشة بالكامل */
        font-family: Arial, sans-serif;
    }

    .footer-container {
        margin-top: 50%;
        /* يجعل الفوتر دائمًا في الأسفل */
        padding: 10px 10px;
        /* مسافة على اليمين واليسار */
        width: 100%;
        background-color: #f9f9f9;
        /* لون خلفية الفوتر */
        box-sizing: border-box;
        /* تضمين الحواف داخل العرض */
    }

    .footer-table {
        margin: auto;
        /* توسيط الجدول داخل الفوتر */
        width: 90%;
        /* عرض الجدول */
        border-collapse: collapse;
        /* إزالة الفراغات بين الحدود */
    }

    .footer-table th,
    .footer-table td {
        border: 1px solid #ccc;
        /* لون حدود الخلايا */
        padding: 10px;
        /* مسافة داخل الخلايا */
        text-align: center;
        /* توسيط النصوص داخل الخلايا */
    }

    .footer-table th {
        background-color: #818181;
        /* لون خلفية العناوين */
        font-weight: bold;
        /* خط سميك */
    }

    .footer-table td:first-child,
    .footer-table td:last-child {
        background-color: #f0f8ff;
        /* لون خلفية الأعمدة الأولى والأخيرة */
    }

    ul li {
        text-align: start;
    }

    ul {
        text-align: right;
        text-decoration: rtl;
    }

    .table .custom-bg {
        background-color: #f1f1f1 !important;
        -webkit-print-color-adjust: exact;
    }

    .table {
        margin-bottom: 5px;
    }

    .table-slim,
    .table-slim td,
    .table-slim th {
        border: 0.5px solid #d5d5d5 !important;
        padding: 2px 4px !important;
    }

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
                                    @if (!empty($receipt_details->company_info))
                                    {{-- {{ $receipt_details->company_info }} --}}
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

                            <div class="row col-xs-12 text-center flex">
                                <!-- Invoice  number, Date  -->

                                <span style="width: 100%" class="pull-left text-left word-wrap">
                                    @if (!empty($receipt_details->invoice_no_prefix))
                                    <b>{!! $receipt_details->invoice_no_prefix !!}</b>
                                    @endif
                                    {{ $receipt_details->invoice_no }}

                                    @if (!empty($receipt_details->invoice_no_prefix))
                                    <br>
                                    {!! $receipt_details->date_label !!}
                                    @endif

                                    {{ $receipt_details->invoice_date }}
                                    </br>

                                    @if (!empty($receipt_details->types_of_service))
                                    <br />
                                    <span class="pull-left text-left">
                                        <strong>{!! $receipt_details->types_of_service_label !!}:</strong>
                                        {{ $receipt_details->types_of_service }}
                                        <!-- Waiter info -->
                                        @if (!empty($receipt_details->types_of_service_custom_fields))
                                        @foreach ($receipt_details->types_of_service_custom_fields as $key => $value)
                                        <br><strong>{{ $key }}: </strong> {{ $value }}
                                        @endforeach
                                        @endif
                                    </span>

                                    @endif

                                    <!-- Table information-->
                                    @if (!empty($receipt_details->table_label) || !empty($receipt_details->table))
                                    <br />
                                    <span class="pull-left text-left">
                                        @if (!empty($receipt_details->table_label))
                                        <b>{!! $receipt_details->table_label !!}</b>
                                        @endif
                                        {{ $receipt_details->table }}

                                        <!-- Waiter info -->
                                    </span>
                                    @endif

                                    <!-- customer info -->
                                    @if (!empty($receipt_details->customer_info))
                                    <br />
                                    <b>{{ $receipt_details->customer_label }}</b> <br> {!!
                                    $receipt_details->customer_info !!}
                                    @endif
                                    @if (!empty($receipt_details->client_id_label))
                                    <br />
                                    <b>{{ $receipt_details->client_id_label }}</b>
                                    {{ $receipt_details->client_id }}
                                    @endif
                                    @if (!empty($receipt_details->customer_tax_label))
                                    <br />
                                    <b>{{ $receipt_details->customer_tax_label }}</b>
                                    {{ $receipt_details->customer_tax_number }}
                                    @endif
                                    @if (!empty($receipt_details->customer_custom_fields))
                                    <br />{!! $receipt_details->customer_custom_fields !!}
                                    @endif
                                    @if (!empty($receipt_details->sales_person_label))
                                    <br />
                                    <b>{{ $receipt_details->sales_person_label }}</b>
                                    {{ $receipt_details->sales_person }}
                                    @endif
                                    @if (!empty($receipt_details->commission_agent_label))
                                    <br />
                                    <strong>{{ $receipt_details->commission_agent_label }}</strong>
                                    {{ $receipt_details->commission_agent }}
                                    @endif
                                    @if (!empty($receipt_details->customer_rp_label))
                                    <br />
                                    <strong>{{ $receipt_details->customer_rp_label }}</strong>
                                    {{ $receipt_details->customer_total_rp }}
                                    @endif
                                </span>

                                @if ($receipt_details->show_barcode || $receipt_details->show_qr_code)
                                <div class="text-center" style="width: 100%">
                                    @if ($receipt_details->show_barcode)
                                    {{-- Barcode --}}
                                    <img class="center-block"
                                        src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, [39, 48, 54], true) }}">
                                    @endif

                                    @if ($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
                                    <img class="center-block"
                                        src="data:image/png;base64,{{ DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54]) }}">
                                    @endif
                                </div>
                                @endif

                                <span class="pull-right text-left"
                                    style="width: 100%;display: flex;justify-content: end;">
                                    <div>
                                        @if (!empty($receipt_details->company_info))
                                        <span style="float: right;"><b>{{ __(' الشركة') }} : <span>{{
                                                    $receipt_details->company_info }}</span>
                                            </b></span>
                                        <br>
                                        @endif

                                        @if (!empty($receipt_details->company_info_state))
                                        <span style="float: right;"><b>{{ __('المنطقة') }} : <span>{{
                                                    $receipt_details->company_info_state }}</span>
                                            </b></span>
                                        <br>
                                        @endif

                                        @if (!empty($receipt_details->company_info_landmark))
                                        <span style="float: right;"><b>{{ __('المعلم') }} : <span>{{
                                                    $receipt_details->company_info_landmark }}</span>
                                            </b></span>
                                        <br>
                                        @endif


                                    </div>


                                </span>
                                {{-- <p style="width: 100%" class="word-wrap">
                                </p> --}}
                            </div>
                            <div class="row" style="color: #000000 !important; margin-bottom:10px;">
                                @includeIf('sale_pos.receipts.partial.common_repair_invoice')
                            </div>
                            <div style="margin-bottom: 10px">
                                <div class="row" style="color: #000000 !important;">
                                    <div class="col-xs-12" style="margin-top: 5px;">
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
                                                        width="20%">

                                                        {{ $receipt_details->table_product_label }}</th>
                                                    <th class="text-center custom-bg" style="vertical-align: top;"
                                                        width="15%">
                                                        {{ $receipt_details->table_qty_label }}</th>
                                                    <th class="text-center custom-bg" style="vertical-align: top;"
                                                        width="15%">
                                                        {{ $receipt_details->table_unit_price_label }}</th>
                                                    @if (!empty($receipt_details->discounted_unit_price_label))
                                                    <th class="text-center custom-bg" style="vertical-align: top;"
                                                        width="10%">
                                                        {{ $receipt_details->discounted_unit_price_label }}</th>
                                                    @endif
                                                    @if (!empty($receipt_details->item_discount_label))
                                                    <th class="text-center custom-bg" style="vertical-align: top;"
                                                        width="10%">
                                                        {{ $receipt_details->item_discount_label }}</th>
                                                    @endif
                                                    <th class="text-center custom-bg" style="vertical-align: top;"
                                                        width="15%">
                                                        {{ $receipt_details->table_subtotal_label }}</th>
                                                    <th class="text-center custom-bg" style="vertical-align: top ;
                                                         width=" 20%>
                                                        الوصف</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($receipt_details->lines as $line)
                                                <tr>
                                                    <td class=" text-center">
                                                        @if (!empty($line['image']))
                                                        <img src="{{ $line['image'] }}" alt="Image" width="50"
                                                            style="float: left; margin-right: 8px;">
                                                        @endif
                                                        {{ $line['name'] }} {{ $line['product_variation'] }}
                                                        {{ $line['variation'] }}
                                                        @if (!empty($line['sub_sku']))
                                                        , {{ $line['sub_sku'] }}
                                                        @endif @if (!empty($line['brand']))
                                                        , {{ $line['brand'] }}
                                                        @endif @if (!empty($line['cat_code']))
                                                        , {{ $line['cat_code'] }}
                                                        @endif
                                                        @if (!empty($line['product_custom_fields']))
                                                        , {{ $line['product_custom_fields'] }}
                                                        @endif
                                                        @if (!empty($line['product_description']))
                                                        <small>
                                                            {!! $line['product_description'] !!}
                                                        </small>
                                                        @endif
                                                        @if (!empty($line['sell_line_note']))
                                                        <br>
                                                        <small>
                                                            {!! $line['sell_line_note'] !!}
                                                        </small>
                                                        @endif
                                                        @if (!empty($line['lot_number']))
                                                        <br> {{ $line['lot_number_label'] }}:
                                                        {{ $line['lot_number'] }}
                                                        @endif
                                                        @if (!empty($line['product_expiry']))
                                                        , {{ $line['product_expiry_label'] }}:
                                                        {{ $line['product_expiry'] }}
                                                        @endif

                                                        @if (!empty($line['warranty_name']))
                                                        <br><small>{{ $line['warranty_name'] }}
                                                        </small>
                                                        @endif @if (!empty($line['warranty_exp_date']))
                                                        <small>-
                                                            {{ @format_date($line['warranty_exp_date']) }}
                                                        </small>
                                                        @endif
                                                        @if (!empty($line['warranty_description']))
                                                        <small>
                                                            {{ $line['warranty_description'] ?? '' }}</small>
                                                        @endif

                                                        @if ($receipt_details->show_base_unit_details &&
                                                        $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                                                        <br><small>
                                                            1 {{ $line['units'] }} =
                                                            {{ $line['base_unit_multiplier'] }}
                                                            {{ $line['base_unit_name'] }} <br>
                                                            {{ $line['base_unit_price'] }} x
                                                            {{ $line['orig_quantity'] }}
                                                            =
                                                            {{ $line['line_total'] }}
                                                        </small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        {{ $line['quantity'] }} {{ $line['units'] }}

                                                        @if ($receipt_details->show_base_unit_details &&
                                                        $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                                                        <br><small>
                                                            {{ $line['quantity'] }} x
                                                            {{ $line['base_unit_multiplier'] }} =
                                                            {{ $line['orig_quantity'] }}
                                                            {{ $line['base_unit_name'] }}
                                                        </small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $line['unit_price_before_discount'] }}
                                                    </td>
                                                    @if (!empty($receipt_details->discounted_unit_price_label))
                                                    <td class="text-center">
                                                        {{ $line['unit_price_inc_tax'] }}
                                                    </td>
                                                    @endif
                                                    @if (!empty($receipt_details->item_discount_label))
                                                    <td class="text-center">
                                                        {{ $line['total_line_discount'] ?? '0.00' }}

                                                        @if (!empty($line['line_discount_percent']))
                                                        ({{ $line['line_discount_percent'] }}%)
                                                        @endif
                                                    </td>
                                                    @endif
                                                    <td class="text-center">{{ $line['line_total'] }}</td>
                                                    <td class="text-center">
                                                        {{ $line['product_description']??'' }}
                                                    </td>

                                                </tr>
                                                @if (!empty($line['modifiers']))
                                                @foreach ($line['modifiers'] as $modifier)
                                                <tr>
                                                    <td>
                                                        {{ $modifier['name'] }}
                                                        {{ $modifier['variation'] }}
                                                        @if (!empty($modifier['sub_sku']))
                                                        , {{ $modifier['sub_sku'] }}
                                                        @endif @if (!empty($modifier['cat_code']))
                                                        , {{ $modifier['cat_code'] }}
                                                        @endif
                                                        @if (!empty($modifier['sell_line_note']))
                                                        ({!! $modifier['sell_line_note'] !!})
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $modifier['quantity'] }}
                                                        {{ $modifier['units'] }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ $modifier['unit_price_inc_tax'] }}
                                                    </td>
                                                    @if (!empty($receipt_details->discounted_unit_price_label))
                                                    <td class="text-center">
                                                        {{ $modifier['unit_price_exc_tax'] }}</td>
                                                    @endif
                                                    @if (!empty($receipt_details->item_discount_label))
                                                    <td class="text-center">0.00</td>
                                                    @endif
                                                    <td class="text-center">{{ $modifier['line_total'] }}</td>

                                                </tr>
                                                @endforeach
                                                @endif
                                                @empty
                                                <tr>
                                                    <td colspan="4">&nbsp;</td>
                                                    @if (!empty($receipt_details->discounted_unit_price_label))
                                                    <td></td>
                                                    @endif
                                                    @if (!empty($receipt_details->item_discount_label))
                                                    <td></td>
                                                    @endif
                                                </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr style="border: 1px solid #ffff;">
                                                    <td colspan="4"
                                                        style="padding: 10px !important; border: 1px solid #ffff !important;">
                                                    </td>
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
                                <div class="col-xs-6" style=" margin-top: 1px;">
                                    <h5 style="text-align: right;padding-right:10px;"> الشروط والأحكام : </h5>
                                    <ul>
                                        <li>جميع المبالغ المدفوعة غير قابلة للاسترداد إلا في حالة .</li>
                                        <li>يجب على العميل مراجعة تفاصيل الفاتورة والتأكد من صحتها قبل .</li>
                                        <li>تحتفظ الشركة بحق تعديل الأسعار أو السياسات دون إشعار .</li>

                                    </ul>
                                </div>



                                <div class="col-xs-6">
                                    <div class="table-responsive">
                                        <table class="table table-slim table-bordered">
                                            <tbody>
                                                @if (!empty($receipt_details->total_quantity_label))
                                                <tr>
                                                    <th style="width:70%" class="custom-bg">
                                                        {!! $receipt_details->total_quantity_label !!}
                                                    </th>
                                                    <td class="text-center" class="custom-bg">
                                                        {{ $receipt_details->total_quantity }}
                                                    </td>
                                                </tr>
                                                @endif

                                                @if (!empty($receipt_details->total_items_label))
                                                <tr>
                                                    <th style="width:70%" class="custom-bg">
                                                        {!! $receipt_details->total_items_label !!}
                                                    </th>
                                                    <td class="text-center">
                                                        {{ $receipt_details->total_items }}
                                                    </td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <th style="width:70%" class="custom-bg">
                                                        {!! $receipt_details->subtotal_label !!}
                                                    </th>
                                                    <td class="text-center">
                                                        {{ $receipt_details->subtotal }}
                                                    </td>
                                                </tr>
                                                @if (!empty($receipt_details->total_exempt_uf))
                                                <tr>
                                                    <th style="width:70%" class="custom-bg">
                                                        @lang('lang_v1.exempt')
                                                    </th>
                                                    <td class="text-center">
                                                        {{ $receipt_details->total_exempt }}
                                                    </td>
                                                </tr>
                                                @endif
                                                <!-- Shipping Charges -->
                                                @if (!empty($receipt_details->shipping_charges))
                                                <tr>
                                                    <th style="width:70%" class="custom-bg">
                                                        {!! $receipt_details->shipping_charges_label !!}
                                                    </th>
                                                    <td class="text-center">
                                                        {{ $receipt_details->shipping_charges }}
                                                    </td>
                                                </tr>
                                                @endif

                                                @if (!empty($receipt_details->packing_charge))
                                                <tr>
                                                    <th style="width:70%" class="custom-bg">
                                                        {!! $receipt_details->packing_charge_label !!}
                                                    </th>
                                                    <td class="text-center">
                                                        {{ $receipt_details->packing_charge }}
                                                    </td>
                                                </tr>
                                                @endif

                                                <!-- Discount -->
                                                @if (!empty($receipt_details->discount))
                                                <tr>
                                                    <th class="custom-bg">
                                                        {!! $receipt_details->discount_label !!}
                                                    </th>

                                                    <td class="text-center">
                                                        (-) {{ $receipt_details->discount }}
                                                    </td>
                                                </tr>
                                                @endif

                                                @if (!empty($receipt_details->total_line_discount))
                                                <tr>
                                                    <th class="custom-bg">
                                                        {!! $receipt_details->line_discount_label !!}
                                                    </th>

                                                    <td class="text-center">
                                                        (-) {{ $receipt_details->total_line_discount }}
                                                    </td>
                                                </tr>
                                                @endif

                                                @if (!empty($receipt_details->additional_expenses))
                                                @foreach ($receipt_details->additional_expenses as $key => $val)
                                                <tr>
                                                    <td>
                                                        {{ $key }}:
                                                    </td>

                                                    <td class="text-center">
                                                        (+)
                                                        {{ $val }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                                @endif

                                                @if (!empty($receipt_details->reward_point_label))
                                                <tr>
                                                    <th class="custom-bg">
                                                        {!! $receipt_details->reward_point_label !!}
                                                    </th>

                                                    <td class="text-center">
                                                        (-) {{ $receipt_details->reward_point_amount }}
                                                    </td>
                                                </tr>
                                                @endif

                                                <!-- Tax -->
                                                @if (!empty($receipt_details->tax))
                                                <tr>
                                                    <th class="custom-bg">
                                                        {!! $receipt_details->tax_label !!}
                                                    </th>
                                                    <td class="text-center">
                                                        (+) {{ $receipt_details->tax }}
                                                    </td>
                                                </tr>
                                                @endif

                                                @if ($receipt_details->round_off_amount > 0)
                                                <tr>
                                                    <th class="custom-bg">
                                                        {!! $receipt_details->round_off_label !!}
                                                    </th>
                                                    <td class="text-center">
                                                        {{ $receipt_details->round_off }}
                                                    </td>
                                                </tr>
                                                @endif

                                                <!-- Total -->
                                                <tr>
                                                    <th class="custom-bg">
                                                        {!! $receipt_details->total_label !!}
                                                    </th>
                                                    <td class="text-center">
                                                        {{ $receipt_details->total }}
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

                                <div class="border-bottom col-md-12">
                                    @if (empty($receipt_details->hide_price) &&
                                    !empty($receipt_details->tax_summary_label))
                                    <!-- tax -->
                                    @if (!empty($receipt_details->taxes))
                                    <table class="table table-slim table-bordered">
                                        <tr>
                                            <th colspan="2" class="text-center custom-bg">
                                                {{ $receipt_details->tax_summary_label }}
                                            </th>
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

    <div class="footer-container">
        <table class="table table-bordered table-sm text-center table-slim footer-table">
            <thead>
                <tr>
                    <th style="width: 30%;" class="custom-bg">اسم البنك</th> <!-- Arabic label -->
                    <th style="width: 40%;">Bank Al Riyadh</th> <!-- Centered value -->
                    <th style="width: 30%;" class="custom-bg">Bank Name</th> <!-- English label -->
                </tr>
            </thead>
            <tbody>
                <!-- Bank Name -->
                <tr>
                    <td class="custom-bg">رقم الحساب</td> <!-- Arabic label -->
                    <td>1234567890</td> <!-- Value -->
                    <td class="custom-bg">Account Number</td> <!-- English label -->
                </tr>
                <!-- Account Number -->
                <tr>
                    <td class="custom-bg">الآيبان</td> <!-- Arabic label -->
                    <td>SA12345678901234567890</td> <!-- Value -->
                    <td class="custom-bg">IBAN</td> <!-- English label -->
                </tr>
                <!-- Beneficiary Name -->
                <tr>
                    <td class="custom-bg">اسم المستفيد</td> <!-- Arabic label -->
                    <td>شركة امدادات العطاء للتجارة والمقاولات</td> <!-- Value -->
                    <td class="custom-bg">Beneficiary Name</td> <!-- English label -->
                </tr>
            </tbody>
        </table>
    </div>

    @if (!empty($receipt_details->letter_footer))
    <div class="page-footer">
        <img id="footer-image" width="100%" src="{{ $receipt_details->letter_footer }}" alt="footer">
    </div>
    @endif
</body>