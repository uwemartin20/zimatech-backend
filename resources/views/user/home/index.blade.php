@extends('user.layouts.index')

@section('title', 'Welcome to Your CMS')

@section('content')
    
    <div class="container py-5 text-center">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <h1 class="display-5 fw-bold mb-3">{{ __('Wilkommen bei') }} <span class="text-title">ZiMaTec GmbH</span></h1>
                <p class="lead text-muted mb-4">
                    {{ __('Manage Zeit, Projekte, Benutzer, und enistellungen alle auf ein platz.') }}
                </p>

                <div class="d-flex justify-content-center gap-3">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-register px-4">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Anmelden
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-person-plus me-1"></i> Registieren
                        </a>
                    @else
                        <a href="{{ route('projects.logs') }}" class="btn btn-success px-4">
                            <i class="bi bi-speedometer2 me-1"></i> {{ __('Projekte Anzeigen') }}
                        </a>

                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-warning px-4">
                                <i class="bi bi-gear-wide-connected me-1"></i> Admin Dashboard
                            </a>
                        @endif
                    @endguest
                </div>

            </div>
        </div>
    </div>
    {{-- === Four-Card Dashboard Section === --}}
    {{-- @auth --}}
    <div class="container py-5">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">

            {{-- Service Cards --}}
            @foreach ($leistungen as $leistung)
                <div class="col">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="{{ asset($leistung['image']) }}" class="card-img-top" alt="Projects" height="200">
                        <div class="card-body">
                            <h5 class="card-title fw-semibold">{{ $leistung['name'] }}</h5>
                            <p class="card-text text-muted small">
                                {{ $leistung['description'] }}
                            </p>
                            <a href="{{ route($leistung['route']) }}" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
    {{-- @endauth --}}
@endsection
