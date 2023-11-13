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


@component('components.widget', ['class' => 'box-primary'])

    @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="{{action([\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'create'])}}">
                <i class="fa fa-plus"></i> @lang('essentials::lang.create_work_cards')</a>
            </div>
        @endslot

        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="operation_table">
                <thead>
                    <tr>
                    <th>@lang('essentials::lang.employee_name')</th>
                    <th>@lang('essentials::lang.Residency_no')</th>
                    <th>@lang('essentials::lang.Residency_end_date')</th>
                         <th>@lang('essentials::lang.project')</th>
                    <th>@lang('essentials::lang.work_card_duration')</th>
                    <th>@lang('essentials::lang.pay_number')</th>
                    <th>@lang('essentials::lang.fixed_no')</th>
                    <th>@lang('essentials::lang.fees')</th>
               
                    <th>@lang('essentials::lang.company_name')</th>
                    <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>

    @endcomponent



</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
    // Countries table
    $(document).ready(function () {
    var customers_table = $('#operation_table').DataTable({
       
        processing: true,
        serverSide: true,
        ajax: {
                    url: "{{ route('cards') }}",
                   
                },
        
       
        columns: [
          

            { data: 'user', name: 'user' },
            { data: 'id_proof_number', name: 'id_proof_number' },
            { data: 'expiration_date', name: 'expiration_date' },
            { data: 'project', name: 'project' },
            {data: 'workcard_duration' ,name:'workcard_duration'},
            {data: 'Payment_number' ,name:'Payment_number'},
            {data: 'fixnumber' ,name:'fixnumber'},
            {data: 'fees' ,name:'fees'},
            {data: 'company_name' ,name:'company_name'},
            { data: 'action', name: 'action', orderable: false, searchable: false },
           
        ]
    });
   
 
 


    
  
});

</script>





@endsection