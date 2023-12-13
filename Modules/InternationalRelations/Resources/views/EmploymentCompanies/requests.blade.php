@extends('layouts.app')
@section('title', __('internationalrelations::lang.Delegation'))

@section('content')


<section class="content-header">
    <h1>
        <span>@lang('internationalrelations::lang.Delegation')</span>
    </h1>
</section>


<!-- Main content -->
<section class="content">
    @component('components.filters', ['title' => __('report.filters')])

    <div class="col-md-3">
        <div class="form-group">
            <label for="agency_filter">@lang('internationalrelations::lang.agency_name'):</label>
            {!! Form::select('agency_filter', $agencys, request('agency_filter'), [
                'class' => 'form-control select2',
                'style' => 'height:36px',
                'placeholder' => __('lang_v1.all'),
                'id' => 'agency_filter',
            ]) !!}
        </div>
    </div>

@endcomponent
    @component('components.widget', ['class' => 'box-primary'])

  
 
           
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="EmpCompany_table">
         <thead> 
                <tr>
                    <th>{{ __('internationalrelations::lang.agency_name') }}</th>   
                    <th>{{ __('internationalrelations::lang.target_quantity') }}</th>
                    <th>{{ __('internationalrelations::lang.currently_proposed_labors_quantity') }}</th>
                    <th>{{ __('sales::lang.profession_name') }}</th>
                    <th>{{ __('sales::lang.specialization_name') }}</th>
                    <th>{{ __('sales::lang.gender') }}</th>
                    <th>{{ __('sales::lang.salary') }}</th>
                    <th>{{ __('sales::lang.additional_allwances') }}</th>
                    <th>{{ __('sales::lang.monthly_cost_for_one') }}</th>
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
        $(document).ready(function() {
            var EmpCompany_table = $('#EmpCompany_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('delegations') }}",
                    data: function(d) {
                      
                        d.agency = $('#agency_filter').val();
                      

                       
                    },
                },

                "columns": [
                    { "data": "agency_name" },
                    { "data": "target_quantity" },
                    { "data": "currently_proposed_labors_quantity" },
                    { "data": "profession_name" },
                    { "data": "specialization_name" },
                    { "data": "gender" },
                    { "data": "service_price" },
                    { "data": "additional_allwances" },
                    { "data": "monthly_cost_for_one" },
                    { "data": "actions" }
                ],
                        

            });

            $('#agency_filter').change(
             function() {
                EmpCompany_table.ajax.reload();

            });
     

        });
    </script>



@endsection
