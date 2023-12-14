@extends('layouts.app')
@section('title', __('followup::lang.contrascts_wishes'))

@section('content')


<section class="content-header">
    <h1>
        <span>@lang('followup::lang.contrascts_wishes')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
              
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('project_name_filter', __('followup::lang.project_name') . ':') !!}
                        {!! Form::select('project_name_filter',
                            $projects, null,
                             [   'class' => 'form-control select2', 'style' => ' height:40px;width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('wish_status_filter', __('followup::lang.wish') . ':') !!}
                        {!! Form::select('wish_status_filter',
                            $wishes, null,
                             [   'class' => 'form-control select2',
                              'id'=>'wish_status_filter',
                              'style' => ' height:40px;width:100%',
                              'placeholder' => __('lang_v1.all')]); !!}
                
                    </div>
                </div>

              
                
              
            @endcomponent
        </div>
    </div>
    @component('components.widget', ['class' => 'box-primary'])
    @slot('tool')
            <div class="box-tools">
                
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#add_wish_contact">
                    <i class="fa fa-plus"></i>  @lang('essentials::lang.add_wish')
                </button>
            </div>
            @endslot
      
      
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="contract_wish_table">
                    <thead>
                        <tr>
                            <th>@lang('followup::lang.worker_number')</th>
                            <th>@lang('followup::lang.worker_name')</th>
                            <th>@lang('sales::lang.project_name')</th>
                            <th>@lang('followup::lang.residency')</th>
                            <th>@lang('sales::lang.start_date')</th>
                            <th>@lang('sales::lang.end_date')</th>
                            <th>@lang('followup::lang.wish')</th>
                         
                            <th>@lang('sales::lang.action')</th>


                        </tr>
                    </thead>
                </table>
            </div>
 
    @endcomponent

    <div class="modal fade" id="add_wish_contact" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        {!! Form::open(['route' => 'addWishcontact', 'enctype' => 'multipart/form-data']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('essentials::lang.add_wish')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
         
                <div class="form-group col-md-6">
                {!! Form::label('employees', __('followup::lang.worker') . ':*') !!}
                {!! Form::select('employees', $employees->pluck('full_name', 'id'), null, [
                    'class' => 'form-control',
                    'placeholder' => __('followup::lang.choose_worker'),
                    'required',
                    'id'=>'employees_select',
                    'style' => 'height:40px',
                    'onchange' => 'loadWishFile(this.value)',
                ]) !!}
            </div>

                  <div class="clearfix"></div>

                    <div class="form-group col-md-6" id="main_reason_box">
                   
                        <div class="form-group">
                           
                        {!! Form::label('offer_status_filter', __('followup::lang.wish') . ':') !!}
                        {!! Form::select('wish',
                            $wishes, null,
                             ['class' => 'form-control',
                              'id'=>'status_dropdown',
                              'style' => ' height:40px;width:100%',
                              'placeholder' => __('lang_v1.all')]); !!}
                        </div>
                    </div>

                  
                    
                    <div class="clearfix"></div>
                
                    


                    <div class="form-group col-md-6">
                                    <button type="button" id="viewWishFileButton" class="btn btn-primary" style="display: none;">
                                        @lang('essentials::lang.view_wish_file')
                                    </button>
                        </div>
                                   
                                    <div id="noWishFileMessage" style="display: none;">
                                        <div class="form-group col-md-8">
                                            <button type="button"  class="btn btn-primary">
                                            @lang('essentials::lang.no_wish_file_to_show')
                                            </button>
                                                <div class="clearfix"></div>
                        </br>

                                                                    {!! Form::label('file', __('essentials::lang.wish_file_select') . ':*') !!}
                                                                    {!! Form::file('file', null, [
                                                                        'class' => 'form-control',
                                                                        'placeholder' => __('essentials::lang.wish_file'),
                                                                    
                                                                        'style'=>'height:40px',
                                                                    ]) !!}
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

    @include('followup::contracts_wishes.change_wish_modal')

</section>


@endsection

@section('javascript')
<script  type="text/javascript">
    $(document).ready(function() {
         
        $('#add_wish_contact').on('shown.bs.modal', function(e) {
                $('#status_dropdown').select2({
                    dropdownParent: $(
                        '#add_wish_contact'),
                    width: '100%',
                });

                $('#employees_select').select2({
                    dropdownParent: $(
                        '#add_wish_contact'),
                    width: '100%',
                });
               
            });

        $('#change_status_modal').on('shown.bs.modal', function() {
           
            WishFile($('#employee_id').val());
        });
     
        function WishFile(employeeId) {
            
            console.log('loadWishFile called with employeeId:', employeeId);
            $.ajax({
                url: '{{ route("getWishFile", ["employeeId" => ":employeeId"]) }}'.replace(':employeeId', employeeId),
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        if (response.wish_file) {
                            $('#viewWishFile').attr('onclick', 'window.location.href = "/uploads/' + response.wish_file + '"');
                            $('#viewWishFile').show();
                            $('#noWishFile').hide();
                        } else {
                            $('#viewWishFile').hide();
                            $('#noWishFile').show();
                        }
                    } else {
                        $('#viewWishFile').hide();
                        $('#noWishFile').show();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Request Failed: ', textStatus, errorThrown);
                }
            });
        }

 
        $('#employee_id').change(function() {
            WishFile($(this).val());
        });
    });
</script>

<script>
    function loadWishFile(employeeId) {
        console.log(employeeId);
      
        $.ajax({
            url: '{{ route("getWishFile", ["employeeId" => ":employeeId"]) }}'.replace(':employeeId', employeeId),
            type: 'GET',
            success: function(response) {
              
                if (response.success) {
                    if (response.wish_file) {
                     
                        $('#viewWishFileButton').attr('onclick', 'window.location.href = "/uploads/' + response.wish_file + '"');
                        $('#viewWishFileButton').show();
                        $('#noWishFileMessage').hide();
                    } else {
                       
                        $('#viewWishFileButton').hide();
                        $('#noWishFileMessage').show();
                    }
                } else {
                
                    $('#viewWishFileButton').hide();
                    $('#noWishFileMessage').show();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
       
                console.error('AJAX Request Failed: ', textStatus, errorThrown);
               
            }
        });
    }
</script>


    <script type="text/javascript">
        $(document).ready(function () {
            var contractWishTable = $('#contract_wish_table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                        "url": '{{ route("contracts_wishes") }}',
                        "data": function ( d ) {
                            

                            d.project_name_filter = $('#project_name_filter').val();
                            d.wish_status_filter = $('#wish_status_filter').val();
                            console.log($('#project_name_filter').val());
                            console.log($('#wish_status_filter').val());
                        }
                    },
                
              
                columns: [
                    { data: 'emp_number', name: 'emp_number' },
                    { data: 'name', name: 'name' },
                    { data: 'project_name', name: 'project_name' },
                    { data: 'residency', name: 'residency' },
                    { data: 'contract_start_date', name: 'contract_start_date' },
                    { data: 'contract_end_date', name: 'contract_end_date' },
                    { data: 'wish', name: 'wish' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });


            $(document).on('change', '#wish_status_filter',  function() {
                contractWishTable.ajax.reload();
                });
            $(document).on('change', '#project_name_filter',  function() {
                contractWishTable.ajax.reload();
                });

            $(document).on('click', 'a.change-status-btn', function(e) {
            e.preventDefault();
            $('#change_status_modal').find('select#status_dropdown').val($(this).data('orig-value')).change();
            $('#change_status_modal').find('#employee_id').val($(this).data('employee-id'));
            $('#change_status_modal').modal('show');
            console.log($(this).data('employee-id'));     
             }); 
        
    
$(document).on('submit', 'form#change_status_form', function (e) {
    e.preventDefault();

    var formData = new FormData(this);

    var ladda = Ladda.create(document.querySelector('.update-offer-status'));
    ladda.start();

    $.ajax({
        method: $(this).attr('method'),
        url: $(this).attr('action'),
        dataType: 'json',
        data: formData,
        processData: false,
        contentType: false,  // Set content type to false to prevent jQuery from adding a content type header
        success: function (result) {
            ladda.stop();

            if (result.success == true) {
                $('div#change_status_modal').modal('hide');
                toastr.success(result.msg);
                contractWishTable.ajax.reload();
            } else {
                toastr.error(result.msg);
            }
        },
    });
});


        });
    </script>
@endsection
