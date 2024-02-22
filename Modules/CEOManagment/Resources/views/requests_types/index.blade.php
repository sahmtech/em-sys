@extends('layouts.app')
@section('title', __('ceomanagment::lang.requests_types'))
@section('content')
    <section class="content-header">
        <h1>@lang('ceomanagment::lang.requests_types')</h1>
    </section>

    <section class="content">


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('ceomanagment.add_requests_type'))
                        @slot('tool')
                            <div class="box-tools">

                                <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                    data-target="#addRequestTypeModal">
                                    <i class="fa fa-plus"></i> @lang('ceomanagment::lang.add_requests_type')
                                </button>
                            </div>
                        @endslot
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="requests_types">
                            <thead>
                                <tr>
                                    <th>@lang('ceomanagment::lang.request_type')</th>
                                    <th>@lang('ceomanagment::lang.request_prefix')</th>
                                    <th>@lang('ceomanagment::lang.request_for')</th>
                                    <th>@lang('ceomanagment::lang.action')</th>


                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>

            <div class="modal fade" id="addRequestTypeModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open(['route' => 'storeRequestType', 'enctype' => 'multipart/form-data']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('ceomanagment::lang.add_requests_type')</h4>
                        </div>

                        <div class="modal-body">

                            <div class="row">

                                <div class="form-group col-md-4">
                                    {!! Form::label('type', __('essentials::lang.request_type') . ':*') !!}
                                    {!! Form::select(
                                        'type',
                                        array_combine($missingTypes, array_map(fn($type) => trans("ceomanagment::lang.$type"), $missingTypes)),
                                        null,
                                        [
                                            'class' => 'form-control',
                                            'id' => 'type_select',
                                            'placeholder' => __('ceomanagment::lang.request_type'),
                                            'required',
                                            'style' => 'height:37px',
                                        ],
                                    ) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('for', __('ceomanagment::lang.request_for') . ':*') !!}
                                    {!! Form::select(
                                        'for',
                                        [
                                            'worker' => __('ceomanagment::lang.worker'),
                                            'employee' => __('ceomanagment::lang.employee'),
                                            'both' => __('ceomanagment::lang.both'),
                                        ],
                                        null,
                                        [
                                            'class' => 'form-control',
                                            'placeholder' => __('ceomanagment::lang.select_type'),
                                            'required',
                                            'style' => 'height:37px',
                                        ],
                                    ) !!}
                                </div>
                                {{-- 
                                <div class="form-group col-md-4">
                                    {!! Form::label('prefix', __('ceomanagment::lang.request_prefix') . ':*') !!}
                                    {!! Form::text('prefix', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('ceomanagment::lang.request_prefix') . ' (' . __('ceomanagment::lang.example') . ': lev)',
                                        'required',
                                    ]) !!}
                                </div> --}}

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



            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open(['route' => 'updateRequestType', 'enctype' => 'multipart/form-data']) !!}
                        <div class="modal-header">
                            <h4 class="modal-title">@lang('ceomanagment::lang.edit_requests_type')</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <div class="row">

                                <div class="form-group col-md-4">
                                    {!! Form::label('type', __('essentials::lang.request_type') . ':*') !!}
                                    {!! Form::select(
                                        'type2',
                                        array_combine($missingTypes, array_map(fn($type) => trans("ceomanagment::lang.$type"), $missingTypes)),
                                        null,
                                        [
                                            'class' => 'form-control',
                                            'id' => 'type_select',
                                            'placeholder' => __('ceomanagment::lang.request_type'),
                                            'required',
                                            'style' => 'height:37px',
                                        ],
                                    ) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('for', __('ceomanagment::lang.request_for') . ':*') !!}
                                    {!! Form::select(
                                        'for2',
                                        [
                                            'worker' => __('ceomanagment::lang.worker'),
                                            'employee' => __('ceomanagment::lang.employee'),
                                            'both' => __('ceomanagment::lang.both'),
                                        ],
                                        null,
                                        [
                                            'class' => 'form-control',
                                            'placeholder' => __('ceomanagment::lang.select_type'),
                                            'required',
                                            'style' => 'height:37px',
                                        ],
                                    ) !!}
                                </div>
                                {{-- 
                                <div class="form-group col-md-4">
                                    {!! Form::label('prefix', __('ceomanagment::lang.request_prefix') . ':*') !!}
                                    {!! Form::text('prefix', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('ceomanagment::lang.request_prefix') . ' (' . __('ceomanagment::lang.example') . ': lev)',
                                        'required',
                                    ]) !!}
                                </div> --}}

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
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
            var requests_types = $('#requests_types').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('requests_types') }}',
                columns: [{
                        data: 'type',
                        render: function(data, type, row) {
                            switch (data) {
                                case 'exitRequest':
                                    return '@lang('ceomanagment::lang.exitRequest')';
                                case 'returnRequest':
                                    return '@lang('ceomanagment::lang.returnRequest')';
                                case 'escapeRequest':
                                    return '@lang('ceomanagment::lang.escapeRequest')';
                                case 'advanceSalary':
                                    return '@lang('ceomanagment::lang.advanceSalary')';
                                case 'leavesAndDepartures':
                                    return '@lang('ceomanagment::lang.leavesAndDepartures')';
                                case 'atmCard':
                                    return '@lang('ceomanagment::lang.atmCard')';
                                case 'residenceRenewal':
                                    return '@lang('ceomanagment::lang.residenceRenewal')';
                                case 'workerTransfer':
                                    return '@lang('ceomanagment::lang.workerTransfer')';
                                case 'residenceCard':
                                    return '@lang('ceomanagment::lang.residenceCard')';
                                case 'workInjuriesRequest':
                                    return '@lang('ceomanagment::lang.workInjuriesRequest')';
                                case 'residenceEditRequest':
                                    return '@lang('ceomanagment::lang.residenceEditRequest')';
                                case 'baladyCardRequest':
                                    return '@lang('ceomanagment::lang.baladyCardRequest')';
                                case 'mofaRequest':
                                    return '@lang('ceomanagment::lang.mofaRequest')';
                                case 'insuranceUpgradeRequest':
                                    return '@lang('ceomanagment::lang.insuranceUpgradeRequest')';
                                case 'chamberRequest':
                                    return '@lang('ceomanagment::lang.chamberRequest')';
                                case 'cancleContractRequest':
                                    return '@lang('ceomanagment::lang.cancleContractRequest')';
                                case 'WarningRequest':
                                    return '@lang('ceomanagment::lang.WarningRequest')';
                                case 'assetRequest':
                                    return '@lang('ceomanagment::lang.assetRequest')';
                                case 'passportRenewal':
                                    return '@lang('ceomanagment::lang.passportRenewal')';
                                case 'AjirAsked':
                                    return '@lang('ceomanagment::lang.AjirAsked')';
                                case 'AlternativeWorker':
                                    return '@lang('ceomanagment::lang.AlternativeWorker')';
                                default:
                                    return data;
                            }
                        }
                    },
                    {
                        data: 'prefix'
                    },
                    {
                        data: 'for',
                        render: function(data, type, row) {
                            if (data === 'employee') {
                                return '@lang('ceomanagment::lang.employee')';
                            } else if (data === 'worker') {
                                return '@lang('ceomanagment::lang.worker')';
                            }
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]

            });

            $(document).on('click', 'button.delete_item_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_item,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    requests_types.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });




        });
    </script>
    <script>
        var typeTranslations = {
            @foreach ($missingTypes as $type)
                '{{ $type }}': '@lang('ceomanagment::lang.' . $type)',
            @endforeach
        };

        $(document).on('click', '.edit-item', function() {
            var itemId = $(this).data('id');
            var requestType = $(this).data('type-value');
            var requestPrefix = $(this).data('prefix-value');
            var requestFor = $(this).data('for-value');

            var editModal = $('#editModal');

            editModal.find('select[name="type2"] option').each(function() {
                if ($(this).text() === typeTranslations[requestType]) {
                    $(this).parent().val($(this).val()).trigger('change');
                    return false;
                }
            });


            editModal.find('select[name="for2"]').val(requestFor).trigger('change');
            editModal.find('input[name="request_type_id"]').val(itemId);

            editModal.modal('show');
        });
    </script>

@endsection
