@extends('admin.layouts.index')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5>Compose Email</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.emails.send') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label>Recipient</label>
                    <input type="email" name="recipient" class="form-control" placeholder="Recipient email" required>
                </div>

                <div class="mb-3">
                    <label>Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="Email subject" required>
                </div>

                <div class="mb-3">
                    <label>Body</label>
                    <textarea name="body" class="form-control" rows="6" placeholder="Type your email here..." required></textarea>
                </div>

                {{-- Optional attachments --}}
                <div class="mb-3">
                    <label>Attachments</label>
                    <input type="file" name="attachments[]" class="form-control" multiple>
                    <small class="text-muted">You can select multiple files</small>
                </div>

                <button type="submit" class="btn btn-primary">Send Email</button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
