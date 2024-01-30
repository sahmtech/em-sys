<div class="col-md-12  box box-primary" id="section3">

<h4>@lang('lang_v1.bank_details'):</h4>

<div class="form-group col-md-4">
    {!! Form::label('account_holder_name', __('lang_v1.account_holder_name') . ':') !!}
    {!! Form::text(
        'bank_details[account_holder_name]',
        !empty($bank_details['account_holder_name']) ? $bank_details['account_holder_name'] : null,
        [
            'class' => 'form-control',
            'style' => 'height:40px',
            'id' => 'account_holder_name',
            'placeholder' => __('lang_v1.account_holder_name'),
        ],
    ) !!}
</div>
<div class="form-group col-md-4">
    {!! Form::label('account_number', __('lang_v1.account_number') . ':') !!}
    {!! Form::text(
        'bank_details[account_number]',
        !empty($bank_details['account_number']) ? $bank_details['account_number'] : null,
        [
            'class' => 'form-control',
            'style' => 'height:40px',
            'id' => 'account_number',
            'placeholder' => __('lang_v1.account_number'),
        ],
    ) !!}
</div>
<div class="form-group col-md-4">
    {!! Form::label('bank_name', __('lang_v1.bank_name') . ':') !!}

    {!! Form::select(
        'bank_details[bank_name]',
        $banks,
        !empty($bank_details['bank_name']) ? $bank_details['bank_name'] : null,
        [
            'class' => 'form-control',
            'style' => 'height:40px',
            'id' => 'bank_name',
            'placeholder' => __('lang_v1.bank_name'),
        ],
    ) !!}

</div>
<div class="form-group col-md-4">
    {!! Form::label('bank_code', __('lang_v1.bank_code') . ':') !!} @show_tooltip(__('lang_v1.bank_code_help'))
    {!! Form::text(
        'bank_details[bank_code]',
        !empty($bank_details['bank_code']) ? $bank_details['bank_code'] : 'SA',
        [
            'class' => 'form-control',
            'style' => 'height:40px',
            'id' => 'bank_code',
            'placeholder' => __('lang_v1.bank_code'),
            'oninput' => 'validateBankCode(this)',
            'maxlength' => '24',
        ],
    ) !!}
    <span id="bankCodeError" class="text-danger"></span>
</div>
<div class="form-group col-md-4">
    {!! Form::label('branch', __('lang_v1.branch') . ':') !!}
    {!! Form::text('bank_details[branch]', !empty($bank_details['branch']) ? $bank_details['branch'] : null, [
        'class' => 'form-control',
        'style' => 'height:40px',
        'id' => 'branch',
        'placeholder' => __('lang_v1.branch'),
    ]) !!}
</div>

<div class="form-group col-md-4">
    {!! Form::label('Iban_file', __('essentials::lang.Iban_file') . ':') !!}
    {!! Form::file('Iban_file', [
        'class' => 'form-control',
        'placeholder' => __('essentials::lang.Iban_file'),
    
        'style' => 'height:40px',
    ]) !!}
</div>





</div>