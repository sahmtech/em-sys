@extends('layouts.app')
@section('title', __('essentials::lang.employees_information_report'))
@section('content')
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.employees_information_report')</span>
    </h1>
</section>


<section class="content">
@component('components.filters', ['title' => __('report.filters')])
  
@if (count($business_locations) > 0)
                    <div class="col-md-3">
                        <div class="form-group">
                        <label for="business_filter">@lang('essentials::lang.business'):</label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-map-marker"></i>
                                </span>
                                {!! Form::select(
                                    'select_location_id',
                                    $business_locations,
                                    null,
                                    [
                                        'class' => 'form-control input-sm',
                                        'id' => 'select_location_id',
                                        'style' => 'height:40px; width:100%',
                                        'placeholder' => __('lang_v1.all'),
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
@endif

@endcomponent



@component('components.widget', ['class' => 'box-primary'])

        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="employee_info_report_table">
                <thead>
                    <tr>
                   
                        <th>@lang('essentials::lang.total_of_employees')</th>
                        <th>@lang('essentials::lang.type_of_employees')</th>
                        <th>@lang('essentials::lang.total_of_nationality')</th>
                        <th>@lang('essentials::lang.type_of_identity_proof')</th>
                        <th>@lang('essentials::lang.age_distribution')</th>
                     
                    </tr>
                </thead>
            </table>
        </div>

    @endcomponent
</section>

@endsection
