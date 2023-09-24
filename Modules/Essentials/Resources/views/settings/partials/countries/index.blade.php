@extends('layouts.app')
@section('title', __('essentials::lang.countries'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_countries')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @can('country.create')
            @slot('tool')
            <div class="box-tools">
                
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addCountryModal">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>
            </div>
            @endslot
        @endcan
        @can('country.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="countries_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.contry_ar_name')</th>
                            <th>@lang('essentials::lang.contry_en_name')</th>                           
                            <th>@lang('essentials::lang.contry_nationality')</th>
                            <th>@lang('essentials::lang.contry_details')</th>
                            <th>@lang('essentials::lang.contry_is_active')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade country_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
 <!-- Modal for adding a new country -->
 <div class="modal fade" id="addCountryModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open(['route' => 'storeCountry']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('essentials::lang.add_country')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::label('arabic_name', __('essentials::lang.country_ar_name') . ':*') !!}
                        {!! Form::text('arabic_name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.country_ar_name'), 'required']) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('english_name', __('essentials::lang.country_en_name') . ':*') !!}
                        {!! Form::text('english_name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.country_en_name'), 'required']) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('nationality', __('essentials::lang.contry_nationality') . ':*') !!}
                        {!! Form::text('nationality', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.contry_nationality'), 'required']) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('details', __('essentials::lang.contry_details') . ':') !!}
                        {!! Form::textarea('details', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.contry_details'), 'rows' => 2]) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('is_active', __('essentials::lang.contry_is_active') . ':*') !!}
                        {!! Form::select('is_active', ['1' => __('essentials::lang.contry_is_active'), '0' => __('essentials::lang.contry_is_unactive')], null, ['class' => 'form-control']) !!}
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
        var countries_table = $('#countries_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("countries") }}', 
            columns: [
                { data: 'nameAr'},
                { data: 'nameEn'},
                { data: 'nationality'},
                { data: 'details' },
                { 
        data: 'is_active',
        render: function (data, type, row) {
            if (data === 1) {
                return '<span style="color: green;">Active</span>';
            } else {
                return '<span style="color: red;">Inactive</span>';
            }
        }
    },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', 'button.delete_country_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_country,
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
                                countries_table.ajax.reload();
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
