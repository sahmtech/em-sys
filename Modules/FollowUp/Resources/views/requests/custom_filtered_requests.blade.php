@extends('layouts.app')
@section('title', __('followup::lang.allRequests'))

@section('content')

    <section class="content-header">
        <h1>
            <span>{{ $pageName }}</span>
        </h1>
    </section>

    <!-- Main content -->

    <section class="content">


        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="requests_table">
                    <thead>
                        <tr>
                            <th>@lang('followup::lang.request_number')</th>
                            <th>@lang('followup::lang.worker_name')</th>
                            <th>@lang('followup::lang.request_type')</th>
                            <th>@lang('followup::lang.request_date')</th>
                            <th>@lang('followup::lang.status')</th>
                            <th>@lang('followup::lang.note')</th>
                            <th>@lang('followup::lang.reason')</th>


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
                    url: "{{ route('allRequests') }}"
                },

                columns: [

                    {
                        data: 'request_no'
                    },

                    {
                        data: 'user'
                    }, {
                        data: 'type'
                    },
                    {
                        data: 'created_at'
                    },

                    {
                        data: 'status',
                        render: function(data, type, full, meta) {
                            switch (data) {


                                case 'approved':
                                    return '{{ trans('followup::lang.approved') }}';
                                case 'under process':
                                    return '{{ trans('followup::lang.under process') }}';

                                case 'rejected':
                                    return '{{ trans('followup::lang.rejected') }}';
                                default:
                                    return data;
                            }
                        }
                    },
                    {
                        data: 'note'
                    },
                    {
                        data: 'reason'
                    },




                ],



            });


        });
    </script>

@endsection
