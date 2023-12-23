@extends('layouts.app')
@section('title', __('sales::lang.contract_appendics'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('sales::lang.contract_appendics')</span>
        </h1>
    </section>

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open(['route' => ['updateAppendix', $appendix->id], 'method' => 'put', 'id' => 'add_appedndix_form']) !!}


            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('sales::lang.edit_appendix')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-12">
                        {!! Form::label('contract', __('sales::lang.contract_number') . ':*') !!}
                        {!! Form::select('contract', $contracts, $appendix->contract_id, [
                            'class' => 'form-control',
                            'placeholder' => __('sales::lang.contract_number'),
                        ]) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('number_of_item', __('sales::lang.number_of_item') . ':*') !!}
                        {!! Form::number('number_of_item', $item->number_of_item, [
                            'class' => 'form-control',
                            'placeholder' => __('sales::lang.number_of_item'),
                            'required',
                        ]) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('name_of_item', __('sales::lang.name_of_item') . ':*') !!}
                        {!! Form::text('name_of_item', $item->name_of_item, [
                            'class' => 'form-control',
                            'placeholder' => __('sales::lang.name_of_item'),
                            'required',
                        ]) !!}
                    </div>


                    <div class="form-group col-md-12">
                        {!! Form::label('notes', __('sales::lang.notes') . ':') !!}
                        {!! Form::textarea('notes', $appendix->notes, [
                            'class' => 'form-control',
                            'placeholder' => __('sales::lang.notes'),
                            'rows' => 2,
                        ]) !!}
                    </div>

                    <div class="form-group col-md-12">

                        <div class="form-group">
                            {!! Form::label('file_contract_appendices', __('sales::lang.file_contract_appendices') . '*') !!}
                            {!! Form::file('file_contract_appendices', ['class' => 'form-control', 'required', 'accept' => 'doc/*']) !!}


                        </div>
                        <a href="{{ env('APP_URL') }}/uploads/{{ $appendix->file_contract_appendices }} "
                            class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> عرض مرفق العقود </a>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>

            {!! Form::close() !!}

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
@endsection
