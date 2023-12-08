@extends('layouts.app')
@section('title', __('internationalrelations::lang.proposed_labor'))

@section('content')
@include('internationalrelations::layouts.nav_proposed_labor')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('internationalrelations::lang.unaccepted_workers')
        </h1>
   
    </section>

    <!-- Main content -->
    <section class="content">
        
        @component('components.filters', ['title' => __('report.filters')])

            <div class="col-md-3">
                <div class="form-group">
                    <label for="professions_filter">@lang('essentials::lang.professions'):</label>
                    {!! Form::select('professions-select', $professions, request('professions-select'), [
                        'class' => 'form-control select2', // Add the select2 class
                        'style' => 'height:36px',
                        'placeholder' => __('lang_v1.all'),
                        'id' => 'professions-select',
                    ]) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="specializations_filter">@lang('essentials::lang.specializations'):</label>
                    {!! Form::select('specializations-select', $specializations, request('specializations-select'), [
                        'class' => 'form-control select2',
                        'style' => 'height:36px',
                        'placeholder' => __('lang_v1.all'),
                        'id' => 'specializations-select',
                    ]) !!}
                </div>
            </div>

        

            <div class="col-md-3">
                <div class="form-group">
                    <label for="agency_filter">@lang('internationalrelations::lang.agency_name'):</label>
                    {!! Form::select('agency_filter', $agencys, request('agency_filter'), [
                        'class' => 'form-control select2', // Add the select2 class
                        'style' => 'height:36px',
                        'placeholder' => __('lang_v1.all'),
                        'id' => 'agency_filter',
                    ]) !!}
                </div>
            </div>
        
        @endcomponent

        @component('components.widget', ['class' => 'box-primary'])
          
        
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="employees">
                        <thead>
                            <tr>
                                <th>@lang('internationalrelations::lang.worker_number')</th>
                                <th>@lang('internationalrelations::lang.worker_name')</th>
                                <th>@lang('internationalrelations::lang.agency_name')</th>
                                <th>@lang('essentials::lang.mobile_number')</th>
                                <th>@lang('essentials::lang.contry_nationality')</th>
                                <th>@lang('essentials::lang.profession')</th>
                                <th>@lang('essentials::lang.specialization')</th>
                                <th>@lang('internationalrelations::lang.interviewStatus')</th>
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
                    url: "{{ route('unaccepted_workers') }}",
                    data: function(d) {
                        d.specialization = $('#specializations-select').val();
                        d.profession = $('#professions-select').val();
                        d.agency = $('#agency_filter').val();
                      

                       
                    },
                },

                "columns": [
                    {
                        "data": "id"
                    },
                    {
                        "data": "full_name"
                    },
                    {
                        "data": "agency_id"
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
                        "data": "interviewStatus",
                     
                    },
                    
                    {
                        "data": "action"
                    }
                ],
              
            });

            $('#specializations-select, #professions-select, #agency_filter').change(
             function() {
                users_table.ajax.reload();

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
