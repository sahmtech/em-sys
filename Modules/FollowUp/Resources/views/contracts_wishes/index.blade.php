@extends('layouts.app')
@section('title', __('followup::lang.contrascts_wishes'))

@section('content')


<section class="content-header">
    <h1>
        <span>@lang('followup::lang.contrascts_wishes')</span>
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
                        {!! Form::select('project_name_filter',$contacts, null, ['class' => 'form-control', 'style' => ' height:40px;width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="offer_status_filter">@lang('essentials::lang.wish'):</label>
                        <select class="form-control select2" name="offer_status_filter" required id="offer_status_filter" style="width: 100%;">
                            <option value="all">@lang('lang_v1.all')</option>
                            <option value="renrew_contract">@lang('followup::lang.renew_contract')</option>
                            <option value="not_renew_contract">@lang('followup::lang.not_renew_contract')</option>
                          

                        </select>
                    </div>
                </div>
                
              
            @endcomponent
        </div>
    </div>
    @component('components.widget', ['class' => 'box-primary'])

      
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="contract_wish_table">
                    <thead>
                        <tr>
                            <th>@lang('followup::lang.worker_number')</th>
                            <th>@lang('followup::lang.worker_name')</th>
                            <th>@lang('sales::lang.project_name')</th>
                            <th>@lang('followup::lang.residency')</th>
                            <th>@lang('sales::lang.start_date')</th>
                            <th>@lang('sales::lang.end_date')</th>
                            <th>@lang('followup::lang.wish')</th>
                         
                            <th>@lang('sales::lang.action')</th>


                        </tr>
                    </thead>
                </table>
            </div>
 
    @endcomponent

    @include('followup::contracts_wishes.change_wish_modal')

</section>


@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function () {
            var contractWishTable = $('#contract_wish_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("contracts_wishes") }}',
                columns: [
                    { data: 'emp_number', name: 'emp_number' },
                    { data: 'name', name: 'name' },
                    { data: 'project_name', name: 'project_name' },
                    { data: 'residency', name: 'residency' },
                    { data: 'contract_start_date', name: 'contract_start_date' },
                    { data: 'contract_end_date', name: 'contract_end_date' },
                    { data: 'wish', name: 'wish' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            $(document).on('click', 'a.change-status-btn', function(e) {
            e.preventDefault();
            $('#change_status_modal').find('select#modal-wish').val($(this).data('wish')).change();
            $('#change_status_modal').find('#employee_id').val($(this).data('employee-id'));
            $('#change_status_modal').modal('show');


            
        });         

$(document).on('submit', 'form#change_status_form', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray();
    var ladda = Ladda.create(document.querySelector('.update-offer-status'));
    ladda.start();

  


    $.ajax({
        method: $(this).attr('method'),
        url: $(this).attr('action'),
        dataType: 'json',
        data: data,
        success: function(result) {
            ladda.stop();
            console.log(data);
            if (result.success == true) {
                $('div#change_status_modal').modal('hide');
                toastr.success(result.msg);
                contractWishTable.ajax.reload();
            } else {
                toastr.error(result.msg);
            }
        },
    });
});


        });
    </script>
@endsection
