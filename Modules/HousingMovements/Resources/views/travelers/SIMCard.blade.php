@extends('layouts.app')
@section('title', __('housingmovements::lang.SIMCard'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.SIMCard')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @include('housingmovements::layouts.nav_trevelers')


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="SIMCards_table">
                            <thead>
                                <tr>
                                    {{-- <th>
                                        <input type="checkbox" id="select-all">
                                    </th> --}}
                                    <th>#</th>
                                    <th>@lang('housingmovements::lang.worker_name')</th>
                                    <th>@lang('housingmovements::lang.border_no')</th>
                                    <th>@lang('housingmovements::lang.company')</th>
                                    <th>@lang('housingmovements::lang.unified_number')</th>
                                    <th>@lang('housingmovements::lang.contact_number')</th>
                                    <th>@lang('housingmovements::lang.cell_phone_company')</th>
                                </tr>
                            </thead>



                        </table>

                    </div>
                @endcomponent
                <!-- Add SIM Modal -->
                <div class="modal fade" id="addSIMModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">

                            {!! Form::open(['route' => 'addSIM', 'id' => 'addSIMForm']) !!}
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">@lang('housingmovements::lang.add_SIM')</h4>
                            </div>

                            <div class="modal-body">

                                <div class="row">
                                    <input type="hidden" name="user">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('cell_phone_company', __('housingmovements::lang.cell_phone_company') . ':*') !!}
                                        {!! Form::select(
                                            'cell_phone_company',
                                            [
                                                '1' => __('housingmovements::lang.company1'),
                                                '2' => __('housingmovements::lang.company2'),
                                                '3' => __('housingmovements::lang.company3'),
                                            ],
                                            null,
                                            [
                                                'class' => 'form-control',
                                                'style' => ' height: 40px',
                                                'placeholder' => __('housingmovements::lang.cell_phone_company'),
                                            ],
                                        ) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('contact_number', __('housingmovements::lang.contact_number') . ':*') !!}
                                        {!! Form::number('contact_number', null, [
                                            'class' => 'form-control',
                                            'style' => ' height: 40px',
                                            'placeholder' => __('housingmovements::lang.contact_number'),
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
            var SIMCards_table = $('#SIMCards_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('SIMCard') }}',
                },
                columns: [
                    // {
                    //     data: null,
                    //     render: function(data, type, row) {
                    //         return '<input type="checkbox" class="select-row" data-id="' + row.id + '">';
                    //     },
                    //     orderable: false,
                    //     searchable: false,
                    // },
                    {
                        data: 'id',
                    },
                    {
                        data: 'full_name',
                        searchable: false

                    },
                    {
                        data: 'border_no',
                    },
                    
                    {
                        data: 'company',
                    },
                    {
                        data:'unified_number',
                    },
                    
                    {
                        data: 'contact_number',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#select-all').change(function() {
                $('.select-row').prop('checked', $(this).prop('checked'));
            });

            $('#SIMCards_table').on('change', '.select-row', function() {
                $('#select-all').prop('checked', $('.select-row:checked').length ===
                    SIMCards_table.rows().count());
            });


        });
    </script>
    <script>
        function addSIM(workerId) {
            $('#addSIMModal').modal('show');
            $('#addSIMForm').find('input[name="user"]').val(workerId);
        }
    </script>


@endsection
