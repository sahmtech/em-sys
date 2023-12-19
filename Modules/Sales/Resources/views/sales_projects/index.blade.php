@extends('layouts.app')
@section('title', __('sales::lang.sales_projects'))




@section('content')

    <section class="content-header">
        <h1>@lang('sales::lang.sales_projects')</h1>
    </section>


    <section class="content">

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    {{-- @slot('tool')
                        <div class="box-tools">

                            <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                data-target="#addContactLocationModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot --}}


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="contact_locations_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang('sales::lang.contact_name')</th>
                                    <th>@lang('sales::lang.contact_location_name')</th>
                                    <th>@lang('sales::lang.contact_location_name_in_charge')</th>
                             
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
            <div class="modal fade" id="addContactLocationModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open(['route' => 'sale.storeSaleProject', 'enctype' => 'multipart/form-data']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('sales::lang.create_contact_location')</h4>
                        </div>

                        <div class="modal-body">

                            <div class="row">

                                <div class="form-group col-md-6">
                                    {!! Form::label('contact_name', __('sales::lang.contact_name') . ':*') !!}

                                    {!! Form::select('contact_name', $contacts, null, [
                                        'class' => 'form-control select2',
                                        'style' => 'height: 40px',
                                        'required',
                                        'placeholder' => __('sales::lang.contact_name'),
                                        'id' => 'contact_name',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('contact_location_name', __('sales::lang.contact_location_name') . ':*') !!}
                                    {!! Form::text('contact_location_name', null, [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'required',
                                        'placeholder' => __('sales::lang.contact_location_name'),
                                        'id' => 'contact_location_name',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('contact_location_city', __('sales::lang.contact_location_city')) !!}
                                    {!! Form::select('contact_location_city', $cities, null, [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('sales::lang.contact_location_city'),
                                        'id' => 'contact_location_city',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('contact_location_name_in_charge', __('sales::lang.contact_location_name_in_charge')) !!}
                                    {!! Form::select('contact_location_name_in_charge', $name_in_charge_choices, null, [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('sales::lang.contact_location_name_in_charge'),
                                        'id' => 'contact_location_name_in_charge',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('contact_location_phone_in_charge', __('sales::lang.contact_location_phone_in_charge')) !!}
                                    {!! Form::text('contact_location_phone_in_charge', null, [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('sales::lang.contact_location_phone_in_charge'),
                                        'id' => 'contact_location_phone_in_charge',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('contact_location_email_in_charge', __('sales::lang.contact_location_email_in_charge')) !!}
                                    {!! Form::email('contact_location_email_in_charge', null, [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('sales::lang.contact_location_email_in_charge'),
                                        'id' => 'contact_location_email_in_charge',
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

        </div>
    </section>

@endsection
@section('javascript')

    <!-- Your other scripts and styles -->



    <script type="text/javascript">
        $(document).ready(function() {
            // var professionSelect = $('#professionSelect');
            // var specializationSelect = $('#specializationSelect');




            $('#addContactLocationModal').on('shown.bs.modal', function(e) {
                $('#contact_name').select2({
                    dropdownParent: $(
                        '#addContactLocationModal'),
                    width: '100%',
                });
            });


            var contact_locations_table = $('#contact_locations_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('sale.saleProjects') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },

                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'contact_name'
                    },
                    {
                        data: 'contact_location_name'
                    },
                    {
                        data: 'assigned_to'
                    },
               
                    {
                        data: 'action'
                    },

                ],
            });
            $(document).on('click', 'button.delete_item_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_country,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        var token = "{{ csrf_token() }}";
                        console.log(href);
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            data: {
                                "_token": token,
                                "_method": "DELETE"
                            },
                            success: function(data) {
                                contact_locations_table.ajax.reload();
                                // if (typeof cust_table !== 'undefined') {
                                //     window.location.reload();
                                //     //  cust_table.ajax.reload();
                                // } else {
                                //     console.log('cust_table is not defined.');
                                // }
                            },
                            error: function(data) {
                                console.log('Error:', data);
                            }
                            // success: function (result) {
                            //     if (result.success == true) {
                            //         toastr.success(result.msg);
                            //         countries_table.ajax.reload();
                            //     } else {
                            //         toastr.error(result.msg);
                            //     }
                            // }
                        });
                    }
                });
            });




            // professionSelect.on('change', function() {
            //     var selectedProfession = $(this).val();
            //     console.log(selectedProfession);
            //     var csrfToken = $('meta[name="csrf-token"]').attr('content');
            //     $.ajax({
            //         url: '{{ route('specializations') }}',
            //         type: 'POST',
            //         data: {
            //             _token: csrfToken,
            //             profession_id: selectedProfession
            //         },
            //         success: function(data) {
            //             specializationSelect.empty();
            //             $.each(data, function(id, name) {
            //                 specializationSelect.append($('<option>', {
            //                     value: id,
            //                     text: name
            //                 }));
            //             });
            //         }
            //     });
            // });

            // function reloadDataTable() {
            //     contact_locations_table.ajax.reload();
            // }



        });
    </script>


@endsection
