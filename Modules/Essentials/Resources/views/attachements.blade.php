@extends('layouts.app')
@section('title', __('essentials::lang.attachements'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('essentials::lang.attachements')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {{-- <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('employee_name_filter', __('sales::lang.contact_name') . ':') !!}
                            {!! Form::select('employee_name_filter', $contacts2, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                                'id' => 'employee_name_filter',
                            ]) !!}

                        </div>
                    </div>
                @endcomponent
            </div>
        </div> --}}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="employees_table" style="table-layout: fixed !important;">
                    <thead>
                        <tr>
                            <th class="table-td-width-25px">#</th>
                            <th class="table-td-width-custom">@lang('followup::lang.emp_name')</th>
                            <th class="table-td-width-custom">@lang('essentials::lang.eqama_number')</th>
                            <th class="table-td-width-custom">@lang('essentials::lang.contract')</th>
                            <th class="table-td-width-custom">@lang('essentials::lang.residence_permit')</th>
                            <th class="table-td-width-custom">@lang('essentials::lang.national_id')</th>
                            <th class="table-td-width-custom">@lang('essentials::lang.passport')</th>
                            <th class="table-td-width-custom">@lang('essentials::lang.international_certificate')</th>
                            <th class="table-td-width-custom">@lang('essentials::lang.drivers_license')</th>
                            <th class="table-td-width-custom">@lang('essentials::lang.Iban')</th>
                            <th class="table-td-width-custom">@lang('essentials::lang.car_registration')</th>
                            <th class="table-td-width-custom">@lang('essentials::lang.qualification_file')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent



    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#employees_table').DataTable({
                processing: true,
                // serverSide: true,

                ajax: {
                    url: "{{ route('attachements') }}",
                    data: function(d) {
                        if ($('#employee_name_filter').val()) {
                            d.project_name = $('#employee_name_filter').val();
                        }
                    }
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'full_name'
                    },
                    {
                        data: 'id_proof_number'
                    },
                    {
                        data: 'contract'
                    },
                    {
                        data: 'activeResidencePermit'
                    },
                    {
                        data: 'activeNationalId'
                    },
                    {
                        data: 'activePassport'
                    },
                    {
                        data: 'activeInternationalCertificate'
                    },
                    {
                        data: 'activeDriversLicense'
                    },
                    {
                        data: 'activeIban'
                    },
                    {
                        data: 'activeCarRegistration'
                    },
                    {
                        data: 'activeQualification'
                    },
                ]
            });

            $('#employee_name_filter').on('change', function() {
                $('#employees_table').DataTable().ajax.reload();
            });
        });
    </script>
@endsection
