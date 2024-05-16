@extends('layouts.app')
@section('title', __('housingmovements::lang.medicalExamination'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.medicalExamination')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @include('essentials::layouts.nav_trevelers')
       

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                  
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="medicalExamination_table">
                            <thead>
                                <tr>
                                    {{-- <th>
                                        <input type="checkbox" id="select-all">
                                    </th> --}}
                                    <th>#</th>
                                    <th>@lang('housingmovements::lang.worker_name')</th>
                                    <th>@lang('housingmovements::lang.medicalExamination')</th>
                                 
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>



                        </table>
                      
                    </div>
                 
                @endcomponent
            </div>



          

        </div>

   
    </section>
    <!-- /.content -->
    {{-- <div class="col-md-8 selectedDiv" style="display:none;">
    </div> --}}
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        var medicalExamination_table = $('#medicalExamination_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('medicalExamination') }}',
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
                },
                {
                    data: 'medical_examination',
                    render: function(data, type, row) {
                        return data === 1 ? '@lang('housingmovements::lang.done')' : '@lang('housingmovements::lang.not_yet')';
                    }
                },
                {
                    data: 'action', 
                    name: 'action', 
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // $('#select-all').change(function() {
        //     $('.select-row').prop('checked', $(this).prop('checked'));
        // });

        // $('#medicalExamination_table').on('change', '.select-row', function() {
        //     $('#select-all').prop('checked', $('.select-row:checked').length === medicalExamination_table.rows().count());
        // });

        window.addFile = function(workerId) {
            const input = document.createElement('input');
            input.type = 'file';
            input.onchange = e => {
                const file = e.target.files[0];
                const formData = new FormData();
                formData.append('file', file);
                formData.append('workerId', workerId);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('uploadMedicalDocument') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                 
                        $('#medicalExamination_table').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        alert('Error uploading file.');
                    }
                });
            };
            input.click(); 
        };
    });
</script>



@endsection
