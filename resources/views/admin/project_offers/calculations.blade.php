@extends('admin.layouts.index')

@section('content')
<div class="container mt-4">

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-calculator"></i> Angebotskalkulation – {{ $offer->subject }}
            </h5>

            <a href="{{ route('admin.project_offers.items.create', $offer->id) }}"
               class="btn btn-sm btn-secondary">
                <i class="bi bi-plus-circle"></i> Neue Kalkulations-Position
            </a>
        </div>

        <div class="card-body">

            {{-- Calculations Table --}}
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Bezeichnung</th>
                            <th>Stunden</th>
                            <th>Betrag(€)</th>
                            <th>Material(€)</th>
                            <th>Fremd-Leistung(€)</th>
                            <th>Stück</th>
                            <th>Einzelpreis</th>
                            <th>Gesamt</th>
                            <th>Angebot je Stück</th>
                            <th>Angebot Gesamt</th>
                            <th>Notizen</th>
                            <th style="width: 180px;"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($calculations as $index => $calc)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $calc->designation }}</td>
                                <td>{{ $calc->hours }}</td>
                                <td>{{ number_format($calc->cost, 2, ',', '.') }}</td>
                                <td>{{ number_format($calc->material_cost, 2, ',', '.') }}</td>
                                <td>{{ number_format($calc->external_cost, 2, ',', '.') }}</td>
                                <td>{{ $calc->pieces }}</td>
                                <td>{{ number_format($calc->total_cost, 2, ',', '.') }}</td>
                                <td>{{ number_format($calc->gesamt_kosten, 2, ',', '.') }}</td>
                                <td>{{ number_format($calc->offer_cost, 2, ',', '.') }}</td>
                                <td class="text-success">{{ number_format($calc->gesamt_angebot, 2, ',', '.') }}</td>
                                <td>{{ $calc->notes }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.project_offers.calculation.show', [$offer, $calc]) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.project_offers.items.edit', [$offer, $calc]) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.project_offers.calculation.duplicate', [$offer, $calc]) }}" 
                                        method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Duplicate">
                                            <i class="bi bi-files"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.project_offers.calculation.destroy', [$offer->id, $calc->id]) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Wirklich löschen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center text-muted">Keine Kalkulationen vorhanden.</td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($calculations->count())
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="7" class="text-end">SUMME:</td>
                                <td>{{ number_format($calculations->sum('total_cost'), 2, ',', '.') }}</td>
                                <td>{{ number_format($calculations->sum(fn($c)=>$c->gesamt_kosten), 2, ',', '.') }}</td>
                                <td>{{ number_format($calculations->sum('offer_cost'), 2, ',', '.') }}</td>
                                <td class="text-success">
                                    {{ number_format($calculations->sum(fn($c)=>$c->gesamt_angebot), 2, ',', '.') }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    @endif

                </table>
            </div>

            <div>
                <a href="{{ route('admin.project_offers.calculation.complete', $offer->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-envelope"></i> Kalkuliert
                </a>

                <a href="{{ route('admin.project_offers.show', $offer->id) }}" class="btn btn-wechsel">
                    <i class="bi bi-arrow-left-circle"></i> Zuruck
                </a>
            </div>

        </div>

    </div>

</div>
@endsection
