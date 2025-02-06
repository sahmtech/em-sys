@extends('layouts.app')
@section('title', __('sales::lang.contract_itmes'))

@section('content')

<!-- Toastr CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet">

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

<section class="content-header">
    <h1>
        <span>@lang('sales::lang.contract_itmes')</span>
    </h1>
</section>
@if(session('output'))
<script>
    var output = @json(session('output'));
        
        if (output.success) {
            toastr.success(output.msg);
        }
</script>
@endif

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
    @if(auth()->user()->hasRole("Admin#1") || auth()->user()->can("sales.add_contract_item"))
    @slot('tool')
    <div class="box-tools">

        <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addItemModal">
            <i class="fa fa-plus"></i> @lang('messages.add')
        </button>
    </div>
    @endslot
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="contract_itmes_table">
            <thead>
                <tr>
                    <th style="width: 20px;">#</th>
                    <th>@lang('sales::lang.contact_name')</th>
                    <th>@lang('sales::lang.project_name')</th>
                    <th>@lang('sales::lang.profession')</th>
                    <th>@lang('sales::lang.nationality')</th>
                    <th>@lang('sales::lang.gender')</th>
                    <th>@lang('sales::lang.monthly_cost')</th>
                    <th>@lang('sales::lang.details')</th>
                    <th>@lang('messages.action')</th>
                </tr>
            </thead>
        </table>
    </div>

    @endcomponent




    <div class="modal fade item_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'storeItem']) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('sales::lang.add_contract_item')</h4>
                </div>

                <div class="modal-body">
                    <div class="row">

                        {{-- Contacts --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('contacts', __('sales::lang.contact_name') . ':*') !!}
                                {!! Form::select('contacts', $contacts, null, [
                                'class' => 'form-control',

                                'id' => 'contactSelect',
                                'style' => 'height:40px',
                                'required',
                                'placeholder' => __('sales::lang.contact_name'),
                                ]) !!}
                            </div>
                        </div>

                        {{-- Projects --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('project_name', __('sales::lang.project_name') . ':*') !!}
                                {!! Form::select('project_name', [], null, [
                                'class' => 'form-control',

                                'id' => 'projectSelect',
                                'style' => 'height:40px',
                                'required',
                                'placeholder' => __('sales::lang.project_name'),
                                ]) !!}
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('profession', __('sales::lang.profession') . ':*') !!}
                                {!! Form::select('profession', $professions, null, [
                                'class' => 'form-control select2',
                                'class' => 'form-control',
                                'id' => 'professionSearch',
                                'required',
                                'style' => 'height:40px',
                                'placeholder' => __('sales::lang.profession'),
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('nationality', __('sales::lang.nationality') . ':*') !!}
                                {!! Form::select('nationality', $nationalities, null, [
                                'class' => 'form-control select2',
                                'id' => 'nationalitySearch',
                                'required',
                                'style' => 'height:40px',
                                'placeholder' => __('sales::lang.nationality'),
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('gender', __('sales::lang.gender') . ':*') !!}
                                {!! Form::select('gender', ['male' => __('sales::lang.male'), 'female' =>
                                __('sales::lang.female')], null, [
                                'class' => 'form-control',
                                'required',
                                'style' => 'height:40px',
                                'placeholder' => __('sales::lang.gender'),
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('essentials_salary', __('essentials::lang.monthly_cost') . ':') !!}
                                {!! Form::number('essentials_salary', !empty($user->essentials_salary) ?
                                $user->essentials_salary : null, [
                                'class' => 'form-control',
                                'style' => 'height:40px',
                                'placeholder' => __('essentials::lang.monthly_cost'),
                                'id' => 'essentials_salary',
                                ]) !!}
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            {!! Form::label('details', __('sales::lang.details') . ':') !!}
                            {!! Form::textarea('details', null, ['class' => 'form-control', 'placeholder' =>
                            __('sales::lang.details'), 'rows' => 2]) !!}
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
    // Countries table
    $(document).ready(function () {
        var contract_itmes_table = $('#contract_itmes_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("contract_itmes") }}', 
            order: [[0, 'desc']], 

            columns: [
                { data: 'id'},
                { data: 'contact_name'},
                { data: 'project_name'},
                { data: 'profession'},
                { data: 'nationality'},
                { data: 'gender'},
                { data: 'monthly_cost_for_one' },
                { data: 'details' },
                { data: 'action', name: 'action', orderable: false, searchable: false }

               
            ]
    });

    $(document).on('click', 'button.delete_item_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_item,
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
                                contract_itmes_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });

    });
    

   
    $('#addItemModal').on('shown.bs.modal', function(e) {
                $('#professionSearch').select2({
                    dropdownParent: $(
                        '#addItemModal'),
                    width: '100%',
                });
                $('#nationalitySearch').select2({
                    dropdownParent: $(
                        '#addItemModal'),
                    width: '100%',
                });
                $('#specializationSearch').select2({
                    dropdownParent: $(
                        '#addItemModal'),
                    width: '100%',
                });

            });


            $(document).ready(function () {
    $('#contactSelect').change(function () {
        var contactId = $(this).val(); // Get selected contact ID

        if (contactId) {
            $.ajax({
                url: '/get-sales-projects/' + contactId, // Updated API route
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $('#projectSelect').empty(); // Clear existing options
                    $('#projectSelect').append('<option value="">' + "حدد مشروع" + '</option>');

                    $.each(data, function (key, value) {
                        $('#projectSelect').append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });
        } else {
            $('#projectSelect').empty();
        }
    });
});



</script>


@endsection