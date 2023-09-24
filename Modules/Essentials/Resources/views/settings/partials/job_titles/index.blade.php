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
      
            <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addJobTitleModal">
                <i class="fa fa-plus"></i> @lang('messages.add')
            </button>
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
    <div class="modal fade" id="addJobTitleModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'storeJob_title']) !!}
  
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.add_job_title' )</h4>
      </div>
    
      <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {!! Form::label('job_title', __('essentials::lang.job_title') . ':*') !!}
                {!! Form::text('job_title', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.job_title'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('job_code', __('essentials::lang.job_code') . ':*') !!}
                {!! Form::text('job_code', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.job_code'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('responsibilities', __('essentials::lang.responsibilities') . ':*') !!}
                {!! Form::textarea('responsibilities', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.responsibilities'),  'required','rows' => 2]) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('supervision_scope', __('essentials::lang.supervision_scope') . ':*') !!}
                {!! Form::text('supervision_scope', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.supervision_scope'), 'required']) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('authorization_and_permissions', __('essentials::lang.authorization_and_permissions') . ':*') !!}
                {!! Form::textarea('authorization_and_permissions', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.authorization_and_permissions'), 'required', 'rows' => 2]) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                {!! Form::textarea('details', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' => 2]) !!}
            </div>
            
            <div class="form-group col-md-6">
                {!! Form::label('is_active', __('essentials::lang.is_active') . ':*') !!}
                {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')], null, ['class' => 'form-control']) !!}
            </div>
        </div>
        
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
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
