@extends('layouts.app')
@section('title', __('housingmovements::lang.residents_details'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.residents_details')</span>
            - {{ __('housingmovements::lang.room_number') }} {{ $roomWorkersHistory->first()->room?->room_number ?? '' }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">


        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">


                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="workers_table">
                        <tr class="bg-green">
                            <th>
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>{{ __('followup::lang.name') }}</th>
                            <th>{{ __('housingmovements::lang.still_housed') }}</th>
                            <th>{{ __('followup::lang.sponsor') }}</th>
                            <th>{{ __('followup::lang.gender') }}</th>
                            <th>{{ __('followup::lang.nationality') }}</th>
                            <th>{{ __('followup::lang.eqama') }}</th>
                            <th>{{ __('followup::lang.eqama_end_date') }}</th>


                            <th>{{ __('followup::lang.contract_end_date') }}</th>
                            <th>{{ __('followup::lang.passport') }}</th>
                            <th>{{ __('followup::lang.passport_end_date') }}</th>

                            <th>{{ __('followup::lang.salary') }}</th>
                            <th>{{ __('followup::lang.profession') }}</th>


                        </tr>
                        <div style="margin-bottom: 10px;">

                            @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.transfer_from_room'))
                                <button type="button" class="btn btn-warning btn-sm custom-btn" id="transfer-selected">
                                    @lang('housingmovements::lang.transfer_from_room')
                                </button>
                            @endif


                            @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.leave_room'))
                                <button type="button" class="btn btn-primary btn-sm custom-btn" id="leave-selected">
                                    @lang('housingmovements::lang.leave_room')
                                </button>
                            @endif
                        </div>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <input type="checkbox" class="select-row" data-id="{{ $user->id }}">

                                </td>
                                <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                <td>
                                    @php
                                        $stillHoused = $roomWorkersHistory
                                            ->where('worker_id', $user->id)
                                            ->pluck('still_housed')
                                            ->first();
                                    @endphp

                                    @if ($stillHoused)
                                        {{ __('housingmovements::lang.yes') }}
                                    @else
                                        {{ __('housingmovements::lang.no') }}
                                    @endif
                                </td>
                                <td>{{ optional(optional($user->appointment)->location)->name }}</td>
                                <td>
                                    @if ($user->gender == 'male')
                                        {{ __('followup::lang.male') }}
                                    @elseif ($user->gender == 'female')
                                        {{ __('followup::lang.female') }}
                                    @else
                                    @endif
                                </td>
                                <td>{{ optional($user->country)->nationality ?? ' ' }}</td>
                                <td>{{ $user->id_proof_number }}</td>
                                <td>
                                    @foreach ($user->OfficialDocument as $off)
                                        @if ($off->type == 'residence_permit')
                                            {{ $off->expiration_date }}
                                        @endif
                                    @endforeach
                                </td>


                                <td>{{ optional($user->contract)->contract_end_date ?? ' ' }}</td>
                                <td>
                                    @foreach ($user->OfficialDocument as $off)
                                        @if ($off->type == 'passport')
                                            {{ $off->number }}
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    @foreach ($user->OfficialDocument as $off)
                                        @if ($off->type == 'passport')
                                            {{ $off->expiration_date }}
                                        @endif
                                    @endforeach
                                </td>

                                <td>
                                    @if ($user->essentials_salary)
                                        {{ __('followup::lang.basic_salary') }}: {{ floor($user->essentials_salary) }}
                                    @endif
                                    <br>
                                    @if ($user->allowancesAndDeductions->isNotEmpty())
                                        {{ __('followup::lang.allowances') }}:
                                        <ul>
                                            @foreach ($user->UserallowancesAndDeductions as $allowanceOrDeduction)
                                                <li>{{ $allowanceOrDeduction->allowancedescription->description ?? '' }}:
                                                    {{ floor($allowanceOrDeduction->amount) }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td>{{ optional(optional($user->appointment)->profession)->name }}</td>

                            </tr>
                        @endforeach
                    </table>

                </div>

            </div>
            <div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open([
                            'url' => action([\Modules\HousingMovements\Http\Controllers\RoomController::class, 'transfer_from_room']),
                            'method' => 'post',
                            'id' => 'transfer_form',
                        ]) !!}

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('housingmovements::lang.transfer_from_room')</h4>
                        </div>

                        <div class="modal-body">

                            <input type="hidden" name="selectedRowsData" id="selectedRowsData" />
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('building', __('housingmovements::lang.building') . ':*') !!}
                                    {!! Form::select('building', $buildings, null, [
                                        'class' => 'form-control select2',
                                        'required',
                                        'style' => 'width:100%;padding:2px;',
                                        'placeholder' => __('housingmovements::lang.select_building'),
                                        'id' => 'buildingSelector',
                                    ]) !!}

                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('room', __('housingmovements::lang.room') . ':') !!}
                                {!! Form::select('room', $availableRooms, null, [
                                    'class' => 'form-control select2',
                                    'required',
                                    'placeholder' => __('housingmovements::lang.room'),
                                    'id' => 'roomSelector',
                                ]) !!}

                                <span id="bedCountMessage" class="text-info"></span>

                            </div>


                            <div class="form-group col-md-12">
                                {!! Form::label('notes', __('housingmovements::lang.notes') . ':') !!}
                                {!! Form::textarea('notes', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('housingmovements::lang.notes'),
                                    'rows' => 2,
                                ]) !!}
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="submitsBtn">@lang('messages.save')</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                        </div>

                        {!! Form::close() !!}
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        @endcomponent


    @endsection
    @section('javascript')
        <script>
            $(document).ready(function() {

                $('#select-all').change(function() {
                    $('.select-row').prop('checked', $(this).prop('checked'));
                });


                $('#workers_table').on('change', '.select-row', function() {

                    var totalRows = $('.select-row').length;
                    var checkedRows = $('.select-row:checked').length;
                    $('#select-all').prop('checked', totalRows === checkedRows);
                });

                $('#leave-selected').click(function() {
                    var selectedRows = $('.select-row:checked').map(function() {
                        return {
                            id: $(this).data('id'),
                        };
                    }).get();

                    var url = '/housingmovements/leaveRoom';

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            selectedRows: selectedRows
                        },
                        success: function(result) {
                            console.log(result);
                            if (result.success == true) {
                                toastr.success(result.msg);
                                window.location.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },

                    });
                });


                $('#transfer-selected').click(function() {
                    var selectedRows = $('.select-row:checked').map(function() {
                        return {
                            id: $(this).data('id'),
                        };
                    }).get();

                    $('#selectedRowsData').val(JSON.stringify(selectedRows));
                    $('#changeStatusModal').modal('show');
                });

                $('#submitsBtn').click(function(e) {
                    e.preventDefault(); 
                    var selectedOption = $('#roomSelector option:selected');
                    var beds_count = selectedOption.data('beds_count');

                    var selectedRows = $('.select-row:checked').length;
                    var messages = {
                        bed_count_exceeded: "{{ __('messages.bed_count_exceeded') }}",
                    };

                  
                

                    if (selectedRows > beds_count) {
                        alert(messages.bed_count_exceeded);
                        return; 
                    } else {
                        var formData = new FormData($('#transfer_form')[0]); 

                        $.ajax({
                            type: 'POST',
                            url: $('#transfer_form').attr(
                            'action'), 
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(result) {
                                console.log(result);
                                console.log(result);
                                if (result.success === true) {
      
                                    toastr.success(result.msg);

                                    window.location.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                            error: function(error) {

                            }
                        });

                        $('#changeStatusModal').modal('hide');
                    }
                });


                $('#buildingSelector').change(function() {
                    var buildingId = $(this).val();
                    if (buildingId) {
                        $.ajax({
                            url: '/housingmovements/getRooms/' + buildingId,
                            type: "GET",
                            dataType: "json",
                            success: function(data) {
                                $('#roomSelector').empty();
                                $('#roomSelector').append(
                                    '<option selected disabled>Select room</option>');
                                $.each(data, function(id, room) {
                                    $('#roomSelector').append('<option value="' + id +
                                        '" data-beds_count="' + room.beds_count + '">' +
                                        room.name + '</option>');
                                });
                            }
                        });
                    } else {
                        $('#roomSelector').empty();
                        $('#bedCountMessage').text('');
                        $('#errorMessage').text('');
                    }
                });

                $('#roomSelector').change(function() {
                    var selectedOption = $(this).find('option:selected');
                    var beds_count = selectedOption.data('beds_count');
                    $('#bedCountMessage').text("Selected room has " + beds_count + " beds.");
                 
                });

                $('#changeStatusModal').on('shown.bs.modal', function(e) {
                $('#roomSelector').select2({
                    dropdownParent: $(
                        '#changeStatusModal'),
                    width: '100%',
                });
            });
            });
        </script>

    @endsection
