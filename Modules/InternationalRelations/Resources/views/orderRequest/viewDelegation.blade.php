<style>
    .custom-modal-dialog {
        width: 50%; 
        max-width: none;
    }
</style>

<div class="modal-dialog custom-modal-dialog no-print" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">@lang('internationalrelations::lang.view_delegation')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    @foreach ($irDelegations as $delegation)
                    <p>
                        {{ __('internationalrelations::lang.Office_name') }}: {{ $delegation->agency->supplier_business_name }}<br>
                        {{ __('internationalrelations::lang.target_quantity') }}: {{ $delegation->targeted_quantity }}<br>
                        {{ __('internationalrelations::lang.currently_proposed_labors_quantity') }}: {{ $delegation->proposed_labors_quantity }}
                    </p>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
