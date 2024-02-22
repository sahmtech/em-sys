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
                    <select class="form-control select2" name="evaluation_filter"  id="evaluation_filter"
                        style="height:40px;">
                        <option value="all">@lang('lang_v1.all')</option>
                        <option value="good">@lang('internationalrelations::lang.good')</option>
                        <option value="bad">@lang('internationalrelations::lang.bad')</option>
                    </select>
                </div>
            </div>

             <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('company_requests_filter', __('internationalrelations::lang.company_requests') . ':') !!}
                    <select class="form-control select2" name="company_requests_filter"  id="company_requests_filter"
                        style="height:40px">
                        <option value="all">@lang('lang_v1.all')</option>
                        <option value="has_agency_requests">@lang('internationalrelations::lang.has_agency_requests')</option>
                        <option value="has_not_agency_requests">@lang('internationalrelations::lang.has_not_agency_requests')</option>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('nationality', __('internationalrelations::lang.nationality') . ':') !!}
                    {!! Form::select('nationality_filter', $nationalities, null, [
                        'id' => 'nationality_filter',
                        'style' => 'height:36px',
                        'class' => 'form-control',
                        'placeholder' => __('internationalrelations::lang.nationality'),
                        
                    ]) !!}

                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('country', __('internationalrelations::lang.country') . ':') !!}
                    {!! Form::select('country_filter', $countries, null, [
                        'id' => 'country_filter',
                        'style' => 'height:36px',
                        'class' => 'form-control',
                        'placeholder' => __('internationalrelations::lang.country'),
                       
                    ]) !!}

                </div>
            </div>
        @endcomponent



        @component('components.widget', ['class' => 'box-primary'])
            @if (auth()->user()->hasRole('Admin#1') ||
                    auth()->user()->can('internationalrelations.add_employment_company'))
                @slot('tool')
                    <div class="box-tools">

                        <button type="button" class="btn btn-block btn-primary" data-toggle="modal"
                            data-target="#addEmpCompanyModal">
                            <i class="fa fa-plus"></i> @lang('internationalrelations::lang.add_empCompany')
                        </button>
                    </div>
                @endslot
            @endif
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
                            <th style="width: 150px">@lang('messages.action')</th>

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
                                        {!! Form::select('nationalities[]', $nationalities, null, [
                                            'id' => 'country',
                                            'multiple',
                                            'style' => 'width: 230px; height: 40px;',
                                            'class' => 'form-control select2',
                                            'placeholder' => __('internationalrelations::lang.nationality'),
                                            'required',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
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

@include('internationalrelations::EmploymentCompanies.edit_modal')
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        var company_table = $('#EmpCompany_table').DataTable({
            ajax: {
                url: "{{ route('international-Relations.EmploymentCompanies') }}",
                data: function(d) {
                    d.nationality = $('#nationality_filter').val();
                    d.country = $('#country_filter').val();
                    d.evaluation_filter = $('#evaluation_filter').val();
                    d.company_requests_filter = $('#company_requests_filter').val();  
                }
            },
            processing: true,
            serverSide: true,
            columns: [
                { data: 'supplier_business_name',  render: function(data, type, row) {
                           
                            @can('internationalrelations.show_employment_company_profile')
                                var link = '<a href="' +
                                    '{{ route('show_employment_company_profile', ['id' => ':id']) }}'
                                    .replace(':id', row.id) + '">' + data + '</a>';
                                return link;
                            @else
                                return data;
                            @endcan
                        } 
                },
                { data: 'country_nameAr', name: 'country_nameAr' },
                { data: 'nationalities', name: 'nationalities' },
                { data: 'name', name: 'name' },
                { data: 'mobile', name: 'mobile' },
                { data: 'email', name: 'email' },
                { data: 'comp_evaluation', name: 'comp_evaluation' },
                { data: 'action', name: 'action' },
            ],

           
        });

        
        $('#nationality_filter, #country_filter, #evaluation_filter, #company_requests_filter').on('change', function() {
        company_table.ajax.reload(); 
         });
       

    $('body').on('click', '.open-edition-modal', function() {
    var empCompanyId = $(this).data('id'); 
    $('#empCompanyIdInput').val(empCompanyId);

    var editUrl = '{{ route("edit.EmploymentCompanies", ":empCompanyId") }}'
    editUrl = editUrl.replace(':empCompanyId', empCompanyId);
    console.log(editUrl);

    $.ajax({
        url: editUrl,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            var data = response.data;

            $('#editEmpCompanyModal input[name="Office_name"]').val(data.employment_companies.supplier_business_name);
            $('#editEmpCompanyModal input[name="name"]').val(data.employment_companies.name);
            $('#editEmpCompanyModal input[name="mobile"]').val(data.employment_companies.mobile);
            $('#editEmpCompanyModal input[name="email"]').val(data.employment_companies.email);
            $('#editEmpCompanyModal input[name="landline"]').val(data.employment_companies.landline);
            
           
           $('#editEmpCompanyModal select[name="nationalities[]"]').val(JSON.parse(data.employment_companies.multi_nationalities)).trigger('change');
           $('#editEmpCompanyModal select[name="country"]').val(data.employment_companies.country).trigger('change');
          
        $('#editEmpCompanyModal select[name="evaluation"]').val(data.employment_companies.evaluation).trigger('change');
                   
            $('#editEmpCompanyModal').modal('show');
        },

        error: function(error) {
            console.error('Error fetching building data:', error);
        }
    });

     $('body').on('submit', '#editEmpCompanyModal form', function (e) {
            e.preventDefault();

            var empCompanyId = $('#empCompanyIdInput').val();

            var urlWithId = '{{ route("update.EmploymentCompanies", ":empCompanyId") }}';
            urlWithId = urlWithId.replace(':empCompanyId', empCompanyId);

            $.ajax({
                url: urlWithId,
                type: 'POST',
                data: new FormData(this),
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.success) {
                        company_table.ajax.reload();
                        toastr.success(response.msg);
                        $('#editEmpCompanyModal').modal('hide');
                    } else {
                        toastr.error(response.msg);
                    }
                },
                error: function (error) {
                    console.error('Error submitting form:', error);
                    toastr.error('An error occurred while submitting the form.', 'Error');
                },
            });
        });
});
 });
</script>


@endsection

