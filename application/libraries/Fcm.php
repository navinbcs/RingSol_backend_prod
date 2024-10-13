<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once 'S:\Ring_php_UAT/google-api-php-client/vendor/autoload.php';
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Fcm {
    private $CI;
    private $serviceAccountKeyFile;
    private $projectID;

    public function __construct($params) {
        $this->CI =& get_instance();
        $this->serviceAccountKeyFile = $params['serviceAccountKeyFile'];
        $this->projectID = $params['projectID'];
    }

    /**
     * Get OAuth 2.0 Access Token using Service Account
     */
    private function getAccessToken() {
        // Define the scope for Firebase Cloud Messaging
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

        // Use Google service account credentials
        $credentials = new ServiceAccountCredentials($scopes, $this->serviceAccountKeyFile);

        // Fetch the OAuth 2.0 access token
        $accessToken = $credentials->fetchAuthToken();

        // Return the access token
        return $accessToken['access_token'];
    }

    /**
     * Send Firebase Cloud Messaging (FCM) Notification
     */
    public function sendNotification($token, $title, $body) {
        $accessToken = $this->getAccessToken();
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectID}/messages:send";
        
        $notification = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ]
            ]
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json'
        ];

        // Send the notification using Guzzle HTTP Client
        $client = new Client();
        try {
            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $notification
            ]);
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return $e->getResponse()->getBody()->getContents();
            }
            return $e->getMessage();
        }
    }
}
