@extends('layouts.app')
@section('title', __('essentials::lang.late_admission'))

@section('content')
  
    <section class="content-header">

        <h1>@lang('essentials::lang.late_admission')
        </h1>
       
        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                    


                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="expired_residencies">
                                <thead>
                                    <tr>

                                    <th>@lang('essentials::lang.emp_name')</th>
                                
                                    <th>@lang('followup::lang.project')</th>
                                    <th>@lang('followup::lang.customer_name')</th>
                                    <th>@lang('followup::lang.status')</th>
                                    <th></th>

                                    </tr>
                                </thead>
                            </table>
                        </div>
                    @endcomponent
                   
                </div>
            </div>

        </section>
    @endsection

    @section('javascript')
        <script type="text/javascript">
            $(document).ready(function() {

               
                var expired_residencies;

                function reloadDataTable() {
                    expired_residencies.ajax.reload();
                }

                expired_residencies = $('#expired_residencies').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('late_admission') }}",

                    },

                    columns: [

                        {
                            data: 'worker_name'
                        },
                       
                        {
                            data: 'project'
                        },
                        {
                            data: 'customer_name'
                        },
                        {
                            data: 'status'
                        },
                        {
                            data: 'action'
                        },
                    ],
                });


             

            });
        </script>
    @endsection
