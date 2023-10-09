@extends('layouts.app')
@section('title', __('essentials::lang.insurance_companies'))

@section('content')
@include('essentials::layouts.nav_hrm_setting')
<section class="content-header">
    <h1>@lang('essentials::lang.insurance_companies')</h1>
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
            @component('components.widget', ['class' => 'box-solid', 'title' => __( 'essentials::lang.insurance_companies' )])
           
                @slot('tool')
                <div class="box-tools">
                    
                    <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal" data-target="#addDocModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
                @endslot
            
            
            <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="insurance_companies_table">
                        <thead>
                            <tr>
                                <th>@lang('essentials::lang.business_name' )</th>
                                <th>@lang('essentials::lang.insurance_company')</th>
                                <th>@lang('essentials::lang.city' )</th>
                                <th>@lang('essentials::lang.state' )</th>
                                <th>@lang('essentials::lang.country' )</th>
                                <th>@lang('essentials::lang.address' )</th>
                                <th>@lang('essentials::lang.tax_number' )</th>
                                <th>@lang('essentials::lang.phone_number' )</th>
                                <th>@lang('essentials::lang.mobile_number' )</th>
                                <th>@lang('messages.action' )</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
        {{-- <div class="modal fade" id="addDocModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'storeOfficialDoc' , 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_Doc')</h4>
                    </div>
        
                    <div class="modal-body">
    
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                {!! Form::select('employee',$users, null, ['class' => 'form-control', 'placeholder' => 'Select employee', 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('doc_type', __('essentials::lang.doc_type') . ':*') !!}
                                {!! Form::select('doc_type', [
                                   
                                    'national_id'=>__('essentials::lang.national_id'),
                                    'passport'=>__('essentials::lang.passport'),
                                    'residence_permit'=>__('essentials::lang.residence_permit'),
                                    'drivers_license'=>__('essentials::lang.drivers_license'),
                                    'car_registration'=>__('essentials::lang.car_registration'),
                                    'international_certificate'=>__('essentials::lang.international_certificate'),
                                ], null, ['class' => 'form-control', 'placeholder' => 'Select type', 'required']) !!}
                            </div>
        
                            <div class="form-group col-md-6">
                                {!! Form::label('doc_number', __('essentials::lang.doc_number') . ':*') !!}
                                {!! Form::number('doc_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.doc_number'), 'required']) !!}
                            </div>
        
                            <div class="form-group col-md-6">
                                {!! Form::label('issue_date', __('essentials::lang.issue_date') . ':*') !!}
                                {!! Form::date('issue_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.issue_date'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('issue_place', __('essentials::lang.issue_place') . ':*') !!}
                                {!! Form::text('issue_place', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.issue_place'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('status', __('essentials::lang.status') . ':*') !!}
                                {!! Form::select('status', [
                                'valid' => __('essentials::lang.valid'),
                                'expired' => __('essentials::lang.expired'),
                              
                            ], null, ['class' => 'form-control', 'placeholder' => 'Select status', 'required']) !!}
                        </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('expiration_date', __('essentials::lang.expiration_date') . ':') !!}
                                {!! Form::date('expiration_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.expiration_date'), 'required']) !!}
                            </div>
                        
                            <div class="form-group col-md-6">
                                {!! Form::label('file', __('essentials::lang.file') . ':*') !!}
                                {!! Form::file('file', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.file'), 'required']) !!}
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
        </div> --}}
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
                    "url": "{{ route(\'insurance_companies\') }}",
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
                    { data: 'supplier_business_name' },
                    { data: 'city' },
                    { data: 'state' },
                    { data: 'country' },
                    { data: 'address_line_1' },
                    { data: 'tax_number' },
                    { data: 'landline' },
                    { data: 'mobile' },
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
