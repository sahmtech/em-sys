@extends('layouts.app')
@section('title', __('essentials::lang.uncomplemete_profiles'))

@section('content')
  
    <section class="content-header">

        <h1>@lang('essentials::lang.uncomplemete_profiles')
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
                                    <th>@lang('essentials::lang.eqama_number')</th>
                                    <th>@lang('followup::lang.project')</th>
                                    <th>@lang('essentials::lang.sponsor')</th>
                                    <th>@lang('followup::lang.missings_files')</th>

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
                        url: "{{ route('uncomplete_profiles') }}",

                    },

                    columns: [

                        {
                            data: 'worker_name'
                        },
                        {
                            data: 'id_proof_number'
                        },
                        {
                            data: 'project'
                        },
                        {
                            data: 'sponsor'
                        },
                       
                        {
                            data: 'missings_files',
                            render: function (data, type, row) {
      
                    return type === 'display' && data != null ? data.replace(/\\n/g, '<br>') : data;
                }
                        },
                       
                    ],
                });


             

            });
        </script>
    @endsection
