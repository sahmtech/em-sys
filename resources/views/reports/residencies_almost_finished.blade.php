@extends('layouts.app')
@section('title', __('essentials::lang.residencies_almost_finished'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.residencies_almost_finished')
        </h1>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="residencies_almost_finished">
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
                        },
                    ],
                });




            });
        </script>
    @endsection
