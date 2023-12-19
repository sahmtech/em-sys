@extends('layouts.app')
@section('title', __('sales::lang.sales_templates'))

@section('content')
<section class="content-header">
    <h1>
        <span>@lang('sales::lang.sales_templates')</span>
    </h1>
</section>
@include('sales::layouts.nav_templates')
		@component('components.widget', ['class' => 'box-solid'])
			{!! Form::open(['url' => action([\Modules\Sales\Http\Controllers\SalesTemplateController::class, 'store']), 'method' => 'post', 'id' => 'proposal_form']) !!}
				<div class="row">
					<div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('contact_id', __('sales::lang.send_to') .':*') !!}
                            {!! Form::select('contact_id', $contacts, null, ['class' => 'form-control select2', 'id' => 'proposal_contact', 'style' => 'width: 100%;', 'required', 'placeholder' => __('messages.please_select')]); !!}
                        </div>
                    </div>
				</div>
				@includeIf('sales::templates.partials.template_form', ['proposal_template' => $proposal_template, 'attachments' => false])
				@if($proposal_template->media->count() > 0)
					<hr>
					<div class="row">
						<div class="col-md-6">
							<h4>
								{{__('sales::lang.attachments')}}
							</h4>
							@includeIf('sales::proposal_template.partials.attachment', ['medias' => $proposal_template->media])
						</div>
					</div>
				@endif
				<button type="submit" class="btn btn-primary ladda-button pull-right m-5" data-style="expand-right">
                    <span class="ladda-label">@lang('sales::lang.send')</span>
                </button>
			{!! Form::close() !!}
    	@endcomponent
	</section>
@endsection
@section('javascript')
<script type="text/javascript">
	$(function () {
		tinymce.init({
	        selector: 'textarea#proposal_email_body',
	        height: 350,
	    });

        $('form#proposal_form').validate({
	        submitHandler: function(form) {
	            form.submit();
	            let ladda = Ladda.create(document.querySelector('.ladda-button'));
    			ladda.start();
	        }
	    });
	    
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