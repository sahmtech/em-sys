@extends('layouts.app')
@section('title', __('essentials::lang.work_cards'))
@section('content')

<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.create_work_cards')</span>
    </h1>
</section>


<!-- Main content -->
<section class="content">
@component('components.filters', ['title' => __('report.filters')])

<div class="col-md-3">
    <div class="form-group">
        <label for="offer_type_filter">@lang('essentials::lang.project'):</label>
        {!! Form::select('contact-select', $sales_projects, null, [
            'class' => 'form-control select2',
            'style' => 'height:36px',
            'placeholder' => __('lang_v1.all'),
          
            'id' => 'contact-select'
        ]) !!}
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        <label for="offer_type_filter">@lang('essentials::lang.proof_numbers'):</label>
        {!! Form::select('proof_numbers_select', $proof_numbers->pluck('full_name','id'), null, [
            'class' => 'form-control select2',
            'multiple'=>'multiple',
            'style' => 'height:36px',
            'placeholder' => __('lang_v1.all'),
            'name'=>'proof_numbers_select[]',
            'id' => 'proof_numbers_select'
        ]) !!}
    </div>
</div>
     

     

@endcomponent

@component('components.widget', ['class' => 'box-primary'])

    @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="{{action([\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'create'])}}">
                <i class="fa fa-plus"></i> @lang('essentials::lang.create_work_cards')</a>
            </div>
        @endslot
        @php 
            $colspan = 14;
        @endphp
        <div class="col-md-8 selectedDiv" style="display:none;">  </div>
      
        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="operation_table">
                <thead>
               
               
             
                    <tr>
                    <th><input type="checkbox" class="largerCheckbox" id="chkAll" /></th>
                    <th>@lang('essentials::lang.card_no')</th>
                    <th>@lang('essentials::lang.company_name')</th>
                   
                    <th>@lang('essentials::lang.employee_name')</th>
                    <th>@lang('essentials::lang.nationality')</th>
                    <th>@lang('essentials::lang.Residency_no')</th>
                    <th>@lang('essentials::lang.Residency_end_date')</th>
                    <th>@lang('essentials::lang.project')</th>
                 {{-- <th>@lang('essentials::lang.responsible_client')</th>--}}   
              
                 
                    <th>@lang('essentials::lang.pay_number')</th>
                    <th>@lang('essentials::lang.fixed')</th>
                    <th>@lang('essentials::lang.fees')</th>
                 
                    <th>@lang('messages.action')</th>
                    </tr>
                </thead>

                    
                <tfoot>
                    <tr>
                    <td colspan="5">
                        <div style="display: flex; width: 100%;">
                        {!! Form::open(['url' => action([\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'postRenewData']),
                                 'method' => 'post', 'id' => 'renew_form' ]) !!}
                            
                                {!! Form::hidden('selected_rows', null, ['id' => 'selected_rows']); !!}
                               
                                @include('essentials::cards.partials.renew_modal')
                               
                                {!! Form::submit(__('essentials::lang.renew'),
                                    array('class' => 'btn btn-xs btn-success', 'id' => 'renew-selected')) !!}

                         {!! Form::close() !!}
                        
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    @endcomponent



</section>


@endsection

@section('javascript')
<script type="text/javascript">
   
   var translations = {
        months: @json(__('essentials::lang.months'))
      
    };

  
    
$(document).ready(function () {
  
    var customers_table = $('#operation_table').DataTable({
       
        processing: true,
        serverSide: true,
        ajax: {
                    url: "{{ route('cards') }}",
                    data: function (d) {
                    d.project = $('#contact-select').val();
                    
                    d.proof_numbers = $('#proof_numbers_select').val();
                    console.log(d);


                       },
                      
                },
        
        rowCallback: function (row, data) {

            if (data.expiration_date) {
                var expiration_date = moment(data.expiration_date, 'YYYY-MM-DD HH:mm:ss');
                
                var threeDaysAgo = moment().subtract(3, 'days');

                if (expiration_date < moment() ) {
                    $('td:eq(6)', row).css('background-color', 'rgba(255, 0, 0, 0.2)'); 
                    console.log(expiration_date);
                } else {
                    $('td:eq()', row).css('background-color', '');
                }
            }

            },     
        columns: [
          
            { 
                data: 'id', 
                name: 'checkbox', 
                orderable: false, 
                searchable: false,
                render: function (data, type, row) {
                    return '<input type="checkbox" name="tblChk[]" class="tblChk" data-id="' + data + '" />';
                }
            },
            { data: 'card_no', name: 'card_no' },
            {data:  'company_name' ,name:'company_name'},
            
            { data: 'user', name: 'user' },
            { data: 'nationality', name: 'nationality' },
            { data: 'proof_number', name: 'proof_number' },
            { data: 'expiration_date', name: 'expiration_date' },
            { data: 'project', name: 'project' },
            // {data: 'responsible_client' ,name:'responsible_client'},
        
            {data: 'Payment_number' ,name:'Payment_number'},
            {data: 'fixnumber' ,name:'fixnumber'},
            {data: 'fees' ,name:'fees'},
          
            { data: 'action', name: 'action', orderable: false, searchable: false },
           
        ]
       
    });
   
 
$('#contact-select').on('change', function () {
 
   
            customers_table.ajax.reload();
});

$('#proof_numbers_select').on('change', function () {
    console.log($('#proof_numbers_select').val());
        customers_table.ajax.reload();
});

$('#operation_table').on('change', '.tblChk', function (){
         
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

function calculateFees(selectedValue) {
    switch (selectedValue) {
        case '3':
            return 2425;
        case '6':
            return 4850;
        case '9':
            return 7275;
        case '12':
            return 9700;
        default:
            return 0; 
    }
}
$('#renew-selected').on('click', function (e) {
        e.preventDefault();

        var selectedRows = getCheckRecords();
        console.log(selectedRows);

        if (selectedRows.length > 0) {
            $('#renewModal').modal('show');

            $.ajax({
                url: '{{ route("getSelectedworkcardData") }}',
                type: 'post',
                data: { selectedRows: selectedRows },
                success: function (data) {
                  
                    $('.modal-body').find('input').remove();

                    console.log(data);
                    var inputClasses = 'form-group';
                    var renewDurationInputs = $('select[name="renew_duration[]"]');
                    renewDurationInputs.on('change', function () {
                        var selectedValue = $(this).val();
                        var feesInput = $(this).closest('.row').find('input[name="fees[]"]');
                        var fees = calculateFees(selectedValue);
                        console.log(fees);
                        feesInput.val(fees);
                    });
                                    
                    $.each(data, function (index, row) {
                        
                        var rowDiv = $('<div>', {
                                class: 'row'
                            });

                       
                      var selectInput = $('<select>', {
                            name: 'renew_duration[]',
                            class: 'form-control',
                            style: 'height: 40px',
                            required: true,
                            id: 'renew_duration_' + index 
                        });

                        var rowIDInput = $('<input>', {
                            type: 'hidden',
                            name: 'id[]',
                            class: inputClasses + 'col-md-2', 
                            style: 'height: 40px',
                            placeholder: '{{ __('essentials::lang.id') }}',
                            required: true,
                            value: row.id
                        });
                        rowDiv.append(rowIDInput);


                        var workerIDInput = $('<input>', {
                            type: 'hidden',
                            name: 'employee_id[]',
                            class: inputClasses + 'col-md-2', 
                            style: 'height: 40px',
                            placeholder: '{{ __('essentials::lang.id') }}',
                            required: true,
                            value: row.employee_id
                        });
                        rowDiv.append(workerIDInput);

                        var numberInput = $('<input>', {
                            type: 'text',
                            name: 'number[]',
                            class: inputClasses + 'col-md-2', 
                            style: 'height: 40px',
                            placeholder: '{{ __('essentials::lang.Residency_no') }}',
                            required: true,
                            value: row.number
                        });

                        rowDiv.append(numberInput);
                        var expiration_dateInput = $('<input>', {
                            type: 'text',
                            name: 'expiration_date[]',
                            class: inputClasses + 'col-md-2', 
                            style: 'height: 40px',
                            placeholder: '{{ __('essentials::lang.expiration_date') }}',
                            required: true,
                            value: row.expiration_date
                        });
                        rowDiv.append(expiration_dateInput);

                        var renewDurationInput = $('<select>', {
                            name: 'renew_duration[]',
                            class: 'form-control ' + inputClasses + 'col-md-2',
                            style: 'height: 40px ; width:150px',
                            required: true,
                        });
                        renewDurationInput.append($('<option>', {
                            value: 'all',
                            text: '{{ __('essentials::lang.all') }}',
                        }));

    $.each({
    
        '3': '{{ __('essentials::lang.3_months') }}',
        '6': '{{ __('essentials::lang.6_months') }}',
        '9': '{{ __('essentials::lang.9_months') }}',
        '12': '{{ __('essentials::lang.12_months') }}',
    },
     function (value, text) {
        renewDurationInput.append($('<option>', {
            value: value,
            text: text,
        }));
    });


                       
    renewDurationInput.val(row.renew_duration);
    renewDurationInput.on('change', function () {
        var selectedValue = $(this).val();
        var feesInput = $(this).closest('.row').find('input[name="fees[]"]');
        var fees = calculateFees(selectedValue);
        console.log(fees);
        feesInput.val(fees);
    });

    rowDiv.append(renewDurationInput);

    var feesInput = $('<input>', {
        type: 'text',
        name: 'fees[]',
        class: inputClasses + 'col-md-2 fees-input', 
        style: 'height: 40px',
        placeholder: '{{ __('essentials::lang.fees') }}',
        required: true,
        value: row.fees
    });

    rowDiv.append(feesInput);

    
                        var pay_numberInput = $('<input>', {
                            type: 'number',
                            name: 'Payment_number[]',
                            class: inputClasses + 'col-md-2', 
                            style: 'height: 40px',
                            placeholder: '{{ __('essentials::lang.pay_number') }}',
                            required: true,
                            value: row.Payment_number
                        });
                        rowDiv.append(pay_numberInput);
                        var fixnumberInput = $('<input>', {
                            type: 'text',
                            name: 'fixnumber[]',
                            class: inputClasses + 'col-md-2', 
                            style: 'height: 40px',
                            placeholder: '{{ __('essentials::lang.fixed') }}',
                            required: true,
                            value: row.fixnumber
                        });

                        rowDiv.append(fixnumberInput);

                        
                        $('.modal-body').append(rowDiv);
                    });
                }
            });



            $('#submitArrived').click(function () {
                
                $.ajax({
                    url: $('#renew_form').attr('action'),
                    type: 'post',
                    data: $('#renew_form').serialize(),
                    success: function (response) {

                        console.log(data);
                        console.log(response);
                        
                        $('#renewModal').modal('hide');
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
});

</script>





@endsection