@extends('layouts.app')
@section('title', __('essentials::lang.insurance'))

@section('content')
    <section class="content-header">
        <h1>
            <span>@lang('essentials::lang.insurance')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-primary pull-right m-5 btn-modal"
                                href="{{ route('essentials.car-insurance-create', ['id' => $car_id]) }}"
                                data-href="{{ route('essentials.car-insurance-create', ['id' => $car_id]) }}"
                                data-container="#add_insurance_model">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="cars_table"
                            style="margin-bottom: 100px;table-layout: fixed !important;">
                            <thead>
                                <tr>
                                    {{-- <th>@lang('housingmovements::lang.driver')</th> --}}

                                    <th class="table-td-width-100px">@lang('housingmovements::lang.insurance_company_id')</th>
                                    <th class="table-td-width-100px">@lang('housingmovements::lang.insurance_start_Date')</th>
                                    <th class="table-td-width-100px">@lang('housingmovements::lang.insurance_end_date')</th>
                                    <th class="table-td-width-100px">@lang('messages.action')</th>
                                </tr>
                            </thead>

                        </table>
                        {{-- <center class="mt-5">
                            {{ $Cars->links() }}
                        </center> --}}
                    </div>


                    <div class="modal fade" id="add_insurance_model" tabindex="-1" role="dialog"></div>
                    <div class="modal fade" id="edit_insurance_model" tabindex="-1" role="dialog">
                    </div>
                @endcomponent
            </div>


    </section>
    <!-- /.content -->

@endsection

@section('javascript')


    <script type="text/javascript">
        $(document).ready(function() {


            $('#carTypeSelect').select2();
            $('#car_type_id').select2();
            $('#driver_select').select2();
            $('#carModel_id').select2();
            $('#worker_select').select2();
            const urlParams = new URLSearchParams(window.location.search);
            const id = urlParams.get('id');

            cars_table = $('#cars_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('essentials.car-insurance') }}',
                    data: function(d) {
                        d.id = id;

                        if ($('#carTypeSelect').val()) {
                            d.carTypeSelect = $('#carTypeSelect').val();
                            // console.log(d.project_name_filter);
                        }
                        if ($('#driver_select').val()) {
                            d.driver_select = $('#driver_select').val();
                            // console.log(d.project_name_filter);
                        }
                    }
                },
                columns: [
                    // { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },

                    {
                        "data": "insurance_company_id"
                    },
                    {
                        "data": "insurance_start_Date"
                    },
                    {
                        "data": "insurance_end_date"
                    },
                    {
                        data: 'action'
                    }
                ]
            });

            $(document).on('click', 'button.delete_car_button', function() {

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
                            cars_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });


            });


            $(document).on('click', 'button.edit_car_button', function() {

                var href = $(this).data('href');
                var data = $(this).serialize();
                $.ajax({
                    method: "get",
                    url: href,
                    dataType: "json",
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);

                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });


            });
            $('#carTypeSelect,#driver_select').on('change',
                function() {
                    cars_table.ajax.reload();
                });
            $(document).on('change', '#car_type_id', function() {
                if ($(this).val() !== '') {
                    $.ajax({
                        url: '/movment/carModel-by-carType_id/' + $(this).val(),
                        dataType: 'json',
                        success: function(result) {
                            console.log(result);
                            $('#carModel_id')
                            $('#carModel_id').empty();
                            $.each(result, function(index, carModel) {
                                $('#carModel_id').append('<option value="' + carModel
                                    .id + '">' + carModel.name_ar + ' - ' + carModel
                                    .name_en + '</option>');
                            });

                        },
                    });
                }
            })

            $(document).on('change', '#car_type_id_select', function() {
                if ($(this).val() !== '') {
                    $.ajax({
                        url: '/essentials/carModel-by-carType_id/' + $(this).val(),
                        dataType: 'json',
                        success: function(result) {
                            console.log(result);
                            $('#carModel_id')
                            $('#carModel_id').empty();
                            $.each(result, function(index, carModel) {
                                $('#carModel_id').append('<option value="' + carModel
                                    .id + '">' + carModel.name_ar + ' - ' + carModel
                                    .name_en + '</option>');
                            });

                        },
                    });
                }
            })

        });
    </script>
@endsection
