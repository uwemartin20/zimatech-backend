@extends('admin.layouts.index')

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Alle Projekten</h5>
                <a href="{{ route('admin.projects.create') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-plus-circle"></i> Projekt Erstellen
                </a>
            </div>

            <div class="card-body">
                
                {{-- ========== FILTER BAR ========== --}}
                <form method="GET" action="{{ route('admin.projects') }}" class="row g-3 mb-4 p-3 bg-light rounded border">
                    <div class="col-md-5">
                        <label for="search" class="form-label small fw-bold text-muted">Suchen (Name, ZT, ZF)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Projektname oder Auftragsnummer..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="status_id" class="form-label small fw-bold text-muted">Status</label>
                        <select name="status_id" id="status_id" class="form-select form-select-sm">
                            <option value="">-- Alle Status --</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                    {{ ucfirst($status->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-filter btn-sm w-100">
                            <i class="bi bi-funnel-fill"></i> Filtern
                        </button>
                        @if(request()->filled('search') || request()->filled('status_id'))
                            <a href="{{ route('admin.projects') }}" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="bi bi-x-circle"></i> Zurücksetzen
                            </a>
                        @endif
                    </div>
                </form>
                {{-- ================================ --}}

                @if($projects->isEmpty())
                    <p class="text-muted text-center mb-0">Keine projekte gefunden.</p>
                @else
                    <div class="table-responsive-wrapper">
                        <table class="table table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Projektname</th>
                                    <th>Erstellt am</th>
                                    <th>Positionen</th>
                                    <th>Bauteilen</th> <th>Status</th>
                                    <th class="text-center">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projects as $index => $project)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $project->project_name }}</span>
                                            <div class="small text-muted mt-1">
                                                {!! $project->auftragsnummer_zt ? '<span class="badge bg-light text-dark border me-1">ZT: '.$project->auftragsnummer_zt.'</span>' : '' !!} 
                                                {!! $project->auftragsnummer_zf ? '<span class="badge bg-light text-dark border">ZF: '.$project->auftragsnummer_zf.'</span>' : '' !!}
                                            </div>
                                        </td>
                                        <td>{{ $project->created_at->format('d M Y') }}</td>
                                        <td>{{ $project->positions->count() }}</td>
                                        <td>{{ $project->bauteile->count() }}</td>
                                        <td>
                                            @if($project->status)
                                                <span class="badge text-white" style="background-color: {{ $project->status->color }}">
                                                    {{ ucfirst($project->status->name) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.projects.positions.index', $project) }}" class="btn btn-outline-success btn-sm" title="Positionen">
                                                    <i class="bi bi-list-ul"></i>
                                                </a>
                                                <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-outline-secondary" title="Anzeigen">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-outline-primary btn-sm" title="Bearbeiten">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Projekt wirklich löschen?')" title="Löschen">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection