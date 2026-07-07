@extends('admin.layouts.index')

@section('content')

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tablar Details</h5>
            <a href="{{ route('admin.tablar.index', ['lager_id' => $material->lager_id]) }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-list"></i> Alle Tablar
            </a>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Material:</strong> {{ $material->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Code:</strong> {{ $material->code }}
                    </div>
                    <div class="mb-3">
                        <strong>Beschreibung:</strong> {{ $material->description }}
                    </div>
                    <div class="mb-3">
                        <strong>Menge:</strong> {{ $material->quantity }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection