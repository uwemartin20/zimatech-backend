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
                @if($projects->isEmpty())
                    <p class="text-muted text-center mb-0">Keine projekte gefunden.</p>
                @else
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Projektname</th>
                                <th>Erstellt am</th>
                                <th>Positionen</th>
                                <th>Buateilen</th>
                                <th>Status</th>
                                <th class="text-center">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $index => $project)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        {{ $project->project_name }} 
                                        <div class="text-muted">
                                            {{ $project->auftragsnummer_zt ? "ZT: ".$project->auftragsnummer_zt : '' }} 
                                            {{ $project->auftragsnummer_zf ? "ZF: ".$project->auftragsnummer_zf : '' }}
                                        </div>
                                    </td>
                                    <td>{{ $project->created_at->format('d M Y') }}</td>
                                    {{-- <td>{{ $project->end_time ? $project->end_time->format('d M Y') : 'NA' }}</td> --}}
                                    <td>{{ $project->positions->count() ?? '0' }}</td>
                                    <td>{{ $project->bauteile->count() ?? '0' }}</td>
                                    <td>
                                        @if($project->status)
                                            <span class="badge" style="background-color: {{ $project->status->color }}">
                                                {{ ucfirst($project->status->name) }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.projects.positions.index', $project) }}"class="btn btn-outline-success btn-sm"
                                            title="Positionen">
                                                <i class="bi bi-list-ul"></i>
                                        </a>
                                        <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this project?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
@endsection
