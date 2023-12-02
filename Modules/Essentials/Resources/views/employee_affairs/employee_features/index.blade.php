@extends('layouts.app')
@section('title', __('essentials::lang.employee_features'))


@section('content')
@include('essentials::layouts.nav_employee_features')
<section class="content-header">

    <h1>@lang('essentials::lang.employee_features')
    </h1>
    <br>
    <h1>@lang('essentials::lang.allowances')
    </h1>
<section class="content">
   
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid'])
           
                @slot('tool')
                <div class="box-tools">
                    
                    <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal" data-target="#addEmployeeAllowanceModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
                @endslot
            
            
            <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="employee_allowances_table">
                        <thead>
                            <tr>
                            <th>#</th>
                                <th>@lang('essentials::lang.employee' )</th>
                                <th>@lang('essentials::lang.allowance' )</th>
                                <th>@lang('essentials::lang.amount' )</th>
                                <th>@lang('messages.action' )</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
         <div class="modal fade" id="addEmployeeAllowanceModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'storeUserAllowance' , 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_allowance')</h4>
                    </div>
        
                    <div class="modal-body">
    
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                {!! Form::select('employee',$users, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_employee'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('allowance', __('essentials::lang.allowance') . ':*') !!}
                                {!! Form::select('allowance',$allowance_types, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_allowance'), 'required']) !!}
                            </div>
   
                            <div class="form-group col-md-6">
                                {!! Form::label('amount', __('essentials::lang.amount') . ':*') !!}
                                {!! Form::number('amount', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.amount'), 'required']) !!}
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
    </div>
</div>
  
</section>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var employee_allowances_table;

            function reloadDataTable() {
                employee_allowances_table.ajax.reload();
            }

            employee_allowances_table  = $('#employee_allowances_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {   
                    url: "{{ route('featureIndex') }}",
               
                },
                
                columns: [
                    { data: 'id' },
                        { data: 'user' },
                        { data: 'description' },
                        { data: 'amount' },
                        { data: 'action' },
                    ],
             });
      

     $(document).on('click', 'button.delete_employee_allowance_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_employee_allowance,
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
                                employee_allowances_table.ajax.reload();
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

