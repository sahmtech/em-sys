@extends('layouts.app')
@section('title', __('role.add_report_access_role'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('role.add_report_access_role')</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @php
            $pos_settings = !empty(session('business.pos_settings'))
                ? json_decode(session('business.pos_settings'), true)
                : [];
        @endphp
        @component('components.widget', ['class' => 'box-primary'])
            {!! Form::open([
                'url' => action(
                    [\App\Http\Controllers\RoleController::class, 'updateAccessRoleReport'],
                    ['roleId' => $accessRole->id],
                ),
                'method' => 'post',
                'id' => 'role_add_form',
            ]) !!}
            <div class="row check_group">
                <div class="col-md-3">
                    <h4>@lang('role.report_access') </h4>
                </div>
                <div class="col-md-12">
                    <div class="checkbox">
                        <label class="custom_permission_lable">
                            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
                        </label>
                    </div>
                </div>
                <div class="col-md-12">
                    <ul>
                        @foreach ($reports as $report)
                            <div class="row check_group">
                                <div class="col-md-12">
                                    <div class="col-md-12">
                                        <div class="checkbox">
                                            <label class="custom_permission_lable">
                                                {!! Form::checkbox('reports[]', $report->id, in_array($report->id, $accessRoleReports), [
                                                    'class' => 'input-icheck',
                                                ]) !!} {{ $report->name }}
                                            </label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="clearfix"></div>


                            </li>
                        @endforeach
                    </ul>


                </div>


            </div>


            <div class="row">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary btn-big">@lang('messages.save')</button>
                </div>
            </div>

            {!! Form::close() !!}
        @endcomponent
    </section>
    <!-- /.content -->
@endsection
