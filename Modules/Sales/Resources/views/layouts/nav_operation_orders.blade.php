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
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('sales.view_operations_order_for_contract'))
                        <li @if (request()->segment(2) == 'orderOperations') class="active" @endif>
                            <a href="{{ route('sale.orderOperations') }}">
                                <i class="fas fa-file-alt" aria-hidden="true" style="font-size: smaller;"></i>
                                @lang('sales::lang.orderOperations_for_contract')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('sales.view_operations_order_for_unSupported_workers'))
                        <li @if (request()->segment(2) == 'orderOperationForUnsupportedWorkers') class="active" @endif>
                            <a href="{{ route('sale.orderOperationForUnsupportedWorkers') }}">
                                <i class="fas fa-exclamation-triangle" aria-hidden="true"
                                    style="font-size: smaller;"></i>
                                @lang('sales::lang.orderOperationForUnsupportedWorkers')
                            </a>
                        </li>
                    @endif



                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
