@extends('layouts.app')
@section('title', __('essentials::lang.worker_medical_insurance'))

@section('content')

<section class="content-header">

    <h1>@lang('essentials::lang.worker_medical_insurance')
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
                    <table class="table table-bordered table-striped" id="worker_medical_insurance">
                        <thead class="bg-green">
                            <tr>

                                <th>@lang('followup::lang.name')</th>
                                    <th>@lang('essentials::lang.english_name')</th>
                                    <th>@lang('essentials::lang.Birth_date')</th>
                                   
                                    <th>@lang('essentials::lang.Residency_no')</th>
                                    <th>@lang('essentials::lang.insurance_company')</th>
                                    <th>@lang('essentials::lang.insurance_class')</th>
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


        var worker_medical_insurance;

        function reloadDataTable() {
            worker_medical_insurance.ajax.reload();
        }

        worker_medical_insurance = $('#worker_medical_insurance').DataTable({
            processing: true,
            serverSide: true,
            footer: true,
                                    buttons: ['excel', {
                                                extend: 'print',
                                                title: ' ',
                                                text: '<i class="glyphicon glyphicon-print" style="padding: 0px 7px"></i><span>Print</span>',
                                                className: 'btn printclass textSan',
                                                customize: function(win) {
                                                        $(win.document.body).prepend('<div style="display: flex;justify-content: space-between;"><div class="row" style="padding: 5px 25px;"><h3>@lang('lang_v1.emdadatalatta_comp')</h3><h3>@lang('essentials::lang.health_insurance')</h3><h4>@lang('lang_v1.report') @lang('essentials::lang.worker_medical_insurance')</h4></div><img src="/uploads/custom_logo.png" class="img-rounded" alt="Logo" style="width: 175px;"> </div>');
        }
        }],
            ajax: {
                url: "{{ route('worker_medical_insurance') }}",

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
                        data: 'insurance_company_id'
                    },
                    {
                        data: 'insurance_classes_id'
                    },
                    {
                        data: 'fixnumber'
                    },
            ],
        });




    });
    </script>
    @endsection