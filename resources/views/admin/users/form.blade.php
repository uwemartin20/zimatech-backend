@extends('admin.layouts.index')

@section('title', isset($user) ? 'Edit User' : 'Add New User')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">{{ isset($user) ? 'Edit User' : 'Add New User' }}</h4>

    <form method="POST"
          action="{{ isset($user)
                ? route('admin.users.update', $user)
                : route('admin.users.store') }}"
          class="col-md-6">

        @csrf
        @isset($user)
            @method('PUT')
        @endisset

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $user->name ?? '') }}"
                   class="form-control"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email"
                   name="email"
                   value="{{ old('email', $user->email ?? '') }}"
                   class="form-control"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">
                Password {{ isset($user) ? '(leave blank to keep)' : '' }}
            </label>
            <input type="password"
                   name="password"
                   class="form-control"
                   {{ isset($user) ? '' : 'required' }}>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password"
                   name="password_confirmation"
                   class="form-control"
                   {{ isset($user) ? '' : 'required' }}>
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="user" @selected(old('role', $user->role ?? '') === 'user')>User</option>
                <option value="admin" @selected(old('role', $user->role ?? '') === 'admin')>Admin</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Company</label>
            <select name="company" class="form-select" required>
                <option value="ZF" @selected(old('company', $user->company ?? '') === 'ZF')>ZF</option>
                <option value="ZT" @selected(old('company', $user->company ?? '') === 'ZT')>ZT</option>
            </select>
        </div>

        <button class="btn btn-success">
            {{ isset($user) ? 'Update User' : 'Create User' }}
        </button>

        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
            Cancel
        </a>
    </form>
</div>
@endsection
