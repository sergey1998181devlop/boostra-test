<?php

namespace services\Integration\BankiRu\Leads;

use services\Integration\BankiRu\Client\Client;
use services\Integration\BankiRu\Dto\AcceptedConsentsDto;
use services\Integration\BankiRu\Dto\AddressesDto;
use services\Integration\BankiRu\Dto\ClientDataDto;
use services\Integration\BankiRu\Dto\DocumentDto;
use services\Integration\BankiRu\Dto\EmailDto;
use services\Integration\BankiRu\Dto\EstateDto;
use services\Integration\BankiRu\Dto\ExpensesDto;
use services\Integration\BankiRu\Dto\IncomesDto;
use services\Integration\BankiRu\Dto\JobDto;
use services\Integration\BankiRu\Dto\LastSeniorityDto;
use services\Integration\BankiRu\Dto\LeadInfoDto;
use services\Integration\BankiRu\Dto\LeadResponseDto;
use services\Integration\BankiRu\Dto\PersonProfileDto;
use services\Integration\BankiRu\Dto\PhoneDto;
use services\Integration\BankiRu\Dto\RelationPersonDto;
use services\Integration\BankiRu\Dto\TotalSeniorityDto;

final class Lead
{
    private $url = 'leads';
    private $authToken;
    public function __construct(string $authToken)
    {
        $this->authToken = $authToken;
    }

    /**
     * @throws \Exception
     */
    public function leads(array $requiredParameters, array $optionalParameters = [])
    {
        $data = [];
        $data['partnerCode'] = $requiredParameters['partnerCode'];
        $data['leadInfo']['clientData']['personProfile']['firstName'] = $requiredParameters['firstName'];
        $data['leadInfo']['requestedAmount'] = (int) $requiredParameters['requestedAmount'];

        // Validation
        $phoneDto = new PhoneDto((int) $requiredParameters['phoneCountryCode'], $requiredParameters['phoneNumber']);
        $data['leadInfo']['clientData']['phone']['countryPrefixCode'] = $phoneDto->countryPrefixCode;
        $data['leadInfo']['clientData']['phone']['number'] = $phoneDto->number;

        $addressesDto = new AddressesDto(
            $requiredParameters['typeCode'],
            $requiredParameters['addressesString'],
            $requiredParameters['addressKladrCode'],
            $requiredParameters['dateStart'] ?? null
        );

        $data['leadInfo']['clientData']['addresses'][0]['typeCode'] =  $addressesDto->typeCode;
        $data['leadInfo']['clientData']['addresses'][0]['addressString'] =  $addressesDto->addressString;
        $data['leadInfo']['clientData']['addresses'][0]['addressKladrCode'] = $addressesDto->addressKladrCode;

        //$emailDto = new EmailDto($requiredParameters['email']);

        $acceptedConsents = (array) $requiredParameters['acceptedConsents'];
        foreach ($acceptedConsents as $consent) {
            $data['leadInfo']['clientData']['acceptedConsents'][]['type'] = $consent;
        }

        // Optional Parameters
        /*if (isset($optionalParameters['transactionId'])) {
            $data['transactionId'] = $optionalParameters['transactionId'];
        }

        $leadInfo = $optionalParameters['leadInfo'] ?? null;
        if ($leadInfo !== null) {
            $leadInfoDto = new LeadInfoDto(
                $leadInfo['purposeCode'] ?? null,
                $leadInfo['requestedTermValue'] ?? null,
                $leadInfo['requestedTermUnitCode'] ?? null,
                $leadInfo['amount'] ?? null,
            );

            if (!$leadInfoDto->isNull()) {
               $data['data']['leadInfo'] = $leadInfoDto->toArray();
            }
        }

        $clientData = $optionalParameters['clientData'] ?? null;

        if ($clientData !== null) {
            $acceptedConsentsDto = new AcceptedConsentsDto(
                $optionalParameters['acceptedConsents']['signature'] ?? null,
                $optionalParameters['acceptedConsents']['acceptDate'] ?? null,
            );

            $personProfileDto = new PersonProfileDto(
                $optionalParameters['personProfile']['secretCodeWorld'] ?? null,
                $optionalParameters['personProfile']['middleName'] ?? null,
                $optionalParameters['personProfile']['lastName'] ?? null,
                $optionalParameters['personProfile']['birthDate'] ?? null,
                $optionalParameters['personProfile']['birthPlace'] ?? null,
                $optionalParameters['personProfile']['genderCode'] ?? null,
                $optionalParameters['personProfile']['educationCode'] ?? null,
                $optionalParameters['personProfile']['maritalCode'] ?? null,
                $optionalParameters['personProfile']['numberChild'] ?? null,
                $optionalParameters['personProfile']['snils'] ?? null,
            );

            $estateDto = new EstateDto(
                $optionalParameters['estate']['realEstateKindCode'] ?? null,
                $optionalParameters['estate']['carEstateKindCode'] ?? null,
            );

            $totalSeniorityDto = new TotalSeniorityDto($optionalParameters['totalSeniority']['value'] ?? null);
            $lastSeniorityDto = new LastSeniorityDto($optionalParameters['lastSeniority']['value'] ?? null);

            $jobDto = new JobDto(
                $optionalParameters['job']['jobTypeCode'] ?? null,
                $optionalParameters['job']['dateStart'] ?? null,
                $totalSeniorityDto,
                $lastSeniorityDto,
                $optionalParameters['job']['positionName'] ?? null,
                $phoneDto,
                $optionalParameters['job']['jobEmployerName'] ?? null,
                $optionalParameters['job']['jobEmployerType'] ?? null,
                $optionalParameters['job']['jobEmployerInn'] ?? null,
                $optionalParameters['job']['jobEmployerNumberStaff'] ?? null,
                $optionalParameters['job']['jobEnterpriseActivityTypeCode'] ?? null,
                $optionalParameters['job']['positionTypeCode'] ?? null,
            );

            $expensesDto = new ExpensesDto($optionalParameters['expenses']['amount'] ?? null);
            $incomesDto = new IncomesDto(
                $optionalParameters['incomes']['amount'] ?? null,
                $optionalParameters['incomes']['incomeProffCode'] ?? null
            );
            $documentDto = new DocumentDto();
            $relationPersonDto = new RelationPersonDto();

            $clientDataDto = new ClientDataDto(
                $addressesDto,
                $acceptedConsentsDto,
                $personProfileDto,
                $estateDto,
                $jobDto,
                $emailDto,
                $expensesDto,
                $incomesDto,
                $documentDto,
                $relationPersonDto
            );

            if (!$clientDataDto->isNull()) {

            }
        }*/

        if (isset($_GET['is_debug_final'])) {
            echo "<pre>";
            var_dump(json_encode($data));
            die;
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $this->authToken,
        ];

        $client  = new Client(isset($_GET['prod']));

        $response = $client->request(Client::POST, $this->url, ['data' => $data], $headers);
        if(isset($_GET['is_debug_final_response'])) {
            var_dump($response);
            die;
        }
        $responseData = $response['data'] ?? $response['result'] ?? null;
        if ($responseData === null) {
            return false;
           // throw new \Exception('Response data is empty');
        }

        if (!isset($responseData['leadId'])) {
            return false;
           // throw new \Exception("Error in lead response - ". json_encode($responseData));
        }

        return new LeadResponseDto(
            $responseData['leadId'],
            $responseData['token'],
            $responseData['url_type'] ?? null, $response['result']['status'] ?? 'success');
    }
}