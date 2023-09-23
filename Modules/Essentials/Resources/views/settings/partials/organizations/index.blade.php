@extends('layouts.app')
@section('title', __('essentials::lang.organizations'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_organizations')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @can('organization.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{ route('createOrganization') }}">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </a>
                </div>
            @endslot
        @endcan
        @can('organization.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="organizations_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.organization_name')</th>
                            <th>@lang('essentials::lang.organization_code')</th>                           
                            <th>@lang('essentials::lang.organization_level_type')</th>
                            <th>@lang('essentials::lang.organization_parent_level')</th>
                            <th>@lang('essentials::lang.organization_account_number')</th>
                            <th>@lang('essentials::lang.organization_bank_name')</th>
                            <th>@lang('essentials::lang.details')</th>
                            <th>@lang('essentials::lang.is_active')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade organization_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
   
    $(document).ready(function () {
        var organizations_table = $('#organizations_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("organizations") }}', 
            columns: [
                { data: 'name'},
                { data: 'code'},
                { data: 'level_type'},
                { data: 'parent_level'},
                { data: 'account_number'},
                { data: 'bank_name'},
                { data: 'details' },
                { data: 'is_active' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', 'button.delete_organization_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_organization,
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
                                organizations_table.ajax.reload();
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
