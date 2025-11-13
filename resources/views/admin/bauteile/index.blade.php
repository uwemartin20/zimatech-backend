@extends('admin.layouts.index')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Alle Bauteile</h5>
            <a href="{{ route('admin.bauteile.create') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Neues Bauteil
            </a>
        </div>

        <div class="card-body">
            <div class="mb-3 d-flex gap-2">
                <a href="{{ route('admin.bauteile.index') }}" class="btn btn-secondary btn-sm">Alle Bauteile</a>
                <a href="{{ route('admin.bauteile.filter', 'werkzeug') }}" class="btn btn-primary btn-sm">Werkzeuge</a>
                <a href="{{ route('admin.bauteile.filter', 'baugruppe') }}" class="btn btn-success btn-sm">Baugruppen</a>
            </div>
            {{-- Recursive Tree --}}
            @include('admin.components.bauteil-tree', ['bauteile' => $bauteile])
        </div>
    </div>
</div>
@endsection
