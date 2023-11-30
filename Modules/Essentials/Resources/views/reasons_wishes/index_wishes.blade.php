@extends('layouts.app')
@section('title', __('essentials::lang.wishes'))
@section('content')
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.wishes')</span>
    </h1>
</section>

<section class="content">
@component('components.filters', ['title' => __('report.filters')])


       <div class="col-md-3">
           <div class="form-group">
               <label for="employee_type_filter">@lang('essentials::lang.employee_type'):</label>
               <select class="form-control select2" name="employee_type_filter" required id="employee_type_filter" style="width: 100%;">
                   <option value="all">@lang('lang_v1.all')</option>
                   <option value="employee">@lang('essentials::lang.employee')</option>
                   <option value="manager">@lang('essentials::lang.manager')</option>
                   <option value="worker">@lang('essentials::lang.worker')</option>
               </select>
           </div>
       </div>


     
     

@endcomponent

@component('components.widget', ['class' => 'box-primary'])

@slot('tool')
            <div class="box-tools">
                
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addwish">
                    <i class="fa fa-plus"></i>  @lang('essentials::lang.add_wish')
                </button>
            </div>
            @endslot
      


        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="wish_table">
                <thead>
                    <tr>
                   
                        <th>@lang('essentials::lang.employee_type')</th>
                     
                        <th>@lang('essentials::lang.wish')</th>
                    
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>

    @endcomponent

    <div class="modal fade" id="addwish" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        {!! Form::open(['route' => 'store_wish', 'enctype' => 'multipart/form-data']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('essentials::lang.add_wish')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
         
              
                <div class="form-group col-md-6">
                    {!! Form::label('employee_type_filter', __('essentials::lang.employee_type') . ':*') !!}
                    {!! Form::select('employee_type', [
                         'employee' => __('essentials::lang.employee'),
                          'manager' => __('essentials::lang.manager'), 'worker' => __('essentials::lang.worker')], null, ['class' => 'form-control select2',  'required', 'id' => 'employee_type_filter', 'style' => 'width: 100%;']) !!}
                </div>
            
                       
                   

                    <div class="clearfix"></div>

                    <div class="form-group col-md-6" id="main_reason_box">
                        {!! Form::label('wish', __('essentials::lang.wish') . ':') !!}
                        <div class="form-group">
                            {!! Form::text('wish', null, ['class' => 'form-control',
                                 'placeholder' => __('essentials::lang.wish')]) !!}
                        </div>
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

    <!-- Edit Modal -->
 <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
    <input type="hidden" id="editItemId" value="">
    <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('essentials::lang.update_wish')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
         
              
                <div class="form-group col-md-6">
                    {!! Form::label('employee_type_filter', __('essentials::lang.employee_type') . ':*') !!}
                    {!! Form::select('employee_type', [
                         'employee' => __('essentials::lang.employee'),
                          'manager' => __('essentials::lang.manager'), 'worker' => __('essentials::lang.worker')], null, ['class' => 'form-control select2',  'required', 'id' => 'employee_type_filter', 'style' => 'width: 100%;']) !!}
                </div>
            
                       
                   

                    <div class="clearfix"></div>

                    <div class="form-group col-md-6" id="main_reason_box">
                        {!! Form::label('wish', __('essentials::lang.wish') . ':') !!}
                        <div class="form-group">
                            {!! Form::text('wish', null, ['class' => 'form-control',
                                 'placeholder' => __('essentials::lang.wish')]) !!}
                        </div>
                    </div>


                   
                   
                </div>
                   
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
          
        </div>
    </div>
</div>

</section>
@endsection
@section('javascript')
<script type="text/javascript">
    $(document).ready(function () {
      
      

        var wish_table = $('#wish_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("wishes") }}',
                data: function (d) {
                    d.employee_type_filter = $('#employee_type_filter').val();
                   
                }
            },
            columns: [
                { data: 'employee_type'},
                { data: 'reason'},
               
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

      
        $('#employee_type_filter').on('change', function () {
            wish_table.ajax.reload();
        });

        $(document).on('click', 'button.delete_country_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_country,
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
                                wish_table.ajax.reload();
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

<script>
    $(document).ready(function () {
        $('.edit_button').click(function () {
            var itemId = $(this).data('id');
            $('#editItemId').val(itemId);
            $('#editModal').modal('show');
        });

      
        $('#saveChangesButton').click(function () {
            var itemId = $('#editItemId').val();
            var employeeType = $('#employee_type_filter').val();
            var wish = $('#wish').val();

         
            $.ajax({
                url: '/wishes/' + itemId + '/update',
                method: 'POST', 
                data: {
                    _token: '{{ csrf_token() }}',
                    employee_type: employeeType,
                    wish: wish
                 
                },
                success: function (data) {
                  
                    console.log(data);
                   
                    $('#editModal').modal('hide');
                },
                error: function (xhr, status, error) {
                  
                    console.error(error);
                }
            });
        });
    });
</script>


@endsection