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
                            <th>@lang('essentials::lang.dob')</th>
                            <th>@lang('essentials::lang.department' )</th>
                            <th>@lang('essentials::lang.profession' )</th>
                            <th>@lang('essentials::lang.specialization' )</th>
                            <th>@lang('essentials::lang.mobile_number' )</th>
                            <th>@lang( 'business.email' )</th>
                            <th>@lang( 'essentials::lang.status' )</th>
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
                    ajax: '/hrm/employees',
                    
                    "columns":[
                        {"data":"id"},
                        {"data":"full_name"},
                        {"data":"dob"},
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
                        {"data":"action"}
                    ]
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
