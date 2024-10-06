@extends('layouts.app')
@section('title', __('essentials::lang.penalties'))

@section('content')

    <section class="content-header">
        <h1>@lang('essentials::lang.penalties')</h1>
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
                        <table class="table table-bordered table-striped" id="penalties_table">
                            <thead>
                                <tr>

                                    <th>@lang('essentials::lang.penalties_user')</th>

                                    <th>@lang('essentials::lang.added_by')</th>
                                    <th>@lang('essentials::lang.penalties_action')</th>
                                    <th>@lang('essentials::lang.implement_status')</th>
                                    <th>@lang('essentials::lang.application_date')</th>


                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
            <div class="modal fade" id="edit_violations" tabindex="-1" role="dialog"></div>

            <div class="modal fade " id="addEmployeesFamilyModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open(['route' => 'store-penalties', 'enctype' => 'multipart/form-data']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('essentials::lang.add_violations')</h4>
                        </div>


                        <div class="modal-body">

                            <div class="row">


                                <div class="form-group col-md-6">
                                    {!! Form::label('violations', __('essentials::lang.penalties_user') . ' *') !!}
                                    <select class="form-control select-2" required="" id="users" name="user_id"
                                        style="padding: 2px 10px;">
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->first_name . ' ' . $user->last_name . ' - ' . $user->id_proof_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>



                                <div class="form-group col-md-6">
                                    {!! Form::label('occurrence', __('essentials::lang.penalties_action') . ' *') !!}
                                    <select class="form-control select-2" required="" id="violation_penalties"
                                        name="violation_penalties_id" style="padding: 2px 10px;">
                                        @foreach ($ViolationPenalties as $penalties)
                                            <option value="{{ $penalties->id }}"
                                                data-amount_type="{{ $penalties->amount_type }}">
                                                {{ $penalties->violation->description . ' - ' . $penalties->descrption . ' - ' }}
                                                @lang('essentials::lang.' . $penalties->occurrence) - @lang('essentials::lang.' . $penalties->amount_type) @if ($penalties->amount > 0)
                                                    - {{ $penalties->amount }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('application_date', __('essentials::lang.application_date') . ' *') !!}
                                    {!! Form::month('application_date_month', null, [
                                        'class' => 'form-control',
                                        'id' => 'application_date_month',
                                        'required' => true,
                                    ]) !!}
                                </div>

                              
                                {!! Form::hidden('application_date', null, ['id' => 'application_date']) !!}


                                <div class="form-group col-md-6" id="violation_file" style="display:none;">
                                    {!! Form::label('file', __('essentials::lang.file') . ' *') !!}
                                    {!! Form::file('violation_file', [
                                        'class' => 'form-control',
                                        // 'id' => 'violation_file',
                                        'style' => '',
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

            $('#addEmployeesFamilyModal').on('shown.bs.modal', function(e) {
                $('#users').select2({
                    dropdownParent: $(
                        '#addEmployeesFamilyModal'),
                    width: '100%',
                });

                $('#violation_penalties').select2({
                    dropdownParent: $(
                        '#addEmployeesFamilyModal'),
                    width: '100%',
                });


            });

            document.getElementById('application_date_month').addEventListener('change', function() {
                var selectedMonth = this.value; 
                var year = selectedMonth.split('-')[0];
                var month = selectedMonth.split('-')[1];

                
                var lastDayOfMonth = new Date(year, month, 0).getDate();

               
                var fullDate = year + '-' + month + '-' + lastDayOfMonth;

               
                document.getElementById('application_date').value = fullDate;
            });


            $('#violation_penalties').on('change', function() {

                var amount_type = $(this).find(':selected').data('amount_type');

                console.log(amount_type);

                if (amount_type === 'warning') {
                    $('#violation_file').show();
                } else {
                    $('#violation_file').hide();


                }
            });

            $('#edit_violations').on('shown.bs.modal', function(e) {
                $('#users').select2({
                    dropdownParent: $(
                        '#edit_violations'),
                    width: '100%',
                });

                $('#violation_penalties').select2({
                    dropdownParent: $(
                        '#edit_violations'),
                    width: '100%',
                });
            });



            penalties_table = $('#penalties_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('penalties') }}",

                },

                columns: [{

                        data: 'user'
                    }, {

                        data: 'added_by'
                    },
                    {
                        data: 'penalties'

                    }, 
                    {
                        data: 'status'
                    },
                    {
                        data: 'application_date'
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
                                    penalties_table.ajax.reload();
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

    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            var eqamaNumberInput = document.getElementById('eqama_number');
            var idProofNumberError = document.getElementById('idProofNumberError');

            eqamaNumberInput.addEventListener('input', function() {
                var inputValue = eqamaNumberInput.value;
                if (/^2\d{0,9}$/.test(inputValue)) {
                    idProofNumberError.textContent = '';
                } else {
                    idProofNumberError.textContent = 'رقم الإقامة يجب أن يبدأ ب 2 ويحتوي فقط 10 خانات';

                    var validInput = inputValue.match(/^2\d{0,9}/);
                    eqamaNumberInput.value = validInput ? validInput[0] : '2';
                }

                if (idProofNumberError.textContent === '') {
                    idProofNumberError.textContent = '';
                }
            });
        });
    </script> --}}






@endsection
