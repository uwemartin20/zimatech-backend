@extends('admin.layouts.index')

@php
    function secondsToIndustryMinutes($seconds) {
        // Real time
        $totalMinutes = $seconds / 60;
        $hours = floor($totalMinutes / 60);
        $minutes = round($totalMinutes % 60);

        $realTime = sprintf("%02d:%02d", $hours, $minutes);

        // Industrial time: 3 real minutes = 5 industrial minutes
        $industryTotalMinutes = ($totalMinutes / 3) * 5;
        $industryHours = floor($industryTotalMinutes / 60);
        $industryMinutes = round($industryTotalMinutes % 60);

        $industryTime = sprintf("%02d:%02d", $industryHours, $industryMinutes);

        return "{$realTime} ({$industryTime})";
    }
@endphp

@section('content')
@php
    if(request('user_id') || request('machine_id' || request('project_id') || request('date') || request('status'))) {
        $isFilterActive = true;
    } else {
        $isFilterActive = false;
    }
@endphp

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Zeitaufzeichnungen</h5>
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button 
                        class="nav-link {{ $isFilterActive ? '' : 'active' }}" 
                        id="wochenuebersicht-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#wochenuebersicht" 
                        type="button" 
                        role="tab" 
                        aria-controls="wochenuebersicht" 
                        aria-selected="true"
                    >
                        Wochenübersicht
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button 
                        class="nav-link {{ $isFilterActive ? 'active' : '' }}" 
                        id="benutzerbasierte-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#benutzerbasierte" 
                        type="button" 
                        role="tab" 
                        aria-controls="benutzerbasierte" 
                        aria-selected="false"
                    >
                        Benutzerbasierte Ansicht
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade {{ $isFilterActive ? '' : 'show active' }}" id="wochenuebersicht" role="tabpanel" aria-labelledby="wochenuebersicht-tab">
                    <div class="mb-3" style="overflow-x:auto; white-space: nowrap;" id="weekSlider">
                        @foreach($weeks as $week)
                            <button 
                                onclick="window.location.href='?week={{ $week['value'] }}'" 
                                style="display:inline-block; width:120px; height:60px; margin-right:4px;"
                                class="week-button {{ $selectedWeek == $week['value'] ? 'bg-secondary bg-opacity-50 text-white shadow' : 'bg-white text-gray text-opacity-80 hover:bg-gray hover:bg-opacity-10' }} border rounded-lg font-medium text-center align-middle"
                                data-week="{{ $week['value'] }}">
                                {{ $week['label'] }}
                            </button>
                        @endforeach

                        <!-- +1 button -->
                        <button id="addWeekBtn" style="display:inline-block; width:120px; height:60px; margin-right:4px;"
                            class="bg-white text-gray text-opacity-80 hover:bg-gray hover:bg-opacity-10 border rounded-lg font-medium text-center align-middle">
                            +1
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>KW</th>
                                    <th>Firma</th>
                                    <th>Auftragsnr.</th>
                                    <th>Position</th>
                                    <th>Maschine</th>
                                    <th>Rustzeit</th>
                                    <th>Mit Aufsicht</th>
                                    <th>Gesamtzeit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($weeklyRecords as $index => $row)
                                    @php
                                        $totalSeconds = $row->rustzeit_seconds + $row->mit_aufsicht_seconds;
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <a 
                                                onclick="getDailyRecords({{ $index }}, {{ $row->calendar_week }}, {{ $row->auftragsnummer }}, {{ $row->position_id }}, {{ $row->machine_id }})" 
                                                data-bs-toggle="collapse" 
                                                href="#collapse{{ $index }}" 
                                                aria-expanded="false" 
                                                aria-controls="collapse{{ $index }}" 
                                                style="cursor: pointer;"
                                            >
                                                KW {{ substr($row->calendar_week, 4) }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge {{ $row->company === 'ZF' ? 'bg-primary' : 'bg-success' }}">
                                                {{ $row->company }}
                                            </span>
                                        </td>
                                        <td>{{ $row->auftragsnummer }}</td>
                                        <td>{{ $row->position_name }}</td>
                                        <td>{{ $row->machine_name }}</td>
                                        <td>{{ secondsToIndustryMinutes($row->rustzeit_seconds) }}</td>
                                        <td>{{ secondsToIndustryMinutes($row->mit_aufsicht_seconds) }}</td>
                                        <td>
                                            <strong>{{ secondsToIndustryMinutes($totalSeconds) }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="p-0">
                                            <div class="collapse" id="collapse{{ $index }}">
                                                <div class="card card-body">
                                                    <!-- Daily records will be loaded here via JavaScript -->
                                                    <x-daily-records-table 
                                                        :index="$index" 
                                                        :week="$row->calendar_week" 
                                                        :auftragsnummer="$row->auftragsnummer"
                                                        :positionId="$row->position_id"
                                                        :machineId="$row->machine_id"
                                                        :autoLoad="false" />
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            Keine Daten für diese Kalenderwochen vorhanden.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade {{ $isFilterActive ? 'show active' : '' }}" id="benutzerbasierte" role="tabpanel" aria-labelledby="benutzerbasierte-tab">
                    <!-- Filter Form -->
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-2">
                            <select name="user_id" class="form-select">
                                <option value="">Alle Benutzer</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
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
                            <a href="{{ route('admin.time.records') }}" class="btn btn-secondary">Zurücksetzen</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Bediener</th>
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
                                        <td>{{ $record->user->name }}</td>
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
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.time.show', $record->id) }}" class="btn btn-outline-ansehen btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                <a href="{{ route('admin.time.edit', $record->id) }}" class="btn btn-sm btn-secondary">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <form action="{{ route('admin.time.delete', $record->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this status?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>

                                            </div>
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
        </div>
    </div>
</div>

<style>
    /* Hide horizontal scrollbar */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
<script>
    window.dailyRecordsCache = window.dailyRecordsCache || {};
    window.dayDetailsCache   = window.dayDetailsCache   || {};

    const slider = document.getElementById('weekSlider');

    function scrollLeft() {
        slider.scrollBy({ left: -200, behavior: 'smooth' });
    }

    function scrollRight() {
        slider.scrollBy({ left: 200, behavior: 'smooth' });
    }

    document.getElementById('addWeekBtn').addEventListener('click', function() {
        const slider = document.getElementById('weekSlider');
        const buttons = slider.querySelectorAll('.week-button');
        const lastButton = buttons[buttons.length - 1];

        // Get last week value, format oW (e.g., 202603)
        let lastWeekValue = lastButton.getAttribute('data-week');
        let year = parseInt(lastWeekValue.slice(0, 4));
        let week = parseInt(lastWeekValue.slice(4, 6));

        // Calculate previous week
        week -= 1;
        if (week < 1) {
            week = 52; // handle previous year
            year -= 1;
        }

        // Pad week to two digits
        let weekStr = week.toString().padStart(2, '0');
        let newWeekValue = year.toString() + weekStr;
        let newWeekLabel = 'KW ' + weekStr + ' / ' + year;

        // Create new button
        const newButton = document.createElement('button');
        newButton.setAttribute('onclick', `window.location.href='?week=${newWeekValue}'`);
        newButton.setAttribute('data-week', newWeekValue);
        newButton.className = "week-button border rounded-lg font-medium bg-white text-gray text-opacity-80 hover:bg-gray hover:bg-opacity-10";
        newButton.style.cssText = "display:inline-block; width:120px; height:60px; margin-right:4px;";
        newButton.innerText = newWeekLabel;

        // Insert before +1 button
        slider.insertBefore(newButton, this);
    });
</script>
@endsection