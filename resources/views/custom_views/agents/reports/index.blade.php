@extends('layouts.app')
@section('title', __('operationsmanagmentgovernment::lang.reports'))

@section('content')
    <section class="content-header">
        <h1>@lang('operationsmanagmentgovernment::lang.reports')</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="form-group col-md-3">
                        {!! Form::label('type_filter', __('operationsmanagmentgovernment::lang.type')) !!}
                        {!! Form::select('type_filter', $reportTypes, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('lang_v1.all'),
                            'id' => 'type_filterSelect',
                        ]) !!}
                    </div>
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="reports_table">
                            <thead>
                                <tr>
                                    <th>@lang('operationsmanagmentgovernment::lang.date')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.time')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.type')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.contact')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.created_by')</th>
                                    <th>@lang('lang_v1.attachments')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>
        <!-- Add Report Modal -->
        <div class="modal fade" id="addReportModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document"> <!-- Larger Modal -->
                <div class="modal-content">
                    {!! Form::open([
                        'route' => 'operationsmanagmentgovernment.reports.store',
                        'id' => 'addReportForm',
                        'files' => true,
                        'method' => 'POST',
                    ]) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                        <h4 class="modal-title">@lang('messages.add')</h4>
                    </div>

                    <div class="modal-body">

                        <div class="row">

                            <!-- First Row: Type, Date, Time -->
                            <div class="form-group col-md-4">
                                {!! Form::label('type', __('operationsmanagmentgovernment::lang.type') . ':*') !!}
                                {!! Form::select('type', $reportTypes, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width: 100%;',
                                    'placeholder' => __('messages.select'),
                                    'required',
                                    'id' => 'report_type_select',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-4">
                                {!! Form::label('date', __('operationsmanagmentgovernment::lang.date') . ':*') !!}
                                {!! Form::date('date', null, ['class' => 'form-control', 'required']) !!}
                            </div>

                            <div class="form-group col-md-4">
                                {!! Form::label('time', __('operationsmanagmentgovernment::lang.time') . ':*') !!}
                                {!! Form::time('time', null, ['class' => 'form-control', 'required']) !!}
                            </div>
                        </div>

                        <!-- Type-Specific Fields -->
                        <div class="row report-fields-container">

                            <!-- Penalty Report Fields -->
                            <div class="penalty-fields report-fields">
                                <div class="form-group col-md-4">
                                    {!! Form::label('full_name', __('operationsmanagmentgovernment::lang.full_name') . ':') !!}
                                    {!! Form::text('penalty_full_name', null, ['class' => 'form-control', 'id' => 'penalty_full_name']) !!}
                                </div>

                                <div class="form-group col-md-4">
                                    {!! Form::label('national_id', __('operationsmanagmentgovernment::lang.national_id') . ':') !!}
                                    {!! Form::text('penalty_national_id', null, ['class' => 'form-control', 'id' => 'penalty_national_id']) !!}
                                </div>

                                <div class="form-group col-md-4">
                                    {!! Form::label('phone_number', __('operationsmanagmentgovernment::lang.phone_number') . ':') !!}
                                    {!! Form::text('penalty_phone_number', null, ['class' => 'form-control', 'id' => 'penalty_phone_number']) !!}
                                </div>

                                <div class="form-group col-md-4">
                                    {!! Form::label('penalty_type', __('operationsmanagmentgovernment::lang.penalty_type') . ':') !!}
                                    {!! Form::text('penalty_type', null, ['class' => 'form-control', 'id' => 'penalty_type']) !!}
                                </div>
                                <div class="form-group col-md-12">
                                    <div class="col-md-1">
                                    </div>
                                    <div class="col-md-10">
                                        <p class="alert text-center" style="font-size: 20px">
                                            @lang('operationsmanagmentgovernment::lang.penalty_declaration')
                                        </p>
                                    </div>
                                    <div class="col-md-1">
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    {!! Form::label('violation_note', __('operationsmanagmentgovernment::lang.violation_note') . ':') !!}
                                    {!! Form::textarea('penalty_violation_note', null, [
                                        'class' => 'form-control',
                                        'rows' => 2,
                                        'id' => 'penalty_violation_note',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('security_supervisor', __('operationsmanagmentgovernment::lang.security_supervisor') . ':') !!}
                                    {!! Form::text('penalty_security_supervisor', null, [
                                        'class' => 'form-control',
                                        'id' => 'penalty_security_supervisor',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label(
                                        'contact_supervisor_for_marien_front',
                                        __('operationsmanagmentgovernment::lang.contact_supervisor_for_marien_front') . ':',
                                    ) !!}
                                    {!! Form::text('penalty_contact_supervisor_for_marien_front', null, [
                                        'class' => 'form-control',
                                        'id' => 'penalty_contact_supervisor_for_marien_front',
                                    ]) !!}
                                </div>
                            </div>

                            <!-- Lost Items Report Fields -->
                            <div class="lost_items-fields report-fields">
                                <div class="form-group col-md-6">
                                    {!! Form::label('receiving_entity_name', __('operationsmanagmentgovernment::lang.receiving_entity_name') . ':') !!}
                                    {!! Form::text('lost_items_receiving_entity_name', null, [
                                        'class' => 'form-control',
                                        'id' => 'lost_items_receiving_entity_name',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('recipient_name', __('operationsmanagmentgovernment::lang.recipient_name') . ':') !!}
                                    {!! Form::text('lost_items_recipient_name', null, [
                                        'class' => 'form-control',
                                        'id' => 'lost_items_recipient_name',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-12">
                                    <button type="button" class="btn btn-success btn-sm" id="addItemRow">Add
                                        @lang('operationsmanagmentgovernment::lang.add_item')</button>
                                </div>

                                <div class="form-group col-md-12" id="itemRows">
                                    <div class="row itemRow">
                                        <div class="form-group col-md-5">
                                            {!! Form::label('item_name[]', __('operationsmanagmentgovernment::lang.item_name') . ':') !!}
                                            {!! Form::text('lost_items_item_name[]', null, ['class' => 'form-control', 'id' => 'lost_items_item_name']) !!}
                                        </div>
                                        <div class="form-group col-md-5">
                                            {!! Form::label('item_contents[]', __('operationsmanagmentgovernment::lang.item_contents') . ':') !!}
                                            {!! Form::text('lost_items_item_contents[]', null, [
                                                'class' => 'form-control',
                                                'id' => 'lost_items_item_contents',
                                            ]) !!}
                                        </div>
                                        <div class="form-group col-md-2" style="padding-top:27px">
                                            <button type="button" class="btn btn-danger btn-sm removeItemRow" disabled>
                                                @lang('operationsmanagmentgovernment::lang.remove_item')</button></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    {!! Form::label('violation_note', __('operationsmanagmentgovernment::lang.notes') . ':') !!}
                                    {!! Form::textarea('lost_items_violation_note', null, [
                                        'class' => 'form-control',
                                        'rows' => 2,
                                        'id' => 'lost_items_violation_note',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('supervisor_name', __('operationsmanagmentgovernment::lang.supervisor_name') . ':') !!}
                                    {!! Form::text('lost_items_supervisor_name', null, [
                                        'class' => 'form-control',
                                        'id' => 'lost_items_supervisor_name',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('ref_number', __('operationsmanagmentgovernment::lang.ref_number') . ':') !!}
                                    {!! Form::text('lost_items_ref_number', null, ['class' => 'form-control', 'id' => 'lost_items_ref_number']) !!}
                                </div>
                            </div>

                            <!-- Subscriber Status Report Fields -->
                            <div class="subscriber_status-fields report-fields">
                                <div class="form-group col-md-4">
                                    {!! Form::label('status_type', __('operationsmanagmentgovernment::lang.status_type') . ':') !!}
                                    {!! Form::text('subscriber_status_type', null, ['class' => 'form-control', 'id' => 'subscriber_status_type']) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('status_location', __('operationsmanagmentgovernment::lang.status_location') . ':') !!}
                                    {!! Form::text('subscriber_status_location', null, [
                                        'class' => 'form-control',
                                        'id' => 'subscriber_status_location',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('full_name', __('operationsmanagmentgovernment::lang.full_name') . ':') !!}
                                    {!! Form::text('subscriber_full_name', null, ['class' => 'form-control', 'id' => 'subscriber_full_name']) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('company', __('operationsmanagmentgovernment::lang.company') . ':') !!}
                                    {!! Form::text('subscriber_company', null, ['class' => 'form-control', 'id' => 'subscriber_company']) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('national_id', __('operationsmanagmentgovernment::lang.national_id') . ':') !!}
                                    {!! Form::text('subscriber_national_id', null, ['class' => 'form-control', 'id' => 'subscriber_national_id']) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label(
                                        'commercial_register_number',
                                        __('operationsmanagmentgovernment::lang.commercial_register_number') . ':',
                                    ) !!}
                                    {!! Form::text('subscriber_commercial_register_number', null, [
                                        'class' => 'form-control',
                                        'id' => 'subscriber_commercial_register_number',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('phone_number', __('operationsmanagmentgovernment::lang.phone_number') . ':') !!}
                                    {!! Form::text('subscriber_phone_number', null, ['class' => 'form-control', 'id' => 'subscriber_phone_number']) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('plate_number', __('operationsmanagmentgovernment::lang.plate_number') . ':') !!}
                                    {!! Form::text('subscriber_plate_number', null, ['class' => 'form-control', 'id' => 'subscriber_plate_number']) !!}
                                </div>
                                <div class="form-group col-md-12">
                                    {!! Form::label('status_details', __('operationsmanagmentgovernment::lang.status_details') . ':') !!}
                                    {!! Form::textarea('subscriber_status_details', null, [
                                        'class' => 'form-control',
                                        'rows' => 2,
                                        'id' => 'subscriber_status_details',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('security_supervisor', __('operationsmanagmentgovernment::lang.security_supervisor') . ':') !!}
                                    {!! Form::text('subscriber_security_supervisor', null, [
                                        'class' => 'form-control',
                                        'id' => 'subscriber_security_supervisor',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('rotana_supervisor', __('operationsmanagmentgovernment::lang.rotana_supervisor') . ':') !!}
                                    {!! Form::text('subscriber_rotana_supervisor', null, [
                                        'class' => 'form-control',
                                        'id' => 'subscriber_rotana_supervisor',
                                    ]) !!}
                                </div>
                            </div>

                            <!-- Photo Consents Report Fields -->
                            <div class="photo_consents-fields report-fields">
                                <div class="form-group col-md-12">
                                    <div class="col-md-12">
                                        <div class="col-md-1">
                                        </div>
                                        <div class="col-md-10">
                                            <p class="alert text-center" style="font-size: 20px">
                                                @lang('operationsmanagmentgovernment::lang.photo_consent_declaration_1')
                                            </p>
                                        </div>
                                        <div class="col-md-1">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="col-md-1">
                                        </div>
                                        <div class="col-md-10">
                                            <p class="alert text-center" style="font-size: 20px">
                                                @lang('operationsmanagmentgovernment::lang.photo_consent_declaration_2')
                                            </p>
                                        </div>
                                        <div class="col-md-1">
                                        </div>

                                    </div>
                                    <div class="col-md-12">
                                        <div class="col-md-1">
                                        </div>
                                        <div class="col-md-10">
                                            <p class="alert text-center" style="font-size: 20px">
                                                @lang('operationsmanagmentgovernment::lang.photo_consent_declaration_3')
                                            </p>
                                        </div>
                                        <div class="col-md-1">
                                        </div>
                                    </div>





                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('full_name', __('operationsmanagmentgovernment::lang.full_name') . ':') !!}
                                    {!! Form::text('photo_full_name', null, ['class' => 'form-control', 'id' => 'photo_full_name']) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('national_eqama_id', __('operationsmanagmentgovernment::lang.national_eqama_id') . ':') !!}
                                    {!! Form::text('photo_national_eqama_id', null, ['class' => 'form-control', 'id' => 'photo_national_eqama_id']) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('phone_number', __('operationsmanagmentgovernment::lang.phone_number') . ':') !!}
                                    {!! Form::text('photo_phone_number', null, ['class' => 'form-control', 'id' => 'photo_phone_number']) !!}
                                </div>
                            </div>

                            <!-- Incident Report Fields -->
                            <div class="incident-fields report-fields">
                                <div class="form-group col-md-4">
                                    {!! Form::label('gathering_supervisor', __('operationsmanagmentgovernment::lang.gathering_supervisor') . ':') !!}
                                    {!! Form::text('incident_gathering_supervisor', null, [
                                        'class' => 'form-control',
                                        'id' => 'incident_gathering_supervisor',
                                    ]) !!}
                                </div>
                                <div class="clearfix">

                                </div>
                                <div class="form-group col-md-12">
                                    {!! Form::label(
                                        'incident_rotion_damage_types',
                                        __('operationsmanagmentgovernment::lang.rotion_damage_types') . ':',
                                    ) !!}

                                    {!! Form::select(
                                        'incident_rotion_damage_types[]',
                                        [
                                            'مصدات ماكرو أو مكعبة' => __('operationsmanagmentgovernment::lang.macro_cubic_bumpers'),
                                            'رصيف' => __('operationsmanagmentgovernment::lang.sidewalk'),
                                            'نخلة' => __('operationsmanagmentgovernment::lang.palm_tree'),
                                            'شجرة' => __('operationsmanagmentgovernment::lang.tree'),
                                            'عامود إنارة' => __('operationsmanagmentgovernment::lang.light_pole'),
                                            'إشارة مرور' => __('operationsmanagmentgovernment::lang.traffic_signal'),
                                            'أخرى' => __('operationsmanagmentgovernment::lang.other'),
                                        ],
                                        null,
                                        [
                                            'class' => 'form-control select2',
                                            'style' => 'width: 100%;',
                                            'multiple' => 'multiple',
                                            'id' => 'rotion_damage_types_select',
                                        ],
                                    ) !!}

                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('incident_location', __('operationsmanagmentgovernment::lang.incident_location') . ':') !!}
                                    {!! Form::text('incident_incident_location', null, [
                                        'class' => 'form-control',
                                        'id' => 'incident_incident_location',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('squar', __('operationsmanagmentgovernment::lang.squar') . ':') !!}
                                    {!! Form::text('incident_squar', null, ['class' => 'form-control', 'id' => 'incident_squar']) !!}
                                </div>
                                <div class="clearfix">
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('full_name', __('operationsmanagmentgovernment::lang.full_name') . ':') !!}
                                    {!! Form::text('incident_full_name', null, ['class' => 'form-control', 'id' => 'incident_full_name']) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('national_id', __('operationsmanagmentgovernment::lang.national_id') . ':') !!}
                                    {!! Form::text('incident_national_id', null, ['class' => 'form-control', 'id' => 'incident_national_id']) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('phone_number', __('operationsmanagmentgovernment::lang.phone_number') . ':') !!}
                                    {!! Form::text('incident_phone_number', null, ['class' => 'form-control', 'id' => 'incident_phone_number']) !!}
                                </div>

                                <div class="form-group col-md-4">
                                    {!! Form::label('insurance_company', __('operationsmanagmentgovernment::lang.insurance_company') . ':') !!}
                                    {!! Form::text('incident_insurance_company', null, [
                                        'class' => 'form-control',
                                        'id' => 'incident_insurance_company',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label(
                                        'insurance_policy_number',
                                        __('operationsmanagmentgovernment::lang.insurance_policy_number') . ':',
                                    ) !!}
                                    {!! Form::text('incident_insurance_policy_number', null, [
                                        'class' => 'form-control',
                                        'id' => 'incident_insurance_policy_number',
                                    ]) !!}
                                </div>

                                <div class="clearfix">
                                </div>

                                <div class="form-group col-md-4">
                                    {!! Form::label('car_plate_number', __('operationsmanagmentgovernment::lang.car_plate_number') . ':') !!}
                                    {!! Form::text('incident_car_plate_number', null, [
                                        'class' => 'form-control',
                                        'id' => 'incident_car_plate_number',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('car_model', __('operationsmanagmentgovernment::lang.car_model') . ':') !!}
                                    {!! Form::text('incident_car_model', null, ['class' => 'form-control', 'id' => 'incident_car_model']) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('car_year', __('operationsmanagmentgovernment::lang.car_year') . ':') !!}
                                    {!! Form::text('incident_car_year', null, ['class' => 'form-control', 'id' => 'incident_car_year']) !!}
                                </div>
                                <div class="clearfix">
                                </div>


                                <div class="form-group col-md-4">
                                    {!! Form::label('damage_quantity', __('operationsmanagmentgovernment::lang.damage_quantity') . ':') !!}
                                    {!! Form::text('incident_damage_quantity', null, [
                                        'class' => 'form-control',
                                        'id' => 'incident_damage_quantity',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    <div class="checkbox" style="padding-top:20px">
                                        <label>
                                            {!! Form::checkbox('incident_full_damage', '1', false, ['required' => 'required']) !!}
                                            @lang('operationsmanagmentgovernment::lang.full_damage')
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                    <div class="checkbox" style="padding-top:20px">
                                        <label>
                                            {!! Form::checkbox('incident_partial_damage', '1', false, ['required' => 'required']) !!}
                                            @lang('operationsmanagmentgovernment::lang.partial_damage')
                                        </label>
                                    </div>
                                </div>

                                <div class="clearfix">
                                </div>
                                <div class="form-group col-md-12">
                                    {!! Form::label('notes', __('operationsmanagmentgovernment::lang.notes') . ':') !!}
                                    {!! Form::textarea('incident_notes', null, ['class' => 'form-control', 'rows' => 2, 'id' => 'incident_notes']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('security_supervisor', __('operationsmanagmentgovernment::lang.security_supervisor') . ':') !!}
                                    {!! Form::text('incident_security_supervisor', null, [
                                        'class' => 'form-control',
                                        'id' => 'incident_security_supervisor',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('star_supervisor', __('operationsmanagmentgovernment::lang.star_supervisor') . ':') !!}
                                    {!! Form::text('incident_star_supervisor', null, [
                                        'class' => 'form-control',
                                        'id' => 'incident_star_supervisor',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Last Row: Attachment -->
                    <div class="form-group col-md-12" id="existing_file_container" style="display: none;">
                        <a href="#" target="_blank" id="existing_file_link" class="btn btn-info btn-sm">
                            <i class="fa fa-file"></i> {{ __('home.view_attach') }}
                        </a>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>





    </section>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {


            var reports_table = $('#reports_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('agent_reports') }}',
                    data: function(d) {
                        d.type = $('#type_filterSelect').val();
                    }
                },
                columns: [{
                        data: 'date'
                    },
                    {
                        data: 'time'
                    },
                    {
                        data: 'type'
                    },
                    {
                        data: 'contact'
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'file',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#type_filterSelect').on('change', function() {
                reports_table.ajax.reload();
            });




            $(document).on('click', '.open-view-modal', function() {
                var reportId = $(this).data('id');

                $.ajax({
                    url: '/agent/agent_reports/view/' + reportId,
                    type: 'GET',
                    success: function(data) {
                        $('#addReportModal').modal('show');
                        $('#addReportModal .modal-title').text("{{ __('messages.view') }}");
                        $('#addReportModal button[type=submit]').hide();

                        $('#report_type_select').val(data.type).trigger('change').prop(
                            'disabled', true);
                        $('#date').val(data.date).prop(
                            'disabled', true);
                        $('#time').val(data.time).prop(
                            'disabled', true);

                        $('.report-fields').hide();
                        $('.report-fields input, .report-fields select, .report-fields textarea')
                            .prop('required', false)
                            .prop('disabled', true)
                            .val('');
                        if (data.file_path) {
                            var fileUrl = '{{ asset('uploads') }}/' + data.file_path;

                            $('#existing_file_link').attr('href', fileUrl);
                            $('#existing_file_container').show();
                        } else {
                            $('#existing_file_link').attr('href', '#');
                            $('#existing_file_container').hide();
                        }
                        if (data.type) {
                            $('.' + data.type + '-fields').show();
                            setTimeout(function() { // Ensures fields are visible before setting values
                                populateFields(data);
                            }, 200);
                        }
                    }
                });
            });

            function populateFields(data) {

                if (data.type == 'penalty') {
                    $('#penalty_full_name').val(data.penalty.full_name);
                    $('#penalty_national_id').val(data.penalty.national_id);
                    $('#penalty_phone_number').val(data.penalty.phone_number);
                    $('#penalty_type').val(data.penalty.penalty_type);
                    $('#penalty_violation_note').val(data.penalty.violation_note);
                    $('#penalty_security_supervisor').val(data.penalty.security_supervisor);
                    $('#penalty_contact_supervisor_for_marien_front').val(data.penalty.contact_supervisor);
                }
                if (data.type == 'lost_items') {
                    console.log(data.lost_items);

                    $('#lost_items_receiving_entity_name').val(data.lost_items.receiving_entity_name);
                    $('#lost_items_recipient_name').val(data.lost_items.recipient_name);
                    $('#lost_items_violation_note').val(data.lost_items.notes);
                    $('#lost_items_supervisor_name').val(data.lost_items.supervisor);
                    $('#lost_items_ref_number').val(data.lost_items.ref_number);
                    $('#addItemRow').hide();

                    if (data.lost_items.items) {
                        data.lost_items.items.forEach(function(item) {
                            var newRow = $('.itemRow:first').clone();
                            newRow.find('input[name="lost_items_item_name[]"]').val(item.item_name);
                            newRow.find('input[name="lost_items_item_contents[]"]').val(item
                                .item_contents);
                            newRow.find('.removeItemRow').hide();
                            $('#itemRows').append(newRow);
                        });
                        $('.itemRow:first').hide();


                    }
                }
                if (data.type == 'subscriber_status') {

                    $('#subscriber_status_type').val(data.subscriber_status.type);
                    $('#subscriber_status_location').val(data.subscriber_status.location);
                    $('#subscriber_full_name').val(data.subscriber_status.name);
                    $('#subscriber_company').val(data.subscriber_status.company);
                    $('#subscriber_national_id').val(data.subscriber_status.national_id);
                    $('#subscriber_phone_number').val(data.subscriber_status.phone_number);
                    $('#subscriber_plate_number').val(data.subscriber_status.plate_number);
                    $('#subscriber_status_details').val(data.subscriber_status.status_details);
                    $('#subscriber_commercial_register_number').val(data.subscriber_status
                        .commercial_register_number);
                    $('#subscriber_security_supervisor').val(data.subscriber_status.security_supervisor);
                    $('#subscriber_rotana_supervisor').val(data.subscriber_status.contact_supervisor);
                }
                if (data.type == 'photo_consents') {

                    $('#photo_full_name').val(data.photo_consents.name);
                    $('#photo_national_eqama_id').val(data.photo_consents.national_id);
                    $('#photo_phone_number').val(data.photo_consents.phone_number);
                }
                if (data.type == 'incident') {

                    $('#incident_gathering_supervisor').val(data.incident.supervisor_name);
                    $('#incident_incident_location').val(data.incident.location);
                    $('#incident_squar').val(data.incident.squar);
                    $('#incident_full_name').val(data.incident.full_name);
                    $('#incident_national_id').val(data.incident.national_id);
                    $('#incident_phone_number').val(data.incident.phone_number);
                    $('#incident_insurance_company').val(data.incident.insurance_company);
                    $('#incident_insurance_policy_number').val(data.incident.insurance_policy_number);
                    $('#incident_car_plate_number').val(data.incident.plate_number);
                    $('#incident_car_model').val(data.incident.car_model);
                    $('#incident_car_year').val(data.incident.car_year);
                    $('#incident_notes').val(data.incident.notes);
                    $('#incident_damage_quantity').val(data.incident.damage_quantity);
                    $('#incident_full_damage').prop('checked', data.incident.full_damage);
                    $('#incident_partial_damage').prop('checked', data.incident.partial_damage);
                    $('#incident_security_supervisor').val(data.incident.security_supervisor);
                    $('#incident_star_supervisor').val(data.incident.contact_supervisor);
                }

            }

            $('#addReportModal').on('hide.bs.modal', function() {
                $('#addReportModal .modal-title').text("{{ __('messages.add') }}");
                $('#addReportModal button[type=submit]').show();
                $('input, select, textarea').prop('disabled', false).val('');
                $('#existing_file_link').attr('href', '#');
                $('#existing_file_container').hide();
            });


            function toggleFields() {
                var selectedType = $('#report_type_select').val();
                $('.report-fields').hide();
                $('.report-fields input, .report-fields select, .report-fields textarea').prop('required', false);
                if (selectedType) {
                    $('.' + selectedType + '-fields').show();
                    $('.' + selectedType + '-fields input, .' + selectedType + '-fields select, .' + selectedType +
                        '-fields textarea').prop('required', true);

                }
            }

            $('#rotion_damage_types_select').on('change', function() {
                var selectedOptions = $(this).find('option:selected').map(function() {
                    return $(this).text(); // Get the text of the selected options
                }).get();

                var selectedValues = $(this).val(); // Get the selected values

                console.log("Selected Options (Text):", selectedOptions);
                console.log("Selected Values (Value Attribute):", selectedValues);

            });
            $('#report_type_select').on('change', function() {
                toggleFields();
            });

            $('#addReportModal').on('show.bs.modal', function() {
                $('#report_type_select').val('').trigger('change');
                toggleFields();
            });

            $(document).on('click', '#addItemRow', function() {
                var newRow = $('.itemRow:first').clone();
                newRow.find('input').val('');
                newRow.find('.removeItemRow').prop('disabled', false);
                $('#itemRows').append(newRow);
            });

            $(document).on('click', '.removeItemRow', function() {
                if ($('.itemRow').length > 1) {
                    $(this).closest('.itemRow').remove();
                }
            });
        });
    </script>
@endsection
