<div class="modal-dialog modal-lg" id="edit_violations" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-plus"></i> @lang('essentials::lang.penalties_action_edit')</h4>
        </div>

        {!! Form::open(['route' => 'update-penalties', 'enctype' => 'multipart/form-data']) !!}

        <div class="modal-body">

            <div class="row">

                <input type="hidden" value="{{ $Penalties->id }}" name="id" />
                <div class="form-group col-md-6">
                    {!! Form::label('violations', __('essentials::lang.penalties_user') . ' *') !!}
                    <select class="form-control select-2" required="" id="users" name="user_id"
                        style="padding: 2px 10px;">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @if ($Penalties->user_id == $user->id) selected @endif>
                                {{ $user->first_name . ' ' . $user->last_name . ' - ' . $user->id_proof_number }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('occurrence', __('essentials::lang.penalties_action') . ' *') !!}
                    <select class="form-control select-2" required="" id="violation_penalties"
                        name="violation_penalties_id" style="padding: 2px 10px;">
                        @foreach ($ViolationPenalties as $penalties)
                            <option value="{{ $penalties->id }}" data-amount_type="{{ $penalties->amount_type }}">
                                {{ $penalties->violation->description . ' - ' . $penalties->descrption . ' - ' }}
                                @lang('essentials::lang.' . $penalties->occurrence) - @lang('essentials::lang.' . $penalties->amount_type) @if ($penalties->amount > 0)
                                    - {{ $penalties->amount }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>


                <div class="form-group col-md-6">
                    {!! Form::label('application_date', __('essentials::lang.application_date') . ' *') !!}
                    {!! Form::month('application_date_month', Carbon::parse($Penalties->application_date)->format('Y-m'), [
                        'class' => 'form-control',
                        'id' => 'application_date_month',
                        'required' => true,
                    ]) !!}
                </div>
              

                {!! Form::hidden('application_date', null, ['id' => 'application_date']) !!}


                <div class="form-group col-md-6" id="violation_file" style="display:none;">
                    {!! Form::label('file', __('essentials::lang.file') . ' *') !!}
                    {!! Form::file('violation_file', [
                        'class' => 'form-control',
                        // 'id' => 'violation_file',
                        'style' => '',
                    ]) !!}
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div> <!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script>
    $(document).ready(function() {

        $('#edit_violations').on('shown.bs.modal', function(e) {
            $('#worker__select').select2({
                dropdownParent: $(
                    '#users'),
                width: '100%',
            });

            $('#car__id').select2({
                dropdownParent: $(
                    '#violation_penalties'),
                width: '100%',
            });
        });


        document.getElementById('application_date_month').addEventListener('change', function() {
            var selectedMonth = this.value;
            var year = selectedMonth.split('-')[0];
            var month = selectedMonth.split('-')[1];


            var lastDayOfMonth = new Date(year, month, 0).getDate();


            var fullDate = year + '-' + month + '-' + lastDayOfMonth;


            document.getElementById('application_date').value = fullDate;
        });


        $('#violation_penalties').on('change', function() {

            var amount_type = $(this).find(':selected').data('amount_type');

            console.log(amount_type);

            if (amount_type === 'warning') {
                $('#violation_file').show();
            } else {
                $('#violation_file').hide();


            }
        });

    });
</script>
