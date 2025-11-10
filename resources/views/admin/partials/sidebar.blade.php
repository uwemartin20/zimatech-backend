<div class="sidebar d-flex flex-column p-3">
    <div class="d-flex align-items-center justify-content-center mb-3">
        <img src="{{ asset('images/zimmermann-logo-192.png') }}" alt="Zimatec Logo"
            class="img-fluid me-2" style="width: 40px; height: 40px;">
        <h5 class="fw-bold mb-0">ZiMaTec</h5>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2 me-2"></i>Dashboard
    </a>
    
    {{-- Users with submenu --}}
    @php
        $usersActive = request()->is('admin/users*');
    @endphp
    <a data-bs-toggle="collapse" href="#usersSubmenu" role="button"
       aria-expanded="{{ $usersActive ? 'true' : 'false' }}"
       aria-controls="usersSubmenu"
       class="{{ $usersActive ? 'active' : '' }}">
        <i class="bi bi-people me-2"></i> Team
    </a>
    <div class="collapse submenu {{ $usersActive ? 'show' : '' }}" id="usersSubmenu">
        <a href="{{ route('admin.users') }}" class="{{ request()->is('admin/users') ? 'active' : '' }}">Alle Benutzer</a>
        <a href="{{ route('admin.users.create') }}" class="{{ request()->is('admin/users/create') ? 'active' : '' }}">Benutzer Erstellen</a>
    </div>

    {{-- Projects with submenu --}}
    @php
        $projectsActive = request()->is('admin/projects*');
    @endphp
    <a data-bs-toggle="collapse" href="#projectsSubmenu" role="button"
       aria-expanded="{{ $projectsActive ? 'true' : 'false' }}"
       aria-controls="projectsSubmenu"
       class="{{ $projectsActive ? 'active' : '' }}">
        <i class="bi bi-folder2-open me-2"></i> Projekte
    </a>
    <div class="collapse submenu {{ $projectsActive ? 'show' : '' }}" id="projectsSubmenu">
        <a href="{{ route('admin.projects') }}" class="{{ request()->is('admin/projects') ? 'active' : '' }}">Alle Projekten</a>
        <a href="{{ route('admin.projects.create') }}" class="{{ request()->is('admin/projects/create') ? 'active' : '' }}">Projekt Erstellen</a>
    </div>

    {{-- Projects with submenu --}}
    @php
        $timeActive = request()->is('admin/time*');
    @endphp
    <a data-bs-toggle="collapse" href="#timeSubmenu" role="button"
       aria-expanded="{{ $timeActive ? 'true' : 'false' }}"
       aria-controls="timeSubmenu"
       class="{{ $timeActive ? 'active' : '' }}">
        <i class="bi bi-folder2-open me-2"></i> Zeit Management
    </a>
    <div class="collapse submenu {{ $timeActive ? 'show' : '' }}" id="timeSubmenu">
        <a href="{{ route('admin.time.logs') }}" class="{{ request()->is('admin/time/logs') ? 'active' : '' }}">Machine Zeiten</a>
        <a href="{{ route('admin.time.records') }}" class="{{ request()->is('admin/time/records*') ? 'active' : '' }}">Mann Zeiten</a>
        <a href="{{ route('admin.time.compare') }}" class="{{ request()->is('admin/time/compare') ? 'active' : '' }}">Zeit Vergleichen</a>
        <a href="{{ route('admin.time.change') }}" class="{{ request()->is('admin/time/change') ? 'active' : '' }}">Nachtrag Requests</a>
    </div>

    {{-- Settings --}}
    @php
        $settingsActive = request()->is('admin/settings*');
    @endphp
    <a data-bs-toggle="collapse" href="#settingsSubmenu" role="button"
       aria-expanded="{{ $settingsActive ? 'true' : 'false' }}"
       aria-controls="settingsSubmenu"
       class="{{ $settingsActive ? 'active' : '' }}">
        <i class="bi bi-gear me-2"></i> Einstellungen
    </a>
    <div class="collapse submenu {{ $settingsActive ? 'show' : '' }}" id="settingsSubmenu">
        <a href="{{ route('admin.settings.machine-status') }}" class="{{ request()->is('admin/settings/machine-status') ? 'active' : '' }}">Machine Status</a>
        <a href="#" class="">Permissions</a>
        <a href="#" class="">Logs</a>
    </div>

    <hr class="text-secondary">
</div>
