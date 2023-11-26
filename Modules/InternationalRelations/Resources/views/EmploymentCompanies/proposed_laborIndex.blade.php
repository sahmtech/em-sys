@extends('layouts.app')
@section('title', __('internationalrelations::lang.proposed_labor'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('internationalrelations::lang.proposed_labor')
        </h1>
   
    </section>

    <!-- Main content -->
    <section class="content">
    
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                @slot('tool')
                <div class="row">
                    <div class="col-sm-3">
                        <div class="box-tools">
                            <a class="btn btn-block btn-primary" 
                            href="{{ route('createProposed_labor') }}">
                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                        </div> 
                    </div>
                </div>
            @endslot
            </div>


                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="employees">
                        <thead>
                            <tr>
                                <th>@lang('internationalrelations::lang.worker_number')</th>
                                <th>@lang('internationalrelations::lang.worker_name')</th>
                                <th>@lang('essentials::lang.mobile_number')</th>
                                <th>@lang('essentials::lang.contry_nationality')</th>
                                <th>@lang('essentials::lang.profession')</th>
                                <th>@lang('essentials::lang.specialization')</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>

        @endcomponent

       


    </section>
    <!-- /.content -->
@stop
@section('javascript')
   


    <script type="text/javascript">
        $(document).ready(function() {
            var users_table = $('#employees').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('proposed_laborIndex') }}",
                },

                "columns": [
                    {
                        "data": "id"
                    },
                    {
                        "data": "full_name"
                    },

                    {
                        "data": "contact_number"
                    },
                    {
                        "data": "nationality_id"
                    },
               
                    {
                        "data": "profession_id",
                       
                    },
                    {
                        "data": "specialization_id",
                     
                    },
                   
                    {
                        "data": "action"
                    }
                ],
              
            });


            $(document).on('click', 'button.delete_user_button', function() {
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
                            success: function(result) {
                                if (result.success == true) {
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
