@extends('admin.layouts.index')

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Create New Project</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.projects.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="kunde" class="form-label">Kunde Name</label>
                        <input type="text" name="kunde" id="kunde" class="form-control" placeholder="Enter customer name" required>
                    </div>

                    <div class="mb-3">
                        <label for="auftragsnummer" class="form-label">Auftragsnummer</label>
                        <input type="text" name="auftragsnummer" id="auftragsnummer" class="form-control" placeholder="Enter order number" required>
                    </div>

                    <div class="mb-3">
                        <label for="project_name" class="form-label">Project Name</label>
                        <input type="text" name="project_name" id="project_name" class="form-control" placeholder="Enter project name" required>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="save_to_db" name="save_to_db" value="1">
                        <label class="form-check-label" for="save_to_db">
                            Save project details to database
                        </label>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Create Project</button>
                    </div>
                </form>
            </div>
        </div>
</div>
@endsection
