<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h6 class="mb-0">Add Additional File</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.project_offers.add_file', $offer->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label>File Name</label>
                    <input type="text" name="file_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>File</label>
                    <input type="file" name="file" class="form-control" required>
                </div>
                <div class="col-12">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <button class="btn btn-success mt-3"><i class="bi bi-upload me-1"></i> Upload File</button>
        </form>
    </div>
</div>
