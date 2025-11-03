@extends('admin.layouts.index')

@section('title', 'Manage Users')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Users</h4>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add User</a>
    </div>

    <table class="table table-striped align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge bg-{{ $user->role === 'admin' ? 'warning' : 'secondary' }}">{{ ucfirst($user->role) }}</span></td>
                    <td>{{ $user->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $users->links() }}
</div>
@endsection
