@extends('layouts.app')
@section('title', __('agent.pills.pills'))

@section('content')

    <section class="content-header">
        <h1>@lang('agent.pills.pills')</h1>
    </section>
    <section class="content">


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="bills_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang('agent.pills.invoice_no')</th>
                                    <th>@lang('agent.pills.transaction_date')</th>
                                    <th>@lang('agent.pills.company')</th>
                                    <th>@lang('agent.pills.type')</th>
                                    <th>@lang('agent.pills.status')</th>
                                    <th>@lang('agent.pills.payment_status')</th>
                                    <th>@lang('agent.pills.tax_amount')</th>
                                    <th>@lang('agent.pills.discount_amount')</th>
                                    <th>@lang('agent.pills.final_total')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>

        </div>
    </section>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var bills_table;

            function reloadDataTable() {
                bills_table.ajax.reload();
            }

            bills_table = $('#bills_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('agent_bills') }}",
                },


                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'invoice_no'
                    },
                    {
                        data: 'transaction_date'
                    },
                    {
                        data: 'company'
                    },
                    {
                        data: 'type'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'payment_status'
                    },
                    {
                        data: 'tax_amount'
                    },
                    {
                        data: 'discount_amount'
                    },
                    {
                        data: 'final_total'
                    }
                ]

            });


        });
    </script>

@endsection
