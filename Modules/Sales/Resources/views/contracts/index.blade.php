@extends('layouts.app')
@section('title', __('sales::lang.contracts'))

@section('content')

    <section class="content-header">
        <h1>@lang('sales::lang.contracts')</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('status_filter', __('essentials::lang.status') . ':') !!}
                            <select class="form-control select2" name="status_filter" required id="status_filter"
                                style="width: 100%;">
                                <option value="all">@lang('lang_v1.all')</option>
                                <option value="valid">@lang('sales::lang.valid')</option>
                                <option value="finished">@lang('sales::lang.finished')</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('contract_form_filter', __('sales::lang.contract_form') . ':') !!}
                            <select class="form-control select2" name="contract_form_filter" required id="contract_form_filter"
                                style="width: 100%;">
                                <option value="all">@lang('lang_v1.all')</option>
                                <option value="monthly_cost">@lang('sales::lang.monthly_cost')</option>
                                <option value="operating_fees">@lang('sales::lang.operating_fees')</option>
                            </select>
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('sales.add_sale_contract'))
                        @slot('tool')
                            <div class="box-tools">

                                <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                    data-target="#addContractModal">
                                    <i class="fa fa-plus"></i> @lang('messages.add')
                                </button>
                            </div>
                        @endslot
                    @endif


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="contracts_table">
                            <thead>
                                <tr>
                                    <th>@lang('sales::lang.id')</th>
                                    <th>@lang('sales::lang.number_of_contract')</th>
                                    <th>@lang('sales::lang.customer_name')</th>
                                    <th>@lang('sales::lang.contract_status')</th>
                                    <th>@lang('sales::lang.start_date')</th>
                                    <th>@lang('sales::lang.contract_duration')</th>
                                    <th>@lang('sales::lang.end_date')</th>
                                    <th>@lang('sales::lang.contract_form')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
                <div class="modal fade" id="addContractModal" tabindex="-1" role="dialog"
                    aria-labelledby="gridSystemModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">

                            {!! Form::open(['route' => 'storeContract', 'enctype' => 'multipart/form-data']) !!}
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">@lang('essentials::lang.add_contract')</h4>
                            </div>

                            <div class="modal-body">

                                <div class="row">


                                    <div class="form-group col-md-6">
                                        {!! Form::label('contract_type', __('sales::lang.contract_type') . ':*') !!}
                                        {!! Form::select('contract_type', ['client' => 'Client', 'offer_price' => 'Offer Price'], null, [
                                            'class' => 'form-control',
                                            'id' => 'contract_type',
                                            'style' => 'height:40px;',
                                            'placeholder' => __('sales::lang.select_contract_type'),
                                        ]) !!}
                                    </div>


                                    <div class="form-group col-md-6" id="offer_price_field">
                                        {!! Form::label('offer_price', __('sales::lang.offer_price') . ':*') !!}
                                        {!! Form::select('offer_price', $offer_prices, null, [
                                            'class' => 'form-control',
                                        
                                            'style' => 'height:40px;',
                                            'placeholder' => __('sales::lang.select_offer_price'),
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6" id="contact_field">
                                        {!! Form::label('customer', __('sales::lang.customer') . ':*') !!}
                                        {!! Form::select('customer', $contacts, null, [
                                            'class' => 'form-control',
                                        
                                            'style' => 'height:40px;',
                                            'placeholder' => __('sales::lang.select_customer'),
                                        ]) !!}
                                    </div>

                                    <div class="form-group col-md-6">
                                        {!! Form::label('contract_signer', __('sales::lang.contract_signer') . ':') !!}
                                        {!! Form::text('contract_signer', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('sales::lang.contract_signer'),
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6" style="float:left;">
                                        {!! Form::label('contract_follower', __('sales::lang.contract_follower') . ':') !!}
                                        {!! Form::text('contract_follower', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('sales::lang.contract_follower'),
                                        ]) !!}
                                    </div>

                                    <div class="form-group col-md-6" style="float:left;">
                                        {!! Form::label('start_date', __('essentials::lang.start_date') . ':') !!}
                                        {!! Form::date('start_date', !empty($contract->start_date) ? $contract->start_date : null, [
                                            'class' => 'form-control',
                                            'style' => 'height:40px',
                                            'id' => 'start_date',
                                            'placeholder' => __('essentials::lang.start_date'),
                                        ]) !!}
                                    </div>

                                    <div class="form-group col-md-6" style="float:left;">
                                        {!! Form::label('end_date', __('essentials::lang.end_date') . ':') !!}
                                        {!! Form::date('end_date', !empty($contract->end_date) ? $contract->end_date : null, [
                                            'class' => 'form-control',
                                            'style' => 'height:40px',
                                            'id' => 'end_date',
                                            'placeholder' => __('essentials::lang.end_date'),
                                        ]) !!}
                                    </div>

                                    <div class="form-group col-md-6" style="float:left;">
                                        {!! Form::label('contract_duration', __('essentials::lang.contract_duration') . ':') !!}
                                        <div class="form-group">
                                            <div class="multi-input">
                                                <div class="input-group">
                                                    {!! Form::number('contract_duration', null, [
                                                        'class' => 'form-control width-40 pull-left',
                                                        'style' => 'height:40px',
                                                        'id' => 'contract_duration',
                                                        'placeholder' => __('essentials::lang.contract_duration'),
                                                    ]) !!}
                                                    {!! Form::select(
                                                        'contract_duration_unit',
                                                        ['months' => __('essentials::lang.months')],
                                                        !empty($contract->contract_per_period) ? $contract->contract_per_period : null,
                                                        ['class' => 'form-control width-60 pull-left', 'style' => 'height:40px;', 'id' => 'contract_duration_unit'],
                                                    ) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>






                                    <div class="form-group col-md-6">
                                        {!! Form::label('is_renewable', __('essentials::lang.is_renewable') . ':*') !!}
                                        {!! Form::select(
                                            'is_renewable',
                                            ['1' => __('essentials::lang.is_renewable'), '0' => __('essentials::lang.is_unrenewable')],
                                            null,
                                            ['class' => 'form-control', 'style' => 'height: 100%'],
                                        ) !!}

                                    </div>
                                    <div class="form-group col-md-6" style="float:left;">
                                        {!! Form::label('file', __('essentials::lang.file') . ':') !!}
                                        {!! Form::file('file', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('essentials::lang.file'),
                                            'required',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('notes', __('sales::lang.notes') . ':') !!}
                                        {!! Form::textarea('notes', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('sales::lang.notes'),
                                            'rows' => 2,
                                        ]) !!}
                                    </div>

                                    <div id="new-contract-fields">
                                        <div class="form-group col-md-6">
                                            {!! Form::label('project_name', __('sales::lang.project_name') . ':*') !!}
                                            {!! Form::text('project_name', null, [
                                                'class' => 'form-control',
                                                'placeholder' => __('sales::lang.project_name'),
                                                'id' => 'project_name',
                                            ]) !!}
                                        </div>

                                        <div class="form-group col-md-6">
                                            {!! Form::label('assigned_to', __('sales::lang.project_follower') . ':*') !!}
                                            {!! Form::select('assigned_to[]', $users, null, [
                                                'class' => 'form-control select2',
                                                'multiple' => 'multiple',
                                                'id' => 'assigned_to',
                                                'style' => 'width:90%;',
                                            ]) !!}
                                        </div>
                                    </div>

                                    <div>





                                    </div>


                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                                <button type="button" class="btn btn-default"
                                    data-dismiss="modal">@lang('messages.close')</button>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
@section('javascript')
    <script>
        $(document).ready(function() {

            $('#offer_price_field').hide();
            $('#contact_field').hide();

            $('#contract_type').change(function() {
                if ($(this).val() === 'offer_price') {
                    $('#offer_price_field').show();
                    $('#contact_field').hide();

                } else {
                    $('#contact_field').show();
                    $('#offer_price_field').hide();
                }
            });
        });
    </script>
    <script>
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


            $('.select2').select2();
            console.log("selectedRadio");

            $('.contract-type-radio').change(function() {

                $('.form-fields').hide();


                var selectedRadio = $('input[name="contract_type"]:checked').val();

                $('#' + selectedRadio + '-contract-fields').show();

                console.log(selectedRadio);



            });

            document.getElementById("new_contract").addEventListener('change', function() {
                document.getElementById("status").required = this.checked;
                document.getElementById("offer_price").required = this.checked;
                document.getElementById("project_name").required = this.checked;
                document.getElementById("assigned_to").required = this.checked;
            })


            document.getElementById("appendix_contract").addEventListener('change', function() {
                document.getElementById("contract-select").required = this.checked;

            })




            $('#appendix_contract').change(function() {

                $('.form-fields').hide();

                var selectedRadio = $('input[name="contract_type"]:checked').val();
                $('#' + selectedRadio + '-appendix-fields').show();
            });
        });
    </script>

    <script>
        $(document).ready(function() {



            $('#start_date').change(function() {
                updateEndDateFromStartDate();
            });
            $('#contract_duration').change(function() {
                updateEndDateFromStartDate();
            });

            function updateEndDateFromStartDate() {
                var startDate = $('#start_date').val();
                var duration = $('#contract_duration').val();
                var unit = $('#contract_duration_unit').val();

                if (startDate && duration && unit) {
                    var newEndDate = calculateEndDate(startDate, duration, unit);
                    $('#end_date').val(newEndDate);
                }
            }




            function calculateEndDate(startDate, duration, unit) {
                var startDateObj = new Date(startDate);
                var endDateObj = new Date(startDateObj);

                if (unit === 'years') {
                    endDateObj.setFullYear(startDateObj.getFullYear() + parseInt(duration));
                } else if (unit === 'months') {
                    endDateObj.setMonth(startDateObj.getMonth() + parseInt(duration));
                }

                return endDateObj.toISOString().split('T')[0];
            }
        });
    </script>



    <script type="text/javascript">
        $(document).ready(function() {
            var contracts_table;
            var newContractFields = document.getElementById('new-contract-fields');
            var appendixContractFields = document.getElementById('appendix-contract-fields');

            function reloadDataTable() {
                contracts_table.ajax.reload();
            }

            contracts_table = $('#contracts_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('saleContracts') }}",
                    data: function(d) {

                        d.status = $('#status_filter').val();
                        d.contract_form = $('#contract_form_filter').val();


                    }

                },



                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'number_of_contract'
                    },
                    {
                        data: 'sales_project_id'
                    },

                    {
                        data: 'status',
                        render: function(data, type, row) {
                            if (data === 'valid') {
                                return '@lang('sales::lang.valid')';

                            } else {
                                return '@lang('sales::lang.finished')';
                            }
                        }
                    },


                    {
                        data: 'start_date'
                    },
                    {
                        data: 'contract_duration',
                        render: function(data, type, row) {
                            var unit = row.contract_per_period;
                            if (data !== null && data !== undefined) {
                                var translatedUnit = (unit === 'years') ? '@lang('sales::lang.years')' :
                                    '@lang('sales::lang.months')';
                                return data + ' ' + translatedUnit;
                            } else {
                                return '';
                            }
                        }
                    },
                    {
                        data: 'end_date'
                    },

                    {
                        data: 'contract_form',
                        render: function(data, type, row) {
                            if (data === 'monthly_cost') {
                                return '@lang('sales::lang.monthly_cost')';

                            } else if (data === 'operating_fees') {
                                return '@lang('sales::lang.operating_fees')';
                            } else {
                                return ' ';
                            }

                        }
                    },


                    {
                        data: 'action'
                    },
                ],
            });




            $('#status_filter, #contract_form_filter').on('change', function() {
                reloadDataTable();
            });


            $(document).on('click', 'button.delete_contract_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_contract,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    contracts_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });

            $('#offer_price').on('change', function() {
                var offerPrice = $(this).val();

                $.ajax({
                    type: 'GET',
                    url: '/sale/getContractValues',
                    data: {
                        'offer_price': offerPrice
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.contract_follower != null && data.contract_signer != null) {
                            $('#contract_follower').val(data.contract_follower.first_name +
                                ' ' +
                                data.contract_follower.last_name);
                            $('#contract_signer').val(data.contract_signer.first_name + ' ' +
                                data
                                .contract_signer.last_name)
                            $('#contract_follower').prop('disabled', true);
                            $('#contract_signer').prop('disabled', true);
                        }
                        $('#contract_duration').val(data.contract_duration);

                    }
                });
            });

            document.querySelectorAll('input[name="contract_type"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    if (this.value === 'new') {
                        newContractFields.style.display = 'block';
                        appendixContractFields.style.display = 'none';
                    } else if (this.value === 'appendix') {
                        newContractFields.style.display = 'none';
                        appendixContractFields.style.display = 'block';
                    }
                });
            });

            $('#offer_price').on('change', function() {
                var offerPrice = $(this).val();
                console.log(offerPrice);

                $.ajax({
                    url: '/sale/get_projects',
                    data: {
                        'id': offerPrice
                    },

                    success: function(data) {

                        var projectsDropdown = $('#appendix_project_id');
                        projectsDropdown.empty();
                        console.log(data);
                        $.each(data, function(key, value) {
                            projectsDropdown.append($('<option>').text(value).attr(
                                'value', key));
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching projects: ' + error);
                    }
                });
            });
        });
    </script>


@endsection
