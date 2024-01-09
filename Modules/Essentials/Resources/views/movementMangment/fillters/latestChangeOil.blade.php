@extends('layouts.app')
@section('title', __('housingmovements::lang.latestChangeOil'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.latestChangeOil')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
     

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                 

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="carsChangeOil_table" style="margin-bottom: 100px;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">@lang('housingmovements::lang.car')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.current_speedometer')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.next_change_oil')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.invoice_no')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.date')</th>
                                    {{-- <th style="text-align: center;">@lang('messages.action')</th> --}}
                                </tr>
                            </thead>

                        </table>
                        {{-- <center class="mt-5">
                            {{ $carModles->links() }}
                        </center> --}}
                    </div>


                    <div class="modal fade" id="add_carsChangeOil_model" tabindex="-1" role="dialog"></div>
                    <div class="modal fade" id="edit_carsChangeOil_model" tabindex="-1" role="dialog"></div>
                @endcomponent
            </div>


    </section>
    <!-- /.content -->

@endsection
@section('javascript')


    <script type="text/javascript">
        $(document).ready(function() {
            $('#car__type_id').select2();

            carsChangeOil_table = $('#carsChangeOil_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('essentials.latest-change-oil') }}',
                    data: function(d) {
                        if ($('#carTypeSelect').val()) {
                            d.carTypeSelect = $('#carTypeSelect').val();
                            // console.log(d.project_name_filter);
                        }
                        if ($('#name').val()) {
                            d.name = $('#name').val();
                            // console.log(d.project_name_filter);
                        }
                    }
                },
                
                columns: [
                    // { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                    {
                        "data": "car"
                    },
                    {
                        "data": "current_speedometer"
                    },
                    {
                        "data": "next_change_oil"
                    },
                    {
                        "data": "invoice_no"
                    },
                    {
                        "data": "date"
                    },

                  
                ]
            });
            $(document).on('click', 'button.delete_carsChangeOil_button', function() {

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
                            carsChangeOil_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });


            });


            $(document).on('click', 'button.edit_carModel_button', function() {

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
            $('#carTypeSelect,#name').on('change',
                function() {
                    carsModel_table.ajax.reload();
                });
        });
    </script>
@endsection
