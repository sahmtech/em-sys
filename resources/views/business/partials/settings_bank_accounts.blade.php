<!--Purchase related settings -->
<div class="pos-tab-content">

    <!-- Main content -->
    <section class="content">


        <div class="row">
            <div class="col-md-12">


                <div class="box-tools">
                    <a class="btn btn-primary pull-right m-5 btn-modal"
                        href="{{ action([\App\Http\Controllers\BankAccountsController::class, 'create']) }}"
                        data-href="{{ action([\App\Http\Controllers\BankAccountsController::class, 'create']) }}"
                        data-container="#add_BankAccounts">
                        <i class="fas fa-plus"></i> @lang('messages.add')</a>
                </div>

            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="bank_accounts" style="margin-bottom: 100px;">
                    <thead>
                        <tr>
                            <th>@lang('messages.action')</th>

                            <th style="text-align: center;">@lang('lang_v1.bank_name')</th>
                            <th style="text-align: center;">@lang('lang_v1.account_number')</th>

                            <th style="text-align: center;">@lang('lang_v1.ibn')</th>
                            <th style="text-align: center;">@lang('lang_v1.account_name_')</th>


                        </tr>
                    </thead>

                    <tbody id="tbody">
                        @foreach ($bankAccounts as $row)
                            <tr>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button id="btnGroupDrop1" type="button"
                                            style="background-color: transparent;
                                        font-size: x-large;
                                        padding: 0px 20px;"
                                            class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-cog" aria-hidden="true"></i>
                                        </button>
                                        <div class="dropdown-menu">

                                            @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('AutomatedMigration.edit'))
                                                <a class="dropdown-item btn-modal" data-container="#edit_BankAccounts"
                                                    style="margin: 2px;" title="@lang('messages.edit')"
                                                    href="{{ action('\App\Http\Controllers\BankAccountsController@edit', $row->id) }}"
                                                    data-href="{{ action('\App\Http\Controllers\BankAccountsController@edit', $row->id) }}">
                                                    <i class="fas fa-edit" style="padding: 2px;color:rgb(8, 158, 16);"></i>
                                                    @lang('messages.edit') </a>
                                            @endif

                                            @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('AutomatedMigration.active_toggle'))
                                                <a class="dropdown-item" style="margin: 2px;" {{-- title="{{ $row->active ? @lang('accounting::lang.active') : @lang('accounting::lang.inactive') }}" --}}
                                                    href="{{ action('\App\Http\Controllers\BankAccountsController@delete', $row->id) }}"
                                                    data-href="{{ action('\App\Http\Controllers\BankAccountsController@delete', $row->id) }}" {{-- data-target="#active_auto_migration" data-toggle="modal" --}} {{-- id="delete_auto_migration" --}}>
                 
                                                        <i class="fa fa-ban" style="padding: 2px;color:red;"
                                                            title="state of automated migration is inactive"></i>
                                                        @lang('messages.delete')
                                                  
                                                </a>
                                            @endif
                                        </div>
                                    </div>




                                </td>
                                <td>
                                    {{ $row->bank->name ?? '' }}

                                </td>
                                <td>
                                    {{ $row->account_number ?? '' }}

                                </td>
                                <td>

                                    {{ $row->ibn ?? '' }}


                                </td>
                                <td>
                                    {{ $row->account_name ?? '' }}
                                </td>

                            </tr>
                        @endforeach

                    </tbody>

                </table>


                {{-- <center class="mt-5">
                            {{ $carModles->links() }}
                        </center> --}}
            </div>


    </div>
    {{-- <div class="modal fade" id="edit_carsChangeOil_model" tabindex="-1" role="dialog"></div> --}}
@endcomponent



</section>

<!-- /.content -->
</div>
