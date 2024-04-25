@extends('layouts.app')
@section('title', __('essentials::lang.import_employees'))

@section('content')
    <!-- Content Header (Page header) -->


    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('essentials::lang.import_employees')
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
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>@lang('essentials::lang.select_operation'):</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="operation" value="add" checked> @lang('essentials::lang.add_new_data')
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="operation" value="update"> @lang('essentials::lang.update_existing_data')
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="add-new-data">
                        {!! Form::open([
                            'url' => action([
                                \Modules\Essentials\Http\Controllers\EssentialsEmployeeImportController::class,
                                'postImportEmployee',
                            ]),
                            'method' => 'post',
                            'enctype' => 'multipart/form-data',
                        ]) !!}
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="col-sm-8">
                                    <div class="form-group">
                                        {!! Form::label('name', __('product.file_to_import') . ':') !!}
                                        {!! Form::file('employee_csv', ['accept' => '.xls']) !!}
                                    </div>
                                </div>
                                @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.import_create_employees'))
                                    <div class="col-sm-4">
                                        <br>
                                        <button type="submit" class="btn btn-primary">@lang('messages.submit')</button>
                                    </div>
                                @endif

                                <div class="col-sm-6">
                                    <a href="{{ asset('files/import_employee_template.xls') }}" class="btn btn-success"
                                        download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file')</a>
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>




                    <div class="update-existing-data" style="display: none;">
                        {!! Form::open([
                            'url' => action([
                                \Modules\Essentials\Http\Controllers\EssentialsEmployeeUpdateImportController::class,
                                'postImportupdateEmployee',
                            ]),
                            'method' => 'post',
                            'enctype' => 'multipart/form-data',
                        ]) !!}
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="col-sm-8">
                                    <div class="form-group">
                                        {!! Form::label('name', __('essentials::lang.file_to_update__import') . ':') !!}
                                        {!! Form::file('update_employee_csv', ['accept' => '.xls,.xlsx']) !!}
                                    </div>
                                </div>
                                @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.import_update_employees'))
                                    <div class="col-sm-4">
                                        <br>
                                        <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                                    </div>
                                @endif
                                <div class="col-sm-6">
                                    <a href="{{ asset('files/import_update_employee_template.xls') }}" class="btn btn-success"
                                        download><i class="fa fa-download"></i> @lang('essentials::lang.download_update_template_file')</a>
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                @endcomponent
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.instructions')])
                    <strong>@lang('lang_v1.instruction_line1')</strong><br>
                    @lang('lang_v1.instruction_line2')
                    <br><br>
                    <table class="table table-striped">
                        <tr>
                            <th>@lang('lang_v1.col_no')</th>
                            <th>@lang('lang_v1.col_name')</th>
                            <th>@lang('lang_v1.instruction')</th>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>@lang('essentials::lang.employee_name') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                            <td>@lang('essentials::lang.employee_name_example') </td>
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
                            <td>@lang('essentials::lang.employee_type') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                            <td>worker ,manager,user,employee, </td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>@lang('essentials::lang.email') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('essentials::lang.email_example') </td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>@lang('essentials::lang.Birth_date') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('lang_v1.dob_ins') ({{ \Carbon::now()->format('Y-m-d') }})</td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>@lang('essentials::lang.Gender') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>

                        <tr>
                            <td>8</td>
                            <td>@lang('essentials::lang.marital_status') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>single,married </td>
                        </tr>

                        <tr>
                            <td>9</td>
                            <td>@lang('essentials::lang.blood_type') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td>@lang('essentials::lang.Mobile_number') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>11</td>
                            <td>@lang('essentials::lang.Alternative_mobile_number') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>05******</td>
                        </tr>
                        <tr>
                            <td>12</td>
                            <td>@lang('essentials::lang.family_number') <br><small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>


                        <tr>
                            <td>13</td>
                            <td>@lang('essentials::lang.Identity_proof_name') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                            <td>@lang('essentials::lang.proof_name_example') </td>
                        </tr>

                        <tr>
                            <td>14</td>
                            <td>@lang('essentials::lang.Identity_proof_id') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>15</td>
                            <td>@lang('essentials::lang.permanent_address') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>16</td>
                            <td>@lang('essentials::lang.current_address') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>17</td>
                            <td>@lang('essentials::lang.bank_account_holder') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>18</td>
                            <td>@lang('essentials::lang.account_number') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>19</td>
                            <td>@lang('essentials::lang.account_name') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>20</td>
                            <td>@lang('essentials::lang.IBAN_number') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>SA*********</td>
                        </tr>
                        <tr>
                            <td>21</td>
                            <td>@lang('essentials::lang.Branch') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>


                        <tr>
                            <td>22</td>
                            <td>@lang('essentials::lang.HR_Department') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>23</td>
                            <td>@lang('essentials::lang.HR_Department_branch') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>24</td>
                            <td>@lang('essentials::lang.Job_title') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('essentials::lang.job_titles_example') </td>
                        </tr>


                        <tr>
                            <td>25</td>
                            <td>@lang('essentials::lang.contract_number') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('essentials::lang.contract_number_example') </td>
                        </tr>
                        <tr>
                            <td>26</td>
                            <td>@lang('essentials::lang.Contract_starting_date') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('lang_v1.dob_ins') ({{ \Carbon::now()->format('Y-m-d') }})</td>
                        </tr>
                        <tr>
                            <td>27</td>
                            <td>@lang('essentials::lang.Contract_end_date') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('lang_v1.dob_ins') ({{ \Carbon::now()->format('Y-m-d') }})</td>
                        </tr>
                        <tr>
                            <td>28</td>
                            <td>@lang('essentials::lang.Duration_contract') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>29</td>
                            <td>@lang('essentials::lang.Trial_period') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>30</td>
                            <td>@lang('essentials::lang.Isrenewable') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>31</td>
                            <td>@lang('essentials::lang.contractstatus') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>

                        <tr>
                            <td>32</td>
                            <td>@lang('essentials::lang.Basic_salary') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>33</td>
                            <td>@lang('essentials::lang.additional_salary_type') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('essentials::lang.additional_salary_type_example') </td>
                        </tr>
                        <tr>
                            <td>34</td>
                            <td>@lang('essentials::lang.additional_salary_amount') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>35</td>
                            <td>@lang('essentials::lang.travel_ticket_categorie') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('essentials::lang.travel_ticket_categorie_example') </td>
                        </tr>



                    </table>
                @endcomponent
            </div>
        </div>
    </section>
    <!-- /.content -->

@endsection
@section('javascript')
    <script>
        $(document).ready(function() {
            $('input[name="operation"]').change(function() {
                if (this.value === 'add') {
                    $('.add-new-data').show();
                    $('.update-existing-data').hide();
                } else if (this.value === 'update') {
                    $('.add-new-data').hide();
                    $('.update-existing-data').show();
                }
            });

            // $('.update-existing-data form').submit(function(event) {
            //     event.preventDefault(); 


            //     $.ajax({
            //         url: $(this).attr('action'),
            //         type: $(this).attr('method'),
            //         data: new FormData(this),
            //         processData: false,
            //         contentType: false,
            //         success: function(response) {

            //             if (response.success) {


            //                  var downloadUrl = '/uploads/' + response.filename;

            //                 var link = document.createElement('a');
            //                 link.href = downloadUrl;
            //                 link.download = response.filename;
            //                 document.body.appendChild(link);
            //                 link.click();
            //                 document.body.removeChild(link);


            //                 setTimeout(function() {
            //                     window.location.href = '/employee_affairs/employees/';
            //                 }, 1000); 
            //             } else {

            //                 console.log(response.filename);
            //             }
            //         },
            //         error: function(xhr, status, error) {

            //             console.error('AJAX error:', error);

            //         }
            //     });
            // });


        });
    </script>

@endsection
