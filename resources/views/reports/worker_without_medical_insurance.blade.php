@extends('layouts.app')
@section('title', __('essentials::lang.worker_without_medical_insurance'))

@section('content')

<section class="content-header">

    <h1>@lang('essentials::lang.worker_without_medical_insurance')
    </h1>

    <section class="content">

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="worker_without_medical_insurance">
                        <thead>
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


        var worker_without_medical_insurance;

        function reloadDataTable() {
            worker_without_medical_insurance.ajax.reload();
        }

        worker_without_medical_insurance = $('#worker_without_medical_insurance').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('worker_without_medical_insurance') }}",

            },

            columns: [

            {
                        data: 'user'
                    },
                    {
                        data:'english_name'
                    },
                    {
                        data: 'dob'
                    },
                   
                    {
                        data:'proof_number'
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