<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@lang('sales::lang.order_operation_details')</title>
    <link rel="stylesheet" href="path_to_your_css_styles">
    <script src="path_to_your_js_scripts"></script>
</head>

<body>



    <div class="container">
        <div class="header"
            style="background-color: {{ $template->header_color }}; border: 1px solid #494949; display: flex; flex-direction:column; align-items: center;">
            {!! $template->primary_header !!}
        </div>

        @foreach ($sections as $section)
            <div class="template-section" style="background-color:#ffffff;">
                @if ($section->header_left || $section->header_right)
                    <div style="display: flex; background-color: {{ $section->header_color }};">
                        <div
                            style="flex: 1; padding:0 10px; text-align: right; direction: rtl; border: 1px solid #494949;">
                            {!! $section->header_right !!}
                        </div>
                        <div
                            style="flex: 1; padding:0 10px; text-align: left; direction: ltr; border: 1px solid #494949;">
                            {!! $section->header_left !!}
                        </div>
                    </div>
                @endif

                @if (isset($section->content))
                    <div style="border: 1px solid #494949; padding: 10px;">
                        {!! $section->content !!}
                    </div>
                @else
                    <div style="display: flex;">
                        <div style="flex: 1; padding: 10px; direction: rtl; border: 1px solid #494949;">
                            {!! $section->content_right !!}
                        </div>
                        <div style="flex: 1; padding: 10px; direction: ltr; border: 1px solid #494949;">
                            {!! $section->content_left !!}
                        </div>
                    </div>
                @endif
            </div>
        @endforeach

        @if (!empty($template->primary_footer))
            <div class="footer"
                style="background-color: {{ $template->primary_footer }}; border: 1px solid #494949; display: flex; flex-direction:column; align-items: center;">
                {!! $template->primary_footer !!}
            </div>
        @endif
    </div>


    <script>
        // Add any necessary JS here
        // Automatically trigger the print dialog when the page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>
