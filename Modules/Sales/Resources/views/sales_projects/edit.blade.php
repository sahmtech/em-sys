@extends('layouts.app')
@section('title', __('sales::lang.contact_locations'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <span>@lang('sales::lang.contact_locations_edit')</span>
        </h1>
    </section>


    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open([
                'route' => ['sale.updateSaleProject', $contactLocation->id],
                'method' => 'put',
                'id' => 'edit_contact_lcoation_form',
            ]) !!}

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('sales::lang.contact_locations_edit')</h4>
            </div>
            <div class="modal-body">

                <div class="row">

                    <div class="form-group col-md-6">
                        {!! Form::label('contact_name', __('sales::lang.contact_name') . ':*') !!}
                        {!! Form::select('contact_name', $contacts, $contactLocation->contact_id , [
                            'class' => 'form-control',
                            'style' => ' height: 40px',
                            'required',
                            'placeholder' => __('sales::lang.contact_name'),
                            'id' => 'contact_name',
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('contact_location_name', __('sales::lang.contact_location_name') . ':*') !!}
                        {!! Form::text('contact_location_name', $contactLocation->name , [
                            'class' => 'form-control',
                            'style' => ' height: 40px',
                            'required',
                            'placeholder' => __('sales::lang.contact_location_name'),
                            'id' => 'contact_location_name',
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('contact_location_city', __('sales::lang.contact_location_city')) !!}
                        {!! Form::select('contact_location_city',$cities,  $contactLocation->city, [
                            'class' => 'form-control',
                            'style' => ' height: 40px',
                            'placeholder' => __('sales::lang.contact_location_city'),
                            'id' => 'contact_location_city',
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('contact_location_name_in_charge', __('sales::lang.contact_location_name_in_charge')) !!}
                        {!! Form::select('contact_location_name_in_charge', $name_in_charge_choices, $contactLocation->name_in_charge , [
                            'class' => 'form-control',
                            'style' => ' height: 40px',
                            'placeholder' => __('sales::lang.contact_location_name_in_charge'),
                            'id' => 'contact_location_name_in_charge',
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('contact_location_phone_in_charge', __('sales::lang.contact_location_phone_in_charge')) !!}
                        {!! Form::text('contact_location_phone_in_charge',  $contactLocation->phone_in_charge , [
                            'class' => 'form-control',
                            'style' => ' height: 40px',
                            'placeholder' => __('sales::lang.contact_location_phone_in_charge'),
                            'id' => 'contact_location_phone_in_charge',
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('contact_location_email_in_charge', __('sales::lang.contact_location_email_in_charge')) !!}
                        {!! Form::email('contact_location_email_in_charge',  $contactLocation->email_in_charge , [
                            'class' => 'form-control',
                            'style' => ' height: 40px',
                            'placeholder' => __('sales::lang.contact_location_email_in_charge'),
                            'id' => 'contact_location_email_in_charge',
                        ]) !!}
                    </div>





                </div>
            </div>


            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>

            {!! Form::close() !!}

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
@endsection
