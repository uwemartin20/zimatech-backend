@extends('admin.layouts.index')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Alle E-Mails</h5>
            <a href="{{ route('admin.emails.new') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Neue Email
            </a>
        </div>

        <div class="card-body">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Sender</th>
                        <th>Empfänger</th>
                        <th>Betreff</th>
                        <th>Body</th>
                        <th>Anhänge</th>
                        <th>Datum</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($emails as $email)
                        <tr>
                            <td>{{ $email['sender'] }}</td>
                            <td>{{ $email['recipient'] }}</td>
                            <td>
                                <a href="{{ route('admin.emails.show', ['id' => $email['id']]) }}">
                                    {{ $email['subject'] }}
                                </a>
                            </td>
                            <td>{!! Str::limit(strip_tags($email['body']), 100) !!}</td>
                            <td>{{ $email['attachments'] }}</td>
                            <td>{{ $email['date'] }}</td>
                        </tr>
                    @endforeach
                    @if($emails->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center">Keine E-Mails gefunden</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
