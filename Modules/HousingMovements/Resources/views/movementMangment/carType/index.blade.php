@extends('layouts.app')
@section('title', __('housingmovements::lang.carTypes'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.carTypes')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    {!! Form::open([
                        'url' => action('\Modules\HousingMovements\Http\Controllers\CarTypeController@search'),
                        'method' => 'post',
                        'id' => 'carType_search',
                    ]) !!}
                    <div class="col-sm-4">
                        <div class="form-group row">
                            {!! Form::label('search_lable', __('بحث') . '  ') !!}
                            {!! Form::text('search', '', [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('الاسم باللغة العربية او الانكليزية'),
                                'id' => 'search',
                            ]) !!}

                        </div>
                    </div>
                    <div class="col-sm-8" style="padding-right: 3px;">
                        <button class="btn btn-block btn-primary" style="width: max-content;margin-top: 25px;" type="submit">
                            بحث</button>
                        @if ($after_serch)
                            <a class="btn btn-primary pull-right m-5 "
                                href="{{ action('Modules\HousingMovements\Http\Controllers\CarTypeController@index') }}"
                                data-href="{{ action('Modules\HousingMovements\Http\Controllers\CarTypeController@index') }}">
                                عرض الكل</a>
                        @endif
                    </div>
                    {!! Form::close() !!}
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    {{-- @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-block btn-primary"
                                href="{{ action('Modules\HousingMovements\Http\Controllers\CarTypeController@create') }}">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot --}}
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-primary pull-right m-5 btn-modal"
                                href="{{ action('Modules\HousingMovements\Http\Controllers\CarTypeController@create') }}"
                                data-href="{{ action('Modules\HousingMovements\Http\Controllers\CarTypeController@create') }}"
                                data-container="#create_account_modal">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="rooms_table" style="margin-bottom: 100px;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">@lang('housingmovements::lang.name_ar')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.name_en')</th>
                                    <th style="text-align: center;">@lang('messages.action')</th>
                                </tr>
                            </thead>
                            <tbody id="tbody">
                                @foreach ($carTypes as $row)
                                    <tr>
                                        <td style="text-align: center;">
                                            {{ $row->name_ar }}

                                        </td>
                                        <td style="text-align: center;">
                                            {{ $row->name_en }}

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
                                                        href="{{ action('Modules\HousingMovements\Http\Controllers\CarTypeController@edit', $row->id) }}"
                                                        data-href="{{ action('Modules\HousingMovements\Http\Controllers\CarTypeController@edit', $row->id) }}"
                                                        data-container="#edit_car_type_model">

                                                        <i class="fas fa-edit cursor-pointer"
                                                            style="padding: 2px;color:rgb(8, 158, 16);"></i>
                                                        @lang('messages.edit') </a>

                                                    <a class="dropdown-item" style="margin: 2px;" {{-- title="{{ $row->active ? @lang('accounting::lang.active') : @lang('accounting::lang.inactive') }}" --}}
                                                        href="{{ action('Modules\HousingMovements\Http\Controllers\CarTypeController@destroy', $row->id) }}"
                                                        data-href="{{ action('Modules\HousingMovements\Http\Controllers\CarTypeController@destroy', $row->id) }}" {{-- data-target="#active_auto_migration" data-toggle="modal" --}} {{-- id="delete_auto_migration" --}}>

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
                            {{ $carTypes->links() }}
                        </center>
                    </div>


                    <div class="modal fade" id="create_account_modal" tabindex="-1" role="dialog"></div>
                    <div class="modal fade" id="edit_car_type_model" tabindex="-1" role="dialog"></div>
                @endcomponent
            </div>


    </section>
    <!-- /.content -->

@endsection

