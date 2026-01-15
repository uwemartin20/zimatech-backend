<?php

namespace App\Services;

use GuzzleHttp\Client;

class GraphService {

    private string $tenantId;
    private string $clientId;
    private string $clientSecret;
    private string $scope;

    public function __construct()
    {
        $this->tenantId = env('GRAPH_TENANT_ID');
        $this->clientId = env('GRAPH_CLIENT_ID');
        $this->clientSecret = env('GRAPH_CLIENT_SECRET');
        $this->scope = env('GRAPH_SCOPE');
    }

    /**
     * Get access token using client credentials
     */
    public function getAccessToken(): string
    {
        $client = new Client();

        $response = $client->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", [
            'form_params' => [
                'client_id' => $this->clientId,
                'scope' => $this->scope,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['access_token'];
    }

    /**
     * Call Microsoft Graph API
     */
    public function getUserMessages(string $userPrincipalName, $direction = 'incoming', int $top = 10)
    {
        $accessToken = $this->getAccessToken();

        $client = new Client([
            'base_uri' => 'https://graph.microsoft.com/v1.0/',
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Accept' => 'application/json',
            ],
        ]);

        $dir = ($direction == 'incoming') ? 'Inbox' : 'SentItems';

        $response = $client->get("users/{$userPrincipalName}//mailFolders/{$dir}/messages?\$top={$top}&\$orderby=receivedDateTime desc");

        return json_decode($response->getBody()->getContents(), true);
    }

    public function sendEmail(string $userPrincipalName, string $to, string $subject, string $body)
    {
        $accessToken = $this->getAccessToken();

        $client = new Client([
            'base_uri' => 'https://graph.microsoft.com/v1.0/',
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ],
        ]);

        $emailData = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'Text', // or 'HTML'
                    'content' => $body,
                ],
                'toRecipients' => [
                    [
                        'emailAddress' => [
                            'address' => $to,
                        ],
                    ],
                ],
            ],
            'saveToSentItems' => true, // optional: save in Sent Items
        ];

        $response = $client->post("users/{$userPrincipalName}/sendMail", [
            'json' => $emailData,
        ]);

        return $response->getStatusCode() === 202 ? 'Email sent successfully' : 'Failed to send email';
    }
}
