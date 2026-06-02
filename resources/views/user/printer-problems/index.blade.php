@extends('user.layouts.index')

@section('title', 'Druckerprobleme')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-semibold">Druckerprobleme</h4>
            <p class="text-muted small mb-0">Übersicht aller gemeldeten Maschinenprobleme</p>
        </div>
        <a href="{{ route('printer-problems.create') }}" class="btn btn-wechsel btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Neues Problem
        </a>
    </div>

    {{-- Search / Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('printer-problems.index') }}" class="row g-2 align-items-end">

                <div class="col-12 col-md-3">
                    <label class="form-label small fw-medium mb-1">Suche</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                           class="form-control form-control-sm"
                           placeholder="Beschreibung, Material…">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-medium mb-1">Problem-ID</label>
                    <input type="text" name="problem_uid" value="{{ $filters['problem_uid'] ?? '' }}"
                           class="form-control form-control-sm" placeholder="PRB-2026-…">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-medium mb-1">Fehler-ID</label>
                    <input type="text" name="error_id" value="{{ $filters['machine_error_id'] ?? '' }}"
                           class="form-control form-control-sm">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-medium mb-1">Material</label>
                    <input type="text" name="material" value="{{ $filters['material'] ?? '' }}"
                           class="form-control form-control-sm">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-medium mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Alle</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}"
                                {{ ($filters['status'] ?? '') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-wechsel btn-sm w-100">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('printer-problems.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>

            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 small fw-semibold">Problem-ID</th>
                        <th class="small fw-semibold">Fehler-ID</th>
                        <th class="small fw-semibold">Kurzbeschreibung</th>
                        <th class="small fw-semibold">Material</th>
                        <th class="small fw-semibold">Status</th>
                        <th class="small fw-semibold">Erstellt</th>
                        <th class="small fw-semibold">Erstellt von</th>
                        <th class="pe-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($problems as $problem)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('printer-problems.show', $problem->id) }}"
                                   class="fw-medium text-decoration-none">
                                    {{ $problem->problem_uid }}
                                </a>
                            </td>
                            <td class="text-muted small">{{ $problem->error_id ?? '—' }}</td>
                            <td>{{ Str::limit($problem->short_description, 60) }}</td>
                            <td class="text-muted small">{{ $problem->material ?? '—' }}</td>
                            <td>
                                <span class="{{ $problem->status == 'open' ? 'badge bg-warning' : 'badge bg-success' }}">
                                    {{ $problem->status == 'open' ? 'Offen' : 'Geschlossen' }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $problem->created_at->format('d.m.Y') }}</td>
                            <td class="text-muted small">{{ $problem->creator->name ?? '—' }}</td>
                            <td class="pe-3 text-end">
                                <a href="{{ route('printer-problems.show', $problem->id) }}"
                                   class="btn btn-sm btn-outline-primary py-0 px-2 me-1">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('printer-problems.edit', $problem->id) }}"
                                   class="btn btn-sm btn-outline-secondary py-0 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                Keine Probleme gefunden.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($problems->hasPages())
            <div class="card-footer bg-white border-top py-2 px-3">
                {{ $problems->links() }}
            </div>
        @endif
    </div>

</div>
@endsection