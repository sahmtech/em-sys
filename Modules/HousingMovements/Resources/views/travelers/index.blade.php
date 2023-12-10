@extends('layouts.app')
@section('title', __('housingmovements::lang.travelers'))
@section('content')

<section class="content-header">
    <h1>
        <span>@lang('housingmovements::lang.travelers')</span>
    </h1>
</section>



<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('project_name_filter', __('followup::lang.project_name') . ':') !!}
                    {!! Form::select('project_name_filter', $salesProjects, null, [
                        'class' => 'form-control select2',
                        'id'=>'project_name_filter',
                        'style' => 'width:100%;padding:2px;',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>

            <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('doc_filter_date_range', __('housingmovements::lang.arrival_date') . ':') !!}
                            {!! Form::text('doc_filter_date_range', null, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control ',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
              
              
            @endcomponent
        </div>
    </div>
    @component('components.widget', ['class' => 'box-primary'])

            @include('housingmovements::travelers.partials.travelers_list')
        
            @include('housingmovements::travelers.partials.housing_modal')
            @include('housingmovements::travelers.partials.border_arrival_modal')
    @endcomponent



</section>
<!-- /.content -->

@endsection
@section('javascript')
<script type="text/javascript">
    var product_table;

    function reloadDataTable() {
        product_table.ajax.reload();
    }

    $(document).ready(function () {
        product_table = $('#product_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("travelers") }}',
                data: function(d) {
                    if ($('#project_name_filter').val()) {
                        d.project_name_filter = $('#project_name_filter').val();
                        console.log(d.project_name_filter);
                    }

                    if ($('#doc_filter_date_range').val()) {
                        var start = $('#doc_filter_date_range').data('daterangepicker').startDate
                            .format('YYYY-MM-DD');
                        var end = $('#doc_filter_date_range').data('daterangepicker').endDate
                            .format('YYYY-MM-DD');
                        d.filter_start_date = start;
                        d.filter_end_date = end;
                        d.date_filter = date_filter;
                    }
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                { "data": "full_name" },
                { "data": "project" },
                { "data": "location" },
                { "data": "arrival_date" },
                { "data": "passport_number" },
                { "data": "profession" },
                { "data": "nationality" },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });


        $('#project_name_filter').on('change', function() {
            date_filter=null;
         
            reloadDataTable() ;
            });

            $('#doc_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#doc_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                }
            );
            $('#doc_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#doc_filter_date_range').val('');
                date_filter = null;
                reloadDataTable();
            });
           
            $('#doc_filter_date_range').on('change', function() {
                date_filter = 1;
                product_table.ajax.reload();
            });

        $('#product_table').on('change', '.tblChk', function (){
         
            if ($('.tblChk:checked').length == $('.tblChk').length) {
                $('#chkAll').prop('checked', true);
            } else {
                $('#chkAll').prop('checked', false);
            }
            getCheckRecords();
        });

        $("#chkAll").change(function () {
          
            if ($(this).prop('checked')) {
                $('.tblChk').prop('checked', true);
            } else {
                $('.tblChk').prop('checked', false);
            }
            getCheckRecords();
        });

        $('#arraived-selected').on('click', function (e) {
            e.preventDefault();

            var selectedRows = getCheckRecords();
            console.log(selectedRows);
            
            if (selectedRows.length > 0) {
               
             $('#arrivedModal').modal('show');

               
                $.ajax({
                    url: '{{ route("getSelectedArrivalsData") }}',
                    type: 'POST',
                    data: { selectedRows: selectedRows },
                    success: function (data) {
                       
                        $.each(data, function (index, row) {
                          
                            $('input[name="worker_name"]').eq(index).val(row.worker_name);
                            $('input[name="passport_number"]').eq(index).val(row.passport_number);
                           
                        });
                    }
                });
                
                 
                $('#submitArrived').click(function() {
    
                       $('#arraived_form').submit();
                });

            }
             else {
              
                $('input#selected_rows').val('');
                swal({
                    title: "@lang('lang_v1.no_row_selected')",
                    icon: "warning",
                    button: "OK",
                });
            }
        });


        $('#edit-selected').on('click', function (e) {
            e.preventDefault();

            var selectedRows =  getCheckRecords();

            if (selectedRows.length > 0) {
                $('#bulkEditModal').modal('show');
            }
             else
             {
                $('input#selected_rows').val('');
                swal('@lang("lang_v1.no_row_selected")');
            }
        });

      
        $('#applyBulkEdit').on('click', function () {
           
            $('#bulkEditModal').modal('hide');
        });


 

    });

        function getCheckRecords() {
            var selectedRows = [];
            $(".selectedDiv").html("");
            $('.tblChk:checked').each(function () {
                if ($(this).prop('checked')) {
                    const rec = "<strong>" + $(this).attr("data-id") + " </strong>";
                    $(".selectedDiv").append(rec);
                    selectedRows.push($(this).attr("data-id"));
                    
                }

            });
        
            return selectedRows;
        }


</script>


<script>
    $(document).ready(function () {
        $('#htr_building_select').on('change', function () {
            var buildingId = $(this).val();
          
            $.ajax({
                url: '{{ route("getRoomNumbers", ["buildingId" => ":buildingId"]) }}'.replace(':buildingId', buildingId),
                type: 'GET',
                success: function (data) {
                  
                    $('#room_number').val(data.roomNumber);
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching room numbers:', error);
                }
            });
        });
    });
</script>



@endsection

