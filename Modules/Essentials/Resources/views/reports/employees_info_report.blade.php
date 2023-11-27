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
                   
                       
                        <th>@lang('essentials::lang.type_of_employees')</th>
                        <th>@lang('essentials::lang.gender_distribution')</th>
                        <th>@lang('essentials::lang.age_distribution')</th>
                       
                     
                    </tr>
                </thead>
            </table>
        </div>

    @endcomponent
</section>

@endsection

@section('javascript')
<script type="text/javascript">
    var translations = {
        employees: '@lang("essentials::lang.employees")',
        managers: '@lang("essentials::lang.managers")',
        workers: '@lang("essentials::lang.workers")',
        group1: '@lang("essentials::lang.ageGroup1")',
        group2: '@lang("essentials::lang.ageGroup2")',
        group3: '@lang("essentials::lang.ageGroup3")',
        female: '@lang("essentials::lang.female")',
        male: '@lang("essentials::lang.male")',
    };

    var employee_info_report_table;

    $(document).ready(function () {
        employee_info_report_table = $('#employee_info_report_table').DataTable({
            processing: true,
            serverSide: true,
            paging: false, // Disable pagination
            searching: false, // Disable search bar
            info: false, // Disable info display
            lengthChange: false, // Disable length change dropdown
            ajax: {
                url: "{{ route('employess-info-report') }}",
            },
            columns: [
                {
                    data: 'typeOfEmployees',
                    render: function (data) {
                        return `
                            <div>${translations.employees}: ${data.employees}</div>
                            <div>${translations.managers}: ${data.managers}</div>
                            <div>${translations.workers}: ${data.workers}</div>
                        `;
                    }
                },
                {
                    data: 'genderDistribution',
                    render: function (data) {
                        var femaleCount = 0;
                        var maleCount = 0;

                        for (var i = 0; i < data.length; i++) {
                            if (data[i].gender === 'female') {
                                femaleCount = data[i].count;
                            } else if (data[i].gender === 'male') {
                                maleCount = data[i].count;
                            }
                        }

                        return `
                            <div>${translations.female}: ${femaleCount}</div>
                            <div>${translations.male}: ${maleCount}</div>
                        `;
                    }
                },
                {
                    data: 'ageDistribution',
                    render: function (data) {
                        return `
                            <div>${translations.group1}: ${data.group1}</div>
                            <div>${translations.group2}: ${data.group2}</div>
                            <div>${translations.group3}: ${data.group3}</div>
                        `;
                    }
                },
            ]
        });
    });
</script>



@endsection
