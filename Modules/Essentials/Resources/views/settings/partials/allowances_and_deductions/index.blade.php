@extends('layouts.app')
@section('title', __('essentials::lang.allowances_and_deductions'))

@section('content')
@include('essentials::layouts.nav_hrm_setting')

<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.allowances_and_deductions')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])

            @slot('tool')
            <div class="box-tools">
                
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addAllowancesAndDeductionsModal">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>
            </div>
            @endslot
      
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="allowances_and_deductions_table">
                    <thead>
                        <tr>
                               <th>#</th>
                            <th>@lang('essentials::lang.applicable_date')</th>   
                            <th>@lang('essentials::lang.description')</th>                           
                            <th>@lang('essentials::lang.type')</th>
                            <th>@lang('essentials::lang.amount')</th>
                            <th>@lang('essentials::lang.amount_type')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
 
    @endcomponent

    <div class="modal fade allowances_and_deductions_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
 <!-- Modal for adding a new  -->
 <div class="modal fade" id="addAllowancesAndDeductionsModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open(['url' => action([\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'store']), 'method' => 'POST', 'id' => 'addRowForm']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('essentials::lang.allowances_and_deductions')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::label('description', __('essentials::lang.description') . ':*') !!}
                        {!! Form::text('description', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.description'), 'required']) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('type', __('essentials::lang.type') . ':*') !!}
                        {!! Form::select('type', ['allowance' =>__('essentials::lang.allowance'), 'deduction' => __('essentials::lang.deduction')], null, ['class' => 'form-control', 'required']) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('amount', __('essentials::lang.amount') . ':*') !!}
                        {!! Form::number('amount', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.amount'),  'step' => '0.0001', 'required']) !!}
                    </div>

                   
                    <div class="form-group col-md-6">
                        {!! Form::label('amount_type', __('essentials::lang.amount_type') . ':') !!}
                        {!! Form::select('amount_type', ['fixed' =>__('essentials::lang.fixed'), 'percent' => __('essentials::lang.percent')], null, ['class' => 'form-control', 'required']) !!}
                        
                    </div>
                    <div class="form-group">
                        {!! Form::label('applicable_date',__('essentials::lang.applicable_date') . ':') !!}
                        {!! Form::date('applicable_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.applicable_date'), 'required']) !!}
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
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
 
 $(document).ready(function () {
    var allowances_and_deductions_table = $('#allowances_and_deductions_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ action([\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'index']) }}',
            type: 'GET', // Specify the HTTP method if needed
            dataType: 'json', // Set the expected data type
        },
        columns: [
              { data: 'id'},
            { data: 'applicable_date'},
            { data: 'description'},
            { data: 'type'},
            { data: 'amount'},
            { data: 'amount_type' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });



        $(document).on('click', 'button.delete_allowances_and_deductions_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_allowances_and_deductions,
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
                        success: function (result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                allowances_and_deductions_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });

    });

</script>
@endsection
