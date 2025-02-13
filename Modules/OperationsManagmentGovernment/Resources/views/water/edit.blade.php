@extends('layouts.app')
@section('title', __('operationsmanagmentgovernment::lang.edit_water_weight'))

@section('content')

    <section class="content-header">
        <h1>@lang('operationsmanagmentgovernment::lang.edit_water_weight')</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-body">
                        {!! Form::open([
                            'route' => ['operationsmanagmentgovernment.water_weight.update', $waterWeight->id],
                            'method' => 'PUT',
                        ]) !!}
                        <div class="row">
                            <div class="col-md-6">
                                <label>@lang('operationsmanagmentgovernment::lang.company')</label>
                                {!! Form::select('company_id', $companies->pluck('name', 'id'), $waterWeight->company_id, [
                                    'class' => 'form-control select2',
                                    'placeholder' => __('messages.select'),
                                ]) !!}
                            </div>

                            <div class="col-md-6">
                                <label>@lang('operationsmanagmentgovernment::lang.contact')</label>
                                {!! Form::select('contact_id', $contacts->pluck('name', 'id'), $waterWeight->contact_id, [
                                    'class' => 'form-control select2',
                                    'placeholder' => __('messages.select'),
                                ]) !!}
                            </div>

                            <div class="col-md-6">
                                <label>@lang('operationsmanagmentgovernment::lang.driver')</label>
                                {!! Form::select('driver_id', $drivers->pluck('name', 'id'), $waterWeight->driver_id, [
                                    'class' => 'form-control select2',
                                    'placeholder' => __('messages.select'),
                                ]) !!}
                            </div>

                            <div class="col-md-6">
                                <label>@lang('operationsmanagmentgovernment::lang.plate_number')</label>
                                {!! Form::text('plate_number', $waterWeight->plate_number, [
                                    'class' => 'form-control',
                                ]) !!}
                            </div>

                            <div class="col-md-6">
                                <label>@lang('operationsmanagmentgovernment::lang.weight_type')</label>
                                {!! Form::select(
                                    'weight_type',
                                    [
                                        '6_tons' => __('operationsmanagmentgovernment::lang.6_tons'),
                                        '18_tons' => __('operationsmanagmentgovernment::lang.18_tons'),
                                        '34_tons' => __('operationsmanagmentgovernment::lang.34_tons'),
                                    ],
                                    $waterWeight->weight_type,
                                    [
                                        'class' => 'form-control select2',
                                        'placeholder' => __('messages.select'),
                                    ],
                                ) !!}
                            </div>

                            <div class="col-md-6">
                                <label>@lang('operationsmanagmentgovernment::lang.sample_result')</label>
                                {!! Form::text('sample_result', $waterWeight->sample_result, [
                                    'class' => 'form-control',
                                ]) !!}
                            </div>

                            <div class="col-md-6">
                                <label>@lang('operationsmanagmentgovernment::lang.date')</label>
                                {!! Form::date('date', $waterWeight->date, [
                                    'class' => 'form-control',
                                ]) !!}
                            </div>
                        </div>

                        <div class="text-right mt-4">
                            <button type="submit" class="btn btn-success">@lang('messages.update')</button>
                            <a href="{{ route('operationsmanagmentgovernment.water') }}"
                                class="btn btn-default">@lang('messages.cancel')</a>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
