@extends('layouts.app')

@section('title', 'template')

@section('content')
    <!-- Main content -->
    <section class="content">


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
    </section>
@endsection
