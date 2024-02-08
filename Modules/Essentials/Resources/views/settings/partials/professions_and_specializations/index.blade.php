@extends('layouts.app')
@section('title', __('essentials::lang.job_titles'))

@section('content')
@include('essentials::layouts.nav_hrm_setting')

<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.job_titles')</span>
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
                            <th>@lang('essentials::lang.job_title')</th>
                            <th>@lang('essentials::lang.en_name')</th>
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
                        <div class="form-group col-md-6">
                            {!! Form::label('name',   __('essentials::lang.job_title') .':*') !!}
                            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.job_title'), 'required']) !!}
                        </div>

                        <div class="form-group col-md-6">
                            {!! Form::label('en_name', __('essentials::lang.en_name') . ' (' . __('essentials::lang.optional') . '):') !!}
                            {!! Form::text('en_name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.en_name')]) !!}
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

@include('essentials::settings.partials.professions_and_specializations.edit_modal')
</section>


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
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

   
    //edit----------------------------------------------------------------
    $('body').on('click', '.open-professions-edit-modal', function() {
            var professionId = $(this).data('id'); 
            $('#professionIdInput').val(professionId);

            var editUrl = '{{ route("professions.edit", ":professionId") }}'
            editUrl = editUrl.replace(':professionId', professionId);
            console.log(editUrl);

            $.ajax({
                url: editUrl,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var data = response.data;

                    $('#editprofessionModal input[name="name"]').val(data.profession.name);
                    $('#editprofessionModal input[name="en_name"]').val(data.profession.en_name);
                    $('#editprofessionModal').modal('show');
                },

                error: function(error) 
                {
                    console.error('Error fetching building data:', error);
                }
            });
        });
        //submit update--------------------------------------------------------------------------------------------  
        $('body').on('submit', '#editprofessionModal form', function (e) {
            e.preventDefault();

            var professionId = $('#professionIdInput').val();
            console.log(professionId);

            var urlWithId = '{{ route("professions.update", ":professionId") }}';
            urlWithId = urlWithId.replace(':professionId', professionId);
            console.log(urlWithId);

            $.ajax({
                url: urlWithId,
                type: 'POST',
                data: new FormData(this),
                contentType: false,
                processData: false,
                success: function (response) {
                    console.log(response); 
                    if (response.success) {
                        console.log(response);
                        professions_table.ajax.reload();
                        toastr.success(response.msg);
                        $('#editprofessionModal').modal('hide');
                    } else {
                        toastr.error(response.msg);
                        console.log(response);
                    }
                },
                error: function (error) {
                    console.error('Error submitting form:', error);
                    
                    toastr.error('An error occurred while submitting the form.', 'Error');
                },
            });
        });
        //-----------------------------------------------------------------
  
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
