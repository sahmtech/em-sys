@extends('layouts.app')
@section('title', __('essentials::lang.bank_accounts'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_bank_accounts')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @can('bank.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{ route('createBank_account') }}">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </a>
                </div>
            @endslot
        @endcan
        @can('bank.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="bank_accounts_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.name')</th>
                            <th>@lang('essentials::lang.phone_number')</th>                           
                            <th>@lang('essentials::lang.mobile_number')</th>
                            <th>@lang('essentials::lang.address')</th>
                            <th>@lang('essentials::lang.details')</th>
                            <th>@lang('essentials::lang.is_active')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade bank_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
 
    $(document).ready(function () {
        var bank_accounts_table = $('#bank_accounts_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("bank_accounts") }}', 
            columns: [
                { data: 'name'},
                { data: 'phone_number'},
                { data: 'mobile_number'},
                { data: 'address'},
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

        $(document).on('click', 'button.delete_bank_account_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_bank,
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
                                bank_accounts_table.ajax.reload();
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
