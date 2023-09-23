@extends('layouts.app')
@section('title', __('essentials::lang.basic_salary_types'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_basic_salary_types')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @can('basic_salary_type.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{ route('createBasicSalary') }}">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </a>
                </div>
            @endslot
        @endcan
        @can('basic_salary_type.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="basic_salary_types_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.basic_salary_type')</th>
                            <th>@lang('essentials::lang.details')</th>
                            <th>@lang('essentials::lang.is_active')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade basic_salary_type_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
  
    $(document).ready(function () {
        var basic_salary_types_table = $('#basic_salary_types_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("basic_salary_types") }}', 
            columns: [
              
                { data: 'type'},
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

        $(document).on('click', 'button.delete_basic_salary_type_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_basic_salary_type,
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
                                basic_salary_types_table.ajax.reload();
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
