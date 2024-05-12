@extends('layouts.app')
@section('title', __('housingmovements::lang.bankAccounts'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.bankAccounts')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @include('housingmovements::layouts.nav_trevelers')


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-block btn-primary" href="#" data-toggle="modal" data-target="#createBankModal">
                                <i class="fa fa-plus"></i> @lang('housingmovements::lang.create_bank')</a>
                        </div>
                    @endslot
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="bankAccounts_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang('housingmovements::lang.worker_name')</th>
                                    <th>@lang('housingmovements::lang.account_holder_name')</th>
                                    <th>@lang('housingmovements::lang.account_number')</th>
                                    <th>@lang('housingmovements::lang.bank_name')</th>
                                    <th>@lang('housingmovements::lang.bank_code')</th>
                                    <th>@lang('housingmovements::lang.iban_file')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent

                <div class="modal fade" id="createBankModal" tabindex="-1" role="dialog"
                    aria-labelledby="gridSystemModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            {!! Form::open(['route' => 'addBank', 'enctype' => 'multipart/form-data']) !!}

                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">@lang('essentials::lang.add_bank_account')</h4>
                            </div>

                            <div class="modal-body">
                                <div class="row">

                                    <div class="form-group col-md-4">
                                        {!! Form::label('user_id', __('essentials::lang.name') . ':*') !!}
                                        {!! Form::select('user_id', $employees, null, [
                                            'class' => 'form-control',
                                            'required',
                                            'style' => 'height:40px',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('account_holder_name', __('lang_v1.account_holder_name') . ':') !!}
                                        {!! Form::text('bank_details[account_holder_name]', null, [
                                            'class' => 'form-control',
                                            'style' => 'height:40px',
                                            'id' => 'account_holder_name',
                                            'placeholder' => __('lang_v1.account_holder_name'),
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('account_number', __('lang_v1.account_number') . ':') !!}
                                        {!! Form::text('bank_details[account_number]', null, [
                                            'class' => 'form-control',
                                            'style' => 'height:40px',
                                            'id' => 'account_number',
                                            'placeholder' => __('lang_v1.account_number'),
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('bank_name', __('lang_v1.bank_name') . ':') !!}

                                        {!! Form::select('bank_details[bank_name]', $banks, null, [
                                            'class' => 'form-control',
                                            'style' => 'height:40px',
                                            'id' => 'bank_name',
                                            'placeholder' => __('lang_v1.bank_name'),
                                        ]) !!}

                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('bank_code', __('lang_v1.bank_code') . ':') !!} @show_tooltip(__('lang_v1.bank_code_help'))
                                        {!! Form::text('bank_details[bank_code]', 'SA', [
                                            'class' => 'form-control',
                                            'style' => 'height:40px',
                                            'id' => 'bank_code',
                                            'placeholder' => __('lang_v1.bank_code'),
                                            'oninput' => 'validateBankCode(this)',
                                            'maxlength' => '24',
                                        ]) !!}
                                        <span id="bankCodeError" class="text-danger"></span>
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('iban_file', __('lang_v1.iban_file') . ':') !!}
                                        {!! Form::file('iban_file', null, [
                                            'class' => 'form-control',
                                            'style' => 'height:40px',
                                            'id' => 'iban_file',
                                            'placeholder' => __('lang_v1.iban_file'),
                                        ]) !!}
                                        <span id="bankCodeError" class="text-danger"></span>
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




        </div>


    </section>

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var bankAccounts_table = $('#bankAccounts_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('bankAccountsForLabors') }}',
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'full_name',
                        name: 'full_name'
                    },
                    {
                        data: 'account_holder_name',
                        name: 'account_holder_name'
                    },
                    {
                        data: 'account_number',
                        name: 'account_number'
                    },
                    {
                        data: 'bank_name',
                        name: 'bank_name'
                    },
                    {
                        data: 'bank_code',
                        name: 'bank_code'
                    },
                    {
                        data: 'iban_file',
                        name: 'iban_file',
                        orderable: false,
                        searchable: false
                    }
                ],
            });


            $('body').on('submit', '#addBankForm', function(e) {
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
                            bankAccounts_table.ajax.reload();
                            toastr.success(response.msg);
                            $('#createBankModal').modal('hide');
                        } else {
                            toastr.error(response.msg);
                            $('#createBankModal').modal('hide');
                            console.log(response);
                        }
                    },
                    error: function(error) {
                        console.error('Error submitting form:', error);
                        toastr.error('An error occurred while submitting the form.', 'Error');
                    },
                });
            });



        });
    </script>

    <script>
        function validateBankCode(input) {
            const bankCode = input.value;

            if (bankCode.length === 24 && bankCode.startsWith('SA')) {
                document.getElementById('bankCodeError').innerText = '';
            } else {
                if (bankCode.length !== 24) {
                    document.getElementById('bankCodeError').innerText = 'رقم البنك يجب أن يحتوي على 24 رقم';
                } else if (!bankCode.startsWith('SA')) {
                    document.getElementById('bankCodeError').innerText = 'رقم البنك يجب أن يبدأ بـ SA';
                }


                if (bankCode.length > 24) {
                    input.value = bankCode.substr(0, 24);
                }
            }
        }
    </script>
@endsection
