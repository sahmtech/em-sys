@extends('layouts.app')
@section('title', __('accounting::lang.companies_access_permissions'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('accounting::lang.companies_access_permissions')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="companies_table" style=" table-layout: fixed !important;">
                    <thead>
                        <tr>
                            <th class="table-td-width-25px">#</th>
                            <th class="table-td-width-100px">@lang('accounting::lang.emp_name')</th>
                            <th class="table-td-width-60px">@lang('accounting::lang.emp_id_proof_number')</th>
                            <th class="table-td-width-100px">@lang('accounting::lang.appointment')</th>
                            <th class="table-td-width-100px">@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent
        <div class="modal fade" id="addUserAccessCompanyModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => 'companies_access_permissions.store', 'enctype' => 'multipart/form-data']) !!}

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('accounting::lang.add_user_company_access_permissions')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-12">
                                {!! Form::hidden('user_id', null, ['id' => 'user_id']) !!}
                                {!! Form::label('companies_ids', __('accounting::lang.companies') . ':*') !!}
                                {!! Form::select('companies_ids[]', $companies, null, [
                                    'class' => 'form-control select2',
                                    'multiple',
                                    'required',
                                    'id' => 'companies_menu',
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

            $('#addUserAccessCompanyModal').on('shown.bs.modal', function(e) {
                $('#companies_menu').select2({
                    dropdownParent: $(
                        '#addUserAccessCompanyModal'),
                    width: '100%',
                });

            });

            $(document).on('click', '.add_access_company_btn', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var user_id = $(this).data('id');
                var $companiesMenu = $('#companies_menu');
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        var companies;
                        if (typeof response.companies === 'string' && response.companies !== '') {
                            companies = JSON.parse(response.companies);
                        } else {
                            companies = response.companies;
                        }

                    

                        if (companies && Object.keys(companies).length > 0) {
                            $companiesMenu.find('option').each(function() {
                                var optionValue = $(this).attr('value');
          
                                if (companies.hasOwnProperty(optionValue)) {
               
                                    $(this).prop('selected', true);
                                } else {
                                  
                                    $(this).prop('selected', false);
                                }
                            });
                        }
                        $companiesMenu.trigger('change');


                        // Update user_id and show modal
                        $('#addUserAccessCompanyModal').find('[name="user_id"]').val(user_id);
                        $('#addUserAccessCompanyModal').modal('show');
                    },
                    error: function(xhr, status, error) {

                        console.error("Error in AJAX request:", error);
                    }
                });


            })




            $('#company_name_filter_select').select2();
            $('#companies_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('companies_access_permissions') }}",
                    data: function(d) {
                        if ($('#company_name_filter').val()) {
                            d.company_name = $('#company_name_filter').val();
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

            $('#company_name_filter').on('change', function() {
                $('#companies_table').DataTable().ajax.reload();
            });
        });
    </script>
@endsection
