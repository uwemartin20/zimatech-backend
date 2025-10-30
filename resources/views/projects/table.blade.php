@extends('layouts.app')

@section('title', 'Fräsmaschine Logs')

@section('content')

<h2 class="mb-4">Fräsmaschine Logs</h2>

<form method="GET" class="mb-4">
    <div class="row g-2 align-items-center">
        <div class="col-auto">
            <label for="projectFilter" class="col-form-label">Projekt:</label>
        </div>
        <div class="col-auto">
            <select name="project_id" id="projectFilter" class="form-select">
                <option value="">Alle Projekte</option>
                @foreach($allProjects as $proj)
                    <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                        {{ $proj->project_name }} ({{ $proj->auftragsnummer }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-auto">
            <label for="startDate" class="col-form-label">Start:</label>
        </div>
        <div class="col-auto">
            <input type="datetime-local" name="start_date" id="startDate" class="form-control" value="{{ request('start_date') }}">
        </div>

        <div class="col-auto">
            <label for="endDate" class="col-form-label">Ende:</label>
        </div>
        <div class="col-auto">
            <input type="datetime-local" name="end_date" id="endDate" class="form-control" value="{{ request('end_date') }}">
        </div>

        <div class="col-auto">
            <button class="btn btn-filter">Filtern</button>
        </div>
        <div class="col-auto">
          <a href="{{ route('projects.table') }}" class="btn btn-secondary">Zurücksetzen</a>
        </div>
    </div>
</form>

@foreach ($projects as $project)
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-dark text-white">
      <strong>Projekt:</strong> {{ $project->project_name ?? 'N/A' }} |
      <strong>Auftragsnummer:</strong> {{ $project->auftragsnummer ?? 'N/A' }}
    </div>
    <div class="card-body">

      {{-- ===================== CASE 1: BAUTEILE EXIST ===================== --}}
      @if ($project->bauteile->count())
        <h5 class="text-secondary mb-2">Bauteile</h5>
        @foreach ($project->bauteile as $bauteil)
          <div class="border rounded p-3 mb-3 bg-light">
            <p class="mb-2">
              <strong>Bauteil:</strong> {{ $bauteil->name }} |
              <strong>Parent ID:</strong> {{ $bauteil->parent_id ?? 0 }} |
              <strong>Anzahl Prozesse:</strong> {{ $bauteil->processes->count() }}
            </p>

            @if ($bauteil->procedures->count())
              <h5 class="text-secondary mb-2">Prozeduren</h5>
              @foreach ($project->procedures as $procedure)
                <div class="border rounded p-3 mb-3 bg-light">
                  <p class="mb-1">
                    <strong>Start:</strong> {{ $procedure->start_time ?? '-' }} |
                    <strong>Ende:</strong> {{ $procedure->end_time ?? '-' }} |
                    <strong>Anzahl Prozesse:</strong> {{ $procedure->processes->count() }} |
                    <strong>Gesamtzeit:</strong> {{ gmdate('H:i:s', strtotime($procedure->end_time) - strtotime($procedure->start_time)) }}
                  </p>

                  @if ($procedure->processes->count())
                    <table class="table table-sm table-bordered">
                      <thead class="table-secondary">
                        <tr>
                          <th>Prozess Name</th>
                          <th>Start zeit</th>
                          <th>End zeit</th>
                          <th>Zeit</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($procedure->processes as $process)
                          <tr>
                            <td>{{ $process->name }}</td>
                            <td>{{ $process->start_time }}</td>
                            <td>{{ $process->end_time }}</td>
                            <td>{{ gmdate('H:i:s', $process->total_seconds) }}</td>
                          </tr>
                        @endforeach
                          <tr class="table-secondary">
                            <td colspan="3">Gesamtzeit</td>
                            <td>{{ gmdate('H:i:s', $procedure->processes->sum('total_seconds')) }}</td>
                          </tr>
                      </tbody>
                    </table>
                  @else
                    <p class="text-muted fst-italic">Keine Prozesse für dieses Prozedur.</p>
                  @endif
                </div>
              @endforeach
            @endif

            @if ($bauteil->processes->count())
              <table class="table table-sm table-bordered">
                <thead class="table-info">
                  <tr>
                    <th>Prozess Name</th>
                    <th>Start zeit</th>
                    <th>End zeit</th>
                    <th>Zeit</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($bauteil->processes as $process)
                    <tr>
                      <td>{{ $process->name }}</td>
                      <td>{{ $process->start_time }}</td>
                      <td>{{ $process->end_time }}</td>
                      <td>{{ gmdate('H:i:s', $process->total_seconds) }}</td>
                    </tr>
                  @endforeach
                    <tr class="table-secondary">
                      <td colspan="3">Gesamtzeit</td>
                      <td>{{ gmdate('H:i:s', $bauteil->processes->sum('total_seconds')) }}</td>
                    </tr>
                </tbody>
              </table>
            @else
              <p class="text-muted fst-italic">Keine Prozesse für dieses Bauteil.</p>
            @endif
          </div>
        @endforeach
      @endif

      {{-- ===================== CASE 2: No Bauteile but PROCEDURES EXIST ===================== --}}
      @if ($project->procedures->count())
        <h5 class="text-secondary mb-2">Prozeduren</h5>
        @foreach ($project->procedures as $procedure)
          <div class="border rounded p-3 mb-3 bg-light">
            <p class="mb-1">
              <strong>Start:</strong> {{ $procedure->start_time ?? '-' }} |
              <strong>Ende:</strong> {{ $procedure->end_time ?? '-' }} |
              <strong>Anzahl Prozesse:</strong> {{ $procedure->processes->count() }} |
              <strong>Gesamtzeit:</strong> {{ gmdate('H:i:s', strtotime($procedure->end_time) - strtotime($procedure->start_time)) }}
            </p>

            @if ($procedure->processes->count())
              <table class="table table-sm table-bordered">
                <thead class="table-secondary">
                  <tr>
                    <th>Prozess Name</th>
                    <th>Start zeit</th>
                    <th>End zeit</th>
                    <th>Zeit</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($procedure->processes as $process)
                    <tr>
                      <td>{{ $process->name }}</td>
                      <td>{{ $process->start_time }}</td>
                      <td>{{ $process->end_time }}</td>
                      <td>{{ gmdate('H:i:s', $process->total_seconds) }}</td>
                    </tr>
                  @endforeach
                    <tr class="table-secondary">
                      <td colspan="3">Gesamtzeit</td>
                      <td>{{ gmdate('H:i:s', $procedure->processes->sum('total_seconds')) }}</td>
                    </tr>
                </tbody>
              </table>
            @else
              <p class="text-muted fst-italic">Keine Prozesse für dieses Prozedur.</p>
            @endif
          </div>
        @endforeach
      @endif

      {{-- ===================== CASE 3: NO PROCEDURES/BAUTEILE BUT DIRECT PROCESSES EXIST ===================== --}}
      @if ($project->processes->whereNull('procedure_id')->whereNull('bauteil_id')->count())
        <h5 class="text-secondary mb-2">Direkte Prozesse</h5>
        <table class="table table-sm table-bordered">
          <thead class="table-warning">
            <tr>
              <th>Prozess Name</th>
              <th>Start Zeit</th>
              <th>End Zeit</th>
              <th>Zeit</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($project->processes->whereNull('procedure_id')->whereNull('bauteil_id') as $process)
              <tr>
                <td>{{ $process->name }}</td>
                <td>{{ $process->start_time }}</td>
                <td>{{ $process->end_time }}</td>
                <td>{{ gmdate('H:i:s', $process->total_seconds) }}</td>
              </tr>
            @endforeach
              <tr class="table-secondary">
                <td colspan="3">Gesamtzeit</td>
                <td>{{ gmdate('H:i:s', $project->processes->whereNull('procedure_id')->whereNull('bauteil_id')->sum('total_seconds')) }}</td>
              </tr>
          </tbody>
        </table>
      @endif

      {{-- ===================== CASE 4: NOTHING EXISTS ===================== --}}
      @if (!$project->processes->count() || !$project->procedures->count() || !$project->bauteile->count())
        <p class="text-muted fst-italic">Keine Daten vorhanden.</p>
      @endif

    </div>
  </div>
@endforeach

<div class="d-flex justify-content-center mt-3">
    {{ $projects->withQueryString()->links('pagination::bootstrap-5') }}
</div>

@endsection