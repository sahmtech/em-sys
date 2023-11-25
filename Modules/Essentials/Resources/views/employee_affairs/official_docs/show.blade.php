<div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title text-center" id="exampleModalLabel">
           @lang('essentials::lang.doc_details')
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>@lang('essentials::lang.doc_number'):</strong> {{$doc->number ?? ''}}</p>
                <p><strong>@lang('essentials::lang.doc_type'):</strong>
                    @if($doc->type === 'national_id')
                        @lang('essentials::lang.national_id')
                    @elseif($doc->type === 'passport')
                        @lang('essentials::lang.passport')
                    @elseif($doc->type === 'residence_permit')
                        @lang('essentials::lang.residence_permit')
                    @elseif($doc->type === 'drivers_license')
                        @lang('essentials::lang.drivers_license')
                    @elseif($doc->type === 'car_registration')
                        @lang('essentials::lang.car_registration')
                    @elseif($doc->type === 'international_certificate')
                        @lang('essentials::lang.international_certificate')
                    @else
                        {{$doc->type ?? ''}}
                    @endif
                </p>
                
                <p><strong>@lang('essentials::lang.issue_date'):</strong> {{$doc->issue_date ?? ''}}</p>
                <p><strong>@lang('essentials::lang.issue_place'):</strong> {{$doc->issue_place ?? ''}}</p>
                <p><strong>@lang('essentials::lang.expiration_date'):</strong> {{$doc->expiration_date ?? ''}}</p>
                <p><strong>@lang('essentials::lang.status'):</strong>
                    @if($doc->status === 'valid')
                        @lang('essentials::lang.valid')
                    @elseif($doc->status === 'expired')
                        @lang('essentials::lang.expired')
                    @else
                        {{$doc->status ?? ''}}
                    @endif
                </p>
                <p><strong>@lang('essentials::lang.employee'):</strong> {{$user->surname .' '.$user->first_name.' '.$user->lastname ?? ''}}</p>

            </div>
        </div>
        </div> <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">@lang('essentials::lang.close')</button>
        @if(!empty($doc->file_path))
        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="window.location.href = '/uploads/{{ $doc->file_path }}'">
            @lang('essentials::lang.view_doc')
        </button>
        @else
        <button type="button" class="btn btn-primary" data-dismiss="modal" >
            @lang('sales::lang.no_doc_file_to_show')
        </button>
        @endif
      </div>
      </div>
     
    </div>
</div>