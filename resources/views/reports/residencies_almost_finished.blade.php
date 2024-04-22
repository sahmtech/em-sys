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


                var residencies_almost_finished;

                function reloadDataTable() {
                    residencies_almost_finished.ajax.reload();
                }

                residencies_almost_finished = $('#residencies_almost_finished').DataTable({
                    processing: true,
                    serverSide: true,
                    footer: true,
                                    buttons: ['excel', {
                                                extend: 'print',
                                                title: ' ',
                                                text: '<i class="glyphicon glyphicon-print" style="padding: 0px 7px"></i><span>Print</span>',
                                                className: 'btn printclass textSan',
                                                customize: function(win) {
                                                        $(win.document.body).prepend('<div style="display: flex;justify-content: space-between;"><div class="row" style="padding: 5px 25px;"><h3>@lang('lang_v1.emdadatalatta_comp')</h3><h3>@lang('essentials::lang.work_cards')</h3><h4>@lang('lang_v1.report') @lang('essentials::lang.residencies_almost_finished')</h4></div><img src="/uploads/custom_logo.png" class="img-rounded" alt="Logo" style="width: 175px;"> </div>');
        }
        }],
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
                        }, {
                            data: "gender",
                        },
                    ],
                });




            });
        </script>
    @endsection
