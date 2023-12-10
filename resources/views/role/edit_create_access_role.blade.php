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
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
        @endphp
        @component('components.widget', ['class' => 'box-primary'])
            {!! Form::open([
                'url' => action([\App\Http\Controllers\RoleController::class, 'updateAccessRole']),
                'method' => 'post',
                'id' => 'role_add_form',
            ]) !!}
            <input type=hidden name="access_role_id" valu="{{ $accessRole->id }}">
            <div class="row check_group">
                <div class="col-md-3">
                    <h4>@lang('role.access_contact_locations') </h4>
                </div>
                <div class="col-md-12">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
                        </label>
                    </div>
                </div>
                <div class="col-md-9">
                    @foreach ($contacts as $contact)
                        <div class="row check_group">
                            <div class="col-md-5">
                                <h4>{{ $contact->supplier_business_name }}</h4>
                            </div>
                            <div class="col-md-12">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-9">

                                @foreach ($contact->salesProject as $project)
                                    <div class="col-md-12">
                                        <div class="checkbox">
                                            <label>
                                                {!! Form::checkbox('projects[]', $project->id, in_array($project->id, $accessRoleProjects), [
                                                    'class' => 'input-icheck',
                                                ]) !!} {{ $project->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                    @endforeach

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
