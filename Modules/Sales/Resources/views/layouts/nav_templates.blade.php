<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    @can('essentials.view_first_choice_offer_price_template')
                        <li @if (request()->segment(2) == 'first_choice_offer_price_template') class="active" @endif>
                            <a href="{{ route('first_choice_offer_price_template') }}">
                                <i class="fas fa-tag" aria-hidden="true" style="font-size: smaller;"></i>
                                @lang('sales::lang.first_choice_offer_price_template')
                            </a>
                        </li>
                    @endcan
                    @can('essentials.view_second_choice_offer_price_template')
                        <li @if (request()->segment(2) == 'second_choice_offer_price_template') class="active" @endif>
                            <a href="{{ route('second_choice_offer_price_template') }}">
                                <i class="fas fa-tag" aria-hidden="true"></i> 
                                @lang('sales::lang.second_choice_offer_price_template')
                            </a>
                        </li>
                    @endcan

                    @can('essentials.view_first_choice_sales_contract_template')
                        <li @if (request()->segment(2) == 'first_choice_sales_contract_template') class="active" @endif>
                            <a href="{{ route('first_choice_sales_contract_template') }}">
                                <i class="fas fa-file-contract" aria-hidden="true"></i> @lang('sales::lang.first_choice_sales_contract_template')
                            </a>
                        </li>
                    @endcan
                    @can('essentials.view_second_choice_sales_contract_template')
                        <li @if (request()->segment(2) == 'second_choice_sales_contract_template') class="active" @endif>
                            <a href="{{ route('second_choice_sales_contract_template') }}">
                                <i class="fas fa-file-contract" aria-hidden="true" style="font-size: smaller;"></i>
                                @lang('sales::lang.second_choice_sales_contract_template')
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>