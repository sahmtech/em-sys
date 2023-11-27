@extends('layouts.app')
@section('title', __('essentials::lang.contracts_end_reasons'))
@section('content')
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.contracts_end_reasons')</span>
    </h1>
</section>

<section class="content">
@component('components.filters', ['title' => __('report.filters')])


       <div class="col-md-3">
           <div class="form-group">
               <label for="employee_type_filter">@lang('essentials::lang.employee_type'):</label>
               <select class="form-control select2" name="employee_type_filter" required id="employee_type_filter" style="width: 100%;">
                   <option value="all">@lang('lang_v1.all')</option>
                   <option value="employee">@lang('essentials::lang.employee')</option>
                   <option value="manager">@lang('essentials::lang.manager')</option>
                   <option value="worker">@lang('essentials::lang.worker')</option>
               </select>
           </div>
       </div>

     

@endcomponent

@component('components.widget', ['class' => 'box-primary'])

    @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary"
                 href="{{action([\Modules\Essentials\Http\Controllers\EssentialsContractsFinishReasonsController::class, 'create'])}}">
                <i class="fa fa-plus"></i> @lang('essentials::lang.create_contracts_finish_reason')</a>
            </div>
        @endslot

        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="finish_reasons_table">
                <thead>
                    <tr>
                   
                        <th>@lang('sales::lang.operation_order_number')</th>
                        <th>@lang('sales::lang.customer_name')</th>
                        <th>@lang('sales::lang.contract_number')</th>
                        <th>@lang('sales::lang.operation_order_type')</th>
                        <th>@lang('sales::lang.Status')</th>
                        <th>@lang('sales::lang.show_operation')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>

    @endcomponent



</section>
@endsection