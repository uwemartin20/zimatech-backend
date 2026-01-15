@extends('admin.layouts.index')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-folder2-open me-2"></i> Subjekt: {{ $offer->subject }}
            </h5>
            <a href="{{ route('admin.project_offers.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left-circle"></i> Zurück
            </a>
        </div>

        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div><strong>Customer:</strong> {{ $offer->customer_name }}</div>
                <div><strong>Email:</strong> {{ $offer->customer_email }}</div>
                <div><strong>Status:</strong> <span class="badge bg-info text-dark">{{ ucfirst($offer->status) }}</span></div>
                @if($offer->assignedUser)
                    <div><strong>Assigned To:</strong> {{ $offer->assignedUser->name }}</div>
                @endif
            </div>
            <hr>

            <div class="row">
                <div class="col-md-8">
                    {{-- Calculations --}}
                    <h5><a href="{{ route('admin.project_offers.calculations', $offer) }}" class="text-decoration-none text-dark">Calculations</a></h5>
                    @if($offer->calculations->count())
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="bi bi-calculator"></i> Kalkulationsübersicht
                                </h6>
                                <a href="{{ route('admin.project_offers.calculation.pdf', $offer) }}" class="btn btn-wechsel btn-sm">
                                    <i class="bi bi-save"></i> PDF
                                </a>
                            </div>

                            <ul class="list-group list-group-flush">
                                @foreach($offer->calculations as $calc)
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $calc->designation }}</strong>
                                                @if($calc->notes)
                                                    <small class="text-muted d-block">{{ $calc->notes }}</small>
                                                @endif
                                                <small class="text-muted">
                                                    {{ $calc->pieces }} Stück · {{ number_format($calc->hours, 2, ',', '.') }} Std.
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <div><span class="text-muted">Gesamt:</span> <strong>{{ number_format($calc->total_cost, 2, ',', '.') }} €</strong></div>
                                                <small class="text-success">Angebot: {{ number_format($calc->offer_cost, 2, ',', '.') }} €</small>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            {{-- Totals Summary --}}
                            <div class="card-footer bg-white text-end">
                                <div class="fw-bold">
                                    Gesamtstücke: 
                                    <span class="text-primary">{{ $offer->calculations->sum('pieces') }}</span>
                                    &nbsp;|&nbsp;
                                    Gesamtkosten: 
                                    <span class="text-dark">{{ number_format($offer->calculations->sum('total_cost'), 2, ',', '.') }} €</span>
                                    &nbsp;|&nbsp;
                                    Angebotspreis: 
                                    <span class="text-success">{{ number_format($offer->calculations->sum('offer_cost'), 2, ',', '.') }} €</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted">Keine Kalkulationen hinzugefügt.</p>
                    @endif

                    <h5>⚙️ Actions</h5>
                    <a href="{{ route('admin.project_offers.accept', $offer) }}" class="btn btn-success">Mark as Accepted</a>
                    <a href="{{ route('admin.project_offers.email-templates', $offer) }}" class="btn btn-outline-primary">Schick Neue Email</a>
                    <a href="{{ route('admin.project_offers.edit', $offer) }}" class="btn btn-warning">Edit Offer</a>
                </div>

                <div class="col-md-4">
                    {{-- General Files --}}
                    <h5>General Files</h5>
                    @if($offer->files->count())
                        <ul class="list-group mb-4">
                            @foreach($offer->files as $file)
                                @if(!$file->email_id)
                                <li class="list-group-item d-flex justify-content-between">
                                    <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank">{{ $file->file_name }}</a>
                                    <small>{{ $file->created_at->format('d M Y') }}</small>
                                </li>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No general files uploaded.</p>
                    @endif

                    {{-- Emails --}}
                    <h5>Emails</h5>
                    @if($offer->emails->count())
                    <div class="accordion mb-4" id="emailsAccordion">
                        @foreach($offer->emails as $email)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $email->id }}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#email{{ $email->id }}">
                                        {{ ucfirst($email->direction) }}: {{ $email->subject }}
                                    </button>
                                </h2>
                                <div id="email{{ $email->id }}" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        {!! nl2br(e($email->body)) !!}

                                        {{-- Email attachments --}}
                                        @if($email->files->count())
                                            <hr>
                                            <strong>Attachments:</strong>
                                            <ul class="list-group mt-2">
                                                @foreach($email->files as $efile)
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <a href="{{ asset('storage/'.$efile->file_path) }}" target="_blank">{{ $efile->file_name }}</a>
                                                        <small>{{ $efile->created_at->format('d M Y') }}</small>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        <a href="{{ route('admin.project_offers.edit_email', $email) }}" class="btn btn-secondary my-3">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @else
                        <p class="text-muted">No emails added yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
