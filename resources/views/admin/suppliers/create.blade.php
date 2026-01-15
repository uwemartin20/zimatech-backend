@extends('admin.layouts.index')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Neuen Lieferanten hinzufügen</h5>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left-circle me-1"></i> Zurück
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.suppliers.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Firma</label>
                        <input type="text" name="company" value="{{ old('company') }}" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Adresse</label>
                    <textarea name="address" rows="2" class="form-control">{{ old('address') }}</textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Telefon</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Website</label>
                        <input type="url" name="website" value="{{ old('website') }}" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="form-label fw-semibold">Dienstleistungen (Mehrfachauswahl möglich)</label>
                    <select name="services[]" class="form-select" multiple>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}">
                                <span class="badge" style="background-color: {{ $service->color }}">{{ $service->name }}</span>
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-wechsel">Speichern</button>
            </form>
        </div>
    </div>
</div>
@endsection
