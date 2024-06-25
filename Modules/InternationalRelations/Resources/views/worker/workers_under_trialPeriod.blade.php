@extends('layouts.app')
@section('title', __('internationalrelations::lang.workers_under_trialPeriod'))

@section('content')
    @include('internationalrelations::layouts.nav_proposed_labor')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('internationalrelations::lang.workers_under_trialPeriod')
        </h1>

    </section>

    <!-- Main content -->
    <section class="content">

        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    <label for="professions_filter">@lang('essentials::lang.professions'):</label>
                    {!! Form::select('professions-select', $professions, request('professions-select'), [
                        'class' => 'form-control select2', // Add the select2 class
                        'style' => 'height:40px',
                        'placeholder' => __('lang_v1.all'),
                        'id' => 'professions-select',
                    ]) !!}
                </div>
            </div>
            {{-- 
            <div class="col-md-3">
                <div class="form-group">
                    <label for="specializations_filter">@lang('essentials::lang.specializations'):</label>
                    {!! Form::select('specializations-select', $specializations, request('specializations-select'), [
                        'class' => 'form-control select2',
                        'style' => 'height:40px',
                        'placeholder' => __('lang_v1.all'),
                        'id' => 'specializations-select',
                    ]) !!}
                </div>
            </div> --}}
        @endcomponent

        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="employees">
                    <thead>
                        <tr>
                            <th>@lang('internationalrelations::lang.worker_number')</th>
                            <th>@lang('internationalrelations::lang.worker_name')</th>
                            <th>@lang('internationalrelations::lang.agency_name')</th>
                            <th>@lang('essentials::lang.mobile_number')</th>
                            <th>@lang('essentials::lang.contry_nationality')</th>
                            <th>@lang('essentials::lang.profession')</th>
                            {{-- <th>@lang('essentials::lang.specialization')</th> --}}
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent




    </section>
    <!-- /.content -->
@stop
@section('javascript')



    <script type="text/javascript">
        $(document).ready(function() {
            var users_table = $('#employees').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('workers_under_trialPeriod') }}",
                    data: function(d) {
                        // d.specialization = $('#specializations-select').val();
                        d.profession = $('#professions-select').val();




                    },
                },

                "columns": [{
                        "data": "id"
                    },
                    {
                        "data": "full_name"
                    },
                    {
                        "data": "agency_id"
                    },


                    {
                        "data": "contact_number"
                    },
                    {
                        "data": "nationality_id"
                    },

                    {
                        "data": "profession_id",

                    },
                    {
                        "data": "specialization_id",

                    },


                    {
                        "data": "action"
                    }
                ],

            });

            $('#professions-select, #agency_filter').change(
                function() {
                    users_table.ajax.reload();

                });



        });
    </script>



@endsection
