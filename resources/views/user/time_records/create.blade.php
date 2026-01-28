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
                <div id="step-user" class="mb-4">
                    <h6>Select Operator</h6>
                
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
                </div>

                <input type="hidden" name="user_id" id="user_id">

                <div id="step-project" class="mb-4 d-none">
                    <h6>Select Project</h6>
                
                    <div class="row g-2">
                        @foreach($projects as $project)
                            <div class="col-md-4">
                                <button type="button"
                                        class="btn btn-outline-primary w-100 project-btn"
                                        data-project-id="{{ $project->id }}"
                                        data-zt="{{ $project->auftragsnummer_zt }}"
                                        data-zf="{{ $project->auftragsnummer_zf }}"
                                        data-positions='@json($project->positions)'>
                                    {{ $project->project_name }}
                                    <small class="text-muted d-block project-auftrag"></small>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <input type="hidden" name="project_id" id="project_id">

                <div id="step-position" class="mb-4 d-none">
                    <h6>Select Position</h6>
                
                    <div id="positions-container" class="row g-2"></div>
                </div>
                
                <input type="hidden" name="position_id" id="position_id">

                <input type="hidden" name="machine_id" id="machine_id">

                <div id="step-machine" class="mb-4 d-none">
                    <h6>Select Machine</h6>

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
                
                <div id="step-status" class="mb-4 d-none">
                    <h6>Status</h6>
                
                    <div class="btn-group">
                        @foreach($statuses as $status)
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
                </div>
                
                <button type="submit" class="btn btn-success d-none" id="start-btn">
                    <i class="bi bi-play-circle"></i> Start Time
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
                                class="btn btn-outline-secondary w-100 position-btn"
                                data-id="${pos.id}">
                            ${pos.name}
                        </button>
                    </div>
                `);
            });
    
            document.getElementById('step-position').classList.remove('d-none');
        });
    });
    
    /* ========== STEP 3: POSITION ========== */
    document.addEventListener('click', e => {
        if (e.target.classList.contains('position-btn')) {
            activateButton(e.target, '.position-btn');
            document.getElementById('position_id').value = e.target.dataset.id;
    
            document.getElementById('step-machine').classList.remove('d-none');
        }
    });

    /* ========== STEP 4: MACHINE BUTTONS ========== */
    document.addEventListener('click', e => {
        if (e.target.classList.contains('machine-btn')) {
            activateButton(e.target, '.machine-btn');

            document.getElementById('machine_id').value = e.target.dataset.id;

            document.getElementById('step-status').classList.remove('d-none');
            document.getElementById('start-btn').classList.remove('d-none');
        }
    });
</script>    
@endsection
