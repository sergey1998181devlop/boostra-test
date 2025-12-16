<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Auth;

use services\Integration\BankiRu\Client\Client;
use services\Integration\BankiRu\Dto\LoginDto;

//session_start();

class Auth
{
   private const STATUS_SUCCESS = 'success';
   private const STATUS_ERROR = 'error';

   private $url = 'tokens';

    /**
     * @throws \Exception
     */
    public function login(string $login, string $password): LoginDto
   {
       /*if (isset($_SESSION['banki'])) {
           return $_SESSION['banki'];
       }*/

       $headers = ['Content-Type: application/json', 'Accept: application/json'];
       $data    = ['data' => ['login' => $login, 'password' => $password]];
       $client  = new Client(isset($_GET['prod']));
       $response = $client->request(Client::POST, $this->url, $data, $headers);

       $responseData = $response['data'] ?? $response['result'] ?? null;
       if ($responseData === null) {
           throw new \Exception('Response data is empty');
       }

       $token = $responseData['token'] ?? '';
       $type = $responseData['type'] ?? 'Bearer';
       $status = $responseData['status'] ?? self::STATUS_ERROR;

       $errors = null;

       if ($status === self::STATUS_ERROR) {
           $faults = $responseData['faults'] ?? null;
           if ($faults !== null) {
               $errors = $faults;
           }
       }
       $loginDto = new LoginDto($token, $type, $status, $errors);
      // $_SESSION['banki'] = $loginDto;

       return $loginDto;
   }
}
