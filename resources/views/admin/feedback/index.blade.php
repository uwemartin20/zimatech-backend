@extends('admin.layouts.index')

@section('content')
<style>
    .filter-box {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 6px;
        background: #fff;
    }
    
    .filter-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-bottom: 4px;
    }
    
    .filter-chip {
        background: #e9f2ff;
        color: #0d6efd;
        border-radius: 20px;
        padding: 2px 8px;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .filter-chip span {
        cursor: pointer;
    }
    
    .filter-dropdown {
        max-height: 200px;
        overflow-y: auto;
    }
    
    /* scrollbar styling */
    .filter-dropdown::-webkit-scrollbar {
        width: 6px;
    }
    .filter-dropdown::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }
    
    .dropdown-item {
        border-radius: 6px;
    }
    
    .dropdown-item:hover {
        background-color: #f1f3f5;
    }
    
</style>

<div class="container-fluid bg-light min-vh-100 p-3">

    {{-- ========================= --}}
    {{-- KPI CARDS --}}
    {{-- ========================= --}}
    <div class="row g-3 mb-3">

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Gesamtzahl</h6>
                    <h3>{{ $kpi['total'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Mit Lösung</h6>
                    <h3>{{ $kpi['with_solution'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Ohne Lösung</h6>
                    <h3>{{ $kpi['without_solution'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Anonym</h6>
                    <h3>{{ $kpi['anonymous'] }}</h3>
                </div>
            </div>
        </div>

    </div>

    {{-- ========================= --}}
    {{-- FILTER BAR --}}
    {{-- ========================= --}}
    <form method="GET" class="filter-form card shadow-sm mb-3">

        <div class="card-body">
    
            <div class="row g-3 align-items-end">
    
                {{-- TYPE --}}
                <div class="col-md-2">
                    {{-- <label class="form-label small text-muted">Type</label> --}}
                
                    <div class="filter-box">
                
                        {{-- Selected chips --}}
                        <div class="filter-chips" id="typeChips"></div>
                
                        {{-- Dropdown --}}
                        <div class="dropdown w-100">
                            <button class="form-control form-control-sm text-start dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown">
                                Typ
                            </button>
                
                            <div class="dropdown-menu p-2 w-100 filter-dropdown">
                
                                @foreach(['maschinen','bereiche','sonstiges'] as $t)
                                    <label class="dropdown-item d-flex align-items-center gap-2">
                                        <input type="checkbox"
                                            name="type[]"
                                            value="{{ $t }}"
                                            onchange="updateChips('type', this, true)"
                                            {{ collect(request('type'))->contains($t) ? 'checked' : '' }}>
                                        <span>{{ ucfirst($t) }}</span>
                                    </label>
                                @endforeach
                
                            </div>
                        </div>
                
                    </div>
                </div>
    
                {{-- MACHINE --}}
                <div class="col-md-3">
                    {{-- <label class="form-label small text-muted">Machine</label> --}}
                
                    <div class="filter-box">
                        <div class="filter-chips" id="machineChips"></div>
                
                        <div class="dropdown w-100">
                            <button class="form-control form-control-sm text-start dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown">
                                Maschinen
                            </button>
                
                            <div class="dropdown-menu p-2 w-100 filter-dropdown">
                
                                @foreach(\App\Models\Feedback::select('machine')->whereNotNull('machine')->distinct()->pluck('machine') as $m)
                                    <label class="dropdown-item d-flex align-items-center gap-2">
                                        <input type="checkbox"
                                               name="machine[]"
                                               value="{{ $m }}"
                                               onchange="updateChips('machine', this, true)"
                                               {{ collect(request('machine'))->contains($m) ? 'checked' : '' }}>
                                        <span>{{ $m }}</span>
                                    </label>
                                @endforeach
                
                            </div>
                        </div>
                    </div>
                </div>
    
                {{-- DEPARTMENT --}}
                <div class="col-md-3">
                    {{-- <label class="form-label small text-muted">Department</label> --}}
                
                    <div class="filter-box">
                        <div class="filter-chips" id="departmentChips"></div>
                
                        <div class="dropdown w-100">
                            <button class="form-control form-control-sm text-start dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown">
                                Bereiche
                            </button>
                
                            <div class="dropdown-menu p-2 w-100 filter-dropdown">
                
                                @foreach(\App\Models\Feedback::select('department')->whereNotNull('department')->distinct()->pluck('department') as $d)
                                    <label class="dropdown-item d-flex align-items-center gap-2">
                                        <input type="checkbox"
                                               name="department[]"
                                               value="{{ $d }}"
                                               onchange="updateChips('department', this, true)"
                                               {{ collect(request('department'))->contains($d) ? 'checked' : '' }}>
                                        <span>{{ $d }}</span>
                                    </label>
                                @endforeach
                
                            </div>
                        </div>
                    </div>
                </div>
    
                {{-- TOGGLES --}}
                <div class="col-md-2">
    
                    {{-- <label class="form-label small text-muted d-block">Options</label> --}}
    
                    <div class="d-flex flex-column gap-1">
    
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                type="checkbox"
                                name="has_attachment"
                                onchange="this.form.submit()"
                                {{ request('has_attachment') ? 'checked' : '' }}>
                            <label class="form-check-label small">Mit Bildern</label>
                        </div>
    
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                type="checkbox"
                                name="anonymous"
                                onchange="this.form.submit()"
                                {{ request('anonymous') ? 'checked' : '' }}>
                            <label class="form-check-label small">Anonym</label>
                        </div>
    
                    </div>
                </div>
    
                {{-- RESET --}}
                <div class="col-md-2 text-end">
                    <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary w-100">
                        Filter Zurücksetzen
                    </a>
                </div>
    
            </div>
    
        </div>
    </form>

    {{-- ========================= --}}
    {{-- CHART GRID --}}
    {{-- ========================= --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h6>Machinen</h6>
                <canvas id="machineChart"></canvas>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h6>Bereiche</h6>
                <canvas id="departmentChart"></canvas>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h6>Fehler Code</h6>
                <canvas id="errorChart"></canvas>
            </div>
        </div>

    </div>

    {{-- ========================= --}}
    {{-- TABLE VIEW (REPLACES FEED) --}}
    {{-- ========================= --}}
    <div class="row align-items-start">

        {{-- ========================= --}}
        {{-- LEFT: TABLE (OVERVIEW) --}}
        {{-- ========================= --}}
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-body p-2" style="max-height: 70vh; overflow-y:auto;">
        
                    @foreach($feedbacks as $fb)
                        <div class="card mb-2 shadow-sm border-0 feedback-item"
                            style="cursor:pointer;"
                            onclick="openDetail({{ json_encode([
                                'type' => $fb->type,
                                'machine' => $fb->machine,
                                'department' => $fb->department,
                                'error_code' => $fb->error_code,
                                'problem' => $fb->problem,
                                'solution' => $fb->solution,
                                'name' => $fb->name,
                                'attachment' => $fb->attachment ? asset('storage/'.$fb->attachment) : null,
                                'created_at' => $fb->created_at->format('d.m.Y H:i')
                            ]) }})"
                        >
                            <div class="card-body py-2 px-3">
        
                                {{-- TOP ROW --}}
                                <div class="d-flex justify-content-between align-items-center mb-1">
        
                                    <span class="badge
                                        @if($fb->type === 'maschinen') bg-danger
                                        @elseif($fb->type === 'bereiche') bg-warning text-dark
                                        @elseif($fb->type === 'sonstiges') bg-primary
                                        @else bg-secondary
                                        @endif
                                    ">
                                        {{ $fb->type }}
                                    </span>
        
                                    <div class="d-flex align-items-center gap-2">
        
                                        @if($fb->attachment)
                                            <span title="Attachment">📎</span>
                                        @endif
        
                                        @if(!$fb->name)
                                            <span class="badge bg-light text-dark border">
                                                Anonym
                                            </span>
                                        @else
                                            <small class="text-muted">
                                                {{ $fb->name }}
                                            </small>
                                        @endif
        
                                    </div>
                                </div>
        
                                {{-- MACHINE / DEPARTMENT --}}
                                <div class="small text-muted mb-1">
                                    @if($fb->machine)
                                        <strong>Maschine:</strong> {{ $fb->machine }}
                                    @elseif($fb->department)
                                        <strong>Bereich:</strong> {{ $fb->department }}
                                    @else
                                        <span class="text-muted">Kein Kontext</span>
                                    @endif
                                </div>
        
                                {{-- PROBLEM PREVIEW --}}
                                <div class="small">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($fb->problem), 100) }}
                                </div>
        
                            </div>
                        </div>
                    @endforeach
        
                </div>
            </div>
        
        </div>
    
        {{-- ========================= --}}
        {{-- RIGHT: DETAIL PANEL --}}
        {{-- ========================= --}}
        <div class="col-md-6">
    
            <div id="detailPane" class="card shadow-sm">
                <div class="card-body" style="height: 70vh; overflow-y: auto;">
    
                    <div id="detailContent" class="text-muted">
                        Wählen Sie eine Feedback aus, um Details anzuzeigen..
                    </div>
    
                </div>
            </div>
    
        </div>
    
    </div>

</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    
        new Chart(document.getElementById('machineChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($byMachine->pluck('machine')) !!},
                datasets: [{
                    label: 'Machines',
                    data: {!! json_encode($byMachine->pluck('count')) !!}
                }]
            }
        });
    
        new Chart(document.getElementById('departmentChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($byDepartment->pluck('department')) !!},
                datasets: [{
                    label: 'Departments',
                    data: {!! json_encode($byDepartment->pluck('count')) !!}
                }]
            }
        });
    
        new Chart(document.getElementById('errorChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($byErrorCode->pluck('error_code')) !!},
                datasets: [{
                    label: 'Errors',
                    data: {!! json_encode($byErrorCode->pluck('count')) !!}
                }]
            }
        });
    
    });

    function getTypeBadgeClass(type) {
        switch (type) {
            case 'maschinen':
                return 'bg-danger';
            case 'bereiche':
                return 'bg-warning text-dark';
            case 'sonstiges':
                return 'bg-primary';
            default:
                return 'bg-secondary';
        }
    }

    function openDetail(data) {

        const container = document.getElementById('detailContent');

        const nameDisplay = data.name ? data.name : 'Anonym';

        const attachmentHtml = data.attachment
            ? `<a href="${data.attachment}" target="_blank">📎 View Attachment</a>`
            : '<span class="text-muted">Kein Anhang</span>';

        const solutionHtml = data.solution
            ? `<div class="border rounded p-3 bg-light">${data.solution}</div>`
            : '<div class="text-muted">Keine Lösung verfügbar</div>';

        container.innerHTML = `
            <div class="d-flex justify-content-between mb-2">
                <span class="badge ${getTypeBadgeClass(data.type)}">${data.type}</span>
                <small class="text-muted">${data.created_at}</small>
            </div>

            <div class="mb-2">
                <strong>Maschine:</strong> ${data.machine ?? 'Keine'}
            </div>

            <div class="mb-2">
                <strong>Bereich:</strong> ${data.department ?? 'Keiner'}
            </div>

            <div class="mb-2">
                <strong>Fehlercode:</strong> ${data.error_code ?? '-'}
            </div>

            <div class="mb-2">
                <strong>Name:</strong> ${nameDisplay}
            </div>

            <div class="mb-3">
                <strong>Anhang:</strong><br>
                ${attachmentHtml}
            </div>

            <hr>

            <div class="mb-3">
                <h6>Problem</h6>
                <div class="border rounded p-3 bg-white">
                    ${data.problem}
                </div>
            </div>

            <div>
                <h6>Solution</h6>
                ${solutionHtml}
            </div>
        `;
    }

    function updateChips(type, el = null, shouldSubmit = false) {

        const form = el ? el.closest('form') : document.querySelector('.filter-form');

        const container = document.getElementById(type + 'Chips');
        const checkboxes = document.querySelectorAll(`input[name="${type}[]"]`);

        container.innerHTML = '';

        checkboxes.forEach(cb => {
            if (cb.checked) {

                const chip = document.createElement('div');
                chip.className = 'filter-chip';

                chip.innerHTML = `
                    ${cb.value}
                    <span onclick="removeChip(this, '${type}', '${cb.value}')">✕</span>
                `;

                container.appendChild(chip);
            }
        });

        // ✅ Only submit when triggered by user
        if (shouldSubmit && el) {
            setTimeout(() => form.submit(), 150);
        }
    }

    function removeChip(el, type, value) {

        const checkbox = document.querySelector(`input[name="${type}[]"][value="${value}"]`);

        if (checkbox) {
            checkbox.checked = false;
            updateChips(type, checkbox, true);
        }
    }

    // initialize on load
    document.addEventListener("DOMContentLoaded", function () {
    updateChips('type');
    updateChips('machine');
    updateChips('department');
    });
</script>