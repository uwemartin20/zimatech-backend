@extends('admin.layouts.index')

@section('title', 'Settings - Machines')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Machines</h5>
            <a href="{{ route('admin.settings.machines.show') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Neue Maschine
            </a>
        </div>

        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($machines as $machine)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $machine->name }}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input type="checkbox"
                                           class="form-check-input toggle-active"
                                           data-id="{{ $machine->id }}"
                                           {{ $machine->active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.settings.machines.show', $machine->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <form action="{{ route('admin.settings.machines.delete', $machine->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Delete this machine?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-active').forEach(el => {
    el.addEventListener('change', function () {
        fetch(`/admin/settings/machines/toggle/${this.dataset.id}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert('Something went wrong');
            }
        });
    });
});
</script>
@endsection
