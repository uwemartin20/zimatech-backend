@extends('admin.layouts.index')

@section('title', 'Machine')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">{{ $machine->id ? 'Edit Machine' : 'Create Machine' }}</h5>
        </div>

        <div class="card-body">
            <form method="POST"
                  action="{{ route('admin.settings.machines.update', $machine->id) }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name', $machine->name) }}"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description"
                              class="form-control"
                              rows="3">{{ old('description', $machine->description) }}</textarea>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input"
                           type="checkbox"
                           name="active"
                           {{ old('active', $machine->active) ? 'checked' : '' }}>
                    <label class="form-check-label">Active</label>
                </div>

                <button class="btn btn-primary">
                    Save
                </button>
                <a href="{{ route('admin.settings.machines') }}"
                   class="btn btn-secondary">
                    Back
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
