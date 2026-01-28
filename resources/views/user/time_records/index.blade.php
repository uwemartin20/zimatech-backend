@extends('user.layouts.index')

@section('content')
<div class="container mt-4">
    @if(!$selectedUser)
        <div class="welcome-wrapper fade-in">
            <div class="text-center">
                <h1 class="fw-bold mb-3">Willkommen zur Zeiterfassung</h1>
                <p class="text-muted fs-5 mb-4">
                    Bitte wählen Sie einen Benutzer aus, um dessen Zeitaufzeichnungen anzuzeigen.
                </p>
        
                <div class="d-flex justify-content-center flex-wrap gap-3 mt-4">
                    @foreach($users as $user)
                        <a href="{{ route('time-records.list', ['user_id' => $user->id]) }}"
                        class="user-tile slide-up">
                            {{ $user->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($selectedUser)
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        @if($selectedUser)
                        <span class="fw-bold fs-4">
                            {{ $selectedUser->name }} - 
                        </span>
                    @endif
                        Zeitaufzeichnungen
                    </h5>
            
                </div>
            
                <div class="d-flex gap-2">
                    @if($selectedUser)
                        <a href="{{ route('time-records.list', request()->except('user_id', 'page')) }}"
                           class="btn btn-outline-light btn-sm">
                            <i class="bi bi-arrow-left-circle me-1"></i>
                            Benutzer wechseln
                        </a>
                    @endif
            
                    <a href="{{ route('time-records.create') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>
                        Neue Aufzeichnung
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-2">
                        <select name="project_id" class="form-select">
                            <option value="">Alle Projekte</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->project_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="machine_id" class="form-select">
                            <option value="">Alle Maschinen</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}" {{ request('machine_id') == $machine->id ? 'selected' : '' }}>
                                    {{ $machine->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-2 d-flex">
                        <button type="submit" class="btn btn-filter me-2">Filtern</button>
                        <a href="{{ route('time-records.list') }}" class="btn btn-secondary">Zurücksetzen</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                @if(!$selectedUser)
                                    <th>Bediener</th>
                                @endif
                                <th>Projekt</th>
                                <th>Position</th>
                                <th>Maschine</th>
                                <th>Start</th>
                                <th>Ende</th>
                                <th>Dauer</th>
                                <th>Aktion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $index => $record)
                                <tr>
                                    <td>{{ $records->firstItem() + $index }}</td>
                                    @if(!$selectedUser)
                                        <td>{{ $record->user->name }}</td>
                                    @endif
                                    <td>{{ $record->project->project_name }}</td>
                                    <td>{{ $record->position->name }}</td>
                                    <td>{{ $record->machine->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($record->start_time)->format('d.m.Y H:i') }}</td>
                                    <td>
                                        @if($record->end_time)
                                            {{ \Carbon\Carbon::parse($record->end_time)->format('d.m.Y H:i') }}
                                        @else
                                            <span class="badge bg-success">Läuft</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->end_time)
                                            {{ \Carbon\Carbon::parse($record->start_time)->diff($record->end_time) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('time-records.show', $record->id) }}" class="btn btn-outline-ansehen btn-sm">
                                            <i class="bi bi-eye"></i> Ansehen
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        Keine Zeitaufzeichnungen gefunden.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $records->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
<style>
    /* User Selection Tiles */
    .user-tile {
        min-width: 220px;
        padding: 20px 30px;
        border-radius: 14px;
        background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        color: #fff;
        font-size: 1.25rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    
    .user-tile:hover {
        transform: translateY(-6px) scale(1.03);
        box-shadow: 0 18px 40px rgba(0,0,0,0.25);
        color: #fff;
    }
    
    /* Animations */
    .fade-in {
        animation: fadeIn 0.6s ease forwards;
    }
    
    .slide-up {
        animation: slideUp 0.6s ease forwards;
    }

    .welcome-wrapper {
        min-height: calc(50vh);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .user-tile {
        animation-delay: 0.1s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection
