@extends('layouts.app')
@section('title', __('housingmovements::lang.residencyAdd&Print'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.residencyAdd&Print')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @include('essentials::layouts.nav_trevelers')


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="residencyPrint_table">
                            <thead>
                                <tr>
                                    {{-- <th>
                                        <input type="checkbox" id="select-all">
                                    </th> --}}
                                    <th>#</th>
                                    <th>@lang('housingmovements::lang.worker_name')</th>
                                    <th>@lang('housingmovements::lang.eqama_number')</th>
                                    <th>@lang('housingmovements::lang.residencyPrint')</th>
                                </tr>
                            </thead>



                        </table>

                    </div>
                @endcomponent

                <div class="modal fade" id="add_eqamaModal" tabindex="-1" role="dialog"
                    aria-labelledby="gridSystemModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">

                            {!! Form::open(['route' => 'addEqama', 'id' => 'add_eqamaForm']) !!}
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">@lang('housingmovements::lang.add_eqama')</h4>
                            </div>

                            <div class="modal-body">

                                <div class="row">
                                    <input type="hidden" name="user">

                                    <div class="form-group col-md-6">
                                        {!! Form::label('id_proof_number', __('housingmovements::lang.eqama_number') . ':*') !!}
                                        {!! Form::number('id_proof_number', null, [
                                            'class' => 'form-control',
                                            'required',
                                            'style' => ' height: 40px',
                                            'placeholder' => __('housingmovements::lang.eqama_number'),
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
            var residencyPrint_table = $('#residencyPrint_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('residencyPrint') }}',
                },
                columns: [

                    {
                        data: 'id',
                    },
                    {
                        data: 'full_name',
                    },
                    {
                        data: 'id_proof_number',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });


        });
    </script>
    <script>
        function print_residency(workerId) {
            $.ajax({
                url: '{{ action('Modules\HousingMovements\Http\Controllers\ProjectWorkersController@updateResidencyPrint') }}', // Ensure the URL is generated correctly
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: workerId
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.msg);
                        $('#residencyPrint_table').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.msg ||
                            'Failed to update. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred. Please try again.');
                }
            });
        }

        function add_eqama(workerId) {
            $('#add_eqamaModal').modal('show');
            $('#add_eqamaForm').find('input[name="user"]').val(workerId);
        }
    </script>



@endsection
