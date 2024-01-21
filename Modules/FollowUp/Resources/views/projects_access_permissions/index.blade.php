@extends('layouts.app')
@section('title', __('followup::lang.projects_access_permissions'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('followup::lang.projects_access_permissions')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {{-- <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('project_name_filter', __('sales::lang.contact_name') . ':') !!}
                            {!! Form::select('project_name_filter', $contacts2, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                                'id' => 'project_name_filter',
                            ]) !!}

                        </div>
                    </div>
                @endcomponent
            </div>
        </div> --}}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="projects_table" style=" table-layout: fixed !important;">
                    <thead>
                        <tr>
                            <th class="table-td-width-25px">#</th>
                            <th class="table-td-width-100px">@lang('followup::lang.emp_name')</th>
                            <th class="table-td-width-60px">@lang('followup::lang.emp_id_proof_number')</th>
                            <th class="table-td-width-100px">@lang('followup::lang.appointment')</th>
                            <th class="table-td-width-100px">@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent
        <div class="modal fade" id="addUserAccessProjectModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => 'projects_access_permissions.store', 'enctype' => 'multipart/form-data']) !!}

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('followup::lang.add_user_project_access_permissions')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-12">
                                {!! Form::hidden('user_id', null, ['id' => 'user_id']) !!}
                                {!! Form::label('projects_ids', __('followup::lang.projects') . ':*') !!}
                                {!! Form::select('projects_ids[]', $projects, null, [
                                    'class' => 'form-control select2',
                                    'multiple',
                                    'required',
                                    'id' => 'projects_menu',
                                    'style' => 'height: 60px;',
                                ]) !!}
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

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

            $('#addUserAccessProjectModal').on('shown.bs.modal', function(e) {
                $('#projects_menu').select2({
                    dropdownParent: $(
                        '#addUserAccessProjectModal'),
                    width: '100%',
                });

            });

            $(document).on('click', '.add_access_project_btn', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var user_id = $(this).data('id');
                var $projectsMenu = $('#projects_menu');
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        var projects;
                        if (typeof response.projects === 'string' && response.projects !== '') {
                            projects = JSON.parse(response.projects);
                        } else {
                            projects = response.projects;
                        }

                        // Clear existing options in Select2
                    

                        if (projects && Object.keys(projects).length > 0) {
                            $projectsMenu.find('option').each(function() {
                                var optionValue = $(this).attr('value');
                                // Check if the current option value is in the response projects
                                if (projects.hasOwnProperty(optionValue)) {
                                    // Mark as selected
                                    $(this).prop('selected', true);
                                } else {
                                    // Optionally, unselect it if you want to deselect previously selected options
                                    $(this).prop('selected', false);
                                }
                            });
                        }
                        $projectsMenu.trigger('change');


                        // Update user_id and show modal
                        $('#addUserAccessProjectModal').find('[name="user_id"]').val(user_id);
                        $('#addUserAccessProjectModal').modal('show');
                    },
                    error: function(xhr, status, error) {

                        console.error("Error in AJAX request:", error);
                    }
                });


            })




            $('#project_name_filter_select').select2();
            $('#projects_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('projects_access_permissions') }}",
                    data: function(d) {
                        if ($('#project_name_filter').val()) {
                            d.project_name = $('#project_name_filter').val();
                        }
                    }
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'full_name'
                    },
                    {
                        data: 'id_proof_number'
                    },
                    {
                        data: 'appointment'
                    },
                    {
                        data: 'action'
                    },
                ]

            });

            $('#project_name_filter').on('change', function() {
                $('#projects_table').DataTable().ajax.reload();
            });
        });
    </script>
@endsection
