@extends('layouts.app')
@section('title', __('essentials::lang.travel_categories'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_travel_categories')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @can('travel_categorie.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{ route('createTravel_categorie') }}">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </a>
                </div>
            @endslot
        @endcan
        @can('travel_categorie.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="travel_categories_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.name')</th>
                            <th>@lang('essentials::lang.employee_ticket_value')</th>                           
                            <th>@lang('essentials::lang.wife_ticket_value')</th>
                            <th>@lang('essentials::lang.children_ticket_value')</th>
                            <th>@lang('essentials::lang.details')</th>
                            <th>@lang('essentials::lang.active')</th>
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
   
    $(document).ready(function () {
        var travel_categories_table = $('#travel_categories_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("travel_categories") }}', 
            columns: [
                { data: 'name'},
                { data: 'employee_ticket_value'},
                { data: 'wife_ticket_value'},
                { data: 'children_ticket_value'},
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

        $(document).on('click', 'button.delete_travel_categorie_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_travel_categorie,
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
                                travel_categories_table.ajax.reload();
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
