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
                    <a class="btn btn-block btn-primary" href="{{ route('createCountry') }}">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </a>
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
