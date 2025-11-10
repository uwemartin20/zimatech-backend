@extends('admin.layouts.index')

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Alle Projekten</h5>
                <a href="{{ route('admin.projects.create') }}" class="btn btn-secondary btn-sm">+ Projekt Erstellen</a>
            </div>

            <div class="card-body">
                @if($projects->isEmpty())
                    <p class="text-muted text-center mb-0">Keine projekte gefunden.</p>
                @else
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Auftragsnummer</th>
                                <th>Projekt Name</th>
                                <th>Erstellt Am</th>
                                <th class="text-center">Actionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $index => $project)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $project->auftragsnummer }}</td>
                                    <td>{{ $project->project_name }}</td>
                                    <td>{{ $project->created_at->format('d M Y') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.projects', $project->id) }}" class="btn btn-sm btn-wechsel">Anzeigen</a>
                                        <a href="{{ route('admin.projects', $project->id) }}" class="btn btn-sm btn-wechsel">Bearbeiten</a>
                                        <form action="{{ route('admin.projects', $project->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this project?')">Loschen</button>
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
