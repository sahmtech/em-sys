@extends('layouts.app')
@section('title', __( 'essentials::lang.employees' ))

@section('content')
@include('essentials::layouts.nav_employee_affairs')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        @lang( 'essentials::lang.manage_employees' )
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
@component('components.filters', ['title' => __('report.filters')])

<div class="col-md-3">
    <div class="form-group">
        <label for="specializations_filter">@lang('essentials::lang.specializations'):</label>
        {!! Form::select('specializations-select', $specializations, null, [
            'class' => 'form-control',
            'style' => 'height:36px',
            'placeholder' => __('lang_v1.all'),
            'required',
            'id' => 'specializations-select'
        ]) !!}
    </div>
</div>
     
<div class="col-md-3">
    <div class="form-group">
        <label for="specializations_filter">@lang('essentials::lang.professions'):</label>
        {!! Form::select('professions-select', $professions, null, [
            'class' => 'form-control',
            'style' => 'height:36px',
            'placeholder' => __('lang_v1.all'),
            'required',
            'id' => 'professions-select'
        ]) !!}
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        <label for="status_filter">@lang('essentials::lang.status'):</label>
        {!! Form::select('status-select', $status, null, [
            'class' => 'form-control',
            'style' => 'height:36px',
            'placeholder' => __('lang_v1.all'),
            'required',
            'id' => 'status-select'
        ]) !!}
    </div>
</div>
     




@endcomponent
    @component('components.widget', ['class' => 'box-primary'])
        @can('user.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" 
                    href="{{ route('createEmployee') }}" >
                    <i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
                 </div> 
            @endslot
        @endcan
        @can('user.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="employees">
                    <thead>
                        <tr>
                          
                            <th>@lang('essentials::lang.employee_number' )</th>
                            <th>@lang('essentials::lang.employee_name' )</th>
                            <th>@lang('essentials::lang.Identity_proof_id')</th>
                            <th>@lang('essentials::lang.department' )</th>
                            <th>@lang('essentials::lang.profession' )</th>
                            <th>@lang('essentials::lang.specialization' )</th>
                            <th>@lang('essentials::lang.mobile_number' )</th>
                            <th>@lang( 'business.email' )</th>
                            <th>@lang( 'essentials::lang.status' )</th>
                            <th>@lang( 'messages.view' )</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade user_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
   
    <div class="modal fade" id="addQualificationModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'storeQualification' , 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_qualification')</h4>
                    </div>
        
                    <div class="modal-body">
    
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                {!! Form::select('employee',$users, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_employee'), 'required']) !!}
                            </div>
                        
                            <div class="form-group col-md-6">
                                {!! Form::label('qualification_type', __('essentials::lang.qualification_type') . ':*') !!}
                                {!! Form::select('qualification_type', [
                                    'bachelors'=>__('essentials::lang.bachelors'),
                                     'master' =>__('essentials::lang.master'),
                                     'PhD' =>__('essentials::lang.PhD'),
                                     'diploma' =>__('essentials::lang.diploma'),
                             
                                 ], null, ['class' => 'form-control', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                             </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('major', __('essentials::lang.major') . ':*') !!}
                                {!! Form::select('major',$spacializations, null, ['class' => 'form-control', 'placeholder' =>  __('essentials::lang.major'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('graduation_year', __('essentials::lang.graduation_year') . ':') !!}
                                {!! Form::date('graduation_year', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.graduation_year'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('graduation_institution', __('essentials::lang.graduation_institution') . ':') !!}
                                {!! Form::text('graduation_institution', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.graduation_institution'), 'required']) !!}
                            </div>
                            
                            <div class="form-group col-md-6">
                                {!! Form::label('graduation_country', __('essentials::lang.graduation_country') . ':') !!}
                                {!! Form::select('graduation_country',$countries, null, ['class' => 'form-control', 'placeholder' =>  __('essentials::lang.select_country'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('degree', __('essentials::lang.degree') . ':') !!}
                                {!! Form::number('degree', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.degree'), 'required']) !!}
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


   
        
        <div class="modal fade" id="add_doc" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                {!! Form::open(['url' => action([\Modules\Essentials\Http\Controllers\DocumentController::class, 'store'])]) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_doc')</h4>
                    </div>
        
                    <div class="modal-body">
    
                    <div class="row">
					<div class="col-md-12">
					
						<div class="row">
                            <div class="col-sm-12">
	                            <div class="col-sm-6">
	                                <div class="form-group">
                                   		{!! Form::label('name', __('essentials::lang.document') . ":*") !!}

                                   		{!! Form::file('name', ['required', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]) !!}
                                   		<p class="help-block">
                        					@includeIf('components.document_help_text')
                        				</p>
	                                 </div>
	                            </div>
	                            <div class="clearfix"></div>
	                            <div class="col-sm-6">
	                                <div class="form-group">
	                                    {!! Form::label('description', __('essentials::lang.description') . ":")!!}
	                                    {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => '4', 'cols' => '50']) !!}
	                                 </div>
	                            </div>
	                            <div class="clearfix"></div>
                        		<div class="col-sm-4">
                                	<button type="submit" class="btn btn-primary btn-sm">
                                		@lang('essentials::lang.submit')
                                	</button>
                                	&nbsp;
									<button type="button" class="btn btn-danger btn-sm cancel_btn">
										@lang('essentials::lang.cancel')
									</button>
                        		</div>
                            </div>
                        </div>
                        <br><hr>
					
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

        <div class="modal fade" id="addContractModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'storeContract' , 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_contract')</h4>
                    </div>
        
                    <div class="modal-body">
    
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('offer_price', __('sales::lang.offer_price') . ':*') !!}
                                {!! Form::select('offer_price',$offer_prices, null, ['class' => 'form-control','id'=>'offer_price', 'placeholder' => __('sales::lang.select_offer_price'), 'required']) !!}
                            </div>
                        
                            <div class="form-group col-md-6">
                                {!! Form::label('contract_signer', __('sales::lang.contract_signer') . ':*') !!}
                                {!! Form::text('contract_signer', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.contract_signer'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('contract_follower', __('sales::lang.contract_follower') . ':*') !!}
                                {!! Form::text('contract_follower', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.contract_follower'), 'required']) !!}
                            </div>
                            
                            <div class="form-group col-md-6">
                                {!! Form::label('start_date', __('essentials::lang.contract_start_date') . ':*') !!}
                                {!! Form::date('start_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.contract_start_date'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('end_date', __('essentials::lang.contract_end_date') . ':*') !!}
                                {!! Form::date('end_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.contract_end_date'), 'required']) !!}
                            </div>
                             <div class="form-group col-md-6">
                                {!! Form::label('status', __('essentials::lang.status') . ':*') !!}
                                {!! Form::select('status', ['valid' => __('sales::lang.valid'), 'finished' => __('sales::lang.finished')] ,null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.status'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-8">
                                {!! Form::label('contract_items', __('sales::lang.contract_items') . ':*') !!}
                                {!! Form::select('contract_items[]', $items, null, [
                                    'class' => 'form-control select2', // Add the 'select2' class for styling
                                    'multiple' => 'multiple', // Enable multiselect
                                    'placeholder' => __('sales::lang.select_contract_items'),
                                    'required'
                                ]) !!}
                            </div>

                          
                            <div class="form-group col-md-6">
                                {!! Form::label('is_renewable', __('essentials::lang.is_renewable') . ':*') !!}
                                {!! Form::select('is_renewable', ['1' => __('essentials::lang.is_renewable'), '0' => __('essentials::lang.is_unrenewable')], null, ['class' => 'form-control']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('file', __('essentials::lang.file') . ':*') !!}
                                {!! Form::file('file', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.file'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('notes', __('sales::lang.notes') . ':') !!}
                                {!! Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.notes'), 'rows' => 2]) !!}
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
@stop
@section('javascript')
<script type="text/javascript">
    //Roles table
    $(document).ready( function(){
        var users_table = $('#employees').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                    url: "{{ route('employees') }}",
                    data: function(d) {
                        
                         d.specialization = $('#specializations-select').val();
                         d.profession = $('#professions-select').val();
                         d.status = $('#status-select').val();

                    }
                },
                   
                    
                    "columns":[
                        {"data":"id"},
                        {"data":"full_name"},
                        {"data":"id_proof_number"},
                        {"data":"essentials_department_id"},
                        {"data":"profession"},
                        {"data":"specialization"},
                        {"data":"contact_number"},
                        {"data":"email"},
                        {
                            data: 'status',
                            render: function (data, type, row) {
                                if (data === 'active') {
                                    return  '@lang('essentials::lang.active')';
                                } else if (data === 'vecation') {
                                    return  '@lang('essentials::lang.vecation')';
                                } else if(data === 'inactive'){
                                    return  '@lang('essentials::lang.inactive')';
                                }else if(data === 'terminated'){
                                    return  '@lang('essentials::lang.terminated')';
                                }else{
                                    return  ' ';
                                }
                            }
                        },
                        {"data":"view"},
                        {"data":"action"}
                    ]
                   
              });
     


$('#specializations-select, #professions-select, #status-select').change(function () {
                users_table.ajax.reload();
                
    });
                });
           
                


        $(document).on('click', 'button.delete_user_button', function(){
            swal({
              title: LANG.sure,
              text: LANG.confirm_delete_user,
              icon: "warning",
              buttons: true,
              dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();
                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        data: data,
                        success: function(result){
                            if(result.success == true){
                                toastr.success(result.msg);
                                users_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
             });
        });


        
 
    
    
</script>
@endsection
