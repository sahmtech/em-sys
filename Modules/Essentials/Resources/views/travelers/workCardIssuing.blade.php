@extends('layouts.app')
@section('title', __('housingmovements::lang.workCardIssuing'))

@section('content')

<section class="content-header">
    <h1>
        <span>@lang('housingmovements::lang.workCardIssuing')</span>
    </h1>
</section>
@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<style>
    /* Match Select2 width with Bootstrap .col-md-4 */
    .col-md-4 .select2-container {
        width: 100% !important;
    }

    /* Style select2 to look like Bootstrap form-control */
    .select2-container .select2-selection--single {
        height: 40px !important;
        border: 1px solid #ced4da !important;
        border-radius: 5px !important;
        padding: 6px 12px !important;
        font-size: 16px !important;
    }

    .select2-container .select2-selection__rendered {
        line-height: 28px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
        right: 10px !important;
    }
    table.dataTable thead th {
        vertical-align: middle;
    }
</style>
@endpush

<!-- Main content -->
<section class="content">
    @include('essentials::layouts.nav_trevelers')


    @component('components.widget', ['class' => 'box-primary'])
    @slot('tool')
    <div class="box-tools">
        <a class="btn btn-block btn-primary" href="#" data-toggle="modal" data-target="#createWorkCardModal">
            <i class="fa fa-plus"></i> @lang('essentials::lang.create_work_cards')</a>
    </div>
    @endslot


    <div class="table-responsive">
        <table class="table table-bordered table-striped ajax_view" id="card_table">
            <thead>

                <tr>

                    <th style="width: 7%;">@lang('essentials::lang.card_no')</th>
                    <th>@lang('essentials::lang.company_name')</th>
                    <th>@lang('essentials::lang.unified_number')</th>
                    <th>@lang('essentials::lang.worker_name')</th>
                    <th>@lang('essentials::lang.border_number')</th>
                    <th>@lang('essentials::lang.nationality')</th>
                    <th>@lang('essentials::lang.project')</th>
                    <th>@lang('essentials::lang.responsible_client')</th>
                    <th>@lang('essentials::lang.workcard_duration')</th>
                    <th>@lang('essentials::lang.work_card_fees')</th>
                    <th>@lang('essentials::lang.passport_fees')</th>
                    <th>@lang('essentials::lang.other_fees')</th>
                    <th>@lang('essentials::lang.pay_number')</th>



                </tr>

            </thead>

        </table>
    </div>
    @endcomponent


</section>

<div class="modal fade" id="createWorkCardModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">@lang('essentials::lang.create_work_cards')</h4>
            </div>

            <!-- Modal Body -->
            {!! Form::open(['url' => route('storeWorkCard'), 'method' => 'post', 'id' => 'workCardForm']) !!}
            <div class="modal-body">
                <div class="row">
                     <!-- Employee Selector -->
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('employee_id', __('essentials::lang.choose_card_owner') . ':*') !!}
                            <select style="width: 100%" name="employee_id" id="employee_id" class="form-control select2" required
                                onchange="updateBorderNo()">
                                <option value="">{{ __('lang_v1.all') }}</option>
                                @foreach ($employees as $id => $details)
                                <option value="{{ $id }}" data-border_no="{{ $details['border_no'] }}">
                                    {{ $details['name'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <input type="hidden" id="border_no" name="border_no" value="">


                    <!-- Business Selector -->
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('company_id', __('essentials::lang.business') . ':') !!}
                            {!! Form::select('company_id', $companies, null, [
                            'class' => 'form-control',
                            'style' => ' height: 40px',
                            'placeholder' => __('essentials::lang.business'),
                            'id' => 'company_id',
                            ]) !!}
                        </div>
                    </div>

                    <!-- Work Card Duration -->
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('workcard_duration', __('essentials::lang.work_card_duration') . ':*') !!}
                            {!! Form::select('workcard_duration_input', $durationOptions, null, [
                            'class' => 'form-control',
                            'id' => 'workcard_duration_input',
                            'required',
                            'style' => ' height: 40px',
                            'placeholder' => __('essentials::lang.work_card_duration'),
                            ]) !!}
                        </div>
                    </div>

                    <!-- Passport Fees -->
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('fees', __('essentials::lang.passport_fees') . ':*') !!}
                            {!! Form::select('passport_fees_input', [], null, [
                            'class' => 'form-control',
                            'id' => 'fees_input',
                            'required',
                            'style' => ' height: 40px',
                            'placeholder' => __('essentials::lang.passport_fees'),
                            ]) !!}
                        </div>
                    </div>

                    <!-- Work Card Fees -->
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('work_card_fees', __('essentials::lang.work_card_fees') . ':*') !!}
                            {!! Form::text('work_card_fees', null, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.work_card_fees'),
                            'id' => 'work_card_fees',
                            'style' => ' height: 40px',
                            'required',
                            ]) !!}
                        </div>
                    </div>

                    <!-- Other Fees -->
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('other_fees', __('essentials::lang.other_fees') . ':') !!}
                            {!! Form::text('other_fees', null, [
                            'class' => 'form-control',
                            'style' => ' height: 40px',
                            'placeholder' => __('essentials::lang.other_fees'),
                            'id' => 'other_fees',
                            ]) !!}
                        </div>
                    </div>

                    <!-- Payment Number -->
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('pay_number', __('essentials::lang.pay_number') . ':') !!}
                            {!! Form::text('Payment_number', null, [
                            'class' => 'form-control',
                            'id' => 'Payment_number',
                            'style' => ' height: 40px',
                            'placeholder' => __('essentials::lang.pay_number'),
                            ]) !!}
                            <div id="error-message" style="color: red; display: none;">You cannot enter more than 14
                                numbers</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="saveButton">@lang('messages.save')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@endsection


@section('javascript')



<script type="text/javascript">
    var translations = {
            months: @json(__('essentials::lang.months')),
            management: @json(__('essentials::lang.management'))
        };



        $(document).ready(function() {


            var card_table = $('#card_table').DataTable({

                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('hrm_workCardIssuing') }}",


                },

                columns: [


                    {
                        data: 'card_no',
                        name: 'card_no'
                    },
                    {
                        data: 'company_name',
                        name: 'company_name'
                    },
                    {
                        data: 'unified_number',
                        name: 'unified_number'
                    },


                    {
                        data: 'user',
                        name: 'user'
                        
                    },
                   
                    {
                        data: 'proof_number',
                        name: 'proof_number'
                    },
                    {
                        data: 'nationality',
                        name: 'nationality'
                    },
                    // {
                    //     data: 'assigned_to',
                    //     name: 'assigned_to'
                    // },
                    {
                        data: 'project',
                        name: 'project'
                    },
                    {
                        data: 'responsible_client',
                        name: 'responsible_client'
                    },
                    {
                        data: 'workcard_duration',
                        name: 'workcard_duration',
                        render: function(data) {
                            if (data != null) {
                                return data + " " + translations['months'];
                            } else {
                                return " ";
                            }

                        }
                    },
                    {
                        data: "work_card_fees",
                        name: "work_card_fees"
                    },
                    {
                        data: 'passport_fees',
                        name: 'passport_fees'
                    },
                    {
                        data: 'other_fees',
                        name: 'other_fees'
                    },

                    {
                        data: 'Payment_number',
                        name: 'Payment_number'
                    },
                    // {
                    //     data: 'fixnumber',
                    //     name: 'fixnumber'
                    // },




                ]

            });


            $('#Payment_number').on('input', function() {
                var payNumber = $(this).val().replace(/\D/g, ''); // Remove non-numeric characters
                if (payNumber.length !== 14) {
                    // If not exactly 14 numbers, show error message
                    $('#error-message').text('يجب أن يتكون رقم السداد من 14 رقم تماما').show();
                } else {
                    // If exactly 14 numbers, hide error message
                    $('#error-message').hide();
                }
            });

            $('#employees').on('change', function() {
                var employeeId = $(this).val();
                $('#employee_id').val(employeeId);
                console.log(employeeId);
            });

            $('#all_responsible_users').on('change', function() {
                var employee = $(this).val();
                $('#responsible_user_id').val(employee);
                console.log(employee);
            });

            $('#createWorkCardModal').find('.select2').select2({
                dropdownParent: $('#createWorkCardModal')
            });

            $('#workcard_duration_input').change(function() {
                var selectedDuration = $(this).val();
                var feesOptions = [];
                switch (selectedDuration) {
                    case '3':
                        feesOptions = ['163', '288', '413'];
                        break;
                    case '6':
                        feesOptions = ['326', '825'];
                        break;
                    case '9':
                        feesOptions = ['488'];
                        break;
                    case '12':
                        feesOptions = ['650', '1150'];
                        break;
                    default:
                        feesOptions = [];
                }
                // Clear previous options
                $('#fees_input').empty();
                // Populate fees dropdown with new options
                $.each(feesOptions, function(index, value) {
                    $('#fees_input').append($('<option>', {
                        value: value,
                        text: value
                    }));
                });

                // Calculate and populate work_card_fees field
                var calculatedFees = calculateFees(selectedDuration);
                $('#work_card_fees').val(calculatedFees);
            });

            $('body').on('submit', '#workCardForm', function(e) {
                e.preventDefault();
                var urlWithId = $(this).attr('action');
                $.ajax({
                    url: urlWithId,
                    type: 'POST',
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        console.log(response);
                        if (response.success) {
                            console.log(response);
                            // Assuming buildings_table is defined elsewhere
                            card_table.ajax.reload();
                            toastr.success(response.msg);
                            $('#createWorkCardModal').modal('hide');
                        } else {
                            toastr.error(response.msg);
                            $('#createWorkCardModal').modal('hide');
                            console.log(response);
                        }
                    },
                    error: function(error) {
                        console.error('Error submitting form:', error);
                        toastr.error('An error occurred while submitting the form.', 'Error');
                    },
                });
            });


            $('#contact-select').on('change', function() {


                card_table.ajax.reload();
            });

            $('#proof_numbers_select').on('change', function() {
                console.log($('#proof_numbers_select').val());
                card_table.ajax.reload();
            });

            function calculateFees(selectedValue) {
                switch (selectedValue) {
                    case '3':
                        return 2425;
                    case '6':
                        return 4850;
                    case '9':
                        return 7275;
                    case '12':
                        return 9700;
                    default:
                        return 0;
                }
            }


        });
</script>

<script>
    function updateBorderNo() {
            var selected = document.getElementById('employee_id');
            var borderNo = selected.options[selected.selectedIndex].dataset.border_no;
            document.getElementById('border_no').value = borderNo || '';
        }


       
</script>

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
<script>
    $(document).ready(function() {
        $('#employee_id').select2({
            placeholder: "{{ __('lang_v1.all') }}",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
@endsection