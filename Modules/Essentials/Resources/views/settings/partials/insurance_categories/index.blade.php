@extends('layouts.app')
@section('title', __('essentials::lang.insurance_categories'))

@section('content')
@include('essentials::layouts.nav_hrm_setting')
<section class="content-header">
    <h1>@lang('essentials::lang.insurance_categories')</h1>
</section>
<section class="content">
    {{-- <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                @if(!empty($users))
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('user_id_filter', __('essentials::lang.employee') . ':') !!}
                        {!! Form::select('user_id_filter', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                @endif

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('doc_type_filter', __('essentials::lang.doc_type') . ':') !!}
                        <select class="form-control select2" name="doc_type_filter" required id="doc_type_filter" style="width: 100%;">
                            <option value="national_id">@lang('essentials::lang.national_id')</option>
                            <option value="passport">@lang('essentials::lang.passport')</option>
                            <option value="residence_permit">@lang('essentials::lang.residence_permit')</option>
                            <option value="drivers_license">@lang('essentials::lang.drivers_license')</option>
                            <option value="car_registration">@lang('essentials::lang.car_registration')</option>
                            <option value="international_certificate">@lang('essentials::lang.international_certificate')</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status_filter">@lang( 'essentials::lang.status' ):</label>
                        <select class="form-control select2" name="status_filter" required id="status_filter" style="width: 100%;">
                            <option value="valid">@lang('essentials::lang.valid')</option>
                            <option value="expired">@lang('essentials::lang.expired')</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('doc_filter_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('doc_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
                    </div>
                </div>
            @endcomponent
        </div>
    </div> --}}

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid', ])
           
                @slot('tool')
                <div class="box-tools">
                    
                    <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal" data-target="#addInsuranceCategoryModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
                @endslot
            
            
            <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="insurance_companies_table">
                        <thead>
                            <tr>
                                <th>@lang('essentials::lang.insurance_category_name' )</th>
                                <th>@lang('messages.action' )</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
        <div class="modal fade" id="addInsuranceCategoryModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'insurance_categories.store']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_insurance_category')</h4>
                    </div>
        
                    <div class="modal-body">
    
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('insurance_category_name', __('essentials::lang.insurance_category_name') . ':*') !!}
                                {!! Form::select('insurance_category_name',
                                ['VIP+'=>'VIP+',
                                'VIP'=>'VIP',
                                'A+'=>'A+',
                                'A'=>'A',
                                'B+'=>'B+',
                                'B'=>'B',
                                'C+'=>'C+',
                                'C'=>'C',
                                'CR+'=>'CR+',
                                'CR'=>'CR'],
                                 null, ['class' => 'form-control', 'required']) !!}
                            </div>        
                        </div>
                    </div>
        
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var insurance_companies_table;

            function reloadDataTable() {
                insurance_companies_table.ajax.reload();
            }

            insurance_companies_table  = $('#insurance_companies_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ route('insurance_categories') }}",
                    // "data": function(d) {
                    //     if ($('#user_id_filter').length) {
                    //         d.user_id = $('#user_id_filter').val();
                    //     }
                    //     d.status = $('#status_filter').val();
                    //     d.doc_type = $('#doc_type_filter').val();
                    //     if ($('#doc_filter_date_range').val()) {
                    //         var start = $('#doc_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    //         var end = $('#doc_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    //         d.start_date = start;
                    //         d.end_date = end;
                    //     }
                    // }
                },
                
                columns: [
                    { data: 'name' },
                    { data: 'action' },
                ],
            });

            // $('#doc_filter_date_range').daterangepicker(
            //     dateRangeSettings,
            //     function(start, end) {
            //         $('#doc_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            //     }
            // );
            // $('#doc_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            //     $('#doc_filter_date_range').val('');
            //     reloadDataTable();
            // });

            // $(document).on('change', '#user_id_filter, #status_filter, #doc_filter_date_range, #doc_type_filter', function() {
            //     reloadDataTable();
            // });
          
       
            
        });
    
    </script>
@endsection
