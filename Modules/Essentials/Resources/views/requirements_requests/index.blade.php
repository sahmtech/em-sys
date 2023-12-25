@extends('layouts.app')
@section('title', __('followup::lang.recruitmentRequests'))

@section('content')
@include('essentials::layouts.nav_recruitmentRequests')
    <section class="content-header">
        <h1>@lang('followup::lang.recruitmentRequests')</h1>
    </section>
    <section class="content">

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
 


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="recruitmentRequests_table">
                            <thead>
                                <tr>

                                    <th>@lang('sales::lang.profession')</th>
                                    <th>@lang('essentials::lang.specialization')</th>
                                    <th>@lang('essentials::lang.nationlity')</th>
                                    <th>@lang('essentials::lang.quantity')</th>
                                    <th>@lang('essentials::lang.required_date')</th>
                                    <th>@lang('essentials::lang.status')</th>
                                    <th>@lang('essentials::lang.notes')</th>
                                    <th>@lang('followup::lang.attachments')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
        
        </div>
    </section>
    @include('essentials::requirements_requests.change_status_modal')
@endsection
@section('javascript')


    <script type="text/javascript">
        $(document).ready(function() {

            var requests_table;
            var professionSelect = $('#professionSelect');
            var specializationSelect = $('#specializationSelect');

            requests_table = $('#recruitmentRequests_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('get-recuirements-requests') }}",

                },

                columns: [

                    {
                        data: 'profession_id'
                    },
                    {
                        data: 'specialization_id'
                    },
                    {
                        data: 'nationality_id'
                    },
                    {
                        data: 'quantity'
                    },
                    {
                        data: 'date'
                    },
                    {
                        data: 'status',
                        render: function(data, type, full, meta) {
                            switch (data) {


                                case 'pending':
                                    return '{{ trans('followup::lang.pending') }}';
                                case 'approved':
                                    return '{{ trans('followup::lang.under approved') }}';

                                case 'rejected':
                                    return '{{ trans('followup::lang.rejected') }}';
                                default:
                                    return data;
                            }
                        }
                    },
                    {
                        data: 'note'
                    },
                    {
                        data: 'attachments'
                    },


                ],
            });


            function reloadDataTable() {
                requests_table.ajax.reload();
            }


            $(document).on('click', 'a.change_status', function(e) {
            e.preventDefault();
            $('#change_status_modal').find('select#status_dropdown').val($(this).data('orig-value')).change();
            $('#change_status_modal').find('#request_id').val($(this).data('request-id'));
            $('#change_status_modal').find('#quantity_value').val($(this).data('quantity')); 
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
                        reloadDataTable();
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });



        });
    </script>
<script>

$('#quantity').on('input', function() {
    var quantityValue = $('#quantity_value').val();
    var enteredQuantity = $(this).val();

    if (!isNaN(quantityValue) && !isNaN(enteredQuantity)) {
        if (enteredQuantity >= quantityValue) {
            $('#quantity-error').text('Entered quantity must be less than or equal to ' +  $('#quantity_value').val());
        } else {
            $('#quantity-error').text('');
        }
    }
});

</script>

@endsection
