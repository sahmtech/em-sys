@extends('layouts.app')
@section('title', __('followup::lang.recruitmentRequests'))

@section('content')
@include('essentials::layouts.nav_recruitmentRequests')
    <section class="content-header">
        <h1>@lang('followup::lang.recruitmentRequests')</h1>
    </section>
    <section class="content">

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
 


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="recruitmentRequests_table">
                            <thead>
                                <tr>

                                    <th>@lang('sales::lang.profession')</th>
                                    <th>@lang('essentials::lang.specialization')</th>
                                    <th>@lang('essentials::lang.nationlity')</th>
                                    <th>@lang('essentials::lang.quantity')</th>
                                    <th>@lang('essentials::lang.required_date')</th>
                                {{--  <th>@lang('essentials::lang.status')</th>--}}   
                                    <th>@lang('essentials::lang.notes')</th>
                                    <th>@lang('followup::lang.attachments')</th>
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

            var requests_table;
            var professionSelect = $('#professionSelect');
            var specializationSelect = $('#specializationSelect');

            requests_table = $('#recruitmentRequests_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('unaccepted-recuirements-requests') }}",

                },

                columns: [

                    {
                        data: 'profession_id'
                    },
                    {
                        data: 'specialization_id'
                    },
                    {
                        data: 'nationality_id'
                    },
                    {
                        data: 'quantity'
                    },
                    {
                        data: 'date'
                    },
                    // {
                    //     data: 'status',
                    //     render: function(data, type, full, meta) {
                    //         switch (data) {


                    //             case 'pending':
                    //                 return '{{ trans('followup::lang.pending') }}';
                    //             case 'approved':
                    //                 return '{{ trans('followup::lang.under approved') }}';

                    //             case 'rejected':
                    //                 return '{{ trans('followup::lang.rejected') }}';
                    //             default:
                    //                 return data;
                    //         }
                    //     }
                    // },
                    {
                        data: 'note'
                    },
                    {
                        data: 'attachments'
                    },


                ],
            });


            function reloadDataTable() {
                requests_table.ajax.reload();
            }



        });
    </script>


@endsection
