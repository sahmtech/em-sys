@extends('layouts.app')
@section('title', __('internationalrelations::lang.proposed_labor'))

@section('content')
    @include('internationalrelations::layouts.nav_proposed_labor')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('internationalrelations::lang.candidate_workers')
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
                            <th>
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>@lang('internationalrelations::lang.worker_number')</th>
                            <th>@lang('internationalrelations::lang.worker_name')</th>
                            <th>@lang('internationalrelations::lang.agency_name')</th>
                            <th>@lang('essentials::lang.mobile_number')</th>
                            <th>@lang('essentials::lang.contry_nationality')</th>
                            <th>@lang('essentials::lang.profession')</th>
                            <th>@lang('essentials::lang.specialization')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>

                <div style="margin-bottom: 10px;">
 
                    @if(auth()->user()->hasRole('Admin#1') ||  auth()->user()->can('internationalrelations.change_worker_interview_status'))
                    <button type="button" class="btn btn-warning btn-sm custom-btn" id="change-status-selected">
                        @lang('internationalrelations::lang.change_interview_status')
                    </button>
                    @endif
                </div>



            </div>

            <div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open([
                            'url' => action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'changeStatus']),
                            'method' => 'post',
                            'id' => 'change_status_form',
                        ]) !!}

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('essentials::lang.change_status')</h4>
                        </div>

                        <div class="modal-body">
                            <div class="form-group">
                                <input type="hidden" name="selectedRowsData" id="selectedRowsData" />
                                <label for="status">@lang('sale.status'):*</label>
                                <select class="form-control select2" name="status" required id="status_dropdown"
                                    style="width: 100%;">
                                    @foreach ($interview_status as $key => $value)
                                        <option value="{{ $key }}">{{ $value['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('note', __('followup::lang.note') . ':') !!}
                                {!! Form::textarea('note', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('followup::lang.note'),
                                    'rows' => 3,
                                ]) !!}
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="submitFilesBtn">@lang('messages.save')</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                        </div>

                        {!! Form::close() !!}
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
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
                info: false,
                ajax: {
                    url: "{{ route('proposed_laborIndex') }}",
                    data: function(d) {
                        d.specialization = $('#specializations-select').val();
                        d.profession = $('#professions-select').val();
                        d.agency = $('#agency_filter').val();



                    },
                },

                "columns": [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return '<input type="checkbox" class="select-row" data-id="' + row.id +
                                '" data-full_name="' + row.full_name +
                                '" data-is_price_offer_sent="' + row.is_price_offer_sent +
                                '" data-is_accepted_by_worker="' + row.is_accepted_by_worker + '">';
                        },
                        orderable: false,
                        searchable: false,
                    },
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



            $('#requests_table').on('click', '.btn-return', function() {
                var requestId = $(this).data('request-id');
                $('#returnModal').modal('show');
                $('#returnModal').data('id', requestId);
            });

            $('#select-all').change(function() {
                $('.select-row').prop('checked', $(this).prop('checked'));
            });

            $('#employees').on('change', '.select-row', function() {
                $('#select-all').prop('checked', $('.select-row:checked').length === users_table.rows()
                    .count());
            });

            $('#change-status-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return {
                        id: $(this).data('id'),
                        full_name: $(this).data('full_name'),
                    };
                }).get();

                $('#selectedRowsData').val(JSON.stringify(selectedRows));
                $('#changeStatusModal').modal('show');
            });

            $('#submitFilesBtn').click(function() {
                var formData = new FormData($('#change_status_form')[0]);

                $.ajax({
                    type: 'POST',
                    url: $('#change_status_form').attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            users_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(error) {

                    }
                });

                $('#changeStatusModal').modal('hide');
            });


        });
    </script>



@endsection
