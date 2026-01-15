@extends('admin.layouts.index')

@section('title', 'Settings - Machine Statuses')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Projekte Statuses</h5>
            <a href="{{ route('admin.settings.project-status.show') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Neue Projektestatus
            </a>
        </div>

        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Status Name</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statuses as $status)
                        <tr id="row-{{ $status->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $status->name }}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input toggle-active"
                                        data-id="{{ $status->id }}" {{ $status->active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.settings.project-status.show', $status->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.settings.project-status.delete', $status->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this status?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                           <td colspan="4" class="text-center py-4 text-muted">No Records Yet.</td> 
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- === AJAX Script for toggle === --}}
<script>
document.querySelectorAll('.toggle-active').forEach((checkbox) => {
    checkbox.addEventListener('change', function() {
        const id = this.dataset.id;
        fetch(`/admin/settings/project-status/toggle/${id}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const status = data.active ? 'activated' : 'deactivated';
                console.log(`Status ${id} ${status}`);
                showAlert(`Project status ${status} successfully.`, 'success');
            } else {
                showAlert('Something went wrong.', 'danger');
            }
        })
        .catch(err => {
            console.error(err);
            showAlert('Server error, please try again.', 'danger');
        });
    });
});
</script>
@endsection
