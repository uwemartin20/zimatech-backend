@extends('admin.layouts.index')

@section('title', 'View Email Template')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Email Template Details - {{ $emailTemplate->subject }}</h5>
            <a href="{{ route('admin.settings.email_templates.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-envelope-paper me-1"></i> All Email Templates
            </a>
        </div>

        <div class="card-body">
            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Subject:</label>
                <div class="col-sm-10">
                    <p class="form-control-plaintext">{{ $emailTemplate->subject }}</p>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Description:</label>
                <div class="col-sm-10">
                    <div class="border rounded p-2 bg-light" style="white-space: pre-line;">
                        {{ $emailTemplate->description }}
                    </div>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Template Type:</label>
                <div class="col-sm-10">
                    <span class="badge bg-primary">
                        {{ ucfirst(str_replace('_',' ',$emailTemplate->template_type)) }}
                    </span>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Notes / Tags:</label>
                <div class="col-sm-10">
                    <div class="border rounded p-2 bg-light">
                        {{ $emailTemplate->note ?? 'No tags defined.' }}
                    </div>
                    <small class="text-muted">You can use tags like <code>[offer_id]</code>, <code>[offer_customer]</code>, <code>[offer_calculation]</code> in your template.</small>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">Active:</label>
                <div class="col-sm-10">
                    @if($emailTemplate->active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('admin.settings.email_templates.edit', $emailTemplate) }}" class="btn btn-wechsel">
                    <i class="bi bi-pencil-square me-1"></i> Edit
                </a>
                <a href="{{ route('admin.settings.email_templates.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
