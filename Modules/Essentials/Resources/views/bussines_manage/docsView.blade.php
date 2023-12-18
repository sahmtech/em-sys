@extends('layouts.app')
@section('title', __('essentials::lang.licenses'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.licenses')</span>
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
                            <th>@lang('essentials::lang.unified_number')</th>
                            <th>@lang('essentials::lang.licence_number')</th>
                            <th>@lang('essentials::lang.licence_date')</th>                           
                            <th>@lang('essentials::lang.renew_date')</th>
                            <th>@lang('essentials::lang.expiration_date')</th>
                            <th>@lang('essentials::lang.issuing_location')</th>
                            <th>@lang('essentials::lang.details')</th>
                            
                            <th>@lang('essentials::lang.action')</th>

                            

            
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
                {!! Form::open(['route' => 'storeBusinessDoc','method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
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
                                'COMMERCIALREGISTER' => __('essentials::lang.COMMERCIALREGISTER'),
                                'Gosi' => __('essentials::lang.Gosi'),
                                'Zatca' =>__('essentials::lang.Zatca'),
                                'Chamber' =>__('essentials::lang.Chamber'),
                                'Balady' => __('essentials::lang.Balady'),
                                'saudizationCertificate'=> __('essentials::lang.saudizationCertificate'),
                                'VAT'=> __('essentials::lang.VAT'),

                                'memorandum_of_association'=> __('essentials::lang.memorandum_of_association'),
                                'national_address'=> __('essentials::lang.national_address'),
                                'activity'=> __('essentials::lang.activity'),

                            ], null, ['class' => 'form-control','style'=>'height:40px',
                             'id' => 'licence_type','placeholder' => __('essentials::lang.select_licence_type'), 'required']) !!}
                        </div>
                        <div class="form-group col-md-6" id="unified_number" style="display: none;">
                            {!! Form::label('unified_number', __('essentials::lang.unified_number') . ':*') !!}
                            {!! Form::number('unified_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.unified_number')]) !!}
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
                            {!! Form::date('renew_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.renew_date')]) !!}
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
                            {!! Form::label('details', __('essentials::lang.contry_details') . ':*') !!}
                            {!! Form::textarea('details', null, ['class' => 'form-control','required',
                                 'placeholder' => __('essentials::lang.contry_details'), 'rows' => 2]) !!}
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
        var id = "{{ $business_id }}";
        var business_docs_table = $('#business_docs_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('business_documents.view', ['id' => ':id']) }}".replace(':id', id),
                type: 'GET',
            },
             
            columns: [
          
                {
                            data: 'licence_type',
                            render: function (data, type, row) {
                                if (data === 'COMMERCIALREGISTER') {
                                    return  '@lang('essentials::lang.COMMERCIALREGISTER')';
                                } else if (data === 'Gosi'){
                                    return  '@lang('essentials::lang.Gosi')';
                                }
                                else if (data === 'Zatca'){
                                    return  '@lang('essentials::lang.Zatca')';
                                }
                                else if (data === 'Chamber'){
                                    return  '@lang('essentials::lang.Chamber')';
                                }
                                else if (data === 'Balady'){
                                    return  '@lang('essentials::lang.Balady')';
                                }
                                else if (data === 'saudizationCertificate'){
                                    return  '@lang('essentials::lang.saudizationCertificate')';
                                }
                                else if (data === 'VAT'){
                                    return  '@lang('essentials::lang.VAT')';
                                }
                                else if (data === 'memorandum_of_association'){
                                    return  '@lang('essentials::lang.memorandum_of_association')';
                                }
                                else if (data === 'national_address'){
                                    return  '@lang('essentials::lang.national_address')';
                                }
                                else if (data === 'activity'){
                                    return  '@lang('essentials::lang.activity')';
                                }
                                else {
                                    return  data;
                                }
                            }
                        },
              
                { data: 'unified_number' },        
                { data: 'licence_number' },
                { data: 'licence_date' },
                { data: 'renew_date' },
                { data: 'expiration_date' },
                { data: 'issuing_location' },
                { data: 'details' },
                { data: 'action' },

             
            ]
        });
        $(document).on('click', 'button.delete_doc_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_doc,
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
                        success: function (result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                business_docs_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });
        $('#licence_type').change(function() {
            if (this.value === 'COMMERCIALREGISTER') {
                $('#unified_number').show();
            } else {
                $('#unified_number').hide();
            }
        });

     
    });
</script>

@endsection
