@extends('layouts.app')
@section('title', __('sales::lang.sales_templates'))

@section('content')
<section class="content-header">
    <h1>
        <span>@lang('sales::lang.sales_templates')</span>
    </h1>
</section>
@include('sales::layouts.nav_templates')

		<div class="box box-info">
			<div class="box-header with-border">
				<div class="box-tools pull-right">
					@if(auth()->user()->can('sales.add_proposal_template'))
					<a href="{{ action([\Modules\Sales\Http\Controllers\SalesTemplateController::class, 'getEdit'], ['id' => $proposal_template->id]) }}" class="btn btn-primary">
						@lang('messages.edit')
					</a>
				@endif
    				@can('sales.access_proposal')
    					<a href="{{action([\Modules\Sales\Http\Controllers\SalesTemplateController::class, 'send'], ['id' => $proposal_template->id]) }}" class="btn  btn-success">
    						@lang('sales::lang.send')
    					</a>
    				@endcan
				</div>
            </div>
            <div class="box-body">
            	<div class="row">
            		<div class="col-md-12">
						<p>
							<strong>CC:</strong> {{$proposal_template->cc}}
						</p>
					</div>
					<div class="col-md-12">
						<p>
							<strong>BCC:</strong> {{$proposal_template->bcc}}
						</p>
					</div>
            		<div class="col-md-12">
						<p>
							<strong>{{__('sales::lang.subject')}}:</strong> {{ trans('sales::lang.' . $proposal_template->subject) }}
						</p>
					</div>
					
				</div>
				<div class="row mt-10">
					<div class="col-md-12">
						<p>
							<strong>{{__('sales::lang.email_body')}}:</strong> {!!$proposal_template->body!!}
						</p>
					</div>
				</div>
				@if($proposal_template->media->count() > 0)
					<hr>
					<div class="row">
						<div class="col-md-6">
							<h4>
								{{__('sales::lang.attachments')}}
							</h4>
							@includeIf('sales::templates.partials.attachment', ['medias' => $proposal_template->media])
						</div>
					</div>
				@endif
			</div>
		</div>
	</section>
@endsection
@section('javascript')
<script type="text/javascript">
	$(function () {
	    $(document).on('click', 'a.delete_attachment', function (e) {
            e.preventDefault();
            var url = $(this).data('href');
            var this_btn = $(this);
            swal({
                title: LANG.sure,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((confirmed) => {
                if (confirmed) {
                    $.ajax({
                        method: 'DELETE',
                        url: url,
                        dataType: 'json',
                        success: function(result) {
                            if(result.success == true){
			                    this_btn.closest('tr').remove();
			                    toastr.success(result.msg);
			                } else {
			                    toastr.error(result.msg);
			                }
                        }
                    });
                }
            });
        });
	});
</script>
@endsection
