@extends('layouts.app')
@section('title', __( 'essentials::lang.import_employees' ))

@section('content')
@include('essentials::layouts.nav_employee_affairs')
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
                    @if(!empty($notification['msg']))
                        {{$notification['msg']}}
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
                {!! Form::open(['url' => action([\Modules\Essentials\Http\Controllers\EssentialsEmployeeImportController::class, 'postImportEmployee']), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
                    <div class="row">
                        <div class="col-sm-6">
                        <div class="col-sm-8">
                            <div class="form-group">
                                {!! Form::label('name', __( 'product.file_to_import' ) . ':') !!}
                                {!! Form::file('employee_csv', ['accept'=> '.xls', 'required' => 'required']); !!}
                              </div>
                        </div>
                        <div class="col-sm-4">
                        <br>
                            <button type="submit" class="btn btn-primary">@lang('messages.submit')</button>
                        </div>
                        </div>
                    </div>

                {!! Form::close() !!}
                <br><br>
                <div class="row">
                    <div class="col-sm-4">
                        <a href="{{ asset('files/import_employee_csv_template.xls') }}" class="btn btn-success" download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file')</a>
                    </div>
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
                        <td>@lang('essentials::lang.surname') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>@lang('essentials::lang.last_name') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>@lang('essentials::lang.Birth_date') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.dob_ins') ({{\Carbon::now()->format('Y-m-d')}})</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>@lang('essentials::lang.Gender') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>@lang('essentials::lang.status') <br><small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>@lang('essentials::lang.blood_type') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>@lang('essentials::lang.Mobile_number') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>@lang('essentials::lang.Alternative_mobile_number') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>@lang('essentials::lang.family_number') <br><small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>11</td>
                        <td>@lang('essentials::lang.Facebook_link') <br><small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>12</td>
                        <td>@lang('essentials::lang.Twitter_link') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>13</td>
                        <td>@lang('essentials::lang.Social_media_1') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>14</td>
                        <td>@lang('essentials::lang.Social_media_2') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>15</td>
                        <td>@lang('essentials::lang.Custom_field_1') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>15</td>
                        <td>@lang('essentials::lang.Custom_field_2') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>15</td>
                        <td>@lang('essentials::lang.Custom_field_3') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>15</td>
                        <td>@lang('essentials::lang.Custom_field_4') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>16</td>
                        <td>@lang('essentials::lang.Name_of_guardian') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>17</td>
                        <td>@lang('essentials::lang.Identity_proof_name') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>18</td>
                        <td>@lang('essentials::lang.permanent_address') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>19</td>
                        <td>@lang('essentials::lang.current_address') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>20</td>
                        <td>@lang('essentials::lang.bank_account_holder') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>21</td>
                        <td>@lang('essentials::lang.account_number') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>22</td>
                        <td>@lang('essentials::lang.IBAN_number') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>23</td>
                        <td>@lang('essentials::lang.Branch') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                   
                    <tr>
                        <td>23</td>
                        <td>@lang('essentials::lang.employee_tax_number') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                   
                    <tr>
                        <td>24</td>
                        <td>@lang('essentials::lang.HR_Department') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>25</td>
                        <td>@lang('essentials::lang.Job_title') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.academic_qualification') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.,contract_number') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.contract_number') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('essentials::lang.contract_number_example') </td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.Contract_starting_date') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.dob_ins') ({{\Carbon::now()->format('Y-m-d')}})</td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.Contract_end_date') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.dob_ins') ({{\Carbon::now()->format('Y-m-d')}})</td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.Duration_contract') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.Trial_period') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.Isrenewable') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.HR_Department') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.Basic_salary') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.Salary_components') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.Type_basic_salary') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.Allowance_type') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.Entitlement_type') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>26</td>
                        <td>@lang('essentials::lang.Class_travel_tickets') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->

@endsection