@extends('admin.layouts.index')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Lager bearbeiten</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.lager.update', $lager->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $lager->name }}" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Beschreibung</label>
                    <textarea class="form-control" id="description" name="description">{{ $lager->description }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="is_active" class="form-label">Aktiv</label>
                    <select class="form-select" id="is_active" name="is_active" required>
                        <option value="1" {{ $lager->is_active ? 'selected' : '' }}>Ja</option>
                        <option value="0" {{ !$lager->is_active ? 'selected' : '' }}>Nein</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <input type="text" class="form-control" id="status" name="status" value="{{ $lager->status }}">
                </div>

                <button type="submit" class="btn btn-filter">Lager aktualisieren</button>
            </form>
        </div>
    </div>
</div>
@endsection
