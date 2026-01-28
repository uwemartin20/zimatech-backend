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

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Maschinenprotokolle</h5>
            <a href="{{ route('admin.time.logs_old') }}" class="btn btn-secondary btn-sm">
              <i class="bi bi-plus-circle me-1"></i> Alte Seite
            </a>
        </div>

        <div class="card-body">
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
                          <th>Gesamtzeit (Minuten)</th>
                      </tr>
                  </thead>
                  <tbody>
                      @forelse($weeklyRecords as $index => $row)
                          @php
                              $totalSeconds = $row->process_seconds - $row->pause_seconds;
                          @endphp
                          <tr>
                              <td>{{ $index + 1 }}</td>
                              <td>
                                      KW {{ substr($row->calendar_week, 4) }}
                              </td>
                              <td>
                                  <span class="badge {{ $row->company === 'ZF' ? 'bg-primary' : 'bg-success' }}">
                                      {{ $row->company }}
                                  </span>
                              </td>
                              <td>{{ $row->auftragsnummer }}</td>
                              <td>{{ $row->position_name }}</td>
                              <td>{{ $row->machine_name }}</td>
                              <td>
                                  <strong>{{ secondsToIndustryMinutes($totalSeconds) }}</strong>
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