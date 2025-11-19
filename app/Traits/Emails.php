<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\OfferFile;
use Illuminate\Support\Facades\Auth;

trait Emails
{
    public function getEmail()
    {

    }

    public function setEmail($email)
    {

    }

    public function saveOfferEmail($offer, $dataOrRequest, $direction = 'inbound')
    {
        // If a request is passed, validate
        if ($dataOrRequest instanceof Request) {
            $data = $dataOrRequest->validate([
                'subject'   => 'required|string|max:255',
                'body'      => 'nullable|string',
                'sender'    => 'nullable|string|max:255',
                'recipient' => 'nullable|string|max:255',
                'direction' => 'nullable|string',
                'attachments'=> 'nullable|array',
            ]);
        } else {
            $data = $dataOrRequest; // Assume array
        }

        $data['direction'] = $direction;

        // Save email via relationship
        $email = $offer->emails()->create($data);

        // Handle attachments if present
        if (!empty($data['attachments']) && is_array($data['attachments'])) {
            foreach ($data['attachments'] as $attachment) {
                // Scenario 1: associative array with 'file'
                if (is_array($attachment) && isset($attachment['file']) && $attachment['file'] instanceof \Illuminate\Http\UploadedFile) {
                    $uploadedFile = $attachment['file'];
                    $fileName = $attachment['file_name'] ?? $uploadedFile->getClientOriginalName();
                    $description = $attachment['description'] ?? null;
                }
                // Scenario 2: direct UploadedFile object
                elseif ($attachment instanceof \Illuminate\Http\UploadedFile) {
                    $uploadedFile = $attachment;
                    $fileName = $uploadedFile->getClientOriginalName();
                    $description = null;
                }
                else {
                    // Not a valid file, skip
                    continue;
                }
                $filePath = $uploadedFile->store('project_offers/email_attachments', 'public');

                OfferFile::create([
                    'project_offer_id' => $offer->id,
                    'offer_email_id'   => $email->id,
                    'file_name'        => $fileName,
                    'description'      => $description,
                    'file_path'        => $filePath,
                    'uploaded_by'      => Auth::id(),
                ]);
            }
        }

        if ($direction == 'inbound') {
            $action = 'recieved';
        } else {
            $action = 'sent';
        }

        // Create admin notification
        Notification::create([
            'user_id' => auth()->id(),
            'type'    => 'offer_email_'.$action,
            'message' => 'New offer email '.$action.': ' . $email->subject,
            'url'     => route('admin.project_offers.show', $offer->id),
        ]);

        return $email;
    }
}
