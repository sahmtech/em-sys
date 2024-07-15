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
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="employees_table" style="table-layout: fixed !important;">
                    <thead>
                        <tr>
                            <th class="table-td-width-25px">#</th>
                            <th class="table-td-width-200px">@lang('followup::lang.emp_name')</th>
                            <th class="table-td-width-100px">@lang('essentials::lang.eqama_number')</th>
                            <th class="table-td-width-custom">@lang('essentials::lang.profile_image')</th>
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

        <form action="{{ route('attachements.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="import_file">Import Excel File:</label>
                <input type="file" name="import_file" id="import_file" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Import</button>
        </form>
    </section>
    <!-- /.content -->
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#employees_table').DataTable({
                processing: true,
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
                        data: 'profile_image'
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
