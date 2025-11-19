<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h6 class="mb-0">Add Offer Email</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.project_offers.add_email', $offer->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label>Subject</label>
                <input type="text" name="subject" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Body</label>
                <textarea name="body" class="form-control" rows="3"></textarea>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Sender</label>
                    <input type="text" name="sender" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Recipient</label>
                    <input type="text" name="recipient" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label>PDF File</label>
                <input type="file" name="pdf" class="form-control" accept="application/pdf">
            </div>

            <button class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Add Email</button>
        </form>
    </div>
</div>
