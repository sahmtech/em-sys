@extends('layouts.app')
@section('title', __('essentials::lang.professions'))

@section('content')
@include('essentials::layouts.nav_hrm_setting')

<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.professions')</span>
    </h1>
</section>

<section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addProfessionModal">
                    <i class="fa fa-plus"></i>@lang('messages.add')
                </button>
            </div>
            @endslot

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="professions_table">
                    <thead>
                        <tr>
                        <th>#</th>
                            <th>@lang('essentials::lang.profession_name')</th>
                            <th>@lang('essentials::lang.en_name')</th>
                            <th>@lang('essentials::lang.specializations')</th>
                            <th>@lang('messages.action')</tr>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <!-- Modal for adding a new profession -->
        <div class="modal fade" id="addProfessionModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => 'storeProfession']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            {!! Form::label('name',   __('essentials::lang.profession_name') .':*') !!}
                            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.profession_name'), 'required']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('en_name', __('essentials::lang.en_name') . ' (' . __('essentials::lang.optional') . '):') !!}

                            {!! Form::text('en_name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.en_name')]) !!}
                        </div>

                        <div id="specializations-container"> 
                            <div class="form-group specialization-group">
                                <label for="specializations[]">@lang('essentials::lang.specialization')</label>
                                <input type="text" name="specializations[]" class="form-control" required>
                                <label for="en_specializations[]">@lang('essentials::lang.en_name')(@lang('essentials::lang.optional'))</label>
                                <input type="text" name="en_specializations[]" class="form-control">
                            </div>
                        </div>
                        <button type="button" id="add-specialization" class="btn btn-primary">@lang('essentials::lang.add_specialization')</button>
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
    
    $(document).ready(function () {
        var professions_table = $('#professions_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("professions") }}', 
            columns: [
                { data: 'id'},
                { data: 'name'},
                { data: 'en_name'},
                {
                     data: 'specializations'
    
                    },

                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        // JavaScript for adding specialization input fields (similar to the previous example)
        $("#add-specialization").click(function() {
            var specializationGroup = $("#specializations-container .form-group").first().clone();
            specializationGroup.find("input").val('');
            $("#specializations-container").append(specializationGroup);
        });
  
    $(document).on('click', 'button.delete_profession_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_profession,
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
                                professions_table.ajax.reload();
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
