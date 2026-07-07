@extends('user.layouts.index')

@section('title', 'Problem bearbeiten – ' . $problem->problem_uid)

@section('content')
<div class="container py-4">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('printer-problems.show', $problem->id) }}" class="text-muted text-decoration-none small">
            <i class="bi bi-arrow-left"></i> Zurück
        </a>
        <span class="text-muted small">/</span>
        <h5 class="mb-0 fw-semibold">
            Problem bearbeiten
            <span class="text-muted fw-normal fs-6 ms-1">{{ $problem->problem_uid }}</span>
        </h5>
        <span class="{{ $problem->status == 'open' ? 'badge bg-danger' : 'badge bg-success' }}">
            {{ $problem->status == 'open' ? 'Offen' : 'Geschlossen' }}
        </span>
    </div>

    @include('user.printer-problems.__form', [
        'problem'  => $problem,
        'action'   => route('printer-problems.update', $problem->id),
        'method'   => 'PUT',
        'statuses' => $statuses,
    ])

</div>
@endsection