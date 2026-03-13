<?php

declare(strict_types=1);

use App\Service\OrganizationService;
use App\Service\VoximplantDncService;
use App\Service\VoximplantCampaignService;
use App\Service\VoximplantApiClient;
use App\Service\VoximplantLogger;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

require_once 'Simpla.php';

if (! function_exists('config')) {
    require_once Simpla . 'app/Core/Helpers/BaseHelper.php';
}

class Voximplant extends Simpla
{
    const DOMAIN = 'boostra2023';
    const TOKEN = '92f5c9e3ea66018f60700a1e7f9f51be37d68758895df31b7feafe95b1eb02eb';
    const API_URL_V3 = 'https://kitapi-ru.voximplant.com/api/v3';
    const API_URL_V4 = 'https://kitapi-ru.voximplant.com/api/v4';

    const COLLECTORS = [
        37144 => "Бадин Антон Александрович",
        40930 => "Баймуратова Виктория Александровна",
        41995 => "Блинова Карина Александровна",
        37367 => "Демкина Наталья Геннадьевна",
        40926 => "Каплун Елена Алексеевна",
        40933 => "Кашаева Анна Максимовна",
        34609 => "Мамонова Виктория Александровна",
        34648 => "Петров Максим Геннадьевич",
        40983 => "Филиппова Анастасия Сергеевна",
        36483 => "Шарафанова Ольга Игоревна",
        //"Дурасова Наталья Олеговна",-
        //"Иванов Михаил Леонидович",-
        //"Кашаев Игорь Анатольевич",-
        //"Ковылкин Максим Дмитриевич",-
        //"Литвинова Анна Анатольевна",-
        //"Прошин Олег Владимирович",-
        //"Трифонова Дарья"-
    ];

    const OUTGOING_CALLS_DNC_LIST_ID = 897;

    public function getTicketId($callId)
    {

        $this->db->query("Select ticket_id from vox_tickets where call_id = ?", (int)$callId);

        $results = $this->db->results();

        return $results[0]->ticket_id;
    }


    public function addTicketId($result = null, $data = null)
    {
        $result = json_decode($result);
        $ticketId = $result->ticket_id;
        $callId = $data['call_id'];

        $this->db->query('INSERT INTO vox_tickets SET ticket_id=?, call_id=?', $ticketId, $callId);
        return true;
    }

    public function sendCcprolongations($managerId = null, $plus = false, $role = null, $minus = false, $organizationId = null, $taskDate = null)
    {
        // Используем переданную дату или сегодняшнюю
        $date = $taskDate ?: date("Y-m-d");
        $filter = [
            'manager_id' => $managerId,
            'date' => $date
        ];
        if ($plus) {
            $filter['vox_call'] = $plus;
        }

        if ($minus) {
            $filter['minus'] = $minus;
        }

        // Добавляем фильтр по организации, если указан
        if ($organizationId !== null) {
            $filter['organization_id'] = (int)$organizationId;
        }

        $users = $this->users->get_users_ccprolongations($filter);
        $users = $this->formatedUsers($users);
        usort($users, array($this, 'compareTimezone'));
        $this->logging(__METHOD__, 'schedule_cron.php', (array)['role' => $role, 'organization_id' => $organizationId], [], 'schedule_cron.log');
        if ($role == "contact_center_robo" || $role == "contact_center_new_robo" || $role == 'robot_minus') {
            $company = $this->managers->getCompany((int)$managerId, $organizationId);
            file_put_contents('voximplant/voximplant.txt', "company : $company \n", FILE_APPEND);
            $data = [
                "campaign_id" => $company,
                'rows' => json_encode($users),
            ];
            $this->sendRobocompany($data, $organizationId, (int)$managerId);
        } else {
            $this->sendPds($users, $managerId, null, $organizationId);
        }

    }

    /**
     * Получает конфигурацию Vox для организации
     * @param int $organizationId
     * @return array|null
     */
    public static function getVoxConfigForOrganization($organizationId)
    {
        $service = new OrganizationService();

        $resolvedId = $service->resolveOrganizationId($organizationId !== null ? (int) $organizationId : null);

        return $service->getVoxCredentials($resolvedId);
    }

    private function getOrganizationService(): OrganizationService
    {
        static $service = null;

        if ($service === null) {
            $service = new OrganizationService();
        }

        return $service;
    }

    private function getDefaultDomain(): string
    {
        return $this->getOrganizationService()->getDefaultVoxCredentials()['domain'] ?? '';
    }

    private function getDefaultToken(): string
    {
        return $this->getOrganizationService()->getDefaultVoxCredentials()['token'] ?? '';
    }

    private function getApiUrlV3(): string
    {
        return $this->getOrganizationService()->getApiUrlV3();
    }

    private function resolveVoxCredentials(?int $organizationId = null, $campaignId = null, ?int $managerId = null): array
    {
        $service = $this->getOrganizationService();

        if ($organizationId !== null) {
            return $service->getVoxCredentials($service->resolveOrganizationId($organizationId));
        }

        if ($campaignId !== null) {
            if (is_object($campaignId) && isset($campaignId->company)) {
                $campaignId = $campaignId->company;
            }
            $resolved = $service->resolveOrganizationIdByCampaign((string) $campaignId);
            if ($resolved !== null) {
                return $service->getVoxCredentials($resolved);
            }
        }

        if ($managerId !== null) {
            $resolved = $service->resolveOrganizationIdByManager((int) $managerId);
            if ($resolved !== null) {
                return $service->getVoxCredentials($resolved);
            }
        }

        return $service->getDefaultVoxCredentials();
    }

    /**
     * Отправка договоров в Vox для конкретной МКК
     * @param int $managerId
     * @param array $users
     * @param int $organizationId
     * @param string $role
     * @return array
     */
    public function sendCcprolongationsForOrganization($managerId, $users, $organizationId, $role = null)
    {
        $response = [
            'success' => false,
            'count' => 0,
            'error' => ''
        ];

        try {
            if (empty($users)) {
                $response['error'] = 'Нет пользователей для отправки';
                return $response;
            }

            $count = count($users);

            // Получаем конфигурацию для МКК
            $voxConfig = self::getVoxConfigForOrganization($organizationId);
        if (! $voxConfig || empty($voxConfig['domain'])) {
                $response['error'] = 'Не настроена конфигурация Vox для МКК ' . $organizationId;
                return $response;
            }

            $domain = $voxConfig['domain'];
            $token = $voxConfig['token'];

            if ($role == "contact_center_robo" || $role == "contact_center_new_robo" || $role == 'robot_minus') {
                $company = $this->managers->getCompany((int)$managerId, $organizationId);
                if (empty($company)) {
                    $response['error'] = 'Не найден PDS для менеджера ' . $managerId;
                    return $response;
                }

                $data = [
                    "campaign_id" => $company,
                    'rows' => json_encode($users, JSON_UNESCAPED_UNICODE),
                    'domain' => $domain,
                    'access_token' => $token
                ];

                $result = $this->sendRobocompanyForOrganization($data, $domain, $token);
            } else {
                $company = $this->managers->getCompany((int)$managerId, $organizationId);
                if (empty($company)) {
                    $response['error'] = 'Не найден PDS для менеджера ' . $managerId;
                    return $response;
                }

                $result = $this->sendPdsForOrganization($users, $managerId, $company, $domain, $token);
            }

            if ($result['success']) {
                $response['success'] = true;
                $response['count'] = $count;
            } else {
                $response['error'] = $result['error'] ?? 'Ошибка при отправке';
            }

        } catch (\Exception $e) {
            $response['error'] = 'Исключение: ' . $e->getMessage();
        }

        return $response;
    }

    /**
     * Отправка в PDS по ID для конкретной организации
     * @param array $users
     * @param int $pdsId
     * @param int $organizationId
     * @return array
     */
    public function sendPdsForOrganizationById($users, $pdsId, $organizationId)
    {
        $response = [
            'success' => false,
            'error' => ''
        ];

        try {
            if (empty($users)) {
                $response['error'] = 'Нет пользователей для отправки';
                return $response;
            }

            // Получаем конфигурацию для МКК
            $voxConfig = self::getVoxConfigForOrganization($organizationId);
            if (!$voxConfig || empty($voxConfig['domain'])) {
                $response['error'] = 'Не настроена конфигурация Vox для МКК ' . $organizationId;
                return $response;
            }

            $domain = $voxConfig['domain'];
            $token = $voxConfig['token'];

            $result = $this->sendPdsForOrganization($users, null, (int)$pdsId, $domain, $token);

            return $result;
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Исключение: ' . $e->getMessage()];
        }
    }

    /**
     * Отправка в PDS для конкретной организации
     */
    private function sendPdsForOrganization($users, $managerId, $companyNumber, $domain, $token)
    {
        $data = [
            'rows' => json_encode($users, JSON_UNESCAPED_UNICODE),
            'campaign_id' => $companyNumber
        ];
        $url = config('services.voximplant.api_url_v3', self::API_URL_V3);
        
        try {
            $mch_api = curl_init();
            curl_setopt($mch_api, CURLOPT_URL, "$url/agentCampaigns/appendContacts?access_token=$token&domain=$domain");
            curl_setopt($mch_api, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
            curl_setopt($mch_api, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($mch_api, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($mch_api, CURLOPT_TIMEOUT, 30);
            curl_setopt($mch_api, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($mch_api, CURLOPT_POST, true);
            curl_setopt($mch_api, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($mch_api, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($mch_api, CURLOPT_POSTFIELDS, http_build_query($data));
            $result = curl_exec($mch_api);
            $httpCode = curl_getinfo($mch_api, CURLINFO_HTTP_CODE);
            curl_close($mch_api);

            file_put_contents('voximplant/voximplant.txt', "sendPdsForOrganization : $result \n", FILE_APPEND);
            logger('voximplant')->info('sendPdsForOrganization', ['data' => $data, 'result' => $result]);

            $decoded = json_decode($result, true);
            if ($httpCode == 200 && isset($decoded['result'])) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => $result];
            }
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Отправка в робокомпанию для конкретной организации
     */
    private function sendRobocompanyForOrganization($data, $domain, $token)
    {
        $url = config('services.voximplant.api_url_v3', self::API_URL_V3);
        $data["domain"] = $domain;
        $data["access_token"] = $token;
        $url = "$url/outbound/";
        $method = "appendToCampaign";
        
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'PHP-MCAPI/2.0',
                CURLOPT_POST => 1,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $url . $method,
                CURLOPT_POSTFIELDS => http_build_query($data),
            ));

            $result = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            logger('voximplant')->info('sendRobocompanyForOrganization', ['data' => $data, 'result' => $result]);
            
            $decoded = json_decode($result, true);
            if ($httpCode == 200 && (isset($decoded['result']) || isset($decoded['success']))) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => $result];
            }
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    /**
     * Форматирование пользователей для отправки
     * 
     * @param array $users Массив пользователей
     * @return array Отформатированные пользователи
     * @deprecated Используйте VoximplantCampaignService::formatUsers() напрямую
     */
    public function formatedUsers($users)
    {
        try {
            $organizationService = new OrganizationService();
            $logger = new VoximplantLogger();
            $apiClient = new VoximplantApiClient($organizationService, $logger);
            $campaignService = new VoximplantCampaignService($apiClient, $logger, $organizationService);
            
            return $campaignService->formatUsers($users);
        } catch (\Throwable $e) {
            // Fallback на старую логику при ошибке
            foreach ($users as $user) {
                if ($user->loan_type == 'IL') {
                    $user->prolongation_amount = 0;
                    $user->zaim_summ = $user->overdue_debt_od_IL
                        + $user->overdue_debt_percent_IL
                        + $user->next_payment_od
                        + $user->next_payment_percent;
                }
            }
            return $users;
        }
    }
    /**
     * Добавление номеров в DNC-лист
     * 
     * @param int|string|object $company ID кампании
     * @param array $dnc Массив номеров телефонов
     * @return void
     * @deprecated Используйте VoximplantDncService::addToDnc() напрямую
     */
    public function sendDnc($company, $dnc)
    {
        try {
            $organizationService = new OrganizationService();
            $logger = new VoximplantLogger();
            $apiClient = new VoximplantApiClient($organizationService, $logger);
            $dncService = new VoximplantDncService($apiClient, $logger, $organizationService);
            
            $dncService->addToDnc($company, $dnc);
        } catch (\Throwable $e) {
            // Сохраняем обратную совместимость - логируем ошибку, но не прерываем выполнение
            error_log("Voximplant::sendDnc error: " . $e->getMessage());
        }
    }

    /**
     * Получение номеров из DNC
     * 
     * @param string|null $status Статус контактов
     * @param string $action Действие
     * @param int|null $managerId ID менеджера
     * @return array
     * @deprecated Используйте VoximplantDncService::getDncNumbers() напрямую
     * 
     * Этот метод сохраняется для обратной совместимости.
     * Внутри используется новый VoximplantDncService с полным логированием.
     */
    public function getDncNumbers($status, $action, $managerId = null): array
    {
        try {
            $organizationService = new OrganizationService();
            $logger = new VoximplantLogger();
            $apiClient = new VoximplantApiClient($organizationService, $logger);
            $dncService = new VoximplantDncService($apiClient, $logger, $organizationService);
            
            return $dncService->getDncNumbers($status, $action, $managerId);
        } catch (\Throwable $e) {

            // Fallback на старую логику при ошибке для обратной совместимости
            error_log("Voximplant::getDncNumbers error: " . $e->getMessage());

            // Старая логика как fallback
            $managers = new Managers();
            $day = date('Y-m-d');
            $from = date('Y-m-d', strtotime('-3 days'));

            if ($action == 'deleteDnc') {
                $day = date('Y-m-d', strtotime('-1 days'));
                $from = date('Y-m-d', strtotime('-10 days'));
            }

            if (! $managerId) {
                $selectedManagers = $managers->getSelectedManagers($day);
                $managerIds = [];
                foreach ($selectedManagers as $manager) {
                    $managerIds[] = $manager->manager_id;
                }
                if (empty($managerIds)) {
                    return [];
                }
                $m = implode(',', $managerIds);
                $company = $managers->getCompany($m);
            } else {
                $company = $managers->getCompany($managerId);
            }

            $result = [];

            if (is_object($company) && isset($company->companies)) {
                $companies = explode(',', (string) $company->companies);
                $companyManagers = explode(',', (string) $company->managers);
                foreach ($companyManagers as $i => $manager) {
                    $result[$manager] = $companies[$i] ?? null;
                }
            } elseif ($managerId !== null && $company !== null) {
                $result[(string) $managerId] = (string) $company;
            }

            if (empty($result)) {
                return [];
            }

            $list = [];
            foreach ($result as $key => $campaignId) {
                if ($campaignId === null || $campaignId === '') {
                    continue;
                }

                $credentials = $this->resolveVoxCredentials(null, $campaignId, (int) $key);
                $domain = $credentials['domain'];
                $token = $credentials['token'];
                $voxUrl = $this->getApiUrlV3();
                $url = $voxUrl . '/agentCampaigns/searchContacts?domain=' . $domain;

                $dnc = [];
                $data = [
                    'campaign_id' => (string) $campaignId,
                    'status' => '["' . $status . '"]',
                    'from' => $from . ' 00:00:00',
                    'to' => $day . ' 23:59:59',
                    'access_token' => $token,
                    'per-page' => '50',
                    'page' => 1,
                ];

                if ($action == 'deleteDnc') {
                    unset($data['status']);
                }

                try {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $response = curl_exec($ch);
                    curl_close($ch);

                    if (!empty($response)) {
                        $decodedResponse = json_decode($response);
                        if (!is_object($decodedResponse) || !isset($decodedResponse->_meta)) {
                            return [];
                        }

                        $pagesCount = (int) $decodedResponse->_meta->pageCount;

                        for ($i = 1; $i <= $pagesCount; $i++) {
                            $pageData = [
                                'campaign_id' => (string) $campaignId,
                                'status' => '["' . $status . '"]',
                                'from' => $from . ' 00:00:00',
                                'to' => $day . ' 23:59:59',
                                'access_token' => $token,
                                'per-page' => '50',
                                'page' => $i,
                            ];

                            if ($action == 'deleteDnc') {
                                unset($pageData['status']);
                            }

                            try {
                                $pageCurl = curl_init();
                                curl_setopt($pageCurl, CURLOPT_URL, $url);
                                curl_setopt($pageCurl, CURLOPT_POST, 1);
                                curl_setopt($pageCurl, CURLOPT_POSTFIELDS, http_build_query($pageData));
                                curl_setopt($pageCurl, CURLOPT_RETURNTRANSFER, 1);
                                $pageResponse = curl_exec($pageCurl);
                                curl_close($pageCurl);

                                $pageDecoded = json_decode($pageResponse);
                                if (is_object($pageDecoded) && isset($pageDecoded->result)) {
                                    foreach ($pageDecoded->result as $res) {
                                        if (isset($res->phone)) {
                                            $dnc[] = $res->phone;
                                        }
                                    }
                                }
                            } catch (\Throwable $e) {
                                $this->sendTelegram(debug_backtrace()[0]['file']);
                            }
                        }

                        if ($action == 'dialings') {
                            if ($dnc) {
                                $chunks = array_chunk($dnc, 50);

                                foreach ($chunks as $chunk) {
                                    $this->sendDnc($campaignId, $chunk);
                                }
                            }
                        } elseif ($action == 'recall') {
                            $list[$key] = $dnc;
                        } elseif ($action == 'checkRecall') {
                            return $dnc;
                        } elseif ($action == 'deleteDnc') {
                            foreach ($dnc as $item) {
                                $this->deleteFromDnc(null, $item, $campaignId);
                            }
                        } elseif ($action == 'getOngoing') {
                            return $dnc;
                        } elseif ($action == 'deleteManager') {
                            return $dnc;
                        }
                    }

                } catch (\Throwable $e) {
                    $this->sendTelegram(debug_backtrace()[0]['file']);
                }
            }

            if ($action == 'recall') {
                return $list;
            }

            return [];
        }
    }

    /**
     * Отправка в PDS кампанию
     * 
     * @param array $users Массив пользователей
     * @param int|null $managerId ID менеджера
     * @param int|string|null $companyNumber ID кампании
     * @param int|null $organizationId ID организации
     * @return void
     * @deprecated Используйте VoximplantCampaignService::sendToPdsCampaign() напрямую
     */
    public function sendPds($users, $managerId, $companyNumber = null, $organizationId = null)
    {
        try {
            $organizationService = new OrganizationService();
            $logger = new VoximplantLogger();
            $apiClient = new VoximplantApiClient($organizationService, $logger);
            $campaignService = new VoximplantCampaignService($apiClient, $logger, $organizationService);
            
            $campaignService->sendToPdsCampaign($users, $managerId, $companyNumber, $organizationId);
        } catch (\Throwable $e) {
            // Сохраняем обратную совместимость - логируем ошибку, но не прерываем выполнение
            error_log("Voximplant::sendPds error: " . $e->getMessage());
        }
    }

    /**
     * Удаление номера из DNC-листа
     * 
     * @param int|null $managerId ID менеджера
     * @param string $number Номер телефона
     * @param int|string|object|null $company ID кампании
     * @return void
     * @deprecated Используйте VoximplantDncService::removeFromDnc() напрямую
     */
    public function deleteFromDnc($managerId, $number, $company = null)
    {
        try {
            $organizationService = new OrganizationService();
            $logger = new VoximplantLogger();
            $apiClient = new VoximplantApiClient($organizationService, $logger);
            $dncService = new VoximplantDncService($apiClient, $logger, $organizationService);
            
            $dncService->removeFromDnc($managerId, $number, $company);
        } catch (\Throwable $e) {
            // Сохраняем обратную совместимость - логируем ошибку, но не прерываем выполнение
            error_log("Voximplant::deleteFromDnc error: " . $e->getMessage());
        }
    }

    public function getVoxParameter()
    {
        $credentials = $this->resolveVoxCredentials();
        $domain = $credentials['domain'];
        $accessToken = $credentials['token'];
        $url = $this->getApiUrlV3();
        $api_url = "$url/usergroup/searchGroups";

        $data = array(
            'access_token' => $accessToken,
            'id' => '[431]',
            'domain' => $domain,
        );

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            return curl_error($ch);
        } else {
            return $response;
        }
    }

    public function addApprovedToVox($orderList)
    {
        $txt = "";
        $date = date('Y-m-d H:i:s');
        $numItems = count($orderList);
        $i = 0;
        usort($orderList, array($this, 'compareTimezone'));
        foreach ($orderList as $order) {
            $txt .= "($order->order_id,'$date'),";
            if (++$i === $numItems) {
                $txt .= "($order->order_id,'$date')";
            }
        }
        $query = $this->db->placehold("INSERT INTO approved_to_vox(order_id,send_time)  VALUES $txt");

        $this->db->query($query);
    }

    public function sendRobocompany($data, ?int $organizationId = null, ?int $managerId = null)
    {
        $campaignId = $data['campaign_id'] ?? null;
        $credentials = $this->resolveVoxCredentials($organizationId, $campaignId, $managerId);
        $domain = $credentials['domain'];
        $accessToken = $credentials['token'];
        $url = rtrim($this->getApiUrlV3(), '/') . '/outbound/';

        $method = "appendToCampaign";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'PHP-MCAPI/2.0',
            CURLOPT_POST => 1,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url . $method,
            CURLOPT_POSTFIELDS => http_build_query(array_merge($data, [
                'domain' => $domain,
                'access_token' => $accessToken,
            ])),
        ));

        $result = curl_exec($curl);
        curl_close($curl);
        $this->logging(__METHOD__, 'schedule_cron.php', (array)$data, (array)$result, 'schedule_cron.log');
        return json_decode($result, true);
    }
    public function compareTimezone($a, $b): int
    {
        $timezonePattern = "/^(\+|-)\d{2}:\d{2}$/";

        if (!preg_match($timezonePattern, $a->UTC) || !preg_match($timezonePattern, $b->UTC)) {
            return 0;
        }

        if ($a->UTC === "+12:00" && $b->UTC !== "+12:00") {
            return -1;
        } elseif ($a->UTC !== "+12:00" && $b->UTC === "+12:00") {
            return 1;
        } else {
            return strcmp($b->UTC, $a->UTC); // Sort in descending order
        }
    }

    public function sendTelegram($message = null, $token = '5973647143:AAEK3SfOT2gUJ1g12qc8wAciyJvbeMS7GOs',$chat_id = '-1001902112960')
    {

        $data = array(
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'html',
        );
        if ($token != '') {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POST => 1,
                CURLOPT_URL => 'https://api.telegram.org/bot' . $token . '/sendMessage',
                CURLOPT_POSTFIELDS => $data
            ));
            $response = curl_exec($curl);
            curl_close($curl);

        }
    }

    public function getSentData($start, $end)
    {

        $query = $this->db->placehold("Select order_id from approved_to_vox
                                       where send_time BETWEEN ? AND ?", $start, $end);
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Добавление номеров в кампанию
     * @param int $campaignId
     * @param array $phoneNumbers
     * @return void
     */
    public function appendToCampaign(int $campaignId, array $phoneNumbers)
    {
        $credentials = $this->resolveVoxCredentials(null, (string) $campaignId);
        $domain = $credentials['domain'];
        $token = $credentials['token'];
        $url = rtrim($this->getApiUrlV3(), '/') . "/outbound/appendToCampaign?domain=$domain";

        $data['access_token'] = $token;
        $data['campaign_id'] = $campaignId;
        $data['rows'] = json_encode($phoneNumbers);

        $curl = curl_init();

        try {
            curl_setopt_array($curl, [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            ]);

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                throw new \Exception('cURL Error: ' . curl_error($curl));
            }

            $this->logResponse($response);

        } catch (\Throwable $e) {
            $this->logError($e);

            $this->sendTelegram($e->getMessage());
        } finally {
            curl_close($curl);
        }
    }

    /**
     * Логирование ответа
     *
     * @param string $response
     */
    private function logResponse(string $response)
    {
        $decodedResponse = json_decode($response, true);

        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'response' => $decodedResponse
        ];

        $formattedLog = json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Добавляем лог в файл, разделяя записи специальной строкой
        file_put_contents('../logs/voximplant.txt', $formattedLog . "\n\n=====\n\n", FILE_APPEND);
    }

    /**
     * Логирование ошибок
     *
     * @param \Throwable $e
     */
    private function logError(\Throwable $e)
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];

        $formattedLog = json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Добавляем лог в файл, разделяя записи специальной строкой
        file_put_contents('../logs/voximplant_errors.txt', $formattedLog . "\n\n=====\n\n", FILE_APPEND);
    }

    public function getCollectorsData()
    {
        $credentials = $this->resolveVoxCredentials();
        $domain = $credentials['domain'];
        $token = $credentials['token'];
        $url = $this->getApiUrlV3() . "/realtimeMetrics/getAgentsMetricsCalls?domain=$domain";

        $data = [
            'access_token' => $token,
            'interval' => 'last24h',
            'agent_ids' => json_encode(array_keys(self::COLLECTORS)),
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function searchCalls(int $callId): array
    {
        $credentials = $this->resolveVoxCredentials();
        $domain = $credentials['domain'];
        $token = $credentials['token'];
        $url = config('services.voximplant.api_url_v4', self::API_URL_V4) . "/history/searchCalls?domain=$domain";

        $data = [
            'access_token' => $token,
            'id' => $callId,
            'with_tags' => true,
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    public function searchUsers(array $queryParams = [], array $bodyParams = []): array
    {
        $credentials = $this->resolveVoxCredentials();
        $domain = $credentials['domain'];

        return $this->callApi(
            '/user/searchUsers',
            array_merge(['access_token' => $credentials['token']], $bodyParams),
            array_merge(['domain' => $domain], $queryParams)
        );
    }

    public function searchCampaigns(array $queryParams = [], array $bodyParams = []): array
    {
        $credentials = $this->resolveVoxCredentials();
        $domain = $credentials['domain'];

        return $this->callApi(
            '/agentCampaigns/searchCampaigns',
            array_merge(['access_token' => $credentials['token']], $bodyParams),
            array_merge(['domain' => $domain], $queryParams)
        );
    }

    public function searchQueues(array $queryParams = [], array $bodyParams = []): array
    {
        $credentials = $this->resolveVoxCredentials();
        $domain = $credentials['domain'];

        return $this->callApi(
            '/queues/searchQueues',
            array_merge(['access_token' => $credentials['token']], $bodyParams),
            array_merge(['domain' => $domain], $queryParams)
        );
    }

    public function searchCallsCursor(string $from, string $to, int $limit = 50, ?string $cursor = null, array $extraBodyParams = []): array
    {
        $credentials = $this->resolveVoxCredentials();
        $domain = $credentials['domain'];

        $body = array_merge([
            'access_token' => $credentials['token'],
            'from' => $from,
            'to' => $to,
            'limit' => $limit,
        ], $extraBodyParams);

        if (!empty($cursor)) {
            $body['cursor'] = $cursor;
        }

        return $this->callApi(
            '/history/searchCalls',
            $body,
            ['domain' => $domain],
            rtrim(config('services.voximplant.api_url_v4', self::API_URL_V4), '/')
        );
    }

    public function searchCallsPaginated(string $from, string $to, int $page = 1, int $perPage = 50, array $extraBodyParams = []): array
    {
        $credentials = $this->resolveVoxCredentials();
        $domain = $credentials['domain'];

        $body = array_merge([
            'access_token' => $credentials['token'],
            'from' => $from,
            'to' => $to,
            'page' => $page,
            'per-page' => $perPage,
        ], $extraBodyParams);

        return $this->callApi(
            '/history/searchCalls',
            $body,
            ['domain' => $domain],
            rtrim(config('services.voximplant.api_url_v3', self::API_URL_V3), '/')
        );
    }

    public function addDncContacts(array $contacts, int $dncListId, string $comment): array
    {
        $credentials = $this->resolveVoxCredentials();
        $domain = $credentials['domain'];
        $token = $credentials['token'];
        $url = $this->getApiUrlV3() . "/dnc/addDncContacts?domain=$domain";

        $data = [
            'access_token' => $token,
            'id' => $dncListId,
            'contacts' => json_encode($contacts),
            'comment' => $comment
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    public function searchDncContacts($number = null, $listId = null, $contactId = null): array
    {
        $credentials = $this->resolveVoxCredentials();
        $domain = $credentials['domain'];
        $token = $credentials['token'];
        $url = $this->getApiUrlV3() . "/dnc/searchDncContacts?domain=$domain";

        $data = array_filter([
            'access_token' => $token,
            'number' => $number,
            'list_id' => $listId,
            'id' => $contactId
        ], function($value) {
            return $value !== null;
        });

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    public function deleteDncContact(int $dncContactId): array
    {
        $credentials = $this->resolveVoxCredentials();
        $domain = $credentials['domain'];
        $token = $credentials['token'];
        $url = $this->getApiUrlV3() . "/dnc/deleteDncContact?domain=$domain";

        $data = [
            'access_token' => $token,
            'id' => $dncContactId,
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    /**
     * Отправляет POST запрос в Voximplant для инициации звонка через сценарий
     *
     * @param string $phone Номер телефона для звонка (например, "81234567890")
     * @param int $scenarioId ID сценария для запуска
     * @param int $phoneNumberId ID исходящего номера телефона
     * @return array
     */
    public function sendVoximplantCall($phone, $scenarioId, $phoneNumberId)
    {
        if (empty($phone)) {
            return [
                'success' => false,
                'error' => 'Phone number is empty'
            ];
        }

        try {
            $result = $this->callApi('/scenario/runScenario', [
                'phone' => $phone,
                'scenario_id' => $scenarioId,
                'phone_number_id' => $phoneNumberId
            ]);

            $response = [
                'success' => true,
                'phone' => $phone,
                'response' => $result,
            ];

            $this->logResponse(json_encode($response));

            return $response;

        } catch (\Throwable $e) {
            $result = [
                'success' => false,
                'phone' => $phone,
                'error' => 'Ошибка запроса: ' . $e->getMessage()
            ];

            $this->logError($e);

            return $result;
        }
    }

    /**
     * Унифицированный вызов API Voximplant
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    private function callApi(string $endpoint, array $formParams, array $queryParams = [], ?string $baseUrl = null): array
    {
        static $client = null;

        if ($client === null) {
            $client = new \GuzzleHttp\Client([
                'timeout' => 30,
                'connect_timeout' => 10,
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'PHP Voximplant Client/1.0',
                ],
                'verify' => false,
            ]);
        }

        try {
            if ($baseUrl === null) {
                $baseUrl = rtrim($this->getApiUrlV3(), '/');
            }

            if (empty($queryParams)) {
                $credentials = $this->resolveVoxCredentials();
                $queryParams = ['domain' => $credentials['domain']];
            }

            if (!isset($formParams['access_token'])) {
                $credentials = $credentials ?? $this->resolveVoxCredentials();
                $formParams = array_merge(['access_token' => $credentials['token']], $formParams);
            }

            $response = $client->post($baseUrl . '/' . ltrim($endpoint, '/'), [
                'query' => $queryParams,
                'form_params' => $formParams,
            ]);

            $body = (string)$response->getBody();
            $decoded = json_decode($body, true);
            return is_array($decoded) ? $decoded : ['raw_response' => $body];
        } catch (\Throwable $e) {
            throw new \Exception('API request failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
