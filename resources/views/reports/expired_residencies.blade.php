@extends('layouts.app')
@section('title', __('essentials::lang.allexpired_residencies'))

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
        <h1>@lang('essentials::lang.allexpired_residencies')
        </h1>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="expired_residencies">
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


                var expired_residencies;

                function reloadDataTable() {
                    expired_residencies.ajax.reload();
                }

                expired_residencies = $('#expired_residencies').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('expired_residencies') }}",

                    },

                    columns: [

                        {
                            data: 'worker_name', searchable: true 
                        },
                        {
                            data: 'residency', searchable: true 
                        },
                        
                        {
                            data: 'end_date', searchable: true 
                        },
                        {
                            "data": "company_name", searchable: true 
                        },
                        {
                            data: 'passport_number', searchable: true 
                        },
                        {
                            data: 'passport_expire_date', searchable: true 
                        },
                        {
                            data: 'border_no', searchable: true 
                        },
                        {
                            data: 'dob', searchable: true 
                        },
                         {
                            data: 'gender',, searchable: true ,
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
                            data: 'customer_name', searchable: true 
                        },
                        {
                            data: 'project', searchable: true 
                        },
                        

                        {
                            data: 'nationality', searchable: true 
                        }, {
                            data: "profession", searchable: true 
                            name: 'profession', searchable: true 
                        },
                    ],
                });




            });
        </script>
    @endsection