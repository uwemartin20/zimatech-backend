@extends('admin.layouts.index')

@section('content')
<div class="container">
    <h1>{{ $email['subject'] }}</h1>
    <p>
        <strong>From:</strong> {{ $email['from']['emailAddress']['name'] ?? '' }} &lt;{{ $email['from']['emailAddress']['address'] ?? '' }}&gt;<br>
        <strong>To:</strong> 
        @foreach($email['toRecipients'] as $recipient)
            {{ $recipient['emailAddress']['name'] ?? '' }} &lt;{{ $recipient['emailAddress']['address'] }}&gt;@if(!$loop->last), @endif
        @endforeach
        <br>
        <strong>Sent:</strong> {{ \Carbon\Carbon::parse($email['sentDateTime'])->format('d M Y H:i') }}
    </p>

    <hr>

    <div>
        {!! $email['body']['content'] !!} {{-- HTML content --}}
    </div>

    @if(count($attachments))
    <hr>
    <h4>Attachments:</h4>
    <ul>
        @foreach($attachments as $attachment)
            @if(isset($attachment['contentBytes']))
                <li>
                    <a href="data:{{ $attachment['contentType'] }};base64,{{ $attachment['contentBytes'] }}" download="{{ $attachment['name'] }}">
                        {{ $attachment['name'] }}
                    </a>
                </li>
            @else
                <li>{{ $attachment['name'] }} (not downloadable)</li>
            @endif
        @endforeach
    </ul>
    @endif
</div>
@endsection
