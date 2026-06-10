@extends('user.layouts.index')

@section('title', 'ZiMaTec GmbH - Ihr Partner für Industrie-Lösungen')

@section('content')
    
    {{-- === Hero Section === --}}
    <div class="hero-section text-white d-flex align-items-center mb-5 shadow">
        <div class="container text-center text-md-start">
            <div class="row align-items-center py-5">
                <div class="col-lg-7">
                    <h1 class="display-3 fw-bold mb-3">
                        {{ __('Exzellenz in jedem Arbeitsschritt') }}
                    </h1>
                    <p class="lead mb-4 opacity-75">
                        {{ __('Willkommen in Ihrem zentralen Arbeitsportal. Gemeinsam setzen wir modernste Mess- und CAD-Systeme ein, um präzise Lösungen für unsere Kunden zu schaffen.') }}
                    </p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-start">
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5 shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Anmelden
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg px-5">
                                <i class="bi bi-person-plus me-1"></i> Registrieren
                            </a>
                        @else
                            <a href="{{ route('projects.logs') }}" class="btn btn-success btn-lg px-5 shadow-sm">
                                <i class="bi bi-speedometer2 me-1"></i> {{ __('Projekte Anzeigen') }}
                            </a>
                            <a href="{{ route('printer-problems.index') }}" class="btn btn-light btn-lg px-5 text-navitem shadow-sm">
                                <i class="bi bi-printer me-1"></i> {{ __('Druckprobleme') }}
                            </a>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- === Services Section === --}}
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-title display-6">{{ __('Unsere Leistungen') }}</h2>
            <div class="mx-auto bg-primary rounded-pill" style="width: 60px; height: 4px;"></div>
            <p class="text-muted mt-3">{{ __('Effiziente Werkzeuge für Ihren Arbeitsalltag') }}</p>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            @foreach ($leistungen as $leistung)
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 service-card">
                        <div class="service-img-wrapper overflow-hidden">
                            <img src="{{ asset($leistung['image']) }}" class="card-img-top" alt="{{ $leistung['name'] }}" height="200" style="object-fit: cover;">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-semibold text-title">{{ $leistung['name'] }}</h5>
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

    {{-- === About Us Section === --}}
    <div class="bg-light py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="{{ asset('images/logo-team-zimmermann.png') }}" alt="ZiMaTec Team" class="img-fluid rounded shadow-sm p-4 bg-white mb-4" style="max-height: 120px">
                    <h2 class="fw-bold text-title">{{ __('Unser Anspruch an Qualität') }}</h2>
                    <p class="lead text-muted">
                        {{ __('Wir bündeln unsere Kompetenzen in Technik, Messtechnik und Konstruktion.') }}
                    </p>
                    <p class="text-muted">
                        {{ __('Dieses Portal unterstützt uns dabei, zielführende Lösungen für komplexe Probleme zu finden egal ob in der Fertigung oder Konstruktion. Nutzen Sie die verfügbaren Daten und Tools, um Projekte effizient abzuwickeln und unsere langjährige Erfahrung gemeinsam optimal einzusetzen. Vom Spritzgussverfahren bis zur optischen Vermessung.') }}
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-4 bg-white rounded shadow-sm text-center h-100 feature-box">
                                <i class="bi bi-shield-check text-success fs-1"></i>
                                <h6 class="fw-bold mt-2">Zuverlässig</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-4 bg-white rounded shadow-sm text-center h-100 feature-box">
                                <i class="bi bi-lightning-charge text-warning fs-1"></i>
                                <h6 class="fw-bold mt-2">Effizient</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-4 bg-white rounded shadow-sm text-center h-100 feature-box">
                                <i class="bi bi-gear text-primary fs-1"></i>
                                <h6 class="fw-bold mt-2">Innovativ</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-4 bg-white rounded shadow-sm text-center h-100 feature-box">
                                <i class="bi bi-graph-up text-danger fs-1"></i>
                                <h6 class="fw-bold mt-2">Wachstum</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- === Statistics Section === --}}
    <div class="container py-5 text-center">
        <div class="row g-4 justify-content-center py-4">
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <h2 class="fw-bold text-title display-5">{{ $stats['projects'] ?? 0 }}</h2>
                    <p class="text-muted text-uppercase small fw-bold mb-0">Projekte</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <h2 class="fw-bold text-title display-5">{{ $stats['machines'] ?? 0 }}</h2>
                    <p class="text-muted text-uppercase small fw-bold mb-0">Maschinen</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <h2 class="fw-bold text-title display-5">{{ $stats['users'] ?? 0 }}</h2>
                    <p class="text-muted text-uppercase small fw-bold mb-0">Benutzer</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <h2 class="fw-bold text-title display-5">{{ $stats['processes'] ?? 0 }}</h2>
                    <p class="text-muted text-uppercase small fw-bold mb-0">Prozesse</p>
                </div>
            </div>
        </div>
    </div>

    {{-- === CTA Section === --}}
    <div class="cta-section text-white py-5 shadow-lg mb-0">
        <div class="container text-center py-4">
            <h3 class="fw-bold mb-3 display-6">{{ __('Gemeinsam besser werden') }}</h3>
            <p class="mb-4 opacity-75 fs-5">{{ __('Haben Sie Feedback zum System oder Verbesserungsvorschläge für unsere internen Prozesse? Lassen Sie es uns wissen.') }}</p>
            <a href="{{ env('FEEDBACK_FORM_URL', '#') }}" class="btn btn-primary btn-lg px-5 shadow-lg">{{ __('Feedback geben') }}</a>
        </div>
    </div>

@endsection

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(rgba(0, 39, 82, 0.9), rgba(0, 39, 82, 0.8)), 
                    url('/images/hero-bg.jpg') center/cover no-repeat;
        background-color: #002752; /* Fallback */
        min-height: 450px;
    }
    
    .text-title {
        color: #002752 !important;
    }
    
    .service-card {
        transition: all 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .service-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.15)!important;
    }
    
    .service-img-wrapper img {
        transition: transform 0.6s ease;
    }
    
    .service-card:hover .service-img-wrapper img {
        transform: scale(1.1);
    }
    
    .feature-box {
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }
    
    .feature-box:hover {
        background-color: #f8f9fa !important;
        transform: scale(1.05);
    }
    
    .cta-section {
        background: linear-gradient(135deg, #002752 0%, #001a3d 100%);
    }
    
    .stat-item {
        padding: 20px;
        border-right: 1px solid #dee2e6;
    }
    
    .stat-item:last-child {
        border-right: none;
    }
    
    .btn-primary {
        background-color: #002752;
        border-color: #002752;
    }
    
    .btn-primary:hover {
        background-color: #001a3d;
        border-color: #001a3d;
    }
    
    @media (max-width: 768px) {
        .stat-item {
            border-right: none;
            border-bottom: 1px solid #dee2e6;
        }
        .stat-item:last-child {
            border-bottom: none;
        }
        .hero-section {
            min-height: auto;
            padding: 60px 0;
        }
    }
</style>
@endpush
