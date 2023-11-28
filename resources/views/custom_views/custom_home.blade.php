@extends('layouts.custom_layouts.custom_home_layout')
@section('title', __('home.home'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header content-header-custom parent-div">
        <div class="card-grid">
            @foreach ($cards as $card)
                <div class="card">
                    <a href="{{ $card['link'] }}" class="card-link">

                        <div class="card-content">
                            <h3>{{ $card['title'] }}</h3>
                            <i class="fa fa-{{ $card['icon'] }}"></i>
                        </div>
                    </a>

                </div>
            @endforeach
        </div> 


      
    </section>
    <!-- Main content -->
    {{-- <section class="content content-custom no-print">

    </section> --}}
    <!-- /.content -->

@stop
