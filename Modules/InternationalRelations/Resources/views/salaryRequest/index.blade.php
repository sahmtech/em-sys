@extends('layouts.app')
@section('title', __('sales::lang.salary_requests'))

@section('content')
<section class="content-header">
    <h1>
        <span>@lang('sales::lang.salary_requests')</span>
    </h1>
</section>


<!-- Main content -->
<section class="content">

    @component('components.widget', ['class' => 'box-primary'])


<div class="row">
    <div class="col-md-12">




      
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="salary_request_table">
                    <thead>
                        <tr>
                            <th>@lang('sales::lang.worker_name')</th>
                            <th>@lang('sales::lang.salary')</th>
                            <th>@lang('sales::lang.arrival_period')</th>
                            <th>@lang('sales::lang.recruitment_fees')</th> 
                            <th>@lang('sales::lang.nationality')</th>
                            <th>@lang('sales::lang.profession')</th> 
                          
                        </tr>
                    </thead>
                </table>
            </div>
    </div>
    </div>


 
    @endcomponent

@include('sales::salary_requests.create_modal')

@include('sales::salary_requests.edit_modal')

</section>
<!-- /.content -->

@endsection
@section('javascript')

<script type="text/javascript">
    
    function reload()
    { 
        $('#salary_request_table').DataTable().ajax.reload();
    
    }
    $(document).ready(function () {
  
        var salary_request_table = $('#salary_request_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("get_Irsalary_requests") }}', 
            columns: [
                { data: 'worker_id'},
                { data: 'salary'},
                { data: 'arrival_period'},
                { data: 'recruitment_fees'},
                { data: 'nationality' },
                { data: 'profession' },

               
            ]
    });

    });


</script>





@endsection
