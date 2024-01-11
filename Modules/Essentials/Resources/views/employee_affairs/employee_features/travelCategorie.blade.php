@extends('layouts.app')
@section('title', __('essentials::lang.employee_features'))


@section('content')
    @include('essentials::layouts.nav_employee_features')
    <section class="content-header">

        <h1>@lang('essentials::lang.travel_categories')
        </h1>
        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        @slot('tool')
                            <div class="box-tools">

                                <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                    data-target="#addEmployeetravel_categorieModal">
                                    <i class="fa fa-plus"></i> @lang('messages.add')
                                </button>
                            </div>
                        @endslot


                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="employee_travel_categorie_table">
                                <thead>
                                    <tr>
                                        <th>@lang('essentials::lang.employee')</th>
                                        <th>@lang('essentials::lang.travel_categorie')</th>
                                        <th>@lang('messages.action')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    @endcomponent
                    <div class="modal fade" id="addEmployeetravel_categorieModal" tabindex="-1" role="dialog"
                        aria-labelledby="gridSystemModalLabel">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">

                                {!! Form::open(['route' => 'storeUserTravelCat', 'enctype' => 'multipart/form-data']) !!}
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                            aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title">@lang('essentials::lang.add_user_travel_categorie')</h4>
                                </div>

                                <div class="modal-body">

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                            {!! Form::select('employee', $users, null, [
                                                'class' => 'form-control',
                                                'id' => 'employees_select',
                                                'placeholder' => __('essentials::lang.select_employee'),
                                                'required',
                                            ]) !!}
                                        </div>
                                        <div class="form-group col-md-6">
                                            {!! Form::label('travel_categoire', __('essentials::lang.travel_categorie') . ':*') !!}
                                            {!! Form::select('travel_categoire', $travelCategories, null, [
                                                'class' => 'form-control',
                                                'placeholder' => __('essentials::lang.travel_categorie'),
                                                'required',
                                            ]) !!}
                                        </div>



                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                                    <button type="button" class="btn btn-default"
                                        data-dismiss="modal">@lang('messages.close')</button>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    @endsection
    @section('javascript')
        <script type="text/javascript">
            $(document).ready(function() {
                $('#addEmployeetravel_categorieModal').on('shown.bs.modal', function(e) {
                    $('#employees_select').select2({
                        dropdownParent: $(
                            '#addEmployeetravel_categorieModal'),
                        width: '100%',
                    });


                });
                var employee_travel_categorie_table;


                employee_travel_categorie_table = $('#employee_travel_categorie_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('userTravelCat') }}",

                    },

                    columns: [{
                            data: 'user'
                        },
                        {
                            data: 'categorie_id'
                        },
                        {
                            data: 'action'
                        },
                    ],
                });
                $(document).on('click', 'button.delete_employee_travel_categorie_button', function() {
                    swal({
                        title: LANG.sure,
                        text: LANG.confirm_delete_employee_travel_categorie,
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
                                success: function(result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        employee_travel_categorie_table.ajax.reload();
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
