<div class="modal-dialog modal-lg" id="edit_violations" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-plus"></i> @lang('essentials::lang.edit_violations')</h4>
        </div>
        {!! Form::open(['route' => 'update-violations', 'enctype' => 'multipart/form-data']) !!}

        <div class="modal-body">

            <div class="row">
                <div class="form-group col-md-6">
                    {!! Form::label('description', __('essentials::lang.description') . ' *') !!}

                    {!! Form::text('description', $ViolationPenalties->descrption, [
                        'class' => 'form-control',
                        'id' => 'description',
                        'required',
                        'placeholder' => __('essentials::lang.description'),
                    ]) !!}
                </div>
                <input type="hidden" value="{{ $ViolationPenalties->descrption }}" name="id" />
                <div class="form-group col-md-6">
                    {!! Form::label('violations', __('essentials::lang.main-violations') . ' *') !!}
                    <select class="form-control" required="" id="violation" name="violation_id"
                        style="padding: 2px 10px;">
                        @foreach ($Violations as $violation)
                            <option value="{{ $violation->id }}" @if ($violation->id == $ViolationPenalties->violation_id)
                                selected
                            @endif>{{ $violation->description }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('occurrence', __('essentials::lang.occurrence') . ' *') !!}
                    <select class="form-control" required="" id="occurrence" name="occurrence"
                        style="padding: 2px 10px;">
                        <option value="First time" @if ($ViolationPenalties->occurrence == 'First time') selected @endif>@lang('essentials::lang.First time')
                        </option>
                        <option value="Secound time" @if ($ViolationPenalties->occurrence == 'Secound time') selected @endif>@lang('essentials::lang.Secound time')
                        </option>
                        <option value="Theard time" @if ($ViolationPenalties->occurrence == 'Theard time') selected @endif>@lang('essentials::lang.Theard time')
                        </option>
                        <option value="Fourth time" @if ($ViolationPenalties->occurrence == 'Fourth time') selected @endif>@lang('essentials::lang.Fourth time')
                        </option>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('amount_type', __('essentials::lang.amount_type') . ' *') !!}
                    <select class="form-control" required="" id="amount_type" name="amount_type"
                        style="padding: 2px 10px;">
                        <option value="fixed" @if ($ViolationPenalties->amount_type == 'fixed') selected @endif>@lang('essentials::lang.fixed')
                        </option>
                        <option value="percent_amount" @if ($ViolationPenalties->amount_type == 'percent_amount') selected @endif>@lang('essentials::lang.percent_amount')
                        </option>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('amount', __('essentials::lang.amount') . ' *') !!}
                    {!! Form::text('amount', $ViolationPenalties->amount, [
                        'class' => 'form-control',
                        'id' => 'amount',
                        'placeholder' => __('essentials::lang.amount'),
                    ]) !!}
                    <div id="idProofNumberError" style="color: red;"></div>
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

        $('#edit_driver_model').on('shown.bs.modal', function(e) {
            $('#worker__select').select2({
                dropdownParent: $(
                    '#edit_driver_model'),
                width: '100%',
            });

            $('#car__id').select2({
                dropdownParent: $(
                    '#edit_driver_model'),
                width: '100%',
            });
        });

    });
</script>
