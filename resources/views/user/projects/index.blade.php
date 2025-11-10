@extends('user.layouts.index')

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Alle Projekten</h5>
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
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $index => $project)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $project->auftragsnummer }}</td>
                                    <td>{{ $project->project_name }}</td>
                                    <td>{{ $project->created_at->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
@endsection
