@extends('layouts.app')


@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">

        <div class="accounting-card-grid">
            @foreach ($cardsOfCompanies as $card)
                <div class="col-md-3">
                    <div class="accounting-card">
                        <a href="{{ $card['link'] }}" class="accounting-card-link">
                            <div class="accounting-card-content">
                                <h3>{{ $card['name'] }}</h3>
                                {{-- <i class="fa fa-{{ $card['icon'] }}"></i> --}}
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>




    </section>

    <!-- Main content -->
    <section class="content">




    </section>
    <!-- /.content -->
@stop

@section('javascript')

@endsection
