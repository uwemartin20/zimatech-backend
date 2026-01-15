@extends('admin.layouts.index')

@section('title', 'Edit Email Template')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Email Template - {{ $emailTemplate->subject }}</h5>
            <a href="{{ route('admin.settings.email_templates.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-envelope-paper me-1"></i> All Email Templates
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.settings.email_templates.update', $emailTemplate) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" name="subject" id="subject" 
                           class="form-control @error('subject') is-invalid @enderror" 
                           value="{{ old('subject', $emailTemplate->subject) }}" required>
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="5" 
                              class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $emailTemplate->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="template_type" class="form-label">Template Type</label>
                    <select name="template_type" id="template_type" class="form-select @error('template_type') is-invalid @enderror" required>
                        @php
                            $types = ['project_offers','inquiry','support','help','supplier_offers'];
                        @endphp
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ old('template_type', $emailTemplate->template_type) == $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_',' ',$type)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('template_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="note" class="form-label">Notes / Tags</label>
                    <textarea name="note" id="note" rows="3" class="form-control">{{ old('note', $emailTemplate->note) }}</textarea>
                    <small class="text-muted">Use tags like [offer_id], [offer_customer], [offer_calculation]</small>
                </div>

                <div class="mb-3 form-check">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" id="active" class="form-check-input" {{ $emailTemplate->active ? 'checked' : '' }}>
                    <label for="active" class="form-check-label">Active</label>
                </div>

                <button type="submit" class="btn btn-wechsel">
                    <i class="bi bi-save me-1"></i> Update Template
                </button>
                <a href="{{ route('admin.settings.email_templates.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
