@extends('layouts.app')


@section('title',__('lang_v1.add_quotation'))

@section('content')

<section class="content-header">
    <h1>@lang('lang_v1.add_quotation')</h1>
</section>

<section class="content no-print">




    {!! Form::open(['route' => ['updateOfferPrice', $offer_price->id], 'method' => 'put', 'id' => 'update_sell_form']) !!}
    
        <div class="row">
            <div class="col-md-12 col-sm-12">
                @component('components.widget', ['class' => 'box-solid'])
               

                @if(count($business_locations) > 0)
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-map-marker"></i>
                                </span>
                                    {!! Form::select('location_id', $business_locations, $offer_price->location_id ?? null, ['class' => 'form-control input-sm',
                                    'id' => 'location_id', 
                                    'required']); !!}
                                
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="form-group col-md-10">
                                {!! Form::label('contact_id', __('sales::lang.customer') . ':*') !!}
                                {!! Form::select('contact_id',$leads,$offer_price->contact_id, ['class' => 'form-control', 'placeholder' => __('sales::lang.select_customer'), 'required']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                        {!! Form::label('contract_form', __('sales::lang.contract_form') . ':*') !!}
                        {!! Form::select('contract_form',
                            ['monthly_cost' => __('sales::lang.monthly_cost'), 
                            'operating_fees' => __('sales::lang.operating_fees')],
                            $offer_price->contract_form,
                            ['class' => 'form-control', 'required',
                            'placeholder' => __('sales::lang.contract_form')]); !!}
                    </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('status', __('sale.status') . ':*') !!}
                            {!! Form::select('status',
                            ['approved' => __('sales::lang.approved'), 
                            'transfared' => __('sales::lang.transfared'),
                            'cancelled' => __('sales::lang.cancelled'),
                            'under_study' => __('sales::lang.under_study'),

                            ], $offer_price->status, ['class' => 'form-control', 'required',
                            'placeholder' => __('sale.status')]); !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                        {!! Form::label('down_payment', __('sales::lang.down_payment') . ':*') !!}
                        {!! Form::Number('down_payment',$offer_price->down_payment, ['class' => 'form-control', 'required',
                            'placeholder' => __('sales::lang.down_payment')]); !!}
                    </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('transaction_date', __('sale.sale_date') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('transaction_date', $offer_price->transaction_date, ['class' => 'form-control', 'required']); !!}
                            </div>
                        </div>
                    </div>

                    
                    
                @endcomponent
            
            </div>
        </div>


	
        <div class="row">
            <div class="col-sm-12 text-center">
                <button type="submit" class="submit btn btn-primary btn-big">@lang('messages.update')</button>
                <button type="button" class="btn btn-default btn-big" data-dismiss="modal">@lang( 'messages.close' )</button>
            </div>
        </div>
      
      
	
	
	{!! Form::close() !!}
</section>


@stop
