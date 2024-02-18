<head>
    <!-- Other head content like title, meta tags, etc. -->
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body, html {
                width: 100%;
            }
            .modal-dialog {
                width: 100%;
                max-width: none; /* May help with modals that use max-width */
            }
            .modal-content {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            .modal-header, .modal-footer {
                display: none; /* Hide elements you don't want to print */
            }
            /* Ensure modal body content is allowed to break across pages */
            .modal-body, .template-section {
                page-break-inside: auto;
            }
        }
    </style>
</head>

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
            <h4 class="modal-title text-center" id="exampleModalLabel">
                @lang('sales::lang.order_operation_details')
            </h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <div class="row">

                <div class="col-md-12">
                    <div class="col-md-12">
                        <div
                            style="background-color: {{ $template->header_color }}; border: 1px solid #494949; display: flex;  flex-direction:column; align-items: center;">
                            {!! $template->primary_header !!}
                            {{-- <h3>{{ $template->secondary_header }}</h3> --}}
                        </div>
                    </div>
                    @foreach ($sections as $section)
                        <div class="col-md-12">
                            <div class="template-section">

                                @if ($section->header_left || $section->header_right)
                                    <div style="display: flex; background-color: {{ $section->header_color }}; ">
                                        <div
                                            style="flex: 1;  padding:0 10px; text-align: right; direction: rtl; border-bottom: 1px solid #494949;  border-right: 1px solid #494949;  border-left: 1px solid #494949;">
                                            {!! $section->header_right !!}
                                        </div>
                                        <div
                                            style="flex: 1;  padding:0 10px; text-align: left; direction: ltr; border-bottom: 1px solid #494949 ; border-left: 1px solid #494949;">
                                            {!! $section->header_left !!}
                                        </div>
                                    </div>
                                @endif

                                @if (isset($section->content))
                                    <div
                                        style="border-bottom: 1px solid #494949;  padding: 10px; border-right: 1px solid #494949;  border-left: 1px solid #494949;">
                                        {!! $section->content !!}
                                    </div>
                                @else
                                    <div style="display: flex;">
                                        <div
                                            style="flex: 1; padding: 10px; direction: rtl; border-bottom: 1px solid #494949;  border-right: 1px solid #494949;  border-left: 1px solid #494949;">
                                            {!! $section->content_right !!}</div>
                                        <div
                                            style="flex: 1; padding: 10px; direction: ltr; border-bottom: 1px solid #494949 ; border-left: 1px solid #494949;">
                                            {!! $section->content_left !!}</div>

                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    @if (!empty($template->primary_footer))
                        <div class="col-md-12">
                            <div
                                style="background-color: {{ $template->primary_footer }}; border-bottom: 1px solid #494949;  border-right: 1px solid #494949;  border-left: 1px solid #494949; display: flex;  flex-direction:column; align-items: center;">
                                {!! $template->primary_footer !!}
                                {{-- <h3>{{ $template->secondary_header }}</h3> --}}
                            </div>
                        </div>
                    @endif
                </div>
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-primary no-print" aria-label="Print"
                onclick="$(this).closest('div.modal').printThis();">
                <i class="fa fa-print"></i> @lang('messages.print')
            </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
            {{-- {!! Form::close() !!} --}}
        </div>



    </div>
</div>
