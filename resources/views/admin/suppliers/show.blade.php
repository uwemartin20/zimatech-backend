@extends('admin.layouts.index')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $supplier->name }}</h5>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left-circle"></i> Zurück
            </a>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Firma:</strong> {{ $supplier->company }}</p>
                    <p><strong>Adresse:</strong> {{ $supplier->address ?? '—' }}</p>
                    <p><strong>Telefon:</strong> {{ $supplier->phone_number ?? '—' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> {{ $supplier->email ?? '—' }}</p>
                    <p><strong>Website:</strong>
                        @if ($supplier->website)
                            <a href="{{ $supplier->website }}" target="_blank">{{ $supplier->website }}</a>
                        @else
                            —
                        @endif
                    </p>
                </div>
            </div>

            <h6 class="fw-bold">Dienstleistungen</h6>
            <div class="mb-3">
                @forelse ($supplier->services as $service)
                    <span class="badge text-white me-1" style="background-color: {{ $service->color }}">
                        {{ $service->name }}
                    </span>
                @empty
                    <span class="text-muted">Keine Dienstleistungen zugeordnet.</span>
                @endforelse
            </div>

            <h6 class="fw-bold mt-4">Angebote</h6>
            @if ($supplier->offers->isEmpty())
                <p class="text-muted">Keine Angebote gefunden.</p>
            @else
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Datum</th>
                            <th>Bauteil</th>
                            <th>Preis</th>
                            <th>Angebot Nr.</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($supplier->offers as $offer)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($offer->date)->format('d.m.Y') }}</td>
                                <td>{{ $offer->bauteil->name ?? '—' }}</td>
                                <td>{{ number_format($offer->price, 2, ',', '.') }} €</td>
                                <td>
                                    <a href="{{ route('admin.suppliers.offers.show', $offer->id) }}" class="text-decoration-none">
                                        {{ $offer->offer_number }}
                                    </a>
                                </td>
                                <td>
                                    @php
                                        $status = $offer->projects->first()?->status;
                                    @endphp
                                    @if($status)
                                        <span class="badge bg-info">{{ $status->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">Keine</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
