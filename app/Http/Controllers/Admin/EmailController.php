<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GraphService;
use GuzzleHttp\Client;

class EmailController extends Controller
{
    public function emails(GraphService $graphService) 
    {
        $user = env('IMAP_USERNAME');
        $messages = $graphService->getUserMessages($user, 'incoming');

        // Map emails for easier Blade consumption
        $emails = collect($messages['value'])->map(function($msg) {
            return [
                'id' => $msg['id'],
                'sender' => $msg['from']['emailAddress']['name'] ?? 'Unknown',
                'recipient' => collect($msg['toRecipients'])->pluck('emailAddress.name')->join(', '),
                'subject' => $msg['subject'] ?? '',
                'body' => $msg['body']['content'] ?? '',
                'attachments' => $msg['hasAttachments'] ? 'Yes' : 'No',
                'date' => \Carbon\Carbon::parse($msg['receivedDateTime'])->format('d.m.Y H:i'),
            ];
        });

        return view('admin.emails.index', compact('emails'));
    }

    public function emailsSent(GraphService $graphService) 
    {
        $user = env('IMAP_USERNAME');
        $messages = $graphService->getUserMessages($user, 'outgoing');

        // Map emails for easier Blade consumption
        $emails = collect($messages['value'])->map(function($msg) {
            return [
                'id' => $msg['id'],
                'sender' => $msg['from']['emailAddress']['name'] ?? 'Unknown',
                'recipient' => collect($msg['toRecipients'])->pluck('emailAddress.name')->join(', '),
                'subject' => $msg['subject'] ?? '',
                'body' => $msg['body']['content'] ?? '',
                'attachments' => $msg['hasAttachments'] ? 'Yes' : 'No',
                'date' => \Carbon\Carbon::parse($msg['receivedDateTime'])->format('d.m.Y H:i'),
            ];
        });

        return view('admin.emails.index', compact('emails'));
    }

    public function compose()
    {
        return view('admin.emails.new-email');
    }

    public function send(GraphService $graphService, Request $request)
    {
        $request->validate([
            'recipient' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'attachments.*' => 'file|max:5120', // optional, max 5MB
        ]);

        $user = env('IMAP_USERNAME'); // your sending account
        $subject = $request->subject;
        $body = $request->body;
        $to = $request->recipient;

        // Call your existing sendEmail function
        $result = $graphService->sendEmail($user, $to, $subject, $body);

        return redirect()->back()->with('success', $result);
    }

    public function show(GraphService $graphService, string $id)
    {
        $user = env('IMAP_USERNAME');

        $accessToken = $graphService->getAccessToken();
        $client = new Client([
            'base_uri' => 'https://graph.microsoft.com/v1.0/',
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Accept' => 'application/json',
            ],
        ]);

        // Get the email by ID
        $response = $client->get("users/{$user}/messages/{$id}");
        $email = json_decode($response->getBody(), true);

        // Get attachments if any
        $attachmentsResponse = $client->get("users/{$user}/messages/{$id}/attachments");
        $attachments = json_decode($attachmentsResponse->getBody(), true)['value'];

        return view('admin.emails.show', compact('email', 'attachments'));
    }
}
