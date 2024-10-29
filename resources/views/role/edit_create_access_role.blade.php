@extends('layouts.app')
@section('title', __('role.add_access_role'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('role.add_access_role')</h1>
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
                'url' => action([\App\Http\Controllers\RoleController::class, 'updateAccessRole'], ['roleId' => $accessRole->id]),
                'method' => 'post',
                'id' => 'role_add_form',
            ]) !!}
            <div class="row check_group">
                <div class="col-md-3">
                    <h4>@lang('role.access_contact_locations') </h4>
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
                        @foreach ($companies as $company)
                            <div class="col-md-4">
                                <div class="row check_group box box-primary">
                                    <div class="col-md-12">
                                        <div class="checkbox">
                                            <h5>
                                                {{-- {!! Form::checkbox('companies[]', $company->id, in_array($company->id, $accessRoleCompanies), [
                                            'class' => 'input-icheck',
                                        ]) !!} --}}
                                                {{ $company->name }}</h5>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="checkbox">
                                            <label class="custom_permission_lable">
                                                <input type="checkbox" class="check_all input-icheck">
                                                {{ __('role.select_all') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <ul>
                                            @foreach ($userTypes as $uesrType)
                                                <div class="row check_group">
                                                    {{-- <div class="col-md-5">
                                                        <h4>{{ $uesrType }}</h4>
                                                    </div> --}}


                                                    <div class="col-md-12">


                                                        <div class="col-md-12">
                                                            <div class="checkbox">
                                                                <label class="custom_permission_lable">
                                                                    {!! Form::checkbox(
                                                                        'usertypes#' . $company->id . '[]',
                                                                        $uesrType,
                                                                        in_array($uesrType, $selectedUserTypes[$company->id] ?? []),
                                                                        [
                                                                            'class' => 'input-icheck',
                                                                        ],
                                                                    ) !!} {{ $userTypesNames[$uesrType] }}
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

                            </div>
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
