@extends('layouts.app')
@section('title', __('essentials::lang.allexpired_residencies'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.allexpired_residencies')
        </h1>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="expired_residencies">
                                <thead>
                                    <tr>

                                        <th>@lang('followup::lang.name')</th>
                                        <th>@lang('followup::lang.residency')</th>
                                        <td class="table-td-width-100px">@lang('essentials::lang.company_name')</td>


                                        <td class="table-td-width-100px">@lang('followup::lang.passport_numer')</td>
                                        <td class="table-td-width-100px">@lang('followup::lang.passport_expire_date')</td>


                                        <td class="table-td-width-100px">@lang('essentials::lang.border_number')</td>
                                        <td class="table-td-width-100px">@lang('essentials::lang.dob')</td>
                                        <th>@lang('followup::lang.project')</th>
                                        <th>@lang('followup::lang.customer_name')</th>

                                        <th>@lang('followup::lang.end_date')</th>
                                        <td class="table-td-width-100px">@lang('followup::lang.nationality')</td>
                                        <td class="table-td-width-100px">@lang('followup::lang.profession')</td>

                                        <td class="table-td-width-100px">@lang('followup::lang.gender')</td>


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
                            data: 'worker_name'
                        },
                        {
                            data: 'residency'
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
                        }, {
                            data: 'dob'
                        },
                        {
                            data: 'project'
                        },
                        {
                            data: 'customer_name'
                        },

                        {
                            data: 'end_date'
                        },
                        {
                            data: 'nationality'
                        }, {
                            data: "profession",
                            name: 'profession'
                        }, {
                            data: "gender",
                        },
                    ],
                });




            });
        </script>
    @endsection
