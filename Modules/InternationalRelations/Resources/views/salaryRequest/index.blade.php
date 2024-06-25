@extends('layouts.app')
@section('title', __('sales::lang.salary_requests'))

@section('content')
    <section class="content-header">
        <h1>
            <span>@lang('sales::lang.salary_requests')</span>
        </h1>
    </section>


    <!-- Main content -->
    <section class="content">

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-md-12">





                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="salary_request_table">
                            <thead>
                                <tr>
                                    <th>@lang('sales::lang.profession')</th>
                                    <th>@lang('sales::lang.specialization')</th>
                                    <th>@lang('sales::lang.nationality')</th>
                                    <th>@lang('sales::lang.quantity')</th>
                                    <th>@lang('sales::lang.note')</th>
                                    <th>@lang('sales::lang.file')</th>
                                    <th>@lang('sales::lang.action')</th>

                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        @endcomponent

        <!-- Add Salary Modal -->
        <!-- Add Salary Modal -->
        <div class="modal fade" id="addSalaryModal" tabindex="-1" role="dialog" aria-labelledby="addSalaryModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form id="addSalaryForm" action="{{ route('updateSalary') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addSalaryModalLabel">{{ __('sales::lang.add_salary') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="sales_id" id="sales_id">
                            <div class="form-group">
                                <label for="salary">{{ __('sales::lang.salary') }}</label>
                                <input type="number" class="form-control" id="salary" name="salary" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ __('sales::lang.close') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('sales::lang.save_salary') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



    </section>
    <!-- /.content -->

@endsection
@section('javascript')

    <script type="text/javascript">
        function reload() {
            $('#salary_request_table').DataTable().ajax.reload();

        }
        $(document).ready(function() {

            var salary_request_table = $('#salary_request_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('get_Irsalary_requests') }}',
                columns: [{
                        data: 'profession_id'
                    },
                    {
                        data: 'specialization_id'
                    },
                    {
                        data: 'nationality_id'
                    },
                    {
                        data: 'quantity'
                    },
                    {
                        data: 'note'
                    },

                    {
                        data: 'file'
                    }, {
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
        $('#addSalaryModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var salesId = button.data('id'); // Extract info from data-* attributes
            var modal = $(this);
            modal.find('.modal-body #sales_id').val(salesId);
        });
    </script>




@endsection
