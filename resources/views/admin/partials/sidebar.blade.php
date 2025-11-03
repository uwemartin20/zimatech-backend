<div class="sidebar d-flex flex-column p-3">
    <div class="d-flex align-items-center justify-content-center mb-3">
        <img src="{{ asset('images/zimmermann-logo-192.png') }}" alt="Zimatech Logo"
            class="img-fluid me-2" style="width: 40px; height: 40px;">
        <h5 class="fw-bold mb-0">Zimatech</h5>
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
        <i class="bi bi-people me-2"></i> Users
    </a>
    <div class="collapse submenu {{ $usersActive ? 'show' : '' }}" id="usersSubmenu">
        <a href="{{ route('admin.users') }}" class="{{ request()->is('admin/users') ? 'active' : '' }}">All Users</a>
        <a href="{{ route('admin.users.create') }}" class="{{ request()->is('admin/users/create') ? 'active' : '' }}">Add User</a>
    </div>

    {{-- Projects with submenu --}}
    @php
        $projectsActive = request()->is('admin/projects*');
    @endphp
    <a data-bs-toggle="collapse" href="#projectsSubmenu" role="button"
       aria-expanded="{{ $projectsActive ? 'true' : 'false' }}"
       aria-controls="projectsSubmenu"
       class="{{ $projectsActive ? 'active' : '' }}">
        <i class="bi bi-folder2-open me-2"></i> Projects
    </a>
    <div class="collapse submenu {{ $projectsActive ? 'show' : '' }}" id="projectsSubmenu">
        <a href="{{ route('admin.projects') }}" class="{{ request()->is('admin/projects') ? 'active' : '' }}">All Projects</a>
        <a href="{{ route('admin.projects.create') }}" class="{{ request()->is('admin/projects/create') ? 'active' : '' }}">Add Project</a>
        <a href="{{ route('admin.projects.logs') }}" class="{{ request()->is('admin/projects/logs') ? 'active' : '' }}">Show Logs</a>
    </div>

    {{-- Settings --}}
    @php
        $settingsActive = request()->is('admin/settings*');
    @endphp
    <a data-bs-toggle="collapse" href="#settingsSubmenu" role="button"
       aria-expanded="{{ $settingsActive ? 'true' : 'false' }}"
       aria-controls="settingsSubmenu"
       class="{{ $settingsActive ? 'active' : '' }}">
        <i class="bi bi-gear me-2"></i> Settings
    </a>
    <div class="collapse submenu {{ $settingsActive ? 'show' : '' }}" id="settingsSubmenu">
        <a href="#" class="">General</a>
        <a href="#" class="">Permissions</a>
        <a href="#" class="">Logs</a>
    </div>

    <hr class="text-secondary">
</div>
