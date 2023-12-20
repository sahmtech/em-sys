@extends('layouts.app')
@section('title', __('sales::lang.sales_templates'))

@section('content')
<section class="content-header">
    <h1>
        <span>@lang('sales::lang.sales_templates')</span>
    </h1>
</section>
@include('sales::layouts.nav_templates')
	
	<!-- Content Header (Page header) -->
	<section class="content-header no-print">
	   <h1>
	   		@lang('crm::lang.proposal_template')
	   		<small>@lang('lang_v1.create')</small>
	   </h1>
	</section>
	<!-- Main content -->
	<section class="content">
		@component('components.widget', ['class' => 'box-solid'])
			{!! Form::open(['url' => action([\Modules\Sales\Http\Controllers\SalesTemplateController::class, 'store']), 'method' => 'post', 'id' => 'proposal_template_form', 'files' => true]) !!}
				@includeIf('sales::templates.partials.template_form', ['attachments' => true])
				<button type="submit" class="btn btn-primary ladda-button pull-right m-5" data-style="expand-right">
                    <span class="ladda-label">@lang('messages.save')</span>
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

     	//initialize file input
        $('#attachments').fileinput({
            showUpload: false,
            showPreview: false,
            browseLabel: LANG.file_browse_label,
            removeLabel: LANG.remove
        });

        $('form#proposal_template_form').validate({
	        submitHandler: function(form) {
	            form.submit();
	            let ladda = Ladda.create(document.querySelector('.ladda-button'));
    			ladda.start();
	        }
	    });
	});
</script>
@endsection