@extends('admin.layouts.index')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5>Edit Offer Email</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.project_offers.update_email', $email->id) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label>Subject</label>
                    <input type="text" name="subject" class="form-control" value="{{ $email->subject }}" required>
                </div>

                <div class="mb-3">
                    <label>Body</label>
                    <textarea name="body" class="form-control" rows="4">{{ $email->body }}</textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Sender</label>
                        <input type="text" name="sender" class="form-control" value="{{ $email->sender }}">
                    </div>
                    <div class="col-md-6">
                        <label>Recipient</label>
                        <input type="text" name="recipient" class="form-control" value="{{ $email->recipient }}">
                    </div>
                </div>

                {{-- Email attachments --}}
                @if($email->files->count())
                    <hr>
                    <strong>Attachments:</strong>
                    <ul class="list-group mt-2">
                        @foreach($email->files as $efile)
                            <li class="list-group-item d-flex justify-content-between">
                                <a href="{{ asset('storage/'.$efile->file_path) }}" target="_blank">{{ $efile->file_name }}</a>
                                <small>{{ $efile->created_at->format('d M Y') }}</small>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mb-3">
                    <label>Attachment Erstellen</label>
                    <input type="file" name="pdf" class="form-control" accept="application/pdf">
                    @if($email->pdf_path)
                        <p class="mt-2">
                            <a href="{{ asset('storage/'.$email->pdf_path) }}" target="_blank" class="text-decoration-none">
                                <i class="bi bi-file-earmark-pdf text-danger"></i> View Current PDF
                            </a>
                        </p>
                    @endif
                </div>

                <button class="btn btn-wechsel">Update</button>
                <a href="{{ route('admin.project_offers.show', $email->project_offer_id) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
