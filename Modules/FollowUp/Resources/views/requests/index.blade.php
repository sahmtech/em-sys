@extends('layouts.app')
@section('title', __('followup::lang.requests'))

@section('content')


<section class="content-header">
    <h1>
        <span>@lang('followup::lang.requests')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    
    @component('components.widget', ['class' => 'box-primary'])

      
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="requests_table">
                    <thead>
                        <tr>
                            <th>@lang('followup::lang.request_type')</th>
                            <th>@lang('followup::lang.request_creation')</th>
                            <th>@lang('followup::lang.direct_management')</th>
                            <th>@lang('followup::lang.general_management')</th>
                            <th>@lang('followup::lang.action')</th>


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

    $(document).ready(function () {
       
    // $('#requests_table').DataTable({
    //     processing: true,
    //     serverSide: true,

    //     ajax: {
    //                 url: "{{ route('requests') }}",
                   
    //             },
    //     columns: [
    //         { data: 'contact_name' },
    //         { data: 'number_of_contract'},
    //         { data: 'start_date'},
    //         { data: 'end_date'},
    //         { data: 'action' },
            

    //     ]

    // });


    });

</script>
@endsection
