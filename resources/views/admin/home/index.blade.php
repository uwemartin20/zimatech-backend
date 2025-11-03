@extends('admin.layouts.index')

@section('title', 'Admin Dashboard')

@php
    // Summary counts
    $projectsCount = 12;
    $usersCount = 25;
    $processesCount = 48;

    // Recent Projects
    $recentProjects = [
        (object)[
            'project_name' => '225054_Ablegetool',
            'owner' => (object)['name' => 'Alice'],
            'start_time' => \Carbon\Carbon::now()->subDays(10),
            'end_time' => \Carbon\Carbon::now()->addDays(5),
            'status' => 'in_progress'
        ],
        (object)[
            'project_name' => '225055_FixTool',
            'owner' => (object)['name' => 'Bob'],
            'start_time' => \Carbon\Carbon::now()->subDays(20),
            'end_time' => \Carbon\Carbon::now()->subDays(2),
            'status' => 'completed'
        ],
        (object)[
            'project_name' => '225056_NewTool',
            'owner' => (object)['name' => 'Charlie'],
            'start_time' => \Carbon\Carbon::now()->subDays(5),
            'end_time' => \Carbon\Carbon::now()->addDays(10),
            'status' => 'pending'
        ],
    ];

    // Projects chart (labels and processes count)
    $projectLabels = ['Ablegetool', 'FixTool', 'NewTool', 'OldTool', 'SampleTool'];
    $projectData = [8, 12, 4, 6, 10];

    // Users chart (labels and number of registrations per day)
    $userLabels = ['01 Nov', '02 Nov', '03 Nov', '04 Nov', '05 Nov', '06 Nov', '07 Nov'];
    $userData = [2, 3, 1, 5, 4, 2, 3];
@endphp

@section('content')
    <div class="container-fluid py-4">

        {{-- Dashboard Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">Dashboard</h3>
            <span class="text-muted">{{ \Carbon\Carbon::now()->format('l, d M Y') }}</span>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-4 mb-4">
            {{-- Projects --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-kanban-fill fs-2 text-primary me-3"></i>
                        <div>
                            <h5 class="card-title mb-0">Projects</h5>
                            <h3 class="fw-bold">{{ $projectsCount ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Users --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-people-fill fs-2 text-success me-3"></i>
                        <div>
                            <h5 class="card-title mb-0">Users</h5>
                            <h3 class="fw-bold">{{ $usersCount ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tasks / Processes --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-list-task fs-2 text-warning me-3"></i>
                        <div>
                            <h5 class="card-title mb-0">Processes</h5>
                            <h3 class="fw-bold">{{ $processesCount ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Projects Overview</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="projectsChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">User Registrations</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="usersChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Projects Table --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Projects</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Project Name</th>
                                    <th>Owner</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProjects as $project)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $project->project_name }}</td>
                                    <td>{{ $project->owner->name ?? 'N/A' }}</td>
                                    <td>{{ $project->start_time?->format('d M Y') ?? '-' }}</td>
                                    <td>{{ $project->end_time?->format('d M Y') ?? '-' }}</td>
                                    <td>
                                        @if($project->status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($project->status == 'in_progress')
                                            <span class="badge bg-warning">In Progress</span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const projectCtx = document.getElementById('projectsChart').getContext('2d');
    new Chart(projectCtx, {
        type: 'bar',
        data: {
            labels: @json($projectLabels),
            datasets: [{
                label: 'Processes per Project',
                data: @json($projectData),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    const userCtx = document.getElementById('usersChart').getContext('2d');
    new Chart(userCtx, {
        type: 'line',
        data: {
            labels: @json($userLabels),
            datasets: [{
                label: 'User Registrations',
                data: @json($userData),
                fill: true,
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                borderColor: 'rgba(255, 206, 86, 1)',
                tension: 0.3
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
@endsection
