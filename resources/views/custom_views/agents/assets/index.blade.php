@extends('layouts.app')
@section('title', __('operationsmanagmentgovernment::lang.asset_assessment'))

@section('content')
    <section class="content-header">
        <h1>@lang('operationsmanagmentgovernment::lang.asset_assessment')</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="asset_assessment_table">
                            <thead>
                                <tr>
                                    <th>@lang('operationsmanagmentgovernment::lang.project')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.zone')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.asset')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.quantity')</th>
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
            $('.select2').select2({
                width: '100%'
            });

            var asset_assessment_table = $('#asset_assessment_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('operationsmanagmentgovernment.asset_assessment') }}",
                columns: [{
                        data: 'project'
                    }, {
                        data: 'zone'
                    },
                    {
                        data: 'asset'
                    },
                    {
                        data: 'quantity'
                    },
                ]
            });
        });
    </script>
@endsection
