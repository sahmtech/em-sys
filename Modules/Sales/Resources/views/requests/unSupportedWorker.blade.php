@extends('layouts.app')
@section('title', __('sales::lang.Unsupported_workers'))

@section('content')

    <section class="content-header">
        <h1>@lang('sales::lang.Unsupported_workers')</h1>
    </section>

    <head>
        <style>
            .text-success {
                color: green;
            }

            .text-danger {
                color: red;
            }

            .text-warning {
                color: yellow;
            }
        </style>
    </head>
    <section class="content">

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @slot('tool')
                        <div class="box-tools">

                            <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                data-target="#addrequestModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="requests_table">
                            <thead>
                                <tr>
                                    <th>@lang('sales::lang.order_number')</th>
                                    <th>@lang('sales::lang.profession')</th>
                                    <th>@lang('essentials::lang.specialization')</th>
                                    <th>@lang('essentials::lang.nationlity')</th>
                                    <th>@lang('essentials::lang.quantity')</th>
                                    <th>@lang('sales::lang.remaining_quantity')</th>
                                    <th>@lang('essentials::lang.salary')</th>
                                    <th>@lang('essentials::lang.required_date')</th>
                                    <th>@lang('essentials::lang.status')</th>
                                    <th>@lang('essentials::lang.notes')</th>
                                    {{-- <th>@lang('sales::lang.attachments')</th> --}}
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
            <div class="modal fade" id="addrequestModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open(['route' => 'storeUnsupported_workers', 'enctype' => 'multipart/form-data']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('sales::lang.create_order')</h4>
                        </div>

                        <div class="modal-body">

                            <div class="row">


                                <div class="form-group col-md-6">
                                    {!! Form::label('profession', __('sales::lang.profession') . ':*') !!}
                                    {!! Form::select('profession', $professions, null, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('sales::lang.profession'),
                                        'id' => 'professionSelect',
                                    ]) !!}

                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('specialization', __('sales::lang.specialization') . ':*') !!}
                                    {!! Form::select('specialization', $specializations, null, [
                                        'class' => 'form-control',
                                        'required',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('sales::lang.specialization'),
                                        'id' => 'specializationSelect',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('nationlity', __('essentials::lang.nationlity') . ':*') !!}
                                    {!! Form::select('nationlity', $nationalities, null, [
                                        'class' => 'form-control',
                                        'id' => 'nationlity_select',
                                        'required',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('essentials::lang.nationlity'),
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('date', __('essentials::lang.required_date') . ':') !!}
                                    {!! Form::date('date', null, [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('essentials::lang.required_date'),
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('quantity', __('essentials::lang.quantity') . ':*') !!}
                                    {!! Form::number('quantity', null, [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'required',
                                        'placeholder' => __('essentials::lang.quantity'),
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('salary', __('essentials::lang.salary') . ':*') !!}
                                    {!! Form::number('salary', null, [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'required',
                                        'placeholder' => __('essentials::lang.quantity'),
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('note', __('sales::lang.note') . ':') !!}
                                    {!! Form::textarea('note', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.note'),
                                        'rows' => 3,
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('attachment', __('sales::lang.attachment') . ':') !!}
                                    {!! Form::file('attachment', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.attachment'),
                                    ]) !!}
                                </div>



                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('javascript')


    <script type="text/javascript">
        $(document).ready(function() {


            $('#addrequestModal').on('shown.bs.modal', function(e) {
                $('#nationlity_select').select2({
                    dropdownParent: $(
                        '#addrequestModal'),
                    width: '100%',
                });

                $('#specializationSelect').select2({
                    dropdownParent: $(
                        '#addrequestModal'),
                    width: '100%',
                });
                $('#professionSelect').select2({
                    dropdownParent: $(
                        '#addrequestModal'),
                    width: '100%',
                });
            });

            var requests_table;
            var professionSelect = $('#professionSelect');
            var specializationSelect = $('#specializationSelect');

            requests_table = $('#requests_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('Unsupported_workers') }}",

                },

                columns: [{
                        data: 'order_no'
                    },
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
                        data: 'total_quantity'
                    },
                    {
                        data: 'remaining_quantity_for_operation'
                    },
                    {
                        data: 'salary'
                    },
                    {
                        data: 'date'
                    },



                    {
                        data: 'status',
                        render: function(data, type, full, meta) {
                            switch (data) {


                                case 'pending':
                                    return '{{ trans('sales::lang.pending') }}';
                                case 'not_started':
                                    return '{{ trans('sales::lang.not_started') }}';
                                case 'ended':
                                    return '{{ trans('sales::lang.ended') }}';
                                default:
                                    return data;
                            }
                        }
                    },
                    {
                        data: 'note'
                    },
                    // {
                    //     data: 'attachments'
                    // },


                ],
            });


            function reloadDataTable() {
                requests_table.ajax.reload();
            }



        });
    </script>


@endsection
