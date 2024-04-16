@extends('layouts.app')


@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header" style="height: 90vh">
        <div class="reports-card-grid">

            @foreach ($cardsOfReports as $card)
                <div class="col-md-3">
                    <div class="reports-card">
                        <a href="{{ $card['link'] }}" class="reports-card-link">
                            <div class="reports-card-content">
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
