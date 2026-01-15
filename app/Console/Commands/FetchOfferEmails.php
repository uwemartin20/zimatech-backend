<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use App\Models\ProjectOffer;
use App\Models\OfferEmail;

class FetchOfferEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:fetch-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch incoming offer request emails and attach to offers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = Client::account('default');
        $client->connect();

        $folder = $client->getFolder('INBOX');
        $messages = $folder->messages()->unseen()->get();

        foreach ($messages as $message) {
            $email = $message->getFrom()[0]->mail;
            $subject = $message->getSubject();

            // Check if offer with same subject exists, otherwise create new
            $offer = ProjectOffer::firstOrCreate(
                ['subject' => $subject],
                [
                    'customer_email' => $email,
                    'customer_name' => $message->getFrom()[0]->personal ?? 'Unknown',
                    'description' => strip_tags($message->getHTMLBody() ?? $message->getTextBody()),
                ]
            );

            OfferEmail::create([
                'project_offer_id' => $offer->id,
                'sender' => $email,
                'recipient' => env('MAIL_FROM_ADDRESS'),
                'subject' => $subject,
                'body' => $message->getHTMLBody() ?? $message->getTextBody(),
                'direction' => 'inbound',
            ]);

            $this->info("Fetched: {$subject}");
            $message->setFlag('Seen');
        }

        return Command::SUCCESS;
    }
}
