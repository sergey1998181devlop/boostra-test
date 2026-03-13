<?php

namespace App\Modules\Clients\Infrastructure\Client;

use App\Modules\Shared\Repositories\SettingsRepository;
use Sites;
use SoapClient;
use SoapFault;
use Users;

class OneCBalanceClient
{
    private string $baseUrl;
    private string $db;
    private string $apiPassword;
    
    public function __construct(SettingsRepository $settingsRepository)
    {
        $this->baseUrl = (string) config('services.1c.url');
        $this->db = (string) config('services.1c.db');
        $this->apiPassword = $settingsRepository->get1CApiPassword();
    }
    
    public function getUserBalances(string $userUid): ?array
    {
        if (empty($this->baseUrl) || empty($this->db)) {
            logger('error')->warning('1C URL or DB not configured');
            return null;
        }
        
        try {
            $wsdlUrl = $this->baseUrl . $this->db . "/ws/WebLK.1cws?wsdl";
            
            $client = new SoapClient($wsdlUrl, [
                'trace' => 0,
                'exceptions' => true,
                'connection_timeout' => 20,
            ]);
            
            $requestObject = $this->createRequestObject($userUid);
            $response = $client->__soapCall('GetLKMassINN', [$requestObject]);
            
            return $this->parseResponse($response);
            
        } catch (SoapFault $e) {
            logger('api')->error('1C SOAP GetLKMassINN failed', [
                'uid' => $userUid,
                'error' => $e->getMessage()
            ]);
            return null;
        } catch (\Exception $e) {
            logger('api')->error('1C balance client error', [
                'uid' => $userUid,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    private function createRequestObject(string $userUid): object
    {
        $site_id = (new Users())->get_site_id_by_user_1c_id($userUid);
        $inn_arr =  (new \Organizations())->get_inns_by_site_id($site_id);
        if (empty($inn_arr)){ 
            return (object) [];
        }

        return (object) [
            'UID' => $userUid,
            'ArrayINN' => json_encode($inn_arr, false),
            'Пароль' => $this->apiPassword,
            'Partner' => 'Boostra',
        ];
    }
    
    private function parseResponse($response): ?array
    {
        if (!isset($response->return)) {
            return null;
        }
        
        $rawResponse = $response->return;
        
        if ($this->isJson($rawResponse)) {
            $decoded = json_decode($rawResponse, true);
            return is_array($decoded) ? $decoded : null;
        }
        
        return is_array($rawResponse) ? $rawResponse : null;
    }
    
    private function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

