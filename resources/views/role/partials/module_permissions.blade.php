@if (count($module_permissions) > 0)
@php
$module_role_permissions = [];
if (!empty($role_permissions)) {
$module_role_permissions = $role_permissions;
}
@endphp
@foreach ($module_permissions as $value)
<div class="col-md-12">
    <div class="box box-primary">

        <div class="row check_group">
            <div class="col-md-12">
                <div class="col-md-3">
                    <h4>{{ $value['group_name'] }}</h4>
                </div>
                <div class="col-md-12">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
                        </label>
                    </div>
                </div>
                <div class="col-md-12">
                    @foreach ($value['group_permissions'] as $module_permission)
                    @php
                    if (empty($role_permissions) && $module_permission['default']) {
                    $module_role_permissions[] = $module_permission['value'];
                    }
                    @endphp
                    <div class="col-md-4">
                        <div class="checkbox">
                            <label class="custom_permission_lable">
                                @if (!empty($module_permission['is_radio']))
                                {!! Form::radio(
                                'radio_option[' . $module_permission['radio_input_name'] . ']',
                                $module_permission['value'],
                                in_array($module_permission['value'], $module_role_permissions),
                                ['class' => 'input-icheck'],
                                ) !!} {{ $module_permission['label'] }}
                                @else
                                {!! Form::checkbox(
                                'permissions[]',
                                $module_permission['value'],
                                in_array($module_permission['value'], $module_role_permissions),
                                ['class' => 'input-icheck'],
                                ) !!} {{ $module_permission['label'] }}
                                @endif
                            </label>
                        </div>

                        {{-- @if (isset($module_permission['end_group']) && $module_permission['end_group'])
                        <hr>
                        @endif --}}
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
@endif