<?php

namespace App\Plugin;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class PpaApiClient {
  private ClientInterface $client;

  public function __construct()
  {
    $this->client = new Client([
      'base_uri' => $_ENV['PPA_API_BASE_URL'],
    ]);
  }
  
  public function sendEmail(string $email, string $message, int $userId): bool {
    $payload = [
      'message_CONTENT' => $message,
      'message_DATETIME' => date('Y-m-d H:m:s', strtotime(' +1 day')),
      'message_TO' => $email,
      'api_key' => $_ENV['EMAIL_API_KEY'],
      'CREATED_BY' => $userId,
    ];
    
    try {
      $this->client->request('POST', '/ppa-api-uams/wsv1/api/email', [
        'json' => $payload
      ]);

      return true;
    } catch (\Exception $exception) {
      return false;
    }
  }
  
  public function sendSMS(string $mobileNumber, string $message, int $userId): bool {
    $payload = [
      'message_CONTENT' => $message,
      'message_DATETIME' => date('Y-m-d H:m:s'),
      'message_TO' => $mobileNumber,
      'api_key' => $_ENV['EMAIL_API_KEY'],
      'CREATED_BY' => $userId,
    ];
    
    try {
      $this->client->request('POST', '/ppa-api-uams/wsv1/api/insertSMSManually', [
        'json' => $payload
      ]);

      return true;
    } catch (\Exception $exception) {
      return false;
    }
  }
}