@extends('layouts.app')
@section('title', __( 'essentials::lang.employees' ))

@section('content')
@include('essentials::layouts.nav_employee_affairs')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        @lang( 'essentials::lang.manage_employees' )
    </h1>
  
</section>

<!-- Main content -->
<section class="content">
@component('components.filters', ['title' => __('report.filters')])

<div class="col-md-3">
    <div class="form-group">
        <label for="specializations_filter">@lang('essentials::lang.specializations'):</label>
        {!! Form::select(
    'specializations-select',
    $specializations,
    request('specializations-select'),
    [
        'class' => 'form-control select2', // Add the select2 class
        'style' => 'height:36px',
        'placeholder' => __('lang_v1.all'),
        'id' => 'specializations-select',
    ]
) !!}
    </div>
</div>
     
<div class="col-md-3">
    <div class="form-group">
        <label for="professions_filter">@lang('essentials::lang.professions'):</label>
        {!! Form::select(
    'professions-select',
    $professions,
    request('professions-select'),
    [
        'class' => 'form-control select2', // Add the select2 class
        'style' => 'height:36px',
        'placeholder' => __('lang_v1.all'),
        'id' => 'professions-select',
    ]
) !!}
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
    <label for="professions_filter">@lang('essentials::lang.status'):</label>
    <select class="form-control select2" name="status_filter" required id="status_filter" style="width: 100%;">
                    <option value="all">@lang('lang_v1.all')</option>
                    <option value="active">@lang('sales::lang.active')</option>
                    <option value="inactive">@lang('sales::lang.inactive')</option>
                    <option value="terminated">@lang('sales::lang.terminated')</option>
                    <option value="vecation">@lang('sales::lang.vecation')</option>


                </select>
    </div>
</div>




@endcomponent
    @component('components.widget', ['class' => 'box-primary'])

    <div class="row">
        <div class="col-sm-3">
            @can('user.create')
                @slot('tool')
                    <div class="box-tools">
                        <a class="btn btn-block btn-primary" href="{{ route('createEmployee') }}">
                            <i class="fa fa-plus"></i> @lang('messages.add')
                        </a>
                    </div>
                @endslot
            @endcan
        </div>
    
        @if(count($business_locations) > 0)
            <div class="col-sm-3">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-map-marker"></i>
                        </span>
                        {!! Form::select('select_location_id', $business_locations, null, [
                            'class' => 'form-control input-sm',
                            'id' => 'select_location_id',
                            'style' => 'height:36px; width:100%',
                            'placeholder' => __('lang_v1.all'),
                            'required',
                            'autofocus'
                        ], $bl_attributes); !!}
    
                        <span class="input-group-addon">
                            @show_tooltip(__('tooltip.sale_location'))
                        </span>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    
        @can('user.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="employees">
                    <thead>
                        <tr>
                          
                            <th>@lang('essentials::lang.employee_number' )</th>
                            <th>@lang('essentials::lang.employee_name' )</th>
                            <th>@lang('essentials::lang.Identity_proof_id')</th>
                            <th>@lang('essentials::lang.contry_nationality')</th>
                            
                            <th>@lang('essentials::lang.admissions_date')</th>
                            <th>@lang('essentials::lang.contract_end_date' )</th>

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
                                {!! Form::select('employee',$users, null,
                                     ['class' => 'form-control','style'=>'height:40px',
                                      'placeholder' => __('essentials::lang.select_employee'), 'required']) !!}
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

                    {!! Form::open(['route' => 'storeOfficialDoc' , 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_Doc')</h4>
                    </div>
        
                    <div class="modal-body">
    
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('employees2', __('essentials::lang.employee') . ':*') !!}
                                {!! Form::select('employees2',$users, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_employee'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('doc_type', __('essentials::lang.doc_type') . ':*') !!}
                                {!! Form::select('doc_type', [
                                   
                                    'national_id'=>__('essentials::lang.national_id'),
                                    'passport'=>__('essentials::lang.passport'),
                                    'residence_permit'=>__('essentials::lang.residence_permit'),
                                    'drivers_license'=>__('essentials::lang.drivers_license'),
                                    'car_registration'=>__('essentials::lang.car_registration'),
                                    'international_certificate'=>__('essentials::lang.international_certificate'),
                                ], null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_type'), 'required']) !!}
                            </div>
        
                            <div class="form-group col-md-6">
                                {!! Form::label('doc_number', __('essentials::lang.doc_number') . ':*') !!}
                                {!! Form::number('doc_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.doc_number'), 'required']) !!}
                            </div>
        
                            <div class="form-group col-md-6">
                                {!! Form::label('issue_date', __('essentials::lang.issue_date') . ':*') !!}
                                {!! Form::date('issue_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.issue_date'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('issue_place', __('essentials::lang.issue_place') . ':*') !!}
                                {!! Form::text('issue_place', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.issue_place'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('status', __('essentials::lang.status') . ':*') !!}
                                {!! Form::select('status', [
                                'valid' => __('essentials::lang.valid'),
                                'expired' => __('essentials::lang.expired'),
                              
                            ], null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_status'), 'required']) !!}
                        </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('expiration_date', __('essentials::lang.expiration_date') . ':') !!}
                                {!! Form::date('expiration_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.expiration_date'), 'required']) !!}
                            </div>
                        
                            <div class="form-group col-md-6">
                                {!! Form::label('file', __('essentials::lang.file') . ':*') !!}
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
                                {!! Form::label('offer_price', __('sales::lang.offer_price') . ':') !!}
                                {!! Form::select('offer_price',$offer_prices, null, ['class' => 'form-control','id'=>'offer_price', 'placeholder' => __('sales::lang.select_offer_price')]) !!}
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
                                {!! Form::label('file', __('essentials::lang.file') . ':') !!}
                                {!! Form::file('file', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.file')]) !!}
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
$(document).on('click', '.btn-modal1', function (e) {
    e.preventDefault();
    var userId = $(this).data('row-id');
    var userName = $(this).data('row-name'); 

    $('#addQualificationModal').modal('show');

   
    $('#employee').empty(); // Clear previous options
    $('#employee').append('<option value="' + userId + '">' + userName + '</option>'); 
});

</script>


<script type="text/javascript">
$(document).on('click', '.btn-modal2', function (e) {
    e.preventDefault();
    var userId = $(this).data('row-id');
    var userName = $(this).data('row-name'); 

    $('#add_doc').modal('show');

   
    $('#employees2').empty(); // Clear previous options
    $('#employees2').append('<option value="' + userId + '">' + userName + '</option>'); 
});

</script>
<script type="text/javascript">



    //Roles table
    $(document).ready( function(){
        var users_table = $('#employees').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('employees') }}",
                        data: function (d) {
                d.specialization = $('#specializations-select').val();
                d.profession = $('#professions-select').val();
                d.status = $('#status_filter').val();
                d.location = $('#select_location_id').val(); 
           
               
            },
                    },
                            
                    
                    "columns":[
                        {"data":"emp_number"},
                        {"data":"full_name"},
                      
                        {"data":"id_proof_number"},
                        {"data":"nationality"},
                        {"data":"admissions_date"},
                        {"data":"contract_end_date"},

                        {"data":"essentials_department_id"},
                        {"data": "profession", name: 'profession'},
                        {"data": "specialization", name: 'specialization'},
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
                    ],
            "createdRow": function (row, data, dataIndex) {
            var contractEndDate = data.contract_end_date;
            console.log(contractEndDate);
            var currentDate = moment().format("YYYY-MM-DD");

            if (contractEndDate !== null && contractEndDate !== undefined) {
                var daysRemaining = moment(contractEndDate).diff(currentDate, 'days');

                if (daysRemaining <= 0) {
                    $('td', row).eq(5).addClass('text-danger'); // Contract expired, colored red
                } else if (daysRemaining <= 25) {
                    $('td', row).eq(5).addClass('text-warning'); // Contract expires within 25 days, colored yellow
                }
            }
        }
                   
              });
     

            
  
    $('#specializations-select, #professions-select, #status-select, #select_location_id').change(function () {
        console.log('Specialization selected: ' + $(this).val());
                  
                    users_table.ajax.reload();
        
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



     
    });
  
    
    
</script>



@endsection
