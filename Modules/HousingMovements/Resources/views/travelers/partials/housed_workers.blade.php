@extends('layouts.app')
@section('title', __('housingmovements::lang.housed'))
@section('content')
@include('housingmovements::layouts.nav_trevelers')

<section class="content-header">
    <h1>
        <span>@lang('housingmovements::lang.housed')</span>
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

    @php 
    $colspan = 5;
   
@endphp
<div class="col-md-8 selectedDiv" style="display:none;">
</div>
<table class="table table-bordered table-striped ajax_view hide-footer" id="product_table2">
    <thead>
        <tr>
             <th>
                <input type="checkbox" class="largerCheckbox" id="chkAll" />
              </th>
          
                    <th>@lang('housingmovements::lang.worker_name')</th>  
                    <th>@lang('housingmovements::lang.project')</th> 
                    <th>@lang('housingmovements::lang.location')</th> 
                    <th>@lang('housingmovements::lang.arrival_date')</th> 
                    <th>@lang('housingmovements::lang.passport_number')</th>          
                    <th>@lang('housingmovements::lang.profession')</th>
                    <th>@lang('housingmovements::lang.nationality')</th>
                    <th>@lang('messages.action')</th>
           
        </tr>
    </thead>
    

    
    <tfoot>
        <tr>
        <td colspan="5">
            <div style="display: flex; width: 100%;">
    
                
                    &nbsp;

                            {!! Form::hidden('selected_products', null, ['id' => 'selected_products_for_edit']); !!}
                            <button type="submit" class="btn btn-xs btn-warning" id="edit-selected"> <i class="fa fa-home"></i>{{__('housingmovements::lang.housed')}}</button>
                           
                
              
               
                </div>
            </td>
        </tr>
    </tfoot>
</table>

            @include('housingmovements::travelers.partials.housing_modal')
       
    @endcomponent



</section>
<!-- /.content -->

@endsection
@section('javascript')

<script type="text/javascript">
   
    var product_table2;

    function reloadDataTable() {
        product_table2.ajax.reload();
    }

    $(document).ready(function () {
        product_table2 = $('#product_table2').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("housed_workers") }}',
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
                        d.date_filter = d.date_filter;
                    }
                }
            },
            rowCallback: function (row, data) {
                var arrivalDate = moment(data.arrival_date, 'YYYY-MM-DD HH:mm:ss');
                var threeDaysAgo = moment().subtract(3, 'days');

                if (arrivalDate < moment() && arrivalDate >= threeDaysAgo) {
                    $('td:eq(4)', row).css('background-color', 'rgba(255, 0, 0, 0.2)'); 
                } else {
                    $('td:eq(4)', row).css('background-color', '');
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
                product_table2.ajax.reload();
            });

        $('#product_table2').on('change', '.tblChk', function (){
         
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
                type: 'post',
                data: { selectedRows: selectedRows },
                success: function (data) {
                 
                    $('.modal-body').find('input').remove();

               
                    var inputClasses = 'form-group col-md-4 ';

              
                    $.each(data, function (index, row) {
                       
                        var workerIDInput = $('<input>', {
                            type: 'hidden',
                            name: 'worker_id[]',
                            class: inputClasses + 'mb-2', 
                            placeholder: '{{ __('housingmovements::lang.id') }}',
                            required: true,
                            value: row.worker_id
                        });
                        
                        var workerNameInput = $('<input>', {
                            type: 'text',
                            name: 'worker_name[]',
                            class: inputClasses + 'mb-2', 
                            placeholder: '{{ __('housingmovements::lang.worker_name') }}',
                            required: true,
                            value: row.worker_name
                        });

                        var passportNumberInput = $('<input>', {
                            type: 'text',
                            name: 'passport_number[]',
                            class: inputClasses+ 'mb-2',
                            placeholder: '{{ __('housingmovements::lang.passport_number') }}',
                            required: true,
                            value: row.passport_number
                        });

                        var borderNoInput = $('<input>', {
                            type: 'number',
                            name: 'border_no[]',
                            class: inputClasses + 'mb-2',
                            placeholder: '{{ __('housingmovements::lang.border_no') }}',
                            required: true
                        });

                     
                        $('.modal-body').append(workerIDInput,workerNameInput, passportNumberInput, borderNoInput);
                    });
                }
            });

            $('#submitArrived').click(function () {
              
                $.ajax({
                    url: $('#arrived_form').attr('action'),
                    type: 'post',
                    data: $('#arrived_form').serialize(),
                    success: function (response) {

                 
                        console.log(response);
                      
                        $('#arrivedModal').modal('hide');
                        reloadDataTable();
                    }
                });
            });

        } else {
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

    var selectedRows = getCheckRecords();

    if (selectedRows.length > 0) {
      
        $('#bulkEditModal').modal('show');

        $('#bulk_edit_form').find('input[name="worker_id[]"]').remove();

        
        $.each(selectedRows, function (index, workerId) {
            var workerIdInput = $('<input>', {
                type: 'hidden',
                name: 'worker_id[]',
                value: workerId
            });

            
            $('#bulk_edit_form').append(workerIdInput);
        });
    } 
    
    else {
        $('input#selected_rows').val('');
        swal('@lang("lang_v1.no_row_selected")');
    }
});

$('#bulk_edit_form').submit(function (e) {
   
    e.preventDefault();


var formData = $(this).serializeArray();
   console.log(formData);
   console.log( $(this).attr('action'));
    $.ajax({
        url: $(this).attr('action'),
        type: 'post',
        data: formData,
        success: function (response) {
         
            console.log(response);

        
            $('#bulkEditModal').modal('hide');
            reloadDataTable();
        }
    });
});


$('#room_status').change(function(){
        var htr_building = $('#htr_building_select').val();
        console.log($(this).val());
        $.getJSON("{{  url('housingmovements/room_status')}}", 
        { option: $(this).val()
        ,htr_building: htr_building }, 
        
        function (data) {
            var model = $('#room_number');

            $('#room_number').empty();
            $('#beds_count').val(''); 

            $.each(data, function (index, room) {
                $('#room_number').append($('<option>', {
                    value: room.id,
                    text: room.text
                }));
            });

            if (data.length > 0) {
                
                $('#beds_count').val(data[0].beds_count);
            }
        });
       
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
            $('#building_htr').val(buildingId);

            $.ajax({
                url: '{{ route("getRoomNumbers", ["buildingId" => ":buildingId"]) }}'.replace(':buildingId', buildingId),
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $('#room_number').empty();

                    $.each(data.roomNumber, function (id, text) {
                        $('#room_number').append($('<option>', {
                            value: id,
                            text: text
                        }));
                    });

                    $('#room_number').trigger('change');
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        
        $('#room_number').change(function(){
                var htr_building = $('#htr_building_select').val();
                var roomId=$(this).val();
                console.log($(this).val());

                $.ajax({
                url: '{{ route("getBedsCount", ["roomId" => ":roomId"]) }}'.replace(':roomId', roomId),
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                
                    var bedsCount = data.roomNumber[roomId]; 
                
                    $('#beds_count').val(bedsCount);
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
  
  

    });
</script>


<script>
    $(document).ready(function () {
        $('#project_name2').on('change', function () {
            var projectId = $(this).val();

            $.ajax({
                url: '{{ route("getShifts", ["projectId" => ":projectId"]) }}'.replace(':projectId', projectId),
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                
                    $('#shift_name').empty();

                
                    $.each(data.shifts, function (id, text) {
                        $('#shift_name').append($('<option>', {
                            value: id,
                            text: text
                        }));
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching shifts:', error);
                }
            });
        });
    });
</script>
@endsection