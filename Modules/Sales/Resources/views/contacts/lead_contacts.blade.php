@extends('layouts.app')
@section('title', __('sales::lang.lead_contacts'))

@section('content')
    @include('sales::layouts.nav_contact')

    <section class="content-header">
        <h1>
            <span>@lang('sales::lang.lead_contacts')</span>
        </h1>
    </section>



    <section class="content">



        @component('components.widget', ['class' => 'box-primary'])
    

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="cust_table">
                    <thead>
                        <tr>
                            {{-- <th>
                                <input type="checkbox" id="select-all">
                            </th> --}}
                            <th>#</th>
                            <th>@lang('sales::lang.contact_number')</th>
                            <th>@lang('sales::lang.supplier_business_name')</th>
                            <th>@lang('sales::lang.commercial_register_no')</th>
                            <th>@lang('sales::lang.created_by')</th>
                            <th>@lang('sales::lang.contact_mobile')</th>
                            <th>@lang('sales::lang.contact_email')</th>
                            <th>@lang('sales::lang.created_at')</th>
                            <th>@lang('messages.action')</th>


                        </tr>
                    </thead>
                </table>
                {{-- <div style="margin-bottom: 10px;">
                    <button type="button" class="btn btn-warning btn-sm custom-btn" id="change-status-selected">
                        @lang('sales::lang.change_contact_status')
                    </button>
                </div> --}}
            </div>
        @endcomponent


        <div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open([
                        'url' => action([\Modules\Sales\Http\Controllers\ClientsController::class, 'changeStatus']),
                        'method' => 'post',
                        'enctype' => 'multipart/form-data',
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
                                @foreach ($status as $key => $value)
                                    <option value="{{ $key }}">{{ $value['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row" style="margin-top:8px; ">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('file_lead', __('sales::lang.file_lead') . ':') !!}
                                    {!! Form::file('file_lead', ['class' => 'form-control', 'accept' => 'doc/*']) !!}


                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('note_lead', __('sales::lang.note_lead') . '') !!}
                                    {!! Form::text('nots', '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.note_lead'),
                                        'id' => 'note_lead',
                                    ]) !!}
                                </div>
                            </div>
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
    </section>
    <div class="modal fade" id="changeStatusContactModal" tabindex="-1" role="dialog"></div>
@endsection

@section('javascript')



    <script>
        $(document).ready(function() {


            var customers_table = $('#cust_table').DataTable({
                ajax: {
                    url: "{{ route('lead_contacts') }}",

                },
                processing: true,
                serverSide: true,
                info: false,


                columns: [
                    // {
                    //     data: null,
                    //     render: function(data, type, row, meta) {
                    //         return '<input type="checkbox" class="select-row" data-id="' + row.id +
                    //             '">';
                    //     },
                    //     orderable: false,
                    //     searchable: false,
                    // },
                    {
                        data: 'id',
                        name: 'id'
                    },
                    
                    {
                        data: 'contact_id',
                        name: 'contact_id',
                        render: function(data, type, row) {
                            var link = '<a href="' +
                                '{{ route('contacts-profile', ['id' => ':id']) }}'
                                .replace(':id', row.id) + '">' + data + '</a>';
                            return link;
                        }
                    },
                    {
                        data: 'supplier_business_name',
                        name: 'supplier_business_name'
                    },


                    {
                        data: 'commercial_register_no',
                        name: 'commercial_register_no'
                    },

                    {
                        data: 'created_by',
                        name: 'created_by'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },

                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },

                ]
            });

            $('#cityDropdown').on('change', function() {
                var selectedCity = $(this).val();
                var url = $(this).data('url');

                $.get(url, {
                    city: selectedCity
                }, function(data) {

                    $('#relatedInput').val(data.relatedData);
                });
            });

            $('input[name="mobile"]').on('input', function() {
                let mobileNumber = $(this).val();


                if (mobileNumber.length > 10) {
                    mobileNumber = mobileNumber.slice(0, 10);
                    $(this).val(mobileNumber);
                }


                if (!mobileNumber.startsWith('05')) {
                    if (mobileNumber.length >= 2) {
                        mobileNumber = '05' + mobileNumber.slice(2);
                        $(this).val(mobileNumber);
                    }
                }
            });

            // $('#allow_login_cs_checkbox').change(function() {

            //     if ($(this).prop('checked')) {
            //         $('#username_cs').prop('required', true);
            //         $('#password_cs').prop('required', true);
            //         $('#confirm_password_cs').prop('required', true);


            //         $('#username_cs_wrapper').show();
            //         $('#password_cs_wrapper').show();
            //         $('#confirm_password_cs_wrapper').show();
            //     } else {

            //         $('#username_cs').prop('required', false);
            //         $('#password_cs').prop('required', false);
            //         $('#confirm_password_cs').prop('required', false);

            //         $('#username_cs_wrapper').hide();
            //         $('#password_cs_wrapper').hide();
            //         $('#confirm_password_cs_wrapper').hide();
            //     }
            // });

            // $('#allow_login_cf_checkbox').change(function() {
            //     if ($(this).prop('checked')) {
            //         $('#username_cf').prop('required', true);
            //         $('#password_cf').prop('required', true);
            //         $('#confirm_password_cf').prop('required', true);

            //         $('#username_cf_wrapper').show();
            //         $('#password_cf_wrapper').show();
            //         $('#confirm_password_cf_wrapper').show();
            //     } else {
            //         $('#username_cf').prop('required', false);
            //         $('#password_cf').prop('required', false);
            //         $('#confirm_password_cf').prop('required', false);

            //         $('#username_cf_wrapper').hide();
            //         $('#password_cf_wrapper').hide();
            //         $('#confirm_password_cf_wrapper').hide();
            //     }
            // });

            // $('#allow_login_cs_checkbox').change(function() {
            //     if (this.checked) {
            //         $('#username_cs_wrapper').show();
            //         $('#password_cs_wrapper').show();
            //         $('#confirm_password_cs_wrapper').show();
            //     } else {
            //         $('#username_cs_wrapper').hide();
            //         $('#password_cs_wrapper').hide();
            //         $('#confirm_password_cs_wrapper').hide();
            //     }
            // });

            // $('#allow_login_cf_checkbox').change(function() {
            //     if (this.checked) {
            //         $('#username_cf_wrapper').show();
            //         $('#password_cf_wrapper').show();
            //         $('#confirm_password_cf_wrapper').show();
            //     } else {
            //         $('#username_cf_wrapper').hide();
            //         $('#password_cf_wrapper').hide();
            //         $('#confirm_password_cf_wrapper').hide();
            //     }
            // });


            $('#moreInfoButton').click(function() {
                $('#more_div').toggleClass('hide');
            });


            $('#contract_follower').click(function() {
                $('#more_div2').toggleClass('hide');
            });


            $('#select-all').change(function() {
                $('.select-row').prop('checked', $(this).prop('checked'));
            });

            $('#cust_table').on('change', '.select-row', function() {
                $('#select-all').prop('checked', $('.select-row:checked').length === cust_table.rows()
                    .count());
            });

            $('#change-status-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return {
                        id: $(this).data('id'),

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
                            customers_table.ajax.reload();
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
