@extends('layouts.app')
@section('title', __('essentials::lang.work_cards_vaction_requests'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('essentials::lang.work_cards_vaction_requests')</span>
        </h1>
    </section>


    <section class="content">


        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="requests_table">
                    <thead>
                        <tr>
                            <th>@lang('request.company')</th>
                            <th>@lang('followup::lang.request_number')</th>
                            <th>@lang('followup::lang.worker_name')</th>
                            <th>@lang('followup::lang.eqama_number')</th>
                            <th>@lang('followup::lang.project_name')</th>
                            <th>@lang('followup::lang.request_type')</th>
                            <th>@lang('followup::lang.request_date')</th>
                            <th>@lang('followup::lang.status')</th>
                            <th>@lang('followup::lang.note')</th>
                            <th>@lang('followup::lang.action')</th>


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




            var requests_table = $('#requests_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('allIrRequests') }}"
                },

                columns: [{
                        data: 'company_id'
                    },
                    {
                        data: 'request_no'
                    },

                    {
                        data: 'user'
                    },
                    {
                        data: 'id_proof_number'
                    },
                    {
                        data: 'assigned_to'
                    },
                    {
                        data: 'type',
                        render: function(data, type, row) {
                            if (data === 'exitRequest') {
                                return '@lang('followup::lang.exitRequest')';

                            } else if (data === 'returnRequest') {
                                return '@lang('followup::lang.returnRequest')';
                            } else if (data === 'escapeRequest') {
                                return '@lang('followup::lang.escapeRequest')';
                            } else if (data === 'advanceSalary') {
                                return '@lang('followup::lang.advanceSalary')';
                            } else if (data === 'leavesAndDepartures') {
                                return '@lang('followup::lang.leavesAndDepartures')';
                            } else if (data === 'atmCard') {
                                return '@lang('followup::lang.atmCard')';
                            } else if (data === 'residenceRenewal') {
                                return '@lang('followup::lang.residenceRenewal')';
                            } else if (data === 'workerTransfer') {
                                return '@lang('followup::lang.workerTransfer')';
                            } else if (data === 'residenceCard') {
                                return '@lang('followup::lang.residenceCard')';
                            } else if (data === 'workInjuriesRequest') {
                                return '@lang('followup::lang.workInjuriesRequest')';
                            } else if (data === 'residenceEditRequest') {
                                return '@lang('followup::lang.residenceEditRequest')';
                            } else if (data === 'baladyCardRequest') {
                                return '@lang('followup::lang.baladyCardRequest')';
                            } else if (data === 'mofaRequest') {
                                return '@lang('followup::lang.mofaRequest')';
                            } else if (data === 'insuranceUpgradeRequest') {
                                return '@lang('followup::lang.insuranceUpgradeRequest')';
                            } else if (data === 'chamberRequest') {
                                return '@lang('followup::lang.chamberRequest')';
                            } else if (data === 'cancleContractRequest') {
                                return '@lang('followup::lang.cancleContractRequest')';
                            } else if (data === 'WarningRequest') {
                                return '@lang('followup::lang.WarningRequest')';
                            } else {
                                return data;
                            }
                        }
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'status',

                    },
                    {
                        data: 'note'
                    },

                    {
                        data: 'can_return',
                        render: function(data, type, row) {
                            var buttonsHtml = '';


                            if (data == 1) {
                                buttonsHtml +=
                                    '<button class="btn btn-danger btn-sm btn-return" data-request-id="' +
                                    row.process_id + '">@lang('followup::lang.return_the_request')</button>';
                            }


                            buttonsHtml +=
                                '<button class="btn btn-primary btn-sm btn-view-request" data-request-id="' +
                                row.id + '">@lang('followup::lang.view_request')</button>';

                            return buttonsHtml;
                        }
                    },



                ],
            });





        });
    </script>





@endsection
