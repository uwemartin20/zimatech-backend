@extends('admin.layouts.index')

@section('title', ($template ? 'Preview Email Template' : 'Create Custom Email') . ' for Offer #'.$offer->id)

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $template ? 'Preview Template: '.$template->subject : 'Create Custom Email' }}</h5>
            <a href="{{ route('admin.project_offers.email-templates', $offer) }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Zuruck
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.project_offers.send_email', $offer) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="recipient" class="form-label fw-bold">An</label>
                    <input type="email" name="recipient" id="recipient" class="form-control" value="{{ old('recipient', $offer->customer_email) }}" required>
                </div>

                <div class="mb-3">
                    <label for="subject" class="form-label fw-bold">Subject</label>
                    <input type="text" name="subject" id="subject" class="form-control" value="{{ old('subject', $subject) }}" required>
                </div>

                <ul class="nav nav-tabs mb-3" id="emailTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="html-tab" data-bs-toggle="tab" data-bs-target="#html" type="button" role="tab">
                            HTML Preview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="plain-tab" data-bs-toggle="tab" data-bs-target="#plain" type="button" role="tab">
                            Plain Text Bearbeiten
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- HTML Preview Tab -->
                    <div class="tab-pane fade show active border p-3" id="html" role="tabpanel">
                        {!! $body !!}
                    </div>

                    <!-- Plain Text Editing Tab -->
                    <div class="tab-pane fade" id="plain" role="tabpanel">
                        <textarea name="body" class="form-control" rows="15">{{ $body }}</textarea>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="attachments" class="form-label fw-bold">Anhangen</label>
                    <input type="file" name="attachments[]" id="attachments" class="form-control" multiple>
                    <small class="text-muted">Sie konnen Anhagen fur das email hochladen.</small>
                </div>

                <button type="submit" class="btn btn-wechsel">
                    <i class="bi bi-envelope-paper me-1"></i> Email Schicken
                </button>

                <a href="{{ route('admin.project_offers.show', $offer) }}" class="btn btn-secondary ms-2">
                    <i class="bi bi-arrow-left me-1"></i> Zuruck zum Angebot
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
