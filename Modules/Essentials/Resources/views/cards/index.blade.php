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
        {!! Form::select('contact-select', $contacts, null, [
            'class' => 'form-control',
            'style' => 'height:36px',
            'placeholder' => __('lang_v1.all'),
            'required',
            'id' => 'contact-select'
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

        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="operation_table">
                <thead>
                    <tr>
                    <th>@lang('essentials::lang.card_no')</th>
                    <th>@lang('essentials::lang.employee_name')</th>
                    <th>@lang('essentials::lang.Residency_no')</th>
                    <th>@lang('essentials::lang.Residency_end_date')</th>
                    <th>@lang('essentials::lang.company_name')</th>
                    <th>@lang('essentials::lang.responsible_client')</th>
                 
                    <th>@lang('essentials::lang.project')</th>
                    <th>@lang('essentials::lang.work_card_duration')</th>
                    <th>@lang('essentials::lang.pay_number')</th>
                    <th>@lang('essentials::lang.fixed_no')</th>
                    <th>@lang('essentials::lang.fees')</th>
                 
                    <th>@lang('messages.action')</th>
                    </tr>
                </thead>
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
                console.log(d);
            },
                },
        
       
        columns: [
          
            
            { data: 'card_no', name: 'card_no' },
            { data: 'user', name: 'user' },
            { data: 'proof_number', name: 'proof_number' },
            { data: 'expiration_date', name: 'expiration_date' },
            {data: 'company_name' ,name:'company_name'},
            {data: 'responsible_client' ,name:'responsible_client'},
           
            { data: 'project', name: 'project' },
            {
                    data: 'workcard_duration',
                    name: 'workcard_duration',
                    render: function (data, type, row) {
                        return data !== null ? data + ' ' + translations.months : '';
                    },
                },
            {data: 'Payment_number' ,name:'Payment_number'},
            {data: 'fixnumber' ,name:'fixnumber'},
            {data: 'fees' ,name:'fees'},
          
            { data: 'action', name: 'action', orderable: false, searchable: false },
           
        ]
    });
   
 
$('#contact-select').on('change', function () {
    customers_table.ajax.reload();
    console.log('loc selected: ' + $('#contact-select').val());
});


    
  
});

</script>





@endsection