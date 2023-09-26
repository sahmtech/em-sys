@extends('layouts.app')
@section('title', __('essentials::lang.business'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.business')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @can('business.create')
            @slot('tool')
            <div class="box-tools">
                
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addBusinessDocModal">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>
            </div>
            @endslot
        
        @endcan
       
        @can('business.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="business_docs_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.licence_type')</th>
                            <th>@lang('essentials::lang.licence_number')</th>
                            <th>@lang('essentials::lang.licence_date')</th>                           
                            <th>@lang('essentials::lang.renew_date')</th>
                            <th>@lang('essentials::lang.expiration_date')</th>
                            <th>@lang('essentials::lang.issuing_location')</th>
                            <th>@lang('essentials::lang.details')</th>
                            <th>@lang('essentials::lang.file')</th>
                            

            
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade business_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade" id="addBusinessDocModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'storeBusinessDoc']) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('essentials::lang.add_BusinessDoc')</h4>
                </div>
    
                <div class="modal-body">

                    <div class="row">
                        <input type="hidden" name="business_id" value="{{ $business_id }}">
                        <div class="form-group col-md-6">
                            {!! Form::label('licence_type', __('essentials::lang.licence_type') . ':*') !!}
                            {!! Form::select('licence_type', [
                                'COMMERCIALREGISTER' => 'Commercial Register',
                                'Gosi' => 'Gosi',
                                'Zatca' => 'Zatca',
                                'Chamber' => 'Chamber',
                                'Balady' => 'Balady',
                            ], null, ['class' => 'form-control', 'placeholder' => 'Select Licence Type', 'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('licence_number', __('essentials::lang.licence_number') . ':*') !!}
                            {!! Form::text('licence_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.licence_number'), 'required']) !!}
                        </div>
    
                        <div class="form-group col-md-6">
                            {!! Form::label('licence_date', __('essentials::lang.licence_date') . ':*') !!}
                            {!! Form::date('licence_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.licence_date'), 'required']) !!}
                        </div>
    
                        <div class="form-group col-md-6">
                            {!! Form::label('renew_date', __('essentials::lang.renew_date') . ':*') !!}
                            {!! Form::date('renew_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.renew_date'), 'required']) !!}
                        </div>
    
                        <div class="form-group col-md-6">
                            {!! Form::label('expiration_date', __('essentials::lang.expiration_date') . ':') !!}
                            {!! Form::date('expiration_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.expiration_date'), 'requires']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('issuing_location', __('essentials::lang.issuing_location') . ':') !!}
                            {!! Form::text('issuing_location', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.issuing_location'), 'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('details', __('essentials::lang.contry_details') . ':') !!}
                            {!! Form::textarea('details', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.contry_details'), 'rows' => 2]) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('file', __('essentials::lang.file') . ':') !!}
                            {!! Form::file('file', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.file'), 'required']) !!}
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
    
</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function () { 
        var business_docs_table = $('#business_docs_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('business_documents.view', ['id' => '1']) }}",
             
            columns: [
                { data: 'licence_type' },
                { data: 'licence_number' },
                { data: 'licence_date' },
                { data: 'renew_date' },
                { data: 'expiration_date' },
                { data: 'issuing_location' },
                { data: 'details' },
                { data: 'path_file' },
             
            ]
        });

     
    });
</script>

@endsection
