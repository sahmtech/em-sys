{{-- @extends('layouts.app')
@section('title', __('essentials::lang.payroll'))

@section('content')

<section class="content-header">
    <h1>@lang('essentials::lang.payroll')
    </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#payroll_tab" data-toggle="tab" aria-expanded="true">
                            <i class="fas fa-coins" aria-hidden="true"></i>
                            @lang('essentials::lang.all_payrolls')
                        </a>
                    </li>
                    @can('essentials.view_all_payroll')
                        <li>
                            <a href="#payroll_group_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-layer-group" aria-hidden="true"></i>
                                @lang('essentials::lang.all_payroll_groups')
                            </a>
                        </li>
                    @endcan
                    @if(auth()->user()->can('essentials.view_allowance_and_deduction') || auth()->user()->can('essentials.add_allowance_and_deduction'))
                        <li>
                            <a href="#pay_component_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fab fa-gg-circle" aria-hidden="true"></i>
                                @lang( 'essentials::lang.pay_components' )
                            </a>
                        </li>
                    @endif
                </ul>
               
            </div>
        </div>
    </div>
   
    <div class="modal fade" id="add_allowance_deduction_modal" tabindex="-1" role="dialog"
 aria-labelledby="gridSystemModalLabel"></div>
</section>
<!-- /.content -->
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

@endsection
 --}}
