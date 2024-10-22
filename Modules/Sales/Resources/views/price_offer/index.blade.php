@extends('layouts.app')
@section('title', __('lang_v1.quotation'))
@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>@lang('lang_v1.list_quotations')
            <small></small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        @include('sales::layouts.nav_offer_prices')




        @component('components.widget', ['class' => 'box-primary'])
            @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('sales.add_offer_price'))
                @slot('tool')
                    <div class="box-tools">
                        <a class="btn btn-block btn-primary"
                            href="{{ action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'create'], ['status' => 'quotation']) }}">
                            <i class="fa fa-plus"></i> @lang('lang_v1.add_quotation')</a>
                    </div>
                @endslot
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-striped ajax_view" id="sale_table">
                    <thead>
                        <tr>

                            {{-- <th>@lang('sales::lang.location')</th> --}}
                            <th>@lang('sales::lang.offer_number')</th>
                            <th>@lang('sales::lang.customer_name')</th>
                            <th>@lang('sales::lang.customer_number')</th>
                            <th>@lang('sales::lang.date')</th>
                            <th>@lang('sales::lang.value')</th>
                            <th>@lang('sales::lang.status')</th>
                            <th>@lang('sales::lang.offer_approve')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent
        @include('sales::price_offer.change_status_modal')
    </section>
    <!-- /.content -->
@stop
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {



                $(document).on('click', '.btn-download', function(e) {
                    e.preventDefault();

                    var downloadUrl = $(this).data('href');

                    // Trigger the download using JavaScript
                    var link = document.createElement('a');
                    link.href = downloadUrl;
                    link.download = downloadUrl.split('/').pop();
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });

                // $(document).on('click', '.btn-preview', function(e) {
                //     e.preventDefault();


                //     var previewUrl = $(this).data('href');

                //     // Open the preview in a new window using JavaScript
                //     var previewWindow = window.open(previewUrl, '_blank');

                //     // Check if the window is blocked by a popup blocker
                //     if (!previewWindow || previewWindow.closed || typeof previewWindow.closed === 'undefined') {
                //         alert('Please allow pop-ups to preview the file.');
                //     }
                // });


                sale_table = $('#sale_table').DataTable({
                    processing: true,
                    serverSide: true,
                    aaSorting: [
                        [0, 'desc']
                    ],
                    ajax: {
                        "url": '{{ route('under_study_offer_prices') }}',
                        "data": function(d) {


                            d.status = $('#status_filter').val();

                        }
                    },

                    columns: [

                        // {
                        //     data: 'location_id'
                        // },
                        {
                            data: 'ref_no'
                        },
                        {
                            data: 'supplier_business_name'
                        },
                        {
                            data: 'mobile'
                        },
                        {
                            data: 'transaction_date'
                        },
                        {
                            data: 'final_total'
                        },

                        {
                            data: 'status'
                        },
                        {
                            data: 'is_approved'
                        },
                        {
                            data: 'action'
                        }




                    ],
                    // "fnDrawCallback": function (oSettings) {
                    //     __currency_convert_recursively($('#purchase_table'));
                    // }
                });

                $(document).on('change', '#status_filter', function() {
                    sale_table.ajax.reload();
                });

                $(document).on('click', 'a.convert-to-proforma', function(e) {
                    e.preventDefault();
                    swal({
                        title: LANG.sure,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(confirm => {
                        if (confirm) {
                            var url = $(this).attr('href');
                            $.ajax({
                                method: 'GET',
                                url: url,
                                dataType: 'json',
                                success: function(result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        sale_table.ajax.reload();
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    });
                });
                $(document).on('click', 'a.change_status', function(e) {
                    e.preventDefault();
                    $('#change_status_modal').find('select#status_dropdown').val($(this).data('orig-value'))
                        .change();
                    $('#change_status_modal').find('#offer_id').val($(this).data('offer-id'));
                    $('#change_status_modal').modal('show');



                });

                $(document).on('submit', 'form#change_status_form', function(e) {
                    e.preventDefault();
                    var data = $(this).serialize();
                    var ladda = Ladda.create(document.querySelector('.update-offer-status'));
                    ladda.start();
                    $.ajax({
                        method: $(this).attr('method'),
                        url: $(this).attr('action'),
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            ladda.stop();
                            if (result.success == true) {
                                $('div#change_status_modal').modal('hide');
                                toastr.success(result.msg);
                                sale_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                });

            }

        );
    </script>

@endsection
