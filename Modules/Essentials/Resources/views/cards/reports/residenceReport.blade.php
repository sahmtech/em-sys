@extends('layouts.app')
@section('title', __('essentials::lang.residencyreports'))
@section('content')

<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.residencyreports')</span>
    </h1>
</section>


<!-- Main content -->
<section class="content">
@component('components.filters', ['title' => __('report.filters')])


<div class="col-md-3">
    <div class="form-group">
        <label for="offer_type_filter">@lang('essentials::lang.proof_numbers'):</label>
        {!! Form::select('proof_numbers_select', $proof_numbers->pluck('full_name','id'), null, [
            'class' => 'form-control select2',
            'multiple'=>'multiple',
            'style' => 'height:40px',
           
            'name'=>'proof_numbers_select[]',
            'id' => 'proof_numbers_select'
        ]) !!}
    </div>
</div>
     


     

@endcomponent


@component('components.widget', ['class' => 'box-primary'])


        <div class="col-md-8 selectedDiv" style="display:none;">  </div>
      
        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="residency_report_table">
                <thead>
               
               
             
                    <tr>
                   
                 
                    <th>@lang('essentials::lang.employee_name')</th>
                    {{--
                        <th>@lang('essentials::lang.company_name')</th>
                    <th>@lang('essentials::lang.project')</th> 
                        --}}
                   
                 
                  
                
                    <th>@lang('essentials::lang.Residency_no')</th>
                    <th>@lang('essentials::lang.renew_start_date')</th>
                    <th>@lang('essentials::lang.renew_duration')</th>
                    <th>@lang('essentials::lang.renew_end_date')</th>
                    </tr>
                </thead>

              
            </table>
        </div>

    @endcomponent



</section>


@endsection
@section('javascript')
<script type="text/javascript">
	$(document).ready(function(){
        var translations = {
        months: @json(__('essentials::lang.months'))
      
    };

		var documents = $("#residency_report_table").DataTable({
			processing: true,
            ajax: {
                    url: "{{ route('getResidencyreport') }}",
                    data: function (d) {
                  //  d.project = $('#contact-select').val();
                    
                    d.proof_numbers = $('#proof_numbers_select').val();
                    console.log(d);


                       },
                },
			
            columns: [
                { data: 'user', name: 'user' },
    
                { data: 'residency_number', name: 'residency_number' },

                { data: 'renew_start_date', name: 'renew_start_date' },
                { data: 'duration', name: 'duration',
                    render: function (data, type, row) {
                     
                        var months = data +' '+ translations.months;


                        return months;
                    }
                },
                { data: 'renew_end_date', name: 'renew_end_date' },
					]
		});
		$('#proof_numbers_select').on('change', function () {
    console.log($('#proof_numbers_select').val());
    documents.ajax.reload();
});


	});
</script>
@endsection