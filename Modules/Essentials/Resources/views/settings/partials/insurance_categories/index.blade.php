@extends('layouts.app')
@section('title', __('essentials::lang.insurance_categories'))

@section('content')
{{-- @include('essentials::layouts.nav_hrm_setting') --}}
<section class="content-header">
    <h1>@lang('essentials::lang.insurance_categories')</h1>
</section>
<section class="content">
   

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid', ])
           
                @slot('tool')
                <div class="box-tools">
                    
                    <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal" data-target="#addInsuranceCategoryModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
                @endslot
            
            
            <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="insurance_companies_table">
                        <thead>
                            <tr>
                                <th>@lang('essentials::lang.insurance_category_name' )</th>
                                <th>@lang('essentials::lang.insurance_company' )</th>

                                <th>@lang('messages.action' )</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
        <div class="modal fade" id="addInsuranceCategoryModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'insurance_categories.store']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_insurance_category')</h4>
                    </div>
        
                    <div class="modal-body">
    
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('insurance_category_name', __('essentials::lang.insurance_category_name') . ':*') !!}
                                {!! Form::select('insurance_category_name',
                                ['VIP+'=>'VIP+',
                                'VIP'=>'VIP',
                                'A+'=>'A+',
                                'A'=>'A',
                                'B+'=>'B+',
                                'B'=>'B',
                                'C+'=>'C+',
                                'C'=>'C',
                                'CR+'=>'CR+',
                                'CR'=>'CR'],
                                 null, ['class' => 'form-control', 'required']) !!}
                            </div>        
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('insurance_company', __('essentials::lang.insurance_company') . ':*') !!}
                 
                            {!! Form::select('insurance_company', $insurance_companies, null, ['class' => 'form-control','placeholder' => __('essentials::lang.insurance_company'),  'required']) !!}
                        </div>
                    </div>
        
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var insurance_companies_table;

            function reloadDataTable() {
                insurance_companies_table.ajax.reload();
            }

            insurance_companies_table  = $('#insurance_companies_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ route('insurance_categories') }}",
                   
                },
                
                columns: [
                    { data: 'name' },
                    { data: 'insurance_company_id' },
                    { data: 'action' },
                ],
            });

            // $('#doc_filter_date_range').daterangepicker(
            //     dateRangeSettings,
            //     function(start, end) {
            //         $('#doc_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            //     }
            // );
            // $('#doc_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            //     $('#doc_filter_date_range').val('');
            //     reloadDataTable();
            // });

            // $(document).on('change', '#user_id_filter, #status_filter, #doc_filter_date_range, #doc_type_filter', function() {
            //     reloadDataTable();
            // });
          
            $(document).on('click', 'button.delete_insurance_category_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_country,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');
                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        success: function (result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                insurance_companies_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });
            
        });
    
    </script>
@endsection
