@extends('admin.layouts.index')

@section('content')
<div class="container">
    <h2 class="mb-4">My Profile</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.profile.update') }}">
        @csrf
        @method('PUT')

        {{-- Name --}}
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input
                type="text"
                name="name"
                class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $user->name) }}"
                required
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input
                type="email"
                name="email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $user->email) }}"
                required
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Role (read-only) --}}
        <div class="mb-3">
            <label class="form-label">Role</label>
            <input
                type="text"
                class="form-control"
                value="{{ ucfirst($user->role) }}"
                disabled
            >
        </div>

        <hr>

        {{-- Password --}}
        <div class="mb-3">
            <label class="form-label">New Password (optional)</label>
            <input
                type="password"
                name="password"
                class="form-control @error('password') is-invalid @enderror"
            >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password Confirmation --}}
        <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input
                type="password"
                name="password_confirmation"
                class="form-control"
            >
        </div>

        <button type="submit" class="btn btn-primary">
            Update Profile
        </button>
    </form>
</div>
@endsection
