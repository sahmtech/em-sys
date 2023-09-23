@extends('layouts.app')
@section('title', __('essentials::lang.cities'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_cities')</span>
    </h1>
</section>


<!-- Main content -->
<section class="content">
    @component('components.widget',['class' => 'box-primary'])
        @can('city.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{ route('createCity') }}">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </a>
                </div>
            @endslot
        @endcan
        @can('city.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="cities_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.city_ar_name')</th>
                            <th>@lang('essentials::lang.city_en_name')</th>
                            <th>@lang('essentials::lang.country_ar_name')</th>
                            <th>@lang('essentials::lang.country_en_name')</th>
                            <th>@lang('essentials::lang.city_details')</th>
                            <th>@lang('essentials::lang.city_is_active')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade city_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
    // Cities table
    $(document).ready(function () {
        var cities_table = $('#cities_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("cities") }}',
            columns: [
                { data: 'nameAr'},
                { data: 'nameEn'},
                { data: 'country_nameAr'},
                { data: 'country_nameEn'},
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

        $(document).on('click', 'button.delete_city_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_city,
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
                                cities_table.ajax.reload();
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
