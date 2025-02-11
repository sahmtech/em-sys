@extends('layouts.app')
@section('title', __('housingmovements::lang.advanceSalaryRequest'))
<!-- Add toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" />

<!-- Add toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

@section('content')
<style>
    #requests_table {
        font-size: 12px;
    }
    table.dataTable thead th {
        vertical-align: middle;
    }
</style>




<section class="content-header">
    <h1>
        <span>@lang('housingmovements::lang.advanceSalaryRequest')</span>
    </h1>
</section>


<section class="content">

    @include('housingmovements::layouts.nav_trevelers')

    @component('components.widget', ['class' => 'box-primary'])
    @slot('tool')
    <div class="box-tools">

        <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
            data-target="#addRequestModal">
            <i class="fa fa-plus"></i> @lang('request.create_order')
        </button>
    </div>
    @endslot

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="requests_table">
            <thead>
                <tr>
                    <th>@lang('request.company')</th>
                    <th>@lang('request.request_number')</th>
                    <th>@lang('request.request_owner')</th>
                    <th>@lang('request.border_number')</th>
                    <th>@lang('request.advSalaryAmount')</th>
                    <th>@lang('request.monthlyInstallment')</th>
                    <th>@lang('request.installmentsNumber')</th>
                    <th>@lang('request.request_date')</th>
                    <th>@lang('request.status')</th>
                    <th>@lang('request.note')</th>


                </tr>
            </thead>
        </table>
    </div>
    @endcomponent

    

    {{-- add request --}}
    <div class="modal fade" id="addRequestModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'hm.returnReq.store', 'enctype' => 'multipart/form-data']) !!}

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('request.create_order')</h4>
                </div>

                <div class="modal-body">
                    {{-- <div class="row">











                    </div> --}}

                    <div class="row">

                        <div class="form-group col-md-6">
                            {!! Form::label('type', __('essentials::lang.type') . ':*') !!}

                            {!! Form::select(
                            'type',
                            \Illuminate\Support\Facades\DB::table('requests_types')
                            ->where('type','advanceSalary')
                            ->get()
                            ->mapWithKeys(function ($requestType) {
                            return [
                            $requestType->id => trans('request.' . $requestType->type) . ' - ' . trans('request.' .
                            $requestType->for),
                            ];
                            })
                            ->toArray(), 
                            null,
                            [
                            'class' => 'form-control',
                            'required',
                            'style' => 'height: 40px',
                            'placeholder' => __('essentials::lang.select_type'),
                            'id' => 'requestType',
                            ]
                            ) !!}
                        </div>


                        <div class="form-group col-md-6">
                            {!! Form::label('user_id', __('essentials::lang.name') . ':*') !!}
                            {!! Form::select('user_id[]', $users, null, [
                            'class' => 'form-control select2',
                            'multiple',
                            'required',
                            'id' => 'worker',
                            'style' => 'height: 60px; width: 250px;',
                            ]) !!}
                        </div>



                        <div class="form-group col-md-6" id="amount">
                            {!! Form::label('amount', __('request.advSalaryAmount') . ':*') !!}
                            {!! Form::number('amount', null, [
                            'class' => 'form-control',
                            'style' => ' height: 40px',
                            'placeholder' => __('request.advSalaryAmount'),
                            'id' => 'advSalaryAmountField',
                            ]) !!}
                        </div>

                        <div class="form-group col-md-6" id="installmentsNumber">
                            {!! Form::label('installmentsNumber', __('request.installmentsNumber') . ':*') !!}
                            {!! Form::number('installmentsNumber', null, [
                            'class' => 'form-control',
                            'style' => ' height: 40px',
                            'placeholder' => __('request.installmentsNumber'),
                            'id' => 'installmentsNumberField',
                            ]) !!}
                        </div>
                        <div class="form-group col-md-6" id="monthlyInstallment">
                            {!! Form::label('monthlyInstallment', __('request.monthlyInstallment') . ':*') !!}
                            {!! Form::number('monthlyInstallment', null, [
                            'class' => 'form-control',
                            'style' => ' height: 40px',
                            'placeholder' => __('request.monthlyInstallment'),
                            'id' => 'monthlyInstallmentField',
                            ]) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('note', __('request.note') . ':') !!}
                            {!! Form::textarea('note', null, [
                            'class' => 'form-control',
                            'placeholder' => __('request.note'),
                            'rows' => 3,
                            ]) !!}
                        </div>


                        <div class="form-group col-md-6">
                            {!! Form::label('attachment', __('request.attachment') . ':') !!}
                            {!! Form::file('attachment', null, [
                            'class' => 'form-control',
                            'placeholder' => __('request.attachment'),
                            ]) !!}
                        </div>
                    </div>
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
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {


            var requests_table = $('#requests_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('advanceSalaryRequest') }}"
                },

                columns: [
                    {
                        data: 'company_id',
                        searchable: false

                    },
                    {
                        data: 'request_no'
                    },

                    {
                        data: 'user',
                        searchable: false

                    },
                    {
                        data: 'border_no',
                        searchable: false

                    },
                    {
                        data: 'advSalaryAmount'
                    },
                    {
                        data: 'monthlyInstallment'
                    },
                    {
                        data: 'installmentsNumber'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'status',

                    },
                    {
                        data: 'note'
                    },


                ],
            });





        });

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            toastr.error('{{ $error }}', {
                timeOut: 5000,
                closeButton: true,
                progressBar: true
            });
        @endforeach
    @endif

    @if (session('success'))
        toastr.success('{{ session('success') }}', {
            timeOut: 5000,
            closeButton: true,
            progressBar: true
        });
    @endif
</script>

@endsection