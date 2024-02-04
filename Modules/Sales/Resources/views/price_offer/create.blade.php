@extends('layouts.app')


@section('title', __('lang_v1.add_quotation'))
<style>
    .larger-text {
        font-size: larger;
    }

    .custom-width-input {
        width: 50px;
    }
</style>
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.add_quotation')</h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
        @if (!empty($pos_settings['allow_overselling']))
            <input type="hidden" id="is_overselling_allowed">
        @endif
        @if (session('business.enable_rp') == 1)
            <input type="hidden" id="reward_point_enabled">
        @endif
        {{-- @if (count($business_locations) > 0)
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                            </span>
                            {!! Form::select(
                                'select_location_id',
                                $business_locations,
                                $default_location->id ?? null,
                                [
                                    'class' => 'form-control input-sm',
                                    'id' => 'select_location_id',
                                    'style' => 'height:36px;',
                                    'required',
                                    'autofocus',
                                ],
                                $bl_attributes,
                            ) !!}

                            <span class="input-group-addon">
                                @show_tooltip(__('tooltip.sale_location'))
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif --}}

        @php
            $custom_labels = json_decode(session('business.custom_labels'), true);
            $common_settings = session()->get('business.common_settings');
        @endphp
        <input type="hidden" id="item_addition_method" value="{{ $business_details->item_addition_method }}">
        {!! Form::open([
            'url' => action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'store']),
            'method' => 'post',
            'id' => 'add_sell_form',
        ]) !!}
        @if (!empty($sale_type))
            <input type="hidden" id="sale_type" name="type" value="{{ $sale_type }}">
        @endif
        <div class="row">
            <div class="col-md-12 col-sm-12">
                @component('components.widget', ['class' => 'box-solid'])
                    {!! Form::hidden('location_id', !empty($default_location) ? $default_location->id : null, [
                        'id' => 'location_id',
                        'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                            ? $default_location->receipt_printer_type
                            : 'browser',
                        'data-default_payment_accounts' => !empty($default_location) ? $default_location->default_payment_accounts : '',
                    ]) !!}


                    {!! Form::hidden('default_price_group', null, ['id' => 'default_price_group']) !!}

                    <div class="clearfix"></div>
                    <div class="@if (!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
                        <div class="form-group">
                            <div class="form-group col-md-10">
                                {!! Form::label('contact_id', __('sales::lang.project_name') . ':*') !!}
                                @if (!empty($id))
                                    {!! Form::select('contact_id', $leads, $id, [
                                        'class' => 'form-control',
                                        'style' => 'height:40px',
                                        // 'placeholder' => __('sales::lang.select_project'),
                                        'required',
                                        // 'disabled' => true,
                                    ]) !!}
                                @else
                                    {!! Form::select('contact_id', $leads, null, [
                                        'class' => 'form-control',
                                        'style' => 'height:40px',
                                        'placeholder' => __('sales::lang.select_project'),
                                        'required',
                                    ]) !!}
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('contract_form', __('sales::lang.contract_form') . ':*') !!}
                            {!! Form::select(
                                'contract_form',
                                ['monthly_cost' => __('sales::lang.monthly_cost'), 'operating_fees' => __('sales::lang.operating_fees')],
                                null,
                                [
                                    'id' => 'contract_form',
                                    'class' => 'form-control',
                                    'required',
                                    'style' => 'height:40px',
                                    'placeholder' => __('sales::lang.contract_form'),
                                ],
                            ) !!}

                        </div>
                    </div>
                    {{-- <div class="@if (!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
                        <div class="form-group">
                            {!! Form::label('status', __('sale.status') . ':*') !!}
                            {!! Form::select(
                                'status',
                                [
                                    'approved' => __('sales::lang.approved'),
                                    'transfared' => __('sales::lang.transfared'),
                                    'cancelled' => __('sales::lang.cancelled'),
                                    'under_study' => __('sales::lang.under_study'),
                                ],
                                null,
                                ['class' => 'form-control', 'required', 'style' => 'height:40px', 'placeholder' => __('sale.status')],
                            ) !!}
                        </div>
                    </div> --}}
                    <div class="clearfix"></div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('down_payment', __('sales::lang.down_payment') . ':*') !!}
                            {!! Form::Number('down_payment', null, [
                                'class' => 'form-control',
                                'required',
                                'style' => 'height:40px',
                                'placeholder' => __('sales::lang.down_payment'),
                            ]) !!}
                        </div>
                    </div>


                    <div class="@if (!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
                        <div class="form-group">
                            {!! Form::label('transaction_date', __('sale.sale_date') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('transaction_date', $default_datetime, [
                                    'class' => 'form-control',
                                    'style' => 'height:40px',
                                    'readonly',
                                    'required',
                                ]) !!}
                            </div>
                        </div>
                    </div>

                    @if (!empty($commission_agent))
                        @php
                            $is_commission_agent_required = !empty($pos_settings['is_commission_agent_required']);
                        @endphp
                        <div class="col-sm-3">
                            <div class="form-group">
                                {!! Form::label('commission_agent', __('lang_v1.commission_agent') . ':') !!}
                                {!! Form::select('commission_agent', $commission_agent, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'height:40px',
                                    'id' => 'commission_agent',
                                    'required' => $is_commission_agent_required,
                                ]) !!}
                            </div>
                        </div>
                    @endif


                    <div class="clearfix"></div>

                    @if ((!empty($pos_settings['enable_sales_order']) && $sale_type != 'sales_order') || $is_order_request_enabled)
                        <div class="col-sm-3">
                            <div class="form-group">
                                {!! Form::label('sales_order_ids', __('lang_v1.sales_order') . ':') !!}
                                {!! Form::select('sales_order_ids[]', [], null, [
                                    'class' => 'form-control select2',
                                    'style' => 'height:40px',
                                    'multiple',
                                    'id' => 'sales_order_ids',
                                ]) !!}
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    @endif
                @endcomponent
                <div id="costs_tablet-id" style="display: none;">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div id="costs_table_id" style="display: none;">
                            <div class="form-group">
                                <h4 style="display: inline-block; margin-right: 10px;">@lang('sales::lang.fixed_costs')</h4>
                                <h5 style="display: inline-block;">(<span style="color: red;">@lang('sales::lang.chang_the_amount_or_duration_if_needed')</span>)</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered table-striped" id="costs_table" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th> @lang('sales::lang.description')</th>
                                                <th style="color: red;">@lang('sales::lang.amount')</th>
                                                <th style="color: red;">@lang('sales::lang.duration_by_month')</th>
                                                <th>@lang('sales::lang.monthly_cost')</th>

                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr style="background-color: rgb(185, 182, 182);">
                                                <td colspan="2" style="text-align: right;">
                                                    <strong><span
                                                            style="color: black; font-weight: bold;">@lang('sales::lang.total')</span></strong>
                                                </td>
                                                <td><span id="total_amount" style="color: black; font-weight: bold;">0.00</span>
                                                </td>
                                                <td></td>
                                                <td><span id="total_monthly_amount"
                                                        style="color: black; font-weight: bold;">0.00</span></td>
                                            </tr>
                                        </tfoot>


                                    </table>
                                </div>
                            </div>
                        </div>
                        {!! Form::hidden('updated_data', null, ['id' => 'updated_data_input']) !!}
                    @endcomponent
                </div>

                {{-- //products --}}
                @component('components.widget', ['class' => 'box-solid'])
                    <div class="form-group">
                        <h4 style="display: inline-block; margin-right: 10px;">@lang('sales::lang.variable_costs')</h4>
                    </div>
                    <div class="col-sm-10 col-sm-offset-1">

                        <div class="form-group">
                            <div class="input-group">

                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-primary btn-flat pos_add_quick_product"
                                        data-href="{{ action([\Modules\Sales\Http\Controllers\SalesTargetedClientController::class, 'clientAdd']) }}"
                                        data-container=".quick_add_client_modal">
                                        <i class="fa fa-plus-circle text-white fa-lg"></i>
                                        @lang('sales::lang.add_element')
                                    </button>
                                </span>

                            </div>
                        </div>
                    </div>

                    <div class="row col-sm-12 pos_product_div" style="min-height: 0">

                        <input type="hidden" name="sell_price_tax" id="sell_price_tax"
                            value="{{ $business_details->sell_price_tax }}">



                        <input type="hidden" id="product_row_count" value="0">

                        <div class="table-responsive">
                            <table class="table table-condensed table-bordered table-striped table-responsive myTable"
                                id="pos_table">
                                <thead>
                                    <tr>
                                        <th class="text-center">
                                            @lang('sales::lang.profession')
                                        </th>
                                        <th class="text-center">
                                            @lang('sales::lang.specialization')
                                        </th>
                                        <th class="text-center">
                                            @lang('sales::lang.nationality')
                                        </th>
                                        <th class="text-center">
                                            @lang('sales::lang.gender')
                                        </th>
                                        <th class="text-center">
                                            @lang('sales::lang.monthly_cost')
                                        </th>
                                        <th class="text-center">
                                            @lang('sales::lang.number_of_clients')
                                        </th>

                                        <th class="text-center">
                                            @lang('sales::lang.total')
                                        </th>

                                        <th class="text-center"><i class="fas fa-times" aria-hidden="true"></i></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="table-responsive" id="price_total">
                            <table class="table table-condensed table-bordered table-striped">
                                <tr>
                                    <td>
                                        <div class="pull-right">
                                            <b>@lang('sale.item'):</b>
                                            <span class="total_quantity">0</span>
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                            <b>@lang('sale.total'): </b>
                                            <span class="price_total">0</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <input type="hidden" name="final_total" id="price_total_input" value="0">
                    <input type="hidden" id="productData" name="productData" value="">
                    <input type="hidden" id="productIds" name="productIds" value="">
                    <input type="hidden" id="quantityArr" name="quantityArr" value="">
                    <input type="hidden" id="quantityArrDisplay" name="quantityArrDisplay" value="">
                    <input type="hidden" name="fees_input" id="fees_input_hidden" value="0">
                    <input type="hidden" id="total_amount_with_fees2" name="total_amount_with_fees" value="">
                    <input type="hidden" id="total_monthly_for_all_workers" name="total_monthly_for_all_workers2" value="">
                    <input type="hidden" id="total_contract_cost2" name="total_contract_cost" value="">
                @endcomponent
                @component('components.widget', ['class' => 'box-solid', 'id' => 'myComponent'])
                    <div id="total_sum">
                        <div class="table-responsive">
                            <table class="table table-condensed table-bordered table-striped">
                                <tr>
                                    <td>
                                        <div class="larger-text">
                                            <b>@lang('sales::lang.total_amount_of_fixed_and_var_costs'):</b>
                                            <span id="total_sum_value">0</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                @endcomponent
                @component('components.widget', ['class' => 'box-solid', 'id' => 'feesComponent'])
                    <div id="fees_section">
                        <div class="table-responsive">
                            <table class="table table-condensed table-bordered table-striped">
                                <tr>
                                    <td>
                                    <td>
                                        <div class="form-group">
                                            <label for="fees_input">@lang('sales::lang.emdadat_fees')</label>
                                            <input type="number" id="fees_input" name="fees_input" class="form-control custom-width-input"
                                                placeholder="Enter fees">
                                        </div>

                                        <div class="larger-text">
                                            <b>@lang('sales::lang.total_amount_with_fees'):</b>
                                            <span id="total_amount_with_fees">0.00</span>
                                        </div>
                                        <div class="larger-text">
                                            <b>@lang('sales::lang.number_of_workers'):</b>
                                            <span id="quantityArrDisplay2">0</span>
                                        </div>
                                        <div class="larger-text">
                                            <b>@lang('sales::lang.total_monthly'):</b>
                                            <span id="total_monthly_for_all_workers2">0</span>
                                        </div>
                                        <br>
                                        <div class="form-group">
                                            <label for="contract_duration_input">@lang('sales::lang.contract_duration')</label>
                                            <input type="number" id="contract_duration_input"
                                                class="form-control custom-width-input" name="contract_duration"
                                                placeholder="Enter Contract Duration (in months)">
                                        </div>
                                        <div class="larger-text">
                                            <b>@lang('sales::lang.total_contract_cost'):</b>
                                            <span id="total_contract_cost">0</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                @endcomponent
              


            </div>
        </div>
        @if (!empty($common_settings['is_enabled_export']) && $sale_type != 'sales_order')
            @component('components.widget', ['class' => 'box-solid', 'title' => __('lang_v1.export')])
                <div class="col-md-12 mb-12">
                    <div class="form-check">
                        <input type="checkbox" name="is_export" class="form-check-input" id="is_export"
                            @if (!empty($walk_in_customer['is_export'])) checked @endif>
                        <label class="form-check-label" for="is_export">@lang('lang_v1.is_export')</label>
                    </div>
                </div>
                @php
                    $i = 1;
                @endphp
                @for ($i; $i <= 6; $i++)
                    <div class="col-md-4 export_div" @if (empty($walk_in_customer['is_export'])) style="display: none;" @endif>
                        <div class="form-group">
                            {!! Form::label('export_custom_field_' . $i, __('lang_v1.export_custom_field' . $i) . ':') !!}
                            {!! Form::text(
                                'export_custom_fields_info[' . 'export_custom_field_' . $i . ']',
                                !empty($walk_in_customer['export_custom_field_' . $i]) ? $walk_in_customer['export_custom_field_' . $i] : null,
                                [
                                    'class' => 'form-control',
                                    'placeholder' => __('lang_v1.export_custom_field' . $i),
                                    'id' => 'export_custom_field_' . $i,
                                ],
                            ) !!}
                        </div>
                    </div>
                @endfor
            @endcomponent
        @endif
        @php
            $is_enabled_download_pdf = config('constants.enable_download_pdf');
            $payment_body_id = 'payment_rows_div';
            if ($is_enabled_download_pdf) {
                $payment_body_id = '';
            }
        @endphp
        @if (
            (empty($status) || !in_array($status, ['quotation', 'draft']) || $is_enabled_download_pdf) &&
                $sale_type != 'sales_order')
            @can('sell.payments')
                @component('components.widget', [
                    'class' => 'box-solid',
                    'id' => $payment_body_id,
                    'title' => __('purchase.add_payment'),
                ])
                    @if ($is_enabled_download_pdf)
                        <div class="well row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('prefer_payment_method', __('lang_v1.prefer_payment_method') . ':') !!}
                                    @show_tooltip(__('lang_v1.this_will_be_shown_in_pdf'))
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fas fa-money-bill-alt"></i>
                                        </span>
                                        {!! Form::select('prefer_payment_method', $payment_types, 'cash', [
                                            'class' => 'form-control',
                                            'style' => 'width:100%;',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('prefer_payment_account', __('lang_v1.prefer_payment_account') . ':') !!}
                                    @show_tooltip(__('lang_v1.this_will_be_shown_in_pdf'))
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fas fa-money-bill-alt"></i>
                                        </span>
                                        {!! Form::select('prefer_payment_account', $accounts, null, [
                                            'class' => 'form-control',
                                            'style' => 'width:100%;',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (empty($status) || !in_array($status, ['quotation', 'draft']))
                        <div class="payment_row" @if ($is_enabled_download_pdf) id="payment_rows_div" @endif>
                            <div class="row">
                                <div class="col-md-12 mb-12">
                                    <strong>@lang('lang_v1.advance_balance'):</strong> <span id="advance_balance_text"></span>
                                    {!! Form::hidden('advance_balance', null, [
                                        'id' => 'advance_balance',
                                        'data-error-msg' => __('lang_v1.required_advance_balance_not_available'),
                                    ]) !!}
                                </div>
                            </div>

                            @include('sale_pos.partials.payment_row_form', [
                                'row_index' => 0,
                                'show_date' => true,
                                'show_denomination' => true,
                            ])
                        </div>
                        <div class="payment_row">
                            <div class="row">
                                <div class="col-md-12">
                                    <hr>
                                    <strong>
                                        @lang('lang_v1.change_return'):
                                    </strong>
                                    <br />
                                    <span class="lead text-bold change_return_span">0</span>
                                    {!! Form::hidden('change_return', $change_return['amount'], [
                                        'class' => 'form-control change_return input_number',
                                        'required',
                                        'id' => 'change_return',
                                    ]) !!}

                                    @if (!empty($change_return['id']))
                                        <input type="hidden" name="change_return_id" value="{{ $change_return['id'] }}">
                                    @endif
                                </div>
                            </div>
                            <div class="row hide payment_row" id="change_return_payment_data">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('change_return_method', __('lang_v1.change_return_payment_method') . ':*') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fas fa-money-bill-alt"></i>
                                            </span>
                                            @php
                                                $_payment_method = empty($change_return['method']) && array_key_exists('cash', $payment_types) ? 'cash' : $change_return['method'];

                                                $_payment_types = $payment_types;
                                                if (isset($_payment_types['advance'])) {
                                                    unset($_payment_types['advance']);
                                                }
                                            @endphp
                                            {!! Form::select('payment[change_return][method]', $_payment_types, $_payment_method, [
                                                'class' => 'form-control col-md-12 payment_types_dropdown',
                                                'id' => 'change_return_method',
                                                'style' => 'width:100%;',
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>
                                @if (!empty($accounts))
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {!! Form::label('change_return_account', __('lang_v1.change_return_payment_account') . ':') !!}
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fas fa-money-bill-alt"></i>
                                                </span>
                                                {!! Form::select(
                                                    'payment[change_return][account_id]',
                                                    $accounts,
                                                    !empty($change_return['account_id']) ? $change_return['account_id'] : '',
                                                    ['class' => 'form-control select2', 'id' => 'change_return_account', 'style' => 'width:100%;'],
                                                ) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @include('sale_pos.partials.payment_type_details', [
                                    'payment_line' => $change_return,
                                    'row_index' => 'change_return',
                                ])
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="pull-right"><strong>@lang('lang_v1.balance'):</strong> <span
                                            class="balance_due">0.00</span></div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endcomponent
            @endcan
        @endif

        <div class="row">
            {!! Form::hidden('is_save_and_print', 0, ['id' => 'is_save_and_print']) !!}
            <div class="col-sm-12 text-center">
                <button type="button" id="submit-sell" class="btn btn-primary btn-big">@lang('messages.save')</button>
                {{-- <button type="button" id="save-and-print" class="btn btn-success btn-big">@lang('lang_v1.save_and_print')</button> --}}
            </div>
        </div>

        @if (empty($pos_settings['disable_recurring_invoice']))
            @include('sale_pos.partials.recurring_invoice_modal')
        @endif

        {!! Form::close() !!}
    </section>

    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>

    <div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade quick_add_client_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">

    </div>

    @include('sale_pos.partials.configure_search_modal')

@stop

@section('javascript')
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/client.js') }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>


    <script type="text/javascript">
        $(document).ready(function() {
            console.log("Document ready");
            updateTotalAmount();
            updateTotalMonthlyAmount();
            updateTotalSum();
            updateTotalFees();
            $('#add_client').on('shown.bs.modal', function(e) {
            $('#professionSearch').select2({
                dropdownParent: $(
                    '#add_client'),
                width: '100%',
            });
            $('#specializationSearch').select2({
                dropdownParent: $(
                    '#add_client'),
                width: '100%',
            });

        });
        
            $('#fees_section').on('input', '#contract_duration_input', function() {
                updateTotalContractCost();
            });

            $('#status').change(function() {
                if ($(this).val() == 'final') {
                    $('#payment_rows_div').removeClass('hide');
                } else {
                    $('#payment_rows_div').addClass('hide');
                }
            });
            $('.paid_on').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });

            $('#shipping_documents').fileinput({
                showUpload: false,
                showPreview: false,
                browseLabel: LANG.file_browse_label,
                removeLabel: LANG.remove,
            });

            $(document).on('change', '#prefer_payment_method', function(e) {
                var default_accounts = $('select#select_location_id').length ?
                    $('select#select_location_id')
                    .find(':selected')
                    .data('default_payment_accounts') : $('#location_id').data('default_payment_accounts');
                var payment_type = $(this).val();
                if (payment_type) {
                    var default_account = default_accounts && default_accounts[payment_type]['account'] ?
                        default_accounts[payment_type]['account'] : '';
                    var account_dropdown = $('select#prefer_payment_account');
                    if (account_dropdown.length && default_accounts) {
                        account_dropdown.val(default_account);
                        account_dropdown.change();
                    }
                }
            });

            function setPreferredPaymentMethodDropdown() {
                var payment_settings = $('#location_id').data('default_payment_accounts');
                payment_settings = payment_settings ? payment_settings : [];
                enabled_payment_types = [];
                for (var key in payment_settings) {
                    if (payment_settings[key] && payment_settings[key]['is_enabled']) {
                        enabled_payment_types.push(key);
                    }
                }
                if (enabled_payment_types.length) {
                    $("#prefer_payment_method > option").each(function() {
                        if (enabled_payment_types.indexOf($(this).val()) != -1) {
                            $(this).removeClass('hide');
                        } else {
                            $(this).addClass('hide');
                        }
                    });
                }
            }

            setPreferredPaymentMethodDropdown();

            $('#is_export').on('change', function() {
                if ($(this).is(':checked')) {
                    $('div.export_div').show();
                } else {
                    $('div.export_div').hide();
                }
            });

            if ($('.payment_types_dropdown').length) {
                $('.payment_types_dropdown').change();
            }

            $('#contract_form').change(function() {
                var selectedValue = $(this).val();
                if (selectedValue === 'monthly_cost') {

                    $('#costs_table_id').show();
                    $('#costs_tablet-id').show();
                    updateTotalAmount();
                    updateTotalMonthlyAmount();
                    updateTotalSum();
                    updateTotalFees();
                } else {

                    $('#costs_table_id').hide();
                    $('#costs_tablet-id').hide();
                    updateTotalAmount();
                    updateTotalMonthlyAmount();

                    var priceTotal = parseFloat($('.price_total').text()) || 0;
                    var totalSum = priceTotal;
                    $('#total_sum_value').text(totalSum.toFixed(2));
                    var totalSum2 = parseFloat($('#total_sum_value').text()) || 0;
                    var fees = parseFloat($('#fees_input').val()) || 0;

                    totalAmountWithFees = totalSum2 + fees;

                    console.log("Grand Total:", totalAmountWithFees);
                    $('#total_amount_with_fees').text(totalAmountWithFees.toFixed(2));
                    $('#total_amount_with_fees2').val(totalAmountWithFees);

                    updateTotalMonthlyForAllWorkers();

                }
            });


            var costs_table = $('#costs_table').DataTable({
                processing: false,
                serverSide: false,
                info: false,
                orderable: false,
                dom: 'lrtip',
                paging: false,
                ajax: {
                    url: "{{ route('sales_costs') }}",

                },

                columns: [{
                        data: 'id',
                        name: 'id',
                        visible: false,
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        render: function(data, type, row) {

                            return '<div contenteditable="true" class="editable-amount" data-row-id="' +
                                row.id + '">' + data + '</div>';
                        }
                    },
                    {
                        data: 'duration_by_month',
                        name: 'duration_by_month',
                        render: function(data, type, row) {
                            return '<div contenteditable="true" class="editable-duration" data-row-id="' +
                                row.id + '">' + data + '</div>';
                        }
                    },
                    {
                        data: 'monthly_cost',
                        name: 'monthly_cost',
                        render: function(data, type, row) {
                            return '<div contenteditable="true" class="editable-monthly-cost" data-row-id="' +
                                row.id + '">' + data + '</div>';
                        }
                    },
                ],

            });

            $('#submit-sell').on('click', function() {
                var updatedData = [];

                $('#costs_table tbody tr').each(function() {
                    var row = {};
                    var rowId = $(this).find('.editable-amount').data('row-id');
                    row['id'] = rowId;
                    row['amount'] = $(this).find('.editable-amount').text();
                    row['duration_by_month'] = $(this).find('.editable-duration').text();
                    updatedData.push(row);
                });

                $('#updated_data_input').val(JSON.stringify(updatedData));

                $('#add_sell_form').submit();
            });



            $('#costs_table').on('blur', '.editable-amount, .editable-duration', function() {
                var rowId = $(this).data('row-id');
                updateMonthlyCost(rowId);
                updateTotalAmount();

                updateTotalMonthlyAmount();
                updateTotalSum();
                updateTotalFees();

            });


            function updateTotalAmount() {
                var totalAmount = 0;
                $('#costs_table tbody tr').each(function() {
                    var amount = parseFloat($(this).find('.editable-amount').text()) || 0;
                    totalAmount += amount;
                });
                console.log("Total Amount:", totalAmount);
                $('#total_amount').text(totalAmount.toFixed(2));
            }

            function updateTotalMonthlyAmount() {
                var totalMonthlyAmount = 0;

                $('#costs_table tbody tr').each(function() {
                    var monthlyCost = parseFloat($(this).find('.editable-monthly-cost').text()) || 0;
                    totalMonthlyAmount += monthlyCost;
                });


                $('#total_monthly_amount').text(totalMonthlyAmount.toFixed(4));
                updateTotalMonthlyForAllWorkers();
            }

            function updateMonthlyCost(rowId) {
                var amount = parseFloat($('#costs_table').find('.editable-amount[data-row-id="' + rowId + '"]')
                    .text()) || 0;
                var duration = parseFloat($('#costs_table').find('.editable-duration[data-row-id="' + rowId + '"]')
                    .text()) || 1;
                var monthlyCost = (amount / duration).toFixed(2);
                $('#costs_table').find('.editable-monthly-cost[data-row-id="' + rowId + '"]').text(monthlyCost);
                updateTotalSum();
            }

            function updateTotalSum() {

                var totalMonthlyAmount = parseFloat($('#total_monthly_amount').text()) || 0;
                var priceTotal = parseFloat($('.price_total').text()) || 0;

                var totalSum = totalMonthlyAmount + priceTotal;


                $('#total_sum_value').text(totalSum.toFixed(2));
                updateTotalFees();
                updateTotalMonthlyForAllWorkers();
            }

            $('#fees_section').on('input', '#fees_input', function() {
                updateTotalFees();
            });

            function updateTotalFees() {
                
                var totalSum = parseFloat($('#total_sum_value').text()) || 0;
                var fees = parseFloat($('#fees_input').val()) || 0;
console.log(totalSum);
console.log(fees);
console.log("fees:", fees);
                totalAmountWithFees = totalSum + fees;

                console.log("Grand Total:", totalAmountWithFees);
                $('#total_amount_with_fees').text(totalAmountWithFees.toFixed(2));
                updateTotalMonthlyForAllWorkers();
            }


            function updateTotalMonthlyForAllWorkers() {

                var totalAmountWithFees = parseFloat($('#total_amount_with_fees').text()) || 0;
                var quantityArrDisplay2 = parseFloat($('#quantityArrDisplay2').text()) || 0;


                var totalMonthlyForAllWorkers = totalAmountWithFees * quantityArrDisplay2;


                $('#total_monthly_for_all_workers2').text(totalMonthlyForAllWorkers.toFixed(2));
                $('#total_monthly_for_all_workers').val(totalMonthlyForAllWorkers);

                updateTotalContractCost();
            }

            function updateTotalContractCost() {
                var contractDuration = parseFloat($('#contract_duration_input').val()) || 0;
                var totalMonthlyForAllWorkers = parseFloat($('#total_monthly_for_all_workers2').text()) || 0;

                var totalContractCost = contractDuration * totalMonthlyForAllWorkers;

                $('#total_contract_cost').text(totalContractCost.toFixed(2));
                $('#total_contract_cost2').val(totalContractCost);

            }

            updateTotalContractCost();



        });


        
    </script>
@endsection
