@extends('layouts.app')
@section('title', __('housingmovements::lang.residencyDelivery'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.residencyDelivery')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @include('essentials::layouts.payroll_nav_trevelers')


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="residencyDelivery_table">
                            <thead>
                                <tr>
                                    {{-- <th>
                                        <input type="checkbox" id="select-all">
                                    </th> --}}
                                    <th>#</th>
                                    <th>@lang('housingmovements::lang.worker_name')</th>


                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>



                        </table>

                    </div>
                @endcomponent

                <div class="modal fade" id="delivery_residencyModal" tabindex="-1" role="dialog"
                    aria-labelledby="gridSystemModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">

                            {!! Form::open([
                                'route' => 'delivery_residency',
                                'enctype' => 'multipart/form-data',
                                'id' => 'delivery_residencyForm',
                            ]) !!}
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">@lang('housingmovements::lang.residencyDelivery')</h4>
                            </div>

                            <div class="modal-body">

                                <div class="row">
                                    <input type="hidden" name="user">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('file', __('housingmovements::lang.delivery_file') . ':*') !!}
                                        {!! Form::file('file', null, [
                                            'class' => 'form-control',
                                            'style' => 'height:40px',
                                            'id' => 'file',
                                            'required',
                                            'placeholder' => __('housingmovements::lang.delivery_file'),
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
            var residencyDelivery_table = $('#residencyDelivery_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('residencyDelivery') }}',
                },
                columns: [

                    {
                        data: 'id',
                    },
                    {
                        data: 'full_name',
                        searchable: false
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
        function delivery_residency(workerId) {
            $('#delivery_residencyModal').modal('show');
            $('#delivery_residencyForm').find('input[name="user"]').val(workerId);
        }
    </script>


@endsection
