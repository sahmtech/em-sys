@extends('layouts.app')
@section('title', __('essentials::lang.residencies_almost_finished'))

@section('content')

    <section class="content-header">
<head>
    <style>
    .bg-green {
        background-color: #28a745; 
        color: #ffffff; 
    }
</style>
</head>
        <h1>@lang('essentials::lang.residencies_almost_finished')
        </h1>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="residencies_almost_finished">
                               <thead class="bg-green">
                                    <tr>
                                        <th>@lang('followup::lang.name')</th>
                                        <th>@lang('followup::lang.residency')</th>
                                        <th>@lang('followup::lang.eqama_end_date')</th>
                                        <th>@lang('essentials::lang.company_name')</th>
                                        <th>@lang('followup::lang.passport_numer')</th>
                                        <th>@lang('followup::lang.passport_expire_date')</th>
                                        <th>@lang('essentials::lang.border_number')</th>
                                        <th>@lang('essentials::lang.dob')</th>
                                        <th>@lang('essentials::lang.gender')</th>
                                        <th>@lang('followup::lang.customer_name')</th>
                                        <th>@lang('followup::lang.project')</th>
                                        <th>@lang('followup::lang.nationality')</th>
                                        <th>@lang('followup::lang.profession')</th>

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


                var residencies_almost_finished;

                function reloadDataTable() {
                    residencies_almost_finished.ajax.reload();
                }

                residencies_almost_finished = $('#residencies_almost_finished').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('residencies_almost_finished') }}",

                    },

                     columns: [

                        {
                            data: 'worker_name'
                        },
                        {
                            data: 'residency'
                        },
                        
                        {
                            data: 'end_date'
                        },
                        {
                            "data": "company_name"
                        },
                        {
                            data: 'passport_number'
                        },
                        {
                            data: 'passport_expire_date'
                        },
                        {
                            data: 'border_no'
                        },
                        {
                            data: 'dob'
                        },
                         {
                            data: 'gender',
                             render: function(data, type, row) {
                            if (data === 'male') {
                                return '@lang('lang_v1.male')';
                            } else if (data === 'female') {
                                return '@lang('lang_v1.female')';

                            } else {
                                return '@lang('lang_v1.others')';
                            }
                        }
                        },
                        {
                            data: 'customer_name'
                        },
                        {
                            data: 'project'
                        },
                        

                        {
                            data: 'nationality'
                        }, {
                            data: "profession",
                            name: 'profession'
                        },
                    ],
                });




            });
        </script>
    @endsection
