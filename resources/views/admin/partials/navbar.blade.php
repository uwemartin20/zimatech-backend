<nav class="navbar navbar-expand-lg navbar-light border-bottom shadow-sm px-3">
    <div class="container-fluid">
        @php
            $notifications = collect([]);
        @endphp
        {{-- Left Side --}}

        {{-- 🔍 Search Bar --}}
        <form class="d-flex justify-content-center w-100 position-relative" role="search" method="GET" action="{{ route('admin.search') }}">
            <div class="input-group" style="max-width: 600px; width: 100%;">
                <input type="search" name="q" class="form-control form-control-sm rounded-start-pill bg-light border-0 ps-3"
                    placeholder="Search..." aria-label="Search">
                <button class="btn btn-top-search rounded-end-pill px-3" type="submit" style="border: none;">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>

        <!-- Right side -->
        <div class="d-flex align-items-center ms-auto gap-3">

            <!-- 🔔 Notifications -->
            <div class="dropdown">
                <button class="btn btn-light position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $notifications->count() ?? 0 }}
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end p-2" style="width: 300px; max-height: 300px; overflow-y: auto;">
                    @forelse($notifications ?? [] as $note)
                        <li class="mb-2">
                            <small class="text-muted">{{ $note->created_at->format('d M Y, H:i') }}</small><br>
                            {{ $note->message }}
                        </li>
                        <li><hr class="dropdown-divider"></li>
                    @empty
                        <li class="text-center text-muted">No notifications</li>
                    @endforelse
                </ul>
            </div>

            <!-- 🧑‍💼 User Menu -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle user-menu" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-4 me-2 text-primary"></i>
                    <span class="fw-semibold username-text">{{ Auth::user()->name ?? 'Admin' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                    <li><a class="dropdown-item" href="{{ route('admin.users.profile') }}">Manage Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</nav>
