<div class="modal-dialog" role="document">
    <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
            <h4 class="modal-title text-center" id="exampleModalLabel">
                @lang('essentials::lang.details')
            </h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="col-md-12">
                        <h4>@lang('essentials::lang.department_Details'):</h4>
                    </div>
                    <p><strong>@lang('essentials::lang.name'):</strong> {{ $name ?? '' }}</p>
                    <p><strong>@lang('essentials::lang.level'):</strong> 
                        @if ($level === 'first_level')
                            {{ __('essentials::lang.first_level') }}
                        @elseif ($level === 'other')
                            {{ __('essentials::lang.other_level') }}
                        @else
                            " "
                        @endif
                    </p>
                    @if ($level === 'other')
                    <p><strong>@lang('essentials::lang.parent_department_id'):</strong> {{ $parent_department_id ?? '' }}</p>
                    @endif

                    <p><strong>@lang('essentials::lang.dep_type'):</strong>
                        @if ($is_main === 1)
                            {{ __('essentials::lang.main_dep') }}
                        @elseif ($is_main === 0)
                            {{ __('essentials::lang.subsidiary_dep') }}
                        @else
                           
                            ""
                        @endif
                    </p>
                    <p><strong>@lang('essentials::lang.address'):</strong> {{ $address ?? '' }}</p>
                    <p><strong>@lang('essentials::lang.is_active'):</strong>
                        @if ($is_active ===1)
                            {{ __('essentials::lang.isActive') }}
                        @else
                            {{ __('essentials::lang.is_unactive') }}
                        @endif
                    </p>
                    
                
                    <div class="col-md-12">
                        <hr>
                        <h4>@lang('essentials::lang.manager_Details'):</h4>
                    </div>
                        <p><strong>@lang('essentials::lang.manager'):</strong> {{ $manager ?? '' }}</p>
                        <p><strong>@lang('essentials::lang.profession'):</strong> {{ $profession_id ?? '' }}</p>
                        <p><strong>@lang('essentials::lang.specialization'):</strong> {{ $specialization_id ?? '' }}</p>
                        <p><strong>@lang('essentials::lang.start_date'):</strong> {{ $manager_start_from ?? '' }}</p>
                  
                        <div class="col-md-12">
                            <hr>
                            <h4>@lang('essentials::lang.delegate_Details'):</h4>
                        </div>
                 
                        <p><strong>@lang('essentials::lang.delegate'):</strong> {{ $delegate?? '' }}</p>
                        <p><strong>@lang('essentials::lang.profession'):</strong> {{ $delegate_profession_id ?? '' }}</p>
                        <p><strong>@lang('essentials::lang.specialization'):</strong> {{ $delegate_specialization_id ?? '' }}</p>
                        <p><strong>@lang('essentials::lang.start_date'):</strong> {{ $delegate_start_from ?? '' }}</p>
                        <p><strong>@lang('essentials::lang.end_date'):</strong> {{ $delegate_end_at ?? '' }}</p>
              
                </div>
                
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">@lang('essentials::lang.close')</button>
        
        </div>
    </div>
</div>
