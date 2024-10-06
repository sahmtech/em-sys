@extends('layouts.app')
@section('title', __('essentials::lang.main-violations'))

@section('content')

    <section class="content-header">
        <h1>@lang('essentials::lang.main-violations')</h1>
    </section>
    <section class="content">


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @slot('tool')
                        <div class="box-tools">

                            <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                data-target="#addEmployeesFamilyModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="violations_table">
                            <thead>
                                <tr>
                                    <th>@lang('essentials::lang.description')</th>
                                    {{-- <th>@lang('essentials::lang.type')</th>

                                    <th>@lang('essentials::lang.occurrence')</th>
                                    <th>@lang('essentials::lang.amount_type')</th>
                                    <th>@lang('essentials::lang.amount')</th> --}}
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
            <div class="modal fade" id="edit_main-violations" tabindex="-1" role="dialog"></div>

            <div class="modal fade" id="addEmployeesFamilyModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open(['route' => 'store-main-violations', 'enctype' => 'multipart/form-data']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('essentials::lang.add_main-violations')</h4>
                        </div>


                        <div class="modal-body">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('description', __('essentials::lang.description') . ' *') !!}

                                    {!! Form::text('description', '', [
                                        'class' => 'form-control',
                                        'id' => 'description',
                                        'required',
                                        'placeholder' => __('essentials::lang.description'),
                                    ]) !!}
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
        </div>
    </section>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

            $('#addEmployeesFamilyModal').on('shown.bs.modal', function(e) {
                $('#employees_select').select2({
                    dropdownParent: $(
                        '#addEmployeesFamilyModal'),
                    width: '100%',
                });


            });
            violations_table = $('#violations_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('main-violations') }}",

                },

                columns: [{
                        data: 'description'
                    },

                    {
                        data: 'action'
                    },
                ],
            });

            $(document).on('click', 'button.delete_violations_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_employeeFamily,
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
                                    violations_table.ajax.reload();
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
