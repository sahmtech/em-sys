@extends('layouts.app')
@section('title', __('essentials::lang.regions'))

@section('content')
@include('essentials::layouts.nav_hrm_setting')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.regions')</span>
    </h1>
</section>


<!-- Main content -->
<section class="content">
    @component('components.widget',['class' => 'box-primary'])
        @can('city.create')
        @slot('tool')
        <div class="box-tools">
            <!-- Button to trigger the Add New region modal -->
            <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addRegionModal">
                <i class="fa fa-plus"></i> @lang('messages.add')
            </button>
        </div>
    @endslot
        @endcan
       
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="regions_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.ar_name')</th>
                            <th>@lang('essentials::lang.en_name')</th>
                            <th>@lang('essentials::lang.city_ar_name')</th>
                            <th>@lang('essentials::lang.city_en_name')</th>
                            <th>@lang('essentials::lang.details')</th>
                            <th>@lang('essentials::lang.is_active')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
       
    @endcomponent

    <div class="modal fade city_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade" id="addRegionModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'storeRegion']) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('essentials::lang.add_region')</h4>
                </div>
    
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('arabic_name', __('essentials::lang.ar_name') . ':*') !!}
                            {!! Form::text('arabic_name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.ar_name'), 'required']) !!}
                        </div>
    
                        <div class="form-group col-md-6">
                            {!! Form::label('english_name', __('essentials::lang.en_name') . ':*') !!}
                            {!! Form::text('english_name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.en_name'), 'required']) !!}
                        </div>
    
                        <div class="form-group col-md-6">
                            {!! Form::label('city', __('essentials::lang.city') . ':*') !!}
                            {!! Form::select('city', $cities, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.city'), 'required']) !!}
                        </div>
    
                        <div class="form-group col-md-6">
                            {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                            {!! Form::textarea('details', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' => 2]) !!}
                        </div>
    
                        <div class="form-group col-md-6">
                            {!! Form::label('is_active', __('essentials::lang.city_is_active') . ':*') !!}
                            {!! Form::select('is_active', ['1' => __('essentials::lang.city_is_active'), '0' => __('essentials::lang.city_is_unactive')], null, ['class' => 'form-control']) !!}
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
    // Cities table
    $(document).ready(function () {
        var regions_table = $('#regions_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("regions") }}',
            columns: [
                { data: 'nameAr'},
                { data: 'nameEn'},
                { data: 'city_nameAr'},
                { data: 'city_nameEn'},
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

        $(document).on('click', 'button.delete_region_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_region,
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
                                regions_table.ajax.reload();
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
