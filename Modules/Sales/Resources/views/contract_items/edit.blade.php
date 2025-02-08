@extends('layouts.app')
@section('title', __('sales::lang.contract_itmes'))

@section('content')

<section class="content-header">
  <h1>
    <span>@lang('sales::lang.contract_itmes')</span>
  </h1>
</section>

<div class="modal-dialog" role="document">
  <div class="modal-content">
    {!! Form::open(['route' => ['updateItem', $item->id], 'method' => 'put', 'id' => 'add_item_form']) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
          aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('sales::lang.edit_item')</h4>
    </div>

    <div class="modal-body">
      <div class="row">
        {{-- Contacts --}}
        <div class="form-group col-md-6">
          {!! Form::label('contacts', __('sales::lang.contact_name') . ':*') !!}
          {!! Form::select('contacts', $contacts, $item?->contact_id??null, [
          'class' => 'form-control',
          'id' => 'contactSelect',
          'style' => 'height:40px',
          // 'disabled'  =>'disabled',
          'required',
          'placeholder' => __('sales::lang.contact_name'),
          ]) !!}
        </div>
        {{-- Projects --}}
        {{-- {{ }} --}}
        <div class="form-group col-md-6">
          {!! Form::label('project_name', __('sales::lang.project_name') . ':*') !!}
          {!! Form::select('project_name',
          $item->contact?->salesProjects?->pluck('name', 'id')->toArray()??[],
          $item->contact?->salesProjects??[], [
          'class' => 'form-control',
          'id' => 'projectSelect',
          'style' => 'height:40px',
          'placeholder' => __('sales::lang.project_name'),
          ]) !!}
        </div>


        {{-- Profession --}}
        <div class="form-group col-md-6">
          {!! Form::label('profession', __('sales::lang.profession') . ':*') !!}
          {!! Form::select('profession', $professions, $item->profession_id, [
          'class' => 'form-control select2',
          'id' => 'professionSearch',
          // 'required',
          'style' => 'height:40px',
          'placeholder' => __('sales::lang.profession'),
          ]) !!}
        </div>

        {{-- Nationality --}}
        <div class="form-group col-md-6">
          {!! Form::label('nationality', __('sales::lang.nationality') . ':*') !!}
          {!! Form::select('nationality', $nationalities, $item->nationality_id , [
          'class' => 'form-control select2',
          'id' => 'nationalitySearch',
          'required',
          'style' => 'height:40px',
          'placeholder' => __('sales::lang.nationality'),
          ]) !!}
        </div>

        {{-- Gender --}}
        <div class="form-group col-md-6">
          {!! Form::label('gender', __('sales::lang.gender') . ':*') !!}
          {!! Form::select('gender', ['male' => __('sales::lang.male'), 'female' => __('sales::lang.female')],
          $item->gender, [
          'class' => 'form-control',
          'required',
          'style' => 'height:40px',
          'placeholder' => __('sales::lang.gender'),
          ]) !!}
        </div>

        {{-- Essentials Salary --}}
        <div class="form-group col-md-6">
          {!! Form::label('essentials_salary', __('essentials::lang.monthly_cost') . ':') !!}
          {!! Form::number('essentials_salary', !empty($item->monthly_cost_for_one) ? $item->monthly_cost_for_one :
          null, [
          'class' => 'form-control',
          'style' => 'height:40px',
          'placeholder' => __('essentials::lang.monthly_cost'),
          'id' => 'essentials_salary',
          ]) !!}
        </div>

        {{-- Details --}}
        <div class="form-group col-md-12">
          {!! Form::label('details', __('sales::lang.details') . ':') !!}
          {!! Form::textarea('details', $item->details, ['class' => 'form-control', 'placeholder' =>
          __('sales::lang.details'), 'rows' => 2]) !!}
        </div>

      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
    </div>

    {!! Form::close() !!}
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
  $('#addItemModal').on('shown.bs.modal', function(e) {
                $('#professionSearch').select2({
                    dropdownParent: $(
                        '#addItemModal'),
                    width: '100%',
                });
                $('#nationalitySearch').select2({
                    dropdownParent: $(
                        '#addItemModal'),
                    width: '100%',
                });
                $('#specializationSearch').select2({
                    dropdownParent: $(
                        '#addItemModal'),
                    width: '100%',
                });

            });


    // $('#contactSelect').change(function () {
    //     var contactId = $(this).val(); // Get selected contact ID

    //     if (contactId) {
    //         $.ajax({
    //             url: '/get-sales-projects/' + contactId, // Updated API route
    //             type: 'GET',
    //             dataType: 'json',
    //             success: function (data) {
    //                 $('#projectSelect').empty(); // Clear existing options
    //                 $('#projectSelect').append('<option value="">' + "حدد مشروع" + '</option>');

    //                 $.each(data, function (key, value) {
    //                     $('#projectSelect').append('<option value="' + key + '">' + value + '</option>');
    //                 });
    //             }
    //         });
    //     } else {
    //         $('#projectSelect').empty();
    //     }
    // });
</script>
@endsection