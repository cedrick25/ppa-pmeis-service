<?php

namespace App\Plugin;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class PpaApiClient {
  private ClientInterface $client;

  public function __construct()
  {
    $this->client = new Client();
    date_default_timezone_set('Asia/Manila');
  }
  
  public function sendEmail(string $email, string $message, int $userId): bool {
    $now = new \DateTime();
    $now->add(new \DateInterval('PT2M'));
    $payload = [
      'message_CONTENT' => $message,
      'message_DATETIME' => $now->format('Y-m-d H:i:s'),
      'message_TO' => $email,
      'api_key' => $_ENV['EMAIL_API_KEY'],
      'CREATED_BY' => $userId,
    ];
    
    try {
      $this->client->request('POST', $_ENV['PPA_API_EMAIL_BASE_URL'] . '/ppa-api-uams/wsv1/api/email', [
        'json' => $payload
      ]);

      return true;
    } catch (\Exception $exception) {
      return false;
    }
  }
  
  public function sendSMS(string $mobileNumber, string $message, int $userId): bool {
    $now = new \DateTime();
    $now->add(new \DateInterval('PT2M'));
    $payload = [
      'message_CONTENT' => $message,
      'message_DATETIME' => $now->format('Y-m-d H:i:s'),
      'message_TO' => $mobileNumber,
      'api_key' => $_ENV['EMAIL_API_KEY'],
      'CREATED_BY' => $userId,
    ];
    
    try {
      $this->client->request('POST', $_ENV['PPA_API_SMS_BASE_URL'] . '/ppa-api-uams/wsv1/api/insertSMSManually', [
        'json' => $payload
      ]);

      return true;
    } catch (\Exception $exception) {
      return false;
    }
  }
}