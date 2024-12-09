    @extends('layouts.app')
    @section('title', __('essentials::lang.violations'))

    @section('content')

        <section class="content-header">
            <h1>@lang('essentials::lang.violations')</h1>
        </section>
        <section class="content">


            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        @slot('tool')
                            <div class="box-tools">

                                <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                    data-target="#addEmployeesFamilyModal">
                                    <i class="fa fa-plus"></i> @lang('messages.add')
                                </button>
                            </div>
                        @endslot


                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="violations_table">
                                <thead>
                                    <tr>
                                        <th>@lang('essentials::lang.main-violations')</th>
                                        <th>@lang('essentials::lang.description')</th>

                                        <th>@lang('essentials::lang.type')</th>

                                        <th>@lang('essentials::lang.occurrence')</th>
                                        <th>@lang('essentials::lang.amount_type')</th>
                                        <th>@lang('essentials::lang.amount')</th>
                                        <th>@lang('essentials::lang.date')</th>
                                        <th>@lang('messages.action')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    @endcomponent
                </div>
                <div class="modal fade" id="edit_violations" tabindex="-1" role="dialog"></div>

                <div class="modal fade" id="addEmployeesFamilyModal" tabindex="-1" role="dialog"
                    aria-labelledby="gridSystemModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">

                            {!! Form::open(['route' => 'store-violations', 'enctype' => 'multipart/form-data']) !!}
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">@lang('essentials::lang.add_violations')</h4>
                            </div>


                            <div class="modal-body">

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('description', __('essentials::lang.description') . ' *') !!}

                                        {!! Form::text('description', '', [
                                            'class' => 'form-control',
                                            'id' => 'description',
                                            'required',
                                            'placeholder' => __('essentials::lang.description'),
                                        ]) !!}
                                    </div>

                                    <div class="form-group col-md-6">
                                        {!! Form::label('violations', __('essentials::lang.main-violations') . ' *') !!}
                                        <select class="form-control" required="" id="violation" name="violation_id"
                                            style="padding: 2px 10px;">
                                            @foreach ($Violations as $violation)
                                                <option value="{{ $violation->id }}">{{ $violation->description }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- 
                                <div class="form-group col-md-6">
                                    {!! Form::label('type', __('essentials::lang.type') . ' *') !!}
                                    <select class="form-control" required="" id="type" name="type"
                                        style="padding: 2px 10px;">
                                        <option value="violation">@lang('essentials::lang.violation')</option>
                                        <option value="incentive">@lang('essentials::lang.incentive')</option>
                                    </select>
                                </div> --}}



                                    <div class="form-group col-md-6">
                                        {!! Form::label('occurrence', __('essentials::lang.occurrence') . ' *') !!}
                                        <select class="form-control" required="" id="occurrence" name="occurrence"
                                            style="padding: 2px 10px;">
                                            <option value="First time" selected>@lang('essentials::lang.First time')</option>
                                            <option value="Secound time">@lang('essentials::lang.Secound time')</option>
                                            <option value="Theard time">@lang('essentials::lang.Theard time')</option>
                                            <option value="Fourth time">@lang('essentials::lang.Fourth time')</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        {!! Form::label('amount_type', __('essentials::lang.amount_type') . ' *') !!}
                                        <select class="form-control select-2" required="" id="amount_type"
                                            name="amount_type" style="padding: 2px 10px;">
                                            <option value="fixed">@lang('essentials::lang.fixed')</option>
                                            <option value="percent_amount">@lang('essentials::lang.percent_amount')</option>
                                            <option value="warning">@lang('essentials::lang.warning')</option>
                                            <option value="1">@lang('essentials::lang.day')</option>
                                            <option value="1.5">@lang('essentials::lang.A day and a half')</option>
                                            <option value="2">@lang('essentials::lang.Two days')</option>
                                            <option value="2.5">@lang('essentials::lang.Two and a half days')</option>
                                            <option value="3">@lang('essentials::lang.Three days')</option>
                                            <option value="3.5">@lang('essentials::lang.Three and a half days')</option>
                                            <option value="4">@lang('essentials::lang.Four days')</option>
                                            <option value="4.5">@lang('essentials::lang.Four and a half days')</option>
                                            <option value="5">@lang('essentials::lang.Five days')</option>
                                        </select>
                                    </div>


                                    <div class="form-group col-md-6" id="amount_field">
                                        {!! Form::label('amount', __('essentials::lang.amount') . ' *') !!}
                                        {!! Form::text('amount', '', [
                                            'class' => 'form-control',
                                            'id' => 'amount',
                                            'placeholder' => __('essentials::lang.amount'),
                                        ]) !!}
                                        <div id="idProofNumberError" style="color: red;"></div>
                                    </div>

                                    <div class="form-group col-md-6" id="date">
                                        <div class="form-group">
                                            {!! Form::label('date', __('essentials::lang.date') . ':') !!}
                                            {!! Form::date('date', '', [
                                                'class' => 'form-control',
                                                'id' => 'date',
                                                'required' => 'required',
                                                'placeholder' => __('essentials::lang.date'),
                                            ]) !!}
                                        </div>
                                    </div>



                                    <div class="form-group col-md-6" id="file_field" style="display: none;">
                                        {!! Form::label('warning_file', __('essentials::lang.upload_warning_file') . ' ') !!}
                                        {!! Form::file('warning_file', ['class' => 'form-control', 'id' => 'warning_file']) !!}
                                    </div>


                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                                <button type="button" class="btn btn-default"
                                    data-dismiss="modal">@lang('messages.close')</button>
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


                $('#amount_type').change(function() {
                    var amountType = $(this).val();

                    if (amountType === 'fixed' || amountType === 'percent_amount') {
                        $('#amount_field').show();
                        $('#amount').prop('required', true);
                        // $('#file_field').hide();
                        // $('#warning_file').prop('required', false);

                    } else {

                        $('#amount_field').hide();
                        $('#amount').prop('required', false);
                        // $('#file_field').show();
                        // $('#warning_file').prop('required', false);

                    }
                });



                $('#addEmployeesFamilyModal').on('shown.bs.modal', function(e) {
                    $('#employees_select').select2({
                        dropdownParent: $(
                            '#addEmployeesFamilyModal'),
                        width: '100%',
                    });

                    $('#amount_type').select2({
                        dropdownParent: $(
                            '#addEmployeesFamilyModal'),
                        width: '100%',
                    });


                });
                violations_table = $('#violations_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('violations') }}",

                    },

                    columns: [{

                            data: 'mainViolation'
                        }, {

                            data: 'descrption'
                        },
                        {
                            data: 'type',

                        },
                        {
                            data: 'occurrence',

                        },
                        {
                            data: 'amount_type'
                        },

                        {
                            data: 'amount'
                        },
                        {
                            data: 'date'
                        },

                        {
                            data: 'action'
                        },
                    ],
                });

                $(document).on('click', 'button.delete_violations_button', function() {
                    swal({
                        title: LANG.sure,
                        text: LANG.confirm_employeeFamily,
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
                                        violations_table.ajax.reload();
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








    @endsection
