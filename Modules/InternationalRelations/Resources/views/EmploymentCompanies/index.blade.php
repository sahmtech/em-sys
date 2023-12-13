@extends('layouts.app')
@section('title', __('internationalrelations::lang.EmploymentCompanies'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('internationalrelations::lang.all_EmploymentCompanies')</span>
        </h1>
    </section>


    <!-- Main content -->
    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('evaluation_filter', __('internationalrelations::lang.Evaluation') . ':') !!}
                    <select class="form-control select2" name="evaluation_filter" required id="evaluation_filter"
                        style="width: 100%;">
                        <option value="all">@lang('lang_v1.all')</option>
                        <option value="good">@lang('internationalrelations::lang.good')</option>
                        <option value="bad">@lang('internationalrelations::lang.bad')</option>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('nationality', __('internationalrelations::lang.nationality') . ':') !!}
                    {!! Form::select('nationality_filter', $nationalities, null, [
                        'id' => 'nationality_filter',
                        'style' => 'height:40px',
                        'class' => 'form-control',
                        'placeholder' => __('internationalrelations::lang.nationality'),
                        'required',
                    ]) !!}

                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('country', __('internationalrelations::lang.country') . ':') !!}
                    {!! Form::select('country_filter', $countries, null, [
                        'id' => 'country_filter',
                        'style' => 'height:40px',
                        'class' => 'form-control',
                        'placeholder' => __('internationalrelations::lang.country'),
                        'required',
                    ]) !!}

                </div>
            </div>
        @endcomponent



        @component('components.widget', ['class' => 'box-primary'])
            @slot('tool')
                <div class="box-tools">

                    <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addEmpCompanyModal">
                        <i class="fa fa-plus"></i> @lang('internationalrelations::lang.add_empCompany')
                    </button>
                </div>
            @endslot

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="EmpCompany_table">
                    <thead>
                        <tr>

                            <th>@lang('internationalrelations::lang.Office_name')</th>
                            <th>@lang('internationalrelations::lang.country')</th>
                            <th>@lang('internationalrelations::lang.nationalities')</th>
                            <th>@lang('internationalrelations::lang.Office_representative')</th>
                            <th>@lang('internationalrelations::lang.mobile')</th>
                            <th>@lang('internationalrelations::lang.email')</th>
                            <th>@lang('internationalrelations::lang.Evaluation')</th>
                            <th>@lang('messages.action')</th>

                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent


        <div class="modal fade" id="addEmpCompanyModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">

            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => 'store.EmploymentCompanies']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('internationalrelations::lang.add_empCompany')</h4>
                    </div>

                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-4 contact_type_div">
                                <div class="form-group">
                                    {!! Form::label('Office_name', __('internationalrelations::lang.Office_name') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-user"></i>
                                        </span>
                                        {!! Form::text('Office_name', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('internationalrelations::lang.Office_name'),
                                        ]) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('country', __('internationalrelations::lang.country') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-briefcase"></i>
                                        </span>
                                        {!! Form::select('country', $countries, null, [
                                            'id' => 'country',
                                            'style' => 'height:40px',
                                            'class' => 'form-control',
                                            'placeholder' => __('essentials::lang.country'),
                                            'required',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('nationality', __('internationalrelations::lang.nationality') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-briefcase"></i>
                                        </span>
                                        {!! Form::select('nationality', $nationalities, null, [
                                            'id' => 'country',
                                            'style' => 'height:40px',
                                            'class' => 'form-control',
                                            'placeholder' => __('internationalrelations::lang.nationality'),
                                            'required',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('Office_representative', __('internationalrelations::lang.Office_representative') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-briefcase"></i>
                                        </span>
                                        {!! Form::text('name', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('internationalrelations::lang.Office_representative'),
                                        ]) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('mobile', __('internationalrelations::lang.mobile') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-briefcase"></i>
                                        </span>
                                        {!! Form::text('mobile', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('internationalrelations::lang.mobile'),
                                        ]) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('email', __('internationalrelations::lang.email') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-briefcase"></i>
                                        </span>
                                        {!! Form::text('email', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('internationalrelations::lang.email'),
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('Evaluation', __('internationalrelations::lang.Evaluation') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-briefcase"></i>
                                        </span>

                                        <select class="form-control select2" name="evaluation" required id="evaluation"
                                            style="width: 100%;">

                                            <option value="good">@lang('internationalrelations::lang.good')</option>
                                            <option value="bad">@lang('internationalrelations::lang.bad')</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('landing', __('internationalrelations::lang.landing') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-briefcase"></i>
                                        </span>
                                        {!! Form::text('landline', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('internationalrelations::lang.landing'),
                                        ]) !!}
                                    </div>
                                </div>
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
    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        // Countries table
        $(document).ready(function() {
            var company_table;

            function reloadDataTable() {
                company_table.ajax.reload();
            }
            company_table = $('#EmpCompany_table').DataTable({
                ajax: {
                    url: "{{ route('international-Relations.EmploymentCompanies') }}",
                    data: function(d) {

                        d.nationality = $('#nationality_filter').val();
                        d.country = $('#country_filter').val();

                    }
                },
                processing: true,
                serverSide: true,


                columns: [

                    {
                        data: 'supplier_business_name',
                        name: 'supplier_business_name'
                    },
                    {
                        data: 'country_nameAr',
                        name: 'country_nameAr'
                    },
                    {
                        data: 'nationality',
                        name: 'nationality'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'evaluation',
                        name: 'evaluation'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });


            $('#evaluation_filter').on('change', function() {
                var evaluationFilter = $(this).val();
                company_table.column('evaluation:name').search(evaluationFilter).draw();
            });




            $('#nationality_filter, #country_filter').on('change', function() {
                reloadDataTable();
            });


        });
    </script>



@endsection
