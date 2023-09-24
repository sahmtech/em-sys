@extends('layouts.app')
@section('title', __('essentials::lang.job_titles'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_job_titles')</span>
    </h1>
</section>


<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @can('job_titles.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{ route('createJob_title') }}">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </a>
                </div>
            @endslot
        @endcan
        @can('job_titles.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="job_titles_table">
                    <thead>
                        <tr> 
                            <th>@lang('essentials::lang.job_title')</th>
                            <th>@lang('essentials::lang.job_code')</th>                           
                            <th>@lang('essentials::lang.responsibilities')</th>
                            <th>@lang('essentials::lang.supervision_scope')</th>
                            <th>@lang('essentials::lang.authorization_and_permissions')</th>
                            <th>@lang('essentials::lang.details')</th>
                            <th>@lang('essentials::lang.is_active')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade job_title_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
   
    $(document).ready(function () {
        var job_titles_table = $('#job_titles_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("job_titles") }}', 
            columns: [
                { data: 'job_title'},
                { data: 'job_code'},
                { data: 'responsibilities'},
                { data: 'supervision_scope' },
                { data: 'authorization_and_permissions' },
                { data: 'details' },
                { data: 'is_active' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', 'button.delete_job_title_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_job_title,
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
                                job_titles_table.ajax.reload();
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
