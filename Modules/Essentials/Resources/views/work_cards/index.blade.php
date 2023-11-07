@extends('layouts.app')
@section('title', __('essentials::lang.work_cards'))
@section('content')

<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.all_work_cards')</span>
    </h1>
</section>

<section class="content">
@component('components.widget', ['class' => 'box-primary'])

@slot('tool')
        <div class="box-tools">
            <a class="btn btn-block btn-primary" href="{{action([\Modules\Essentials\Http\Controllers\WorkCardsController::class, 'create'])}}">
            <i class="fa fa-plus"></i> @lang('essentials::lang.add_work_card')</a>
        </div>
    @endslot

    <div class="table-responsive">
        <table class="table table-bordered table-striped ajax_view" id="work_table">
            <thead>
                <tr>
                    
                    <th>@lang('essentials::lang.employee_name')</th>
                    <th>@lang('essentials::lang.Residency_no')</th>
                    <th>@lang('essentials::lang.Residency_end_date')</th>
                    <th>@lang('essentials::lang.project')</th>
                    <th>@lang('essentials::lang.work_card_duration')</th>
                    <th>@lang('essentials::lang.pay_number')</th>
                    <th>@lang('essentials::lang.fees')</th>
                    <th>@lang('essentials::lang.fixed_no')</th>
                    <th>@lang('essentials::lang.company_name')</th>
                    <th>@lang('messages.action')</th>
                </tr>
            </thead>
        </table>
    </div>

@endcomponent

@section('javascript')
<script type="text/javascript">
    // Countries table
    $(document).ready(function () {
    var customers_table = $('#work_table').DataTable({
       
        processing: true,
        serverSide: true,
        ajax: {
                    url: 'hrm/workCards',
                   
                },
        
       
        columns: [
          
         
            { data: 'employee_name', name: 'employee_name' },
            { data: 'Residency_no', name: 'Residency_no' },
            { data: 'Residency_end_date', name: 'Residency_end_date' },
            { data: 'project', name: 'project' },
            { data: 'work_card_duration', name: 'work_card_duration' },
             {data: 'pay_number' ,name:'pay_number'},
             {data: 'fee' ,name:'fee'},
             {data: 'fixed_no' ,name:'fixed_no'},
             {data: 'company_name' ,name:'company_name'},
            { data: 'action', name: 'action', orderable: false, searchable: false },
           
        ]
    });
    
 
   


    
   
});

</script>





@endsection


</section>
@endsection
