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
                    @slot('tool')
                        <div class="box-tools">

                            <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                data-target="#addContractModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot


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

                                        <div class="form-group col-md-12">
                                                {!! Form::radio('contract_type', 'new', null, ['id' => 'new_contract']) !!}
                                                <label for="new_contract">{{ __('sales::lang.new_contract') }}</label>

                                                {!! Form::radio('contract_type', 'appendix', null, ['id' => 'appendix_contract']) !!}
                                                <label for="appendix_contract">{{ __('sales::lang.appendix_contract') }}</label>

                                        </div>

                                    <div id="new-contract-fields" class="form-fields" style="display:none;">
            
                                            <div class="form-group col-md-6">
                                                {!! Form::label('offer_price', __('sales::lang.offer_price') . ':*') !!}
                                                {!! Form::select('offer_price', $offer_prices, null, [
                                                    'class' => 'form-control',
                                                    'id' => 'offer_price',
                                                    'placeholder' => __('sales::lang.select_offer_price'),
                                                 
                                                ]) !!}
                                            </div>

                                            <div class="form-group col-md-6">
                                                {!! Form::label('contract_signer', __('sales::lang.contract_signer') . ':') !!}
                                                {!! Form::text('contract_signer', null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => __('sales::lang.contract_signer'),
                                                
                                                ]) !!}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {!! Form::label('contract_follower', __('sales::lang.contract_follower') . ':') !!}
                                                {!! Form::text('contract_follower', null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => __('sales::lang.contract_follower'),
                                                
                                                ]) !!}
                                            </div>

                                            <div class="form-group col-md-6">
                                                {!! Form::label('start_date', __('essentials::lang.start_date') . ':') !!}
                                                {!! Form::date('start_date', !empty($contract->start_date) ? $contract->start_date : null, [
                                                    'class' => 'form-control',
                                                    'style' => 'height:36px',
                                                    'id' => 'start_date',
                                                    'placeholder' => __('essentials::lang.start_date'),
                                                ]) !!}
                                            </div>
                                            <div class="form-group col-md-8">
                                                {!! Form::label('contract_duration', __('essentials::lang.contract_duration') . ':') !!}
                                                <div class="form-group">
                                                    <div class="multi-input">
                                                        <div class="input-group">
                                                            {!! Form::number(
                                                                'contract_duration',
                                                                !empty($contract->contract_duration) ? $contract->contract_duration : null,
                                                                [
                                                                    'class' => 'form-control width-40 pull-left',
                                                                    'style' => 'height:36px',
                                                                    'id' => 'contract_duration',
                                                                    'placeholder' => __('essentials::lang.contract_duration'),
                                                                ],
                                                            ) !!}
                                                            {!! Form::select(
                                                                'contract_duration_unit',
                                                                ['years' => __('essentials::lang.years'), 'months' => __('essentials::lang.months')],
                                                                !empty($contract->contract_per_period) ? $contract->contract_per_period : null,
                                                                ['class' => 'form-control width-60 pull-left', 'style' => 'height:36px;', 'id' => 'contract_duration_unit'],
                                                            ) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-6">
                                                {!! Form::label('end_date', __('essentials::lang.end_date') . ':') !!}
                                                {!! Form::date('end_date', !empty($contract->end_date) ? $contract->end_date : null, [
                                                    'class' => 'form-control',
                                                    'style' => 'height:36px',
                                                    'id' => 'end_date',
                                                    'placeholder' => __('essentials::lang.end_date'),
                                                ]) !!}
                                            </div>

                                            <div class="form-group col-md-6">
                                                {!! Form::label('status', __('essentials::lang.status') . ':*') !!}
                                                {!! Form::select(
                                                    'status',
                                                    ['valid' => __('sales::lang.valid'), 'finished' => __('sales::lang.finished')],
                                                    null,
                                                    ['class' => 'form-control','id'=>'status',
                                                     'placeholder' => __('essentials::lang.status'), ],
                                                ) !!}
                                            </div>
                                            <div class="form-group col-md-8">
                                                {!! Form::label('contract_items', __('sales::lang.contract_items') . ':*') !!}
                                                {!! Form::select('contract_items[]', $items, null, [
                                                    'class' => 'form-control select2', 
                                                    'multiple' => 'multiple', 
                                                    'placeholder' => __('sales::lang.select_contract_items'),
                                                    'required',
                                                ]) !!}
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
                                            <div class="form-group col-md-6">
                                                {!! Form::label('file', __('essentials::lang.file') . ':*') !!}
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
                                        
                                            <div id="new-contract-fields" >
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
                                                        'id'=>'assigned_to',
                                                        'style' => 'width:90%;',
                                                    ]) !!}
                                                </div>
                                            </div>

                                            <div >
                                            

                                            </div>

                                        
                                    </div>

                                    <div id="appendix-contract-fields" class="form-fields" style="display:none;">
                                            <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="offer_type_filter">@lang('sales::lang.contract'):</label>
                                                            {!! Form::select('contract-select', $contracts->pluck('contract_number', 'contract_number'), null, [
                                                                'class' => 'form-control',
                                                                'style' => 'height:36px',
                                                                'placeholder' => __('lang_v1.all'),
                                                               
                                                                'id' => 'contract-select',
                                                            ]) !!}
                                                        </div>
                                            </div>
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
    $(document).ready(function () {
        
        $('.select2').select2();
        console.log("selectedRadio");
        
        $('.contract-type-radio').change(function () {
          
            $('.form-fields').hide();

            
            var selectedRadio = $('input[name="contract_type"]:checked').val();
          
            $('#' + selectedRadio + '-contract-fields').show();

            console.log(selectedRadio);

              
           
        });

        document.getElementById("new_contract").addEventListener('change', function(){
            document.getElementById("status").required = this.checked ;
            document.getElementById("offer_price").required = this.checked ;
            document.getElementById("project_name").required = this.checked ;
            document.getElementById("assigned_to").required = this.checked ;
        })


        document.getElementById("appendix_contract").addEventListener('change', function(){
            document.getElementById("contract-select").required = this.checked ;
          
        })



        
        $('#appendix_contract').change(function () {
            
            $('.form-fields').hide();

            
            var selectedRadio = $('input[name="contract_type"]:checked').val();
            $('#' + selectedRadio + '-appendix-fields').show();
        });
    });
</script>


    <script>
        $(document).ready(function() {
            
            $('#start_date, #end_date').change(function() {
                updateContractDuration();
            });

            
            $('#contract_duration, #contract_duration_unit').change(function() {
                updateEndDate();
            });

            function updateContractDuration() {
                var startDate = $('#start_date').val();
                var endDate = $('#end_date').val();

                if (startDate && endDate) {
                    var durationObj = calculateDuration(startDate, endDate);
                    $('#contract_duration').val(durationObj.value);
                    $('#contract_duration_unit').val(durationObj.unit);
                }
            }

            function updateEndDate() {
                var startDate = $('#start_date').val();
                var duration = $('#contract_duration').val();
                var unit = $('#contract_duration_unit').val();

                if (startDate && !duration && !unit) {
                    var endDate = $('#end_date').val();
                    if (endDate) {
                        var durationObj = calculateDuration(startDate, endDate);
                        $('#contract_duration').val(durationObj.value);
                        $('#contract_duration_unit').val(durationObj.unit);
                    }
                } else if (startDate && duration && unit) {
                    var newEndDate = calculateEndDate(startDate, duration, unit);
                    $('#end_date').val(newEndDate);
                }
            }

            function calculateDuration(startDate, endDate) {
                var startDateObj = new Date(startDate);
                var endDateObj = new Date(endDate);
                var diffInMonths = (endDateObj.getFullYear() - startDateObj.getFullYear()) * 12 + endDateObj
                    .getMonth() - startDateObj.getMonth();
                var diffInYears = diffInMonths / 12;

                if (Number.isInteger(diffInYears)) {
                    return {
                        value: diffInYears,
                        unit: 'years'
                    };
                } else {
                    return {
                        value: diffInMonths,
                        unit: 'months'
                    };
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
                        $('#contract_follower').val(data.contract_follower.first_name + ' ' +
                            data.contract_follower.last_name);
                        $('#contract_signer').val(data.contract_signer.first_name + ' ' + data
                            .contract_signer.last_name)
                        $('#contract_follower').prop('disabled', true);
                        $('#contract_signer').prop('disabled', true);
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
