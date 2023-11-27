@extends('layouts.app')
@section('title', __('essentials::lang.contracts_end_reasons'))
@section('content')
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.contracts_end_reasons')</span>
    </h1>
</section>

<section class="content">
@component('components.filters', ['title' => __('report.filters')])


       <div class="col-md-3">
           <div class="form-group">
               <label for="employee_type_filter">@lang('essentials::lang.employee_type'):</label>
               <select class="form-control select2" name="employee_type_filter" required id="employee_type_filter" style="width: 100%;">
                   <option value="all">@lang('lang_v1.all')</option>
                   <option value="employee">@lang('essentials::lang.employee')</option>
                   <option value="manager">@lang('essentials::lang.manager')</option>
                   <option value="worker">@lang('essentials::lang.worker')</option>
               </select>
           </div>
       </div>


       <div class="col-md-3">
        <div class="form-group">
            <label for="reason_type_filter">@lang('essentials::lang.reason_type'):</label>
            <select class="form-control select2" name="reason_type_filter" id="reason_type_filter" style="width: 100%;">
                <option value="all">@lang('lang_v1.all')</option>
                <option value="main">@lang('essentials::lang.main')</option>
                <option value="sub_main">@lang('essentials::lang.sub_main')</option>
            </select>
        </div>
    </div>
     

@endcomponent

@component('components.widget', ['class' => 'box-primary'])

@slot('tool')
            <div class="box-tools">
                
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addfinishReason">
                    <i class="fa fa-plus"></i>  @lang('essentials::lang.create_contracts_finish_reason')
                </button>
            </div>
            @endslot
      


        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="finish_reasons_table">
                <thead>
                    <tr>
                   
                        <th>@lang('essentials::lang.employee_type')</th>
                        <th>@lang('essentials::lang.reason_type')</th>
                        <th>@lang('essentials::lang.main_reason2')</th>
                        <th>@lang('essentials::lang.sub_reason2')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>

    @endcomponent

    <div class="modal fade" id="addfinishReason" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        {!! Form::open(['route' => 'store_finish_reasons', 'enctype' => 'multipart/form-data']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('essentials::lang.create_contracts_finish_reason')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-6">
                    <label for="employee_type_filter">@lang('essentials::lang.employee_type'):</label>
                        <select class="form-control select2" name="employee_type" required id="employee_type_filter" style="width: 100%;">
                            <option value="all">@lang('lang_v1.all')</option>
                            <option value="employee">@lang('essentials::lang.employee')</option>
                            <option value="manager">@lang('essentials::lang.manager')</option>
                            <option value="worker">@lang('essentials::lang.worker')</option>
                        </select>
                    </div>
                       
                    <div class="clearfix"></div>
                    <div class="form-group col-md-6">
                        {!! Form::label('reason_type', __('essentials::lang.reason_type') . ':*') !!}
                        <div class="form-check form-check-inline">
                            {!! Form::radio('reason_type', 'main', true, ['class' => 'form-check-input', 'id' => 'main_reason']) !!}
                            {!! Form::label('main_reason', __('essentials::lang.main_reason'), ['class' => 'form-check-label']) !!}

                            {!! Form::radio('reason_type', 'sub_main', false, ['class' => 'form-check-input', 'id' => 'sub_reason']) !!}
                            {!! Form::label('sub_reason', __('essentials::lang.sub_reason'), ['class' => 'form-check-label']) !!}
                        </div>
                    </div>

                    <div class="clearfix"></div>

                    <div class="form-group col-md-6" id="main_reason_box">
                        {!! Form::label('reason', __('essentials::lang.reason') . ':') !!}
                        <div class="form-group">
                            {!! Form::text('reason', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.reason')]) !!}
                        </div>
                    </div>

                    <div class="form-group col-md-6" id="main_reason_dropdown_box" style="display: none;">
                    <div class="form-group">
                        {!! Form::label('main_reason_select', __('essentials::lang.main_reason2') . ':') !!}
                        {!! Form::select('main_reason_select', $main_reasons, null, ['class' => 'form-control', 'style' => 'height:40px', 'placeholder' => __('essentials::lang.main_reason_select')]) !!}
                    </div>
                </div>
                    <div class="clearfix"></div>
                    <div class="form-group col-md-6" id="sub_reason_box" style="display: none;">
                        <div class="form-group">
                            {!! Form::label('sub_reason', __('essentials::lang.sub_reason2') . ':') !!}
                            {!! Form::text('sub_reason', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.sub_reason2')]) !!}
                        </div>
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
@endsection
@section('javascript')
<script type="text/javascript">
    $(document).ready(function () {
      
        $('.select2').select2();

        var finish_reasons_table = $('#finish_reasons_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("contracts_finish_reasons") }}',
                data: function (d) {
                    d.employee_type_filter = $('#employee_type_filter').val();
                    d.reason_type_filter = $('#reason_type_filter').val();
                }
            },
            columns: [
                { data: 'employee_type'},
                { data: 'reason_type'},
                { data: 'reason'},
                { data: 'sub_reason'},
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

      
        $('#employee_type_filter, #reason_type_filter').on('change', function () {
            finish_reasons_table.ajax.reload();
        });

        var mainReasonRadio = document.getElementById('main_reason');
        var subReasonRadio = document.getElementById('sub_reason');
        var mainReasonBox = document.getElementById('main_reason_box');
        var subReasonBox = document.getElementById('sub_reason_box');
        var mainReasonDropdownBox = document.getElementById('main_reason_dropdown_box');

        mainReasonRadio.addEventListener('change', function () {
            if (mainReasonRadio.checked) {
                mainReasonBox.style.display = 'block';
                subReasonBox.style.display = 'none';
                mainReasonDropdownBox.style.display = 'none';
            }
        });

        subReasonRadio.addEventListener('change', function () {
            if (subReasonRadio.checked) {
                mainReasonBox.style.display = 'none';
                subReasonBox.style.display = 'block';
                mainReasonDropdownBox.style.display = 'block';
            }
        });
    });
</script>
@endsection
