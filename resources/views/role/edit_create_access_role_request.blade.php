@extends('layouts.app')
@section('title', __('role.add_request_access_role'))
@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('role.add_request_access_role')</h1>
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
                    [\App\Http\Controllers\RoleController::class, 'updateAccessRoleRequest'],
                    ['roleId' => $accessRole->id],
                ),
                'method' => 'post',
                'id' => 'role_add_form',
            ]) !!}
            <div class="row check_group">
                <div class="col-md-12">
                    <h4>@lang('role.request_access') </h4>
                </div>
                <div class="col-md-12">
                    <div class="checkbox">
                        <label class="custom_permission_lable">
                            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <ul>
                        @php
                            $grouped_requests = $requests->groupBy('type');
                        @endphp
                        @foreach ($grouped_requests as $type => $type_requests)
                            <div class="box box-primary">
                                <div class="row check_group">
                                    <div class="col-md-12">
                                        <div class="checkbox">
                                            <label class="custom_permission_lable">
                                                {{ __('request.' . $type) }}
                                            </label>
                                        </div>
                                    </div>

                                    @foreach ($type_requests as $request)
                                        <div class="col-md-4">
                                            <div class="checkbox">
                                                <label class="custom_permission_lable">
                                                    {!! Form::checkbox('requests[]', $request->id, in_array($request->id, $accessRoleRequests), [
                                                        'class' => 'input-icheck',
                                                    ]) !!} {{ __('request.' . $request->for) }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>
                            </div>
                            <div class="clearfix"></div>
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
