@extends('layouts.app')
@section('title', __('internationalrelations::lang.importWorkers'))

@section('content')

    <section class="content-header">
        <h1>@lang('internationalrelations::lang.importWorkers')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        @if (session('notification') || !empty($notification))
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        @if (!empty($notification['msg']))
                            {{ $notification['msg'] }}
                        @elseif(session('notification.msg'))
                            {{ session('notification.msg') }}
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-sm-12">
                @component('components.widget', ['class' => 'box-primary'])
                    {!! Form::open([
                        'url' => action([
                            \Modules\HousingMovements\Http\Controllers\HousingMovementsController::class,
                            'postImportWorkersNewArrival',
                        ]),
                        'method' => 'post',
                        'enctype' => 'multipart/form-data',
                    ]) !!}
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    {!! Form::label('name', __('product.file_to_import') . ':') !!}
                                    {!! Form::file('workers_csv', ['accept' => '.xls', 'required' => 'required']) !!}
                                </div>
                            </div>
                            {{-- <input type="hidden" name="delegation_id" value="">
                            <input type="hidden" name="agency_id" value="">
                            <input type="hidden" name="unSupportedworker_order_id" value=""> --}}

                            <div class="col-sm-4">
                                <br>
                                <button type="submit" class="btn btn-primary">@lang('messages.submit')</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <a href="{{ asset('files/import_workers_new_arrival_template.xlsx') }}" class="btn btn-success"
                                download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file')</a>
                        </div>
                    </div>

                    {!! Form::close() !!}
                @endcomponent
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.instructions')])
                    <div class="mb-3">
                        <strong>@lang('lang_v1.instruction_line1')</strong><br>
                        @lang('lang_v1.instruction_line2')
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>@lang('lang_v1.col_no')</th>
                                    <th>@lang('lang_v1.col_name')</th>
                                    <th>@lang('lang_v1.instruction')</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>@lang('essentials::lang.employee_name') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                                    <td>@lang('essentials::lang.employee_name_example')</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>@lang('essentials::lang.mid_name') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>@lang('essentials::lang.last_name') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>@lang('essentials::lang.nationality') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>@lang('essentials::lang.passport_number') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                                    <td>&nbsp;</td>
                                </tr>

                                <tr>
                                    <td>6</td>
                                    <td>@lang('essentials::lang.sponsor') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                                    <td class="text-wrap">
                                        @lang('lang_v1.company_id_ins') <br>
                                        <ul>
                                            <li>1 - شركة إمدادات العطاء للتجارة والمقاولات</li>
                                            <li>2 - مؤسسة المسكن الثاني</li>
                                            <li>3 - شركة امدادات للتسويق</li>
                                            <li>4 - شركة أعمال نافا للتشغيل والصيانة</li>
                                            <li>5 - شركة بطل الخبرة للمقاولات</li>
                                            <li>6 - شركة بطل الجودة</li>
                                            <li>7 - مؤسسة تلال الفجر</li>
                                            <li>8 - مؤسسة خالد مسفر فلاح الكبرى</li>
                                            <li>9 - شركة أداء العمل</li>
                                            <li>10 - شركة الترا الذهبية للتجارة</li>
                                            <li>11 - شركة اهتمام</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td>7</td>
                                    <td>@lang('essentials::lang.project') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>7</td>
                                    <td>@lang('essentials::lang.gender') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>8</td>
                                    <td>@lang('essentials::lang.dob') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                                    <td>@lang('lang_v1.dob_ins') ({{ \Carbon::now()->format('Y-m-d') }})</td>
                                </tr>
                                <tr>
                                    <td>9</td>
                                    <td>@lang('essentials::lang.arrival_date') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                                    <td>@lang('lang_v1.dob_ins') ({{ \Carbon::now()->format('Y-m-d') }})</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>

    </section>
    <!-- /.content -->

@endsection
