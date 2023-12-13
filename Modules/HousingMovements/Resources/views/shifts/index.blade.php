@extends('layouts.app')
@section('title', __('housingmovements::lang.shifts'))

@section('content')
    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.shifts')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {{-- <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    {!! Form::open([
                        'url' => action('\Modules\HousingMovements\Http\Controllers\ShiftController@search'),
                        'method' => 'post',
                        'id' => 'carType_search',
                    ]) !!}
                    <div class="col-sm-4">
                        <div class="form-group row">
                            {!! Form::label('search_lable', __('housingmovements::lang.search') . '  ') !!}
                            {!! Form::text('search', '', [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('housingmovements::lang.name_in_ar_en'),
                                'id' => 'search',
                            ]) !!}

                        </div>
                    </div>
                    <div class="col-sm-8" style="padding-right: 3px;">
                        <button class="btn btn-block btn-primary" style="width: max-content;margin-top: 25px;" type="submit">
                            @lang('housingmovements::lang.search')</button>
                      
                    </div>
                    {!! Form::close() !!}
                @endcomponent
            </div>
        </div> --}}

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    {{-- @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-block btn-primary"
                                href="{{ action('Modules\HousingMovements\Http\Controllers\ShiftController@create') }}">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot --}}
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-primary pull-right m-5 btn-modal"
                                href="{{ action('Modules\HousingMovements\Http\Controllers\ShiftController@create') }}"
                                data-href="{{ action('Modules\HousingMovements\Http\Controllers\ShiftController@create') }}"
                                data-container="#add_shits_model">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="rooms_table" style="margin-bottom: 100px;">
                            <thead>
                                <tr>
                                <tr>
                                    <th style="text-align: center;">@lang('lang_v1.name')</th>
                                    {{-- <th style="text-align: center;">@lang('essentials::lang.shift_type')</th> --}}
                                    <th style="text-align: center;">@lang('restaurant.start_time')</th>
                                    <th style="text-align: center;">@lang('restaurant.end_time')</th>
                                    <th style="text-align: center;">@lang('essentials::lang.holiday')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.project_name')</th>
                                    <th style="text-align: center;">@lang('messages.action')</th>
                                </tr>

                            </thead>
                            <tbody id="tbody">
                                @foreach ($shifts as $row)
                                    <tr>
                                        <td style="text-align: center;">
                                            {{ $row->name }}

                                        </td>
                                        {{-- <td style="text-align: center;">
                                            {{ $row->type }}

                                        </td> --}}
                                        @php
                                            $dateTime = DateTime::createFromFormat('H:i:s', $row->start_time);
                                            $start_time = $dateTime->format('g:i A');
                                            $dateTime_ = DateTime::createFromFormat('H:i:s', $row->end_time);
                                            $end_time = $dateTime_->format('g:i A');
                                        @endphp
                                        <td style="text-align: center;">
                                            {{ $start_time }}

                                        </td>
                                        <td style="text-align: center;">
                                            {{ $end_time }}

                                        </td>

                                        <td style="display: flex;gap: 5px;">
                                            @foreach ($row->holidays as $holiday)
                                                <h6 style="margin-top: 0px;"><span
                                                        class="badge badge-secondary">{{ __('lang_v1.' . $holiday) }}</span>
                                                </h6>
                                            @endforeach


                                        </td>

                                        <td style="text-align: center;">

                                            {{ $row->project->name }}



                                        </td>




                                        <td style="text-align: center;">
                                            <div class="btn-group" role="group">
                                                <button id="btnGroupDrop1" type="button"
                                                    style="background-color: transparent;
                                                font-size: x-large;
                                                padding: 0px 20px;"
                                                    class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-cog" aria-hidden="true"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item btn-modal" style="margin: 2px;"
                                                        title="@lang('messages.edit')"
                                                        href="{{ action('Modules\HousingMovements\Http\Controllers\ShiftController@edit', $row->id) }}"
                                                        data-href="{{ action('Modules\HousingMovements\Http\Controllers\ShiftController@edit', $row->id) }}"
                                                        data-container="#edit_shits_model">

                                                        <i class="fas fa-edit cursor-pointer"
                                                            style="padding: 2px;color:rgb(8, 158, 16);"></i>
                                                        @lang('messages.edit') </a>

                                                    <a class="dropdown-item" style="margin: 2px;" {{-- title="{{ $row->active ? @lang('accounting::lang.active') : @lang('accounting::lang.inactive') }}" --}}
                                                        href="{{ action('Modules\HousingMovements\Http\Controllers\ShiftController@destroy', $row->id) }}"
                                                        data-href="{{ action('Modules\HousingMovements\Http\Controllers\ShiftController@destroy', $row->id) }}"
                                                        {{-- data-target="#active_auto_migration" data-toggle="modal" --}} {{-- id="delete_auto_migration" --}}>

                                                        <i class="fa fa-trash cursor-pointer"
                                                            style="padding: 2px;color:red;"></i>
                                                        @lang('messages.delete')

                                                    </a>
                                                </div>
                                            </div>




                                        </td>




                                    </tr>
                                @endforeach

                            </tbody>
                        </table>

                        <center class="mt-5">
                            {{ $shifts->links() }}
                        </center>
                    </div>

                    {{-- <div class="modal fade" id="add_shits_model" tabindex="-1" role="dialog"
                        aria-labelledby="gridSystemModalLabel">
                        @include('housingmovements::shifts.create')
                    </div> --}}
                    <div class="modal fade" id="add_shits_model" tabindex="-1" role="dialog"></div>
                    <div class="modal fade" id="edit_shits_model" tabindex="-1" role="dialog"></div>
                @endcomponent
            </div>


    </section>
    <!-- /.content -->

@endsection


@section('javascript')


    <script type="text/javascript">
        $(document).ready(function() {
            $('#holidays').select2();


        });
    </script>
@endsection
