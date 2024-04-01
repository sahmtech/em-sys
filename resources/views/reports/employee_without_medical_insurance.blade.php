@extends('layouts.app')
@section('title', __('essentials::lang.employee_without_medical_insurance'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.employee_without_medical_insurance')
         </h1>
<head>
<style>
    .bg-green {
        background-color: #28a745; 
        color: #ffffff; 
    }
</style>
</head>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="employee_without_medical_insurance">
                                <thead class="bg-green">
                                    <tr>

                                        <th>@lang('followup::lang.name')</th>
                                        <th>@lang('essentials::lang.english_name')</th>
                                        <th>@lang('essentials::lang.Birth_date')</th>

                                        <th>@lang('essentials::lang.Residency_no')</th>
                                        <td>@lang('essentials::lang.company_name')</td>

                                        <th>@lang('essentials::lang.fixed')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    @endcomponent

                </div>
            </div>

        </section>
    @endsection

    @section('javascript')
        <script type="text/javascript">
            $(document).ready(function() {


                var employee_without_medical_insurance;

                function reloadDataTable() {
                    employee_without_medical_insurance.ajax.reload();
                }

                employee_without_medical_insurance = $('#employee_without_medical_insurance').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('employee_without_medical_insurance') }}",

                    },

                    columns: [

                        {
                            data: 'user'
                        },
                        {
                            data: 'english_name'
                        },
                        {
                            data: 'dob'
                        },

                        {
                            data: 'proof_number'
                        },
                        {
                            data: 'company_name'
                        },
                        {
                            data: 'fixnumber'
                        },
                    ],
                });




            });
        </script>
    @endsection
