@extends('admin.layouts.index')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">
                Add Position – {{ $project->project_name }}
            </h5>
        </div>

        <div class="card-body">
            <form method="POST"
                  action="{{ route('admin.projects.positions.store', $project) }}"
                  class="col-md-6">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Position Name</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name') }}"
                           required>
                    @error('name')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Projekt Leistung</label>
                    <select name="project_service_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}"
                                @selected(old('project_service_id') == $service->id)>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_service_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Save
                </button>

                <a href="{{ route('admin.projects.positions.index', $project) }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
