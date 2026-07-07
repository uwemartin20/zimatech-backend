@extends('user.layouts.index')

@section('title', 'Lagerverwaltung')

@section('content')
<div class="container py-4" style="max-width: 720px;">

    <div class="text-center mb-4">
        <h1 class="fw-bold fs-2">🏭 Lagerverwaltung</h1>
        <p class="text-muted mb-0">Bitte wählen Sie ein Lager aus</p>
    </div>

    @if($lagers->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
            Keine Lager verfügbar.
        </div>
    @else
        <div class="row g-3">
            @foreach($lagers as $lager)
                <div class="col-12 col-md-6">
                    <a href="{{ route('tablar.index', $lager->id) }}"
                       class="card card-body text-decoration-none border shadow-sm h-100 lager-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="fs-1">🗄️</div>
                            <div>
                                <div class="fw-bold fs-5 text-dark">{{ $lager->name }}</div>
                                @if($lager->description)
                                    <div class="text-muted small">{{ $lager->description }}</div>
                                @endif
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-boxes me-1"></i>
                                    {{ $lager->materials_count ?? $lager->materials()->count() }} Materialien
                                </div>
                            </div>
                            <i class="bi bi-chevron-right ms-auto text-muted"></i>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

</div>

<style>
.lager-card {
    transition: box-shadow 0.2s, transform 0.15s;
    cursor: pointer;
}
.lager-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.10) !important;
    transform: translateY(-2px);
}
</style>
@endsection