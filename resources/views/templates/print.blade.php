{{-- resources/views/templates/print.blade.php --}}

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Template: {{ $template->name }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .template-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-header {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .content {
            text-align: justify;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="template-header">
        <h1>{{ $template->primary_header }}</h1>
        <h2>{{ $template->secondary_header }}</h2>
    </div>

    @foreach ($sections as $section)
        <div class="section">
            <div class="section-header" style="background-color: {{ $section->header_color }};">
                <div>{{ $section->header_left }}</div>
                <div>{{ $section->header_right }}</div>
            </div>
            <div class="content">
                <div>{!! $section->content_left !!}</div>
                <div>{!! $section->content_right !!}</div>
            </div>
        </div>
    @endforeach

    <button class="no-print" onclick="window.print();">Print</button>

</body>

</html>
