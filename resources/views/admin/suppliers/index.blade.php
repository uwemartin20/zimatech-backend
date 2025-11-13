@extends('admin.layouts.index')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Alle Lieferanten</h5>
            <a href="{{ route('admin.suppliers.create') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Neuer Lieferant
            </a>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('admin.suppliers.index') }}" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Suche nach Name oder Firma..."
                               value="{{ request('search') }}">
                    </div>
            
                    <div class="col-md-4">
                        <select name="service_id" class="form-select">
                            <option value="">-- Alle Dienstleistungen --</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
            
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-filter">
                            <i class="bi bi-search me-1"></i> Filtern
                        </button>
            
                        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Zurücksetzen
                        </a>
                    </div>
                </div>
            </form>

            <table class="table table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Firma</th>
                        <th>Email</th>
                        <th>Telefon</th>
                        <th>Website</th>
                        <th>Dienstleistungen</th>
                        <th class="text-end">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->company }}</td>
                            <td>{{ $supplier->email }}</td>
                            <td>{{ $supplier->phone_number }}</td>
                            <td>
                                @if ($supplier->website)
                                    <a href="{{ $supplier->website }}" target="_blank">{{ parse_url($supplier->website, PHP_URL_HOST) }}</a>
                                @endif
                            </td>
                            <td>
                                @foreach ($supplier->services as $service)
                                    <span class="badge text-white" style="background-color: {{ $service->color }}">
                                        {{ $service->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.suppliers.show', $supplier) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Diesen Lieferanten wirklich löschen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Keine Lieferanten gefunden.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
