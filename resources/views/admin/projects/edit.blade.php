@extends('admin.layouts.index')

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Projekt bearbeiten</h5>
                <a href="{{ route('admin.projects') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left-circle"></i> Zurück
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.projects.update', $project) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="project_name" class="form-label fw-bold">Projektname *</label>
                            <input type="text" name="project_name" id="project_name" class="form-control" value="{{ old('project_name', $project->project_name) }}" placeholder="Projektname" required>
                        </div>

                        <div class="col-md-6">
                            <label for="project_status_id" class="form-label fw-bold">Status *</label>
                            <select name="project_status_id" id="project_status_id" class="form-select" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" {{ (old('project_status_id', $project->project_status_id ?? '') == $status->id) ? 'selected' : '' }}>
                                        {{ ucfirst($status->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="auftragsnummer_zt" class="form-label fw-bold">Auftragsnummer <span class="text-muted">(Nur ein oder beide eingeben)</span> *</label>
                            <input type="text" name="auftragsnummer_zt" id="auftragsnummer_zt" class="form-control" value="{{ old('auftragsnummer_zt', $project->auftragsnummer_zt) }}" placeholder="ZimaTech">
                        </div>

                        <div class="col-md-6">
                            <input type="text" name="auftragsnummer_zf" id="auftragsnummer_zf" class="form-control" value="{{ old('auftragsnummer_zf', $project->auftragsnummer_zf) }}" placeholder="Zimmermann Formtechnik">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="kunde" class="form-label fw-bold">Kundenname</label>
                        <input type="text" name="kunde" id="kunde" class="form-control" value="{{ old('kunde', $project->kunde) }}" placeholder="Kundenname">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_time" class="form-label fw-bold">Projektstart</label>
                            <input type="datetime-local" name="start_time" id="start_time" class="form-control"
                                value="{{ old('start_time', isset($project) ? $project->start_time : '') }}">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="end_time" class="form-label fw-bold">Projektende</label>
                            <input type="datetime-local" name="end_time" id="end_time" class="form-control"
                                value="{{ old('end_time', isset($project) && $project->end_time ? $project->end_time : '') }}">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-wechsel">Aktualisieren</button>
                    </div>
                </form>
            </div>
        </div>
@endsection
