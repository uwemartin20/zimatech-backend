@extends('user.layouts.index')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Neue Zeit Erfassung</h5>
            <a href="{{ route('time-records.list') }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Alle Aufzeichnung
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('time-records.store') }}" method="POST">
                @csrf
                
                <!-- STEP 1: USER SELECTION -->
                <div id="step-user" class="mb-4">
                    <h6>Bediener Auswahlen</h6>
                
                    @if(!$selectedUser)
                        <div class="row g-2">
                            @foreach($users as $user)
                                <div class="col-md-3">
                                    <button type="button"
                                            class="btn btn-outline-dark w-100 user-btn"
                                            data-user-id="{{ $user->id }}"
                                            data-company="{{ $user->company }}">
                                        <i class="bi bi-person"></i> {{ $user->name }}
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="row g-2">
                            @foreach($users as $user)
                                @if($user->id == $selectedUser->id)
                                    <div class="col-md-3">
                                        @php 
                                            $isActive = isset($selectedUser) && $selectedUser->id == $user->id;
                                        @endphp
                                        <button type="button"
                                                class="btn {{ $isActive ? 'btn-primary active' : 'btn-outline-dark' }} w-100 user-btn"
                                                data-user-id="{{ $user->id }}"
                                                data-company="{{ $user->company }}">
                                            <i class="bi bi-person"></i> {{ $user->name }}
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <input type="hidden" name="user_id" id="user_id" value="{{ $selectedUser->id ?? '' }}">

                <!-- STEP 2: PROJECT SELECTION (UX UPGRADED) -->
                <div id="step-project" class="mb-4 {{ isset($selectedUser) ? '' : 'd-none' }}">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h6 class="mb-0">Projekte Auswahlen</h6>
                        
                        <!-- Search Input -->
                        <div class="input-group" style="max-width: 300px;">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="project-search" class="form-control border-start-0 ps-0" placeholder="Projekt / Auftragsnr..." autocomplete="off">
                        </div>
                    </div>
                
                    <!-- Scrollable Project Container -->
                    <div class="row g-2 project-list-container" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
                        @foreach($projects as $project)
                            <!-- project-wrapper with data-search attribute for JS filtering -->
                            <div class="col-md-4 project-wrapper" data-search="{{ strtolower($project->project_name . ' ' . $project->auftragsnummer_zt . ' ' . $project->auftragsnummer_zf) }}">
                                <button type="button"
                                        class="btn btn-outline-primary w-100 project-btn py-3"
                                        data-project-id="{{ $project->id }}"
                                        data-zt="{{ $project->auftragsnummer_zt }}"
                                        data-zf="{{ $project->auftragsnummer_zf }}"
                                        data-positions='@json($project->positions)'>
                                    <strong class="d-block text-truncate">{{ $project->project_name }}</strong>
                                    <small class="text-muted d-block project-auftrag mt-1">
                                        @if(isset($selectedUser))
                                            {{ $selectedUser->company === 'ZF' ? "(ZF: " . ($project->auftragsnummer_zf ?? '—') . ")" : "(ZT: " . ($project->auftragsnummer_zt ?? '—') . ")" }}
                                        @endif
                                    </small>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <!-- No Results Message -->
                    <div id="no-projects-msg" class="text-center text-muted mt-4 d-none">
                        <i class="bi bi-search fs-3 d-block mb-2 text-secondary"></i>
                        Keine Projekte gefunden.
                    </div>
                </div>
                
                <input type="hidden" name="project_id" id="project_id">

                <!-- STEP 3: POSITION SELECTION -->
                <div id="step-position" class="mb-4 d-none">
                    <h6>Position Auswahlen</h6>
                
                    <div id="positions-container" class="row g-2"></div>
                </div>
                
                <input type="hidden" name="position_id" id="position_id">

                <!-- STEP 4: MACHINE SELECTION -->
                <input type="hidden" name="machine_id" id="machine_id">

                <div id="step-machine" class="mb-4 d-none">
                    <h6>Maschine Auswahlen</h6>

                    <div class="row g-2">
                        @foreach($machines as $machine)
                            <div class="col-md-3">
                                <button type="button"
                                        class="btn btn-outline-dark w-100 machine-btn"
                                        data-id="{{ $machine->id }}">
                                    <i class="bi bi-cpu"></i> {{ $machine->name }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- STEP 5: STATUS SELECTION -->
                <div id="step-status" class="mb-4 d-none">
                    <h6>Status</h6>
                
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <div class="btn-group">
                            @foreach($statuses as $status)
                                @php
                                    $isMitAufsicht = strtolower($status->name ?? '') === 'mit aufsicht';
                                @endphp
                                <input type="radio"
                                    class="btn-check"
                                    name="status_id"
                                    id="status-{{ $status->id }}"
                                    value="{{ $status->id }}"
                                    required>
                    
                                <label class="btn btn-outline-dark"
                                    for="status-{{ $status->id }}">
                                    {{ $status->name }}
                                </label>
                            @endforeach
                        </div>
                        @if($isMitAufsicht)
                            <div class="d-none d-flex align-items-center gap-2" id="manual-process-wrap">
                                <div class="form-check m-0">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        id="manual-process-checkbox"
                                        name="manual_process"
                                        value="1">
                                    <label class="form-check-label ms-1" for="manual-process-checkbox">
                                        Manueller Prozess
                                    </label>
                                </div>
                                <div id="manual-process-name-wrap" class="d-none ms-3">
                                    <input type="text"
                                        class="form-control form-control-sm border-dark-subtle shadow-sm"
                                        id="manual-process-name"
                                        name="manual_process_name"
                                        placeholder="Prozess Name"
                                        autocomplete="off">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success d-none" id="start-btn">
                    <i class="bi bi-play-circle"></i> Start
                </button>
                
            </form>
        </div>
    </div>
</div>

<script>
    let selectedCompany = null;

    /* ========== UTIL HELPERS ========== */
    function activateButton(button, selector) {
        document.querySelectorAll(selector).forEach(b => {
            b.classList.remove('btn-primary', 'btn-success', 'btn-dark', 'active');
            b.classList.add('btn-outline-dark');
        });

        button.classList.remove('btn-outline-dark');
        button.classList.add('btn-primary', 'active');
    }
    
    document.querySelectorAll('.user-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            activateButton(btn, '.user-btn');
            document.getElementById('user_id').value = btn.dataset.userId;
            selectedCompany = btn.dataset.company;
            
            // Update project labels AFTER user selection
            document.querySelectorAll('.project-btn').forEach(projectBtn => {
                const label = projectBtn.querySelector('.project-auftrag');
                label.textContent = selectedCompany === 'ZF'
                    ? `(ZF: ${projectBtn.dataset.zf ?? '—'})`
                    : `(ZT: ${projectBtn.dataset.zt ?? '—'})`;
            });
    
            document.getElementById('step-project').classList.remove('d-none');
            
            // Auto-focus search input if on a device with keyboard, skip for pure touch
            const searchInput = document.getElementById('project-search');
            if(searchInput && window.innerWidth > 768) {
                searchInput.focus();
            }
        });
    });
    
    /* ========== STEP 2: PROJECT ========== */
    document.querySelectorAll('.project-btn').forEach(btn => {
    
        btn.addEventListener('click', () => {
            activateButton(btn, '.project-btn');
            document.getElementById('project_id').value = btn.dataset.projectId;
    
            const positions = JSON.parse(btn.dataset.positions);
            const container = document.getElementById('positions-container');
            container.innerHTML = '';
    
            positions.forEach(pos => {
                container.insertAdjacentHTML('beforeend', `
                    <div class="col-md-4">
                        <button type="button"
                                class="btn btn-outline-secondary w-100 position-btn py-2"
                                data-id="${pos.id}">
                            ${pos.name}
                        </button>
                    </div>
                `);
            });
    
            document.getElementById('step-position').classList.remove('d-none');
        });
    });

    /* ========== PROJECT LIVE SEARCH LOGIC ========== */
    const searchInput = document.getElementById('project-search');
    const projectWrappers = document.querySelectorAll('.project-wrapper');
    const noProjectsMsg = document.getElementById('no-projects-msg');

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            let visibleCount = 0;

            projectWrappers.forEach(wrapper => {
                const searchableText = wrapper.getAttribute('data-search');
                
                if (searchableText.includes(searchTerm)) {
                    wrapper.classList.remove('d-none');
                    visibleCount++;
                } else {
                    wrapper.classList.add('d-none');
                }
            });

            // Show/Hide "No projects found" message
            if (visibleCount === 0) {
                noProjectsMsg.classList.remove('d-none');
            } else {
                noProjectsMsg.classList.add('d-none');
            }
        });
    }
    
    /* ========== STEP 3: POSITION ========== */
    document.addEventListener('click', e => {
        // Use closest to handle clicks on inner elements if you ever add icons
        const btn = e.target.closest('.position-btn');
        if (btn) {
            activateButton(btn, '.position-btn');
            document.getElementById('position_id').value = btn.dataset.id;
    
            document.getElementById('step-machine').classList.remove('d-none');
        }
    });

    /* ========== STEP 4: MACHINE BUTTONS ========== */
    document.addEventListener('click', e => {
        const btn = e.target.closest('.machine-btn');
        if (btn) {
            activateButton(btn, '.machine-btn');

            document.getElementById('machine_id').value = btn.dataset.id;

            document.getElementById('step-status').classList.remove('d-none');
            document.getElementById('start-btn').classList.remove('d-none');
        }
    });

    /* ========== MANUAL PROCESS (Mit Aufsicht) ========== */
    const manualProcessWrap = document.getElementById('manual-process-wrap');
    const manualProcessCheckbox = document.getElementById('manual-process-checkbox');
    const manualProcessNameWrap = document.getElementById('manual-process-name-wrap');
    const manualProcessName = document.getElementById('manual-process-name');

    function toggleManualProcessUI() {
        if (!manualProcessWrap || !manualProcessCheckbox || !manualProcessNameWrap || !manualProcessName) {
            return;
        }

        const selectedStatus = document.querySelector('input[name="status_id"]:checked');
        const isMitAufsicht = selectedStatus && selectedStatus.nextElementSibling
            ? selectedStatus.nextElementSibling.textContent.trim().toLowerCase() === 'mit aufsicht'
            : false;

        if (!isMitAufsicht) {
            manualProcessWrap.classList.add('d-none');
            manualProcessCheckbox.checked = false;
            manualProcessNameWrap.classList.add('d-none');
            manualProcessName.required = false;
            manualProcessName.value = '';
            return;
        }

        manualProcessWrap.classList.remove('d-none');
        if (manualProcessCheckbox.checked) {
            manualProcessNameWrap.classList.remove('d-none');
            manualProcessName.required = true;
        } else {
            manualProcessNameWrap.classList.add('d-none');
            manualProcessName.required = false;
            manualProcessName.value = '';
        }
    }

    document.addEventListener('change', e => {
        if (e.target.name === 'status_id' || e.target.id === 'manual-process-checkbox') {
            toggleManualProcessUI();
        }
    });
</script>    
@endsection