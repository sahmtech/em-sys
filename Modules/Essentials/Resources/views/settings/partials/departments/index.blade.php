@extends('layouts.app')
@section('title', __('essentials::lang.departments'))

@section('content')


<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.departments')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])

            @slot('tool')
            <div class="box-tools">
                
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addDepartmentModal">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>
            </div>
            @endslot
      
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="departments_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.name')</th>                      
                            <th>@lang('essentials::lang.is_main_dep')</th>
                            <th>@lang('essentials::lang.parent_department_id')</th>  
                            <th>@lang('essentials::lang.creation_date')</th>
                            <th>@lang('essentials::lang.address')</th>
                            <th>@lang('essentials::lang.details')</th>
                            <th>@lang('essentials::lang.is_active')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
 
    @endcomponent

    <div class="modal fade DepartmentModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

 <div class="modal fade" id="addDepartmentModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
             {!! Form::open(['route' => 'storeDepartment']) !!}
        {{--     --}}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('essentials::lang.add_department')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::label('name', __('essentials::lang.name') . ':*') !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.name'), 'required']) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('level', __('essentials::lang.level') . ':*') !!}
                        {!! Form::select('level', ['1' => __('essentials::lang.first_level'), '2' => __('essentials::lang.other_level')], null, ['class' => 'form-control']) !!}
                    </div>
                   
                    <div class="form-group col-md-6">
                        {!! Form::label('parent_level', __('essentials::lang.parent_level') . ':*') !!}
                        {!! Form::select('parent_level', $parent_departments, null, ['class' => 'form-control select2', 'placeholder' => __('essentials::lang.parent_level'), 'required']) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('is_main', __('essentials::lang.is_main_dep') . ':*') !!}
                        {!! Form::select('is_main', ['1' => __('essentials::lang.is_main_dep'), '0' => __('essentials::lang.is_not_main_dep')], null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('creation_date', __('essentials::lang.creation_date') . ':') !!}
                        {!! Form::date('creation_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.creation_date')]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('address', __('essentials::lang.address') . ':') !!}
                        {!! Form::text('address', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.address')]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                        {!! Form::textarea('details', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.contry_details'), 'rows' => 2]) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('is_active', __('essentials::lang.is_active') . ':*') !!}
                        {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')], null, ['class' => 'form-control']) !!}
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
        var departments_table = $('#departments_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("departments") }}', 
            columns: [
                { data: 'name'},
              
                { 
                    data: 'is_main',
                    render: function (data, type, row) {
                        if (data === 1) {
                           
                            return  '@lang('essentials::lang.main_dep')';
                        } else {
                           
                            return  '@lang('essentials::lang.subsidiary_dep')';
                        }
                    }
                },
                { data: 'parent_department_id'},
                { data: 'creation_date'},
                { data: 'location'},

                { data: 'details' },
                { 
                    data: 'is_active',
                    render: function (data, type, row) {
                        if (data === 1) {
        
                         
                            return  '@lang('essentials::lang.dep_is_active')';
                        } else {
                         
                            return  '@lang('essentials::lang.is_unactive')';
                        }
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
    });

    $(document).on('click', 'button.delete_department_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_department,
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
                                departments_table.ajax.reload();
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
