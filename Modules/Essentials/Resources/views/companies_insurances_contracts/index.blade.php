@extends('layouts.app')
@section('title', __('essentials::lang.companies_insurance_contracts'))

@section('content')

<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.companies_insurance_contracts')</span>
    </h1>
</section>


<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('insurance_company_filter', __('essentials::lang.insurance_company') . ':') !!}
                    {!! Form::select('insurance_company_filter',
                         $insurance_companies, null, ['class' => 'form-control select2','style' => 'width:100%','placeholder' => __('lang_v1.all')]) !!}
                </div>
            </div>
           {{--<div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('doc_filter_date_range', __('essentials::lang.insurance_end_date') . ':') !!}
                    {!! Form::text('doc_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
                </div>
            </div> --}}
            
        @endcomponent
        </div>
    </div>


    @component('components.widget',['class' => 'box-primary'])
     
 
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="insurance_companies_contracts_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.company')</th>
                            <th>@lang('essentials::lang.insurance_company')</th>
                            <th>@lang('essentials::lang.insurance_start_date')</th>
                            <th>@lang('essentials::lang.insurance_end_date')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
      
    @endcomponent

    
    <div class="modal fade" id="editinsuranceCompaniesContracts" tabindex="-1" role="dialog">
    </div>
</section>


@endsection

@section('javascript')

<script type="text/javascript">
    $(document).ready(function () {

        
        var insurance_companies_contracts_table;
        var companiesData = @json($companies);
        

        function reloadDataTable() {
            insurance_companies_contracts_table.ajax.reload();
        }
        insurance_companies_contracts_table = $('#insurance_companies_contracts_table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                "url":"{{ route('get_companies_insurance_contracts') }}",

                "data": function(d) {
                        d.insurance_company_filter = $('#insurance_company_filter').val();
                        console.log( d.insurance_company_filter);
                        d.insurance_policy_number_filter = $('#insurance_policy_number_filter').val();
                        if ($('#doc_filter_date_range').val()) {
                            var start = $('#doc_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                            var end = $('#doc_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                    }
            },
            columns: [
                {
                data: 'company_id',
                
                },

                { data: 'insur_id' },

                { data: 'insurance_start_date'},
                { data: 'insurance_end_date'},              
           
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
            , initComplete: function () {
            this.api().columns().every(function () {
                var column = this;
                var input = document.createElement("input");
                $(input).appendTo($(column.footer()).empty())
                    .on('change', function () {
                        column.search($(this).val(), false, false, true).draw();
                    });
            });
        }
        });

        $('#doc_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#doc_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                }
            );
            $('#doc_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#doc_filter_date_range').val('');
                reloadDataTable();
            });
            $('#insurance_company_filter, #insurance_policy_number_filter, #doc_filter_date_range').on('change', function() {
                reloadDataTable();
            });


       

        $(document).on('click', 'button.delete_companies_insurance_contract_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_city,
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
                                insurance_companies_contracts_table.ajax.reload();
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
