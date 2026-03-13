<?php

require_once 'View.php';

class ClientChecker extends View
{
    private const EXCEPTION_REGIONS_CODE_FOR_REG_REGION = 'exception_regions_code_for_reg_region';

    public function fetch()
    {
        if ($this->request->method('post')) {
            $client_ip = $_SERVER['REMOTE_ADDR'];
            $this->db->query("WITH checker_client AS (SELECT cip.client_id, cip.id
                                                        FROM s_checker_clients_ip cip
                                                        WHERE cip.ip = ?
                                                        LIMIT 1)
                                SELECT
                                    checker_client.id ip_id
                                    , cl.requests_limit
                                    , GROUP_CONCAT(cip.id) ip_group
                                FROM s_checker_clients cl
                                JOIN checker_client
                                    ON cl.id = checker_client.client_id
                                JOIN s_checker_clients_ip cip
                                    ON cip.client_id = cl.id
                                GROUP BY checker_client.id, cl.requests_limit", $client_ip);
            $client_limit = $this->db->result();
            if(!$client_limit) {
                header('HTTP/1.1 403 Forbidden');
                exit;
            }
            $this->db->query('SET @limit_start_time := CURRENT_DATE();');
            $this->db->query("SELECT COUNT(id) cnt
                              FROM s_checker_requests creq
                              WHERE
                                creq.request_time >= @limit_start_time
                                AND creq.client_ip_id IN ({$client_limit->ip_group})");
            $requests_count = $this->db->result('cnt');
            if($client_limit->requests_limit < $requests_count) {
                header('HTTP/1.1 429 Too Many Requests');
                exit;
            }
            $request_json = json_encode($_POST);
            $this->db->query("INSERT INTO `s_checker_requests` (`client_ip_id`, `request_body`) VALUES ({$client_limit->ip_id}, '$request_json')");

            $phone = $this->request->post('phone', 'string');
            $user  = $this->users->get_user($this->users->get_phone_user($phone));
            $response = ['status' => 1, 'phone' => $phone];
            if($user) {
                switch(true) {
                    case true:
                        if(!$user->inn) {
                            $response = ['status' => 0, 'reason' => 'INN'];
                            break;
                        }
                    case true:
                        if($this->users->getMoratoriumByUserId($user->id)) {
                            $response = ['status' => 0, 'reason' => 'Moratorium'];
                            break;
                        }
                    case true:
                        $this->db->query($this->db->placehold("SELECT id FROM __blacklist WHERE user_id = ?", $user->id));
                        $black_list_crm = $this->db->result('id');
                        if($black_list_crm) {
                            $response = ['status' => 0, 'reason' => 'black_list_crm'];
                            break;
                        }
                    case true:
                        if($user->Regregion_code && !$this->checkIsRegionAvailableByRegionCode($user->Regregion_code)) {
                            $response = ['status' => 0, 'reason' => 'Region'];
                            break;
                        }
                    case true:
                        $this->db->query("SELECT MAX(o.id) decline, MAX(o2.id) closed
                                            FROM s_orders o
                                            LEFT JOIN s_orders o2
                                                ON o2.user_id = o.user_id
                                                AND o2.status IN (10, 12) /* Выдан, Закрыт */
                                                AND o2.`1c_status` = '6.Закрыт'
                                            WHERE
                                                o.user_id = {$user->id}
                                                AND o.status = 3 /* Отказ */
                                                AND o.reason_id NOT IN (34, 36) /* Истек срок авто-одобрения, Истёк срок действия */
                                                AND o.`1c_status` <> '7.Технический отказ'");
                        $decline_check = $this->db->result();
                        if(!empty($decline_check) && empty($decline_check->closed)) {
                            $response = ['status' => 0, 'reason' => 'decline_check'];
                            break;
                        }
                    case true:
                        if($user->UID && $this->checkIsUserIn1cBlacklist($user->UID)) {
                            $response = ['status' => 0, 'reason' => 'UserIn1cBlacklist'];
                            break;
                        }
                }
            }
            $response['phone'] = $phone;
            echo json_encode($response);
            $this->logging(
                __METHOD__ . " - Phone #$phone",
                null,
                $phone,
                $response,
                'checker.txt'
            );
            exit;
        }
        header('HTTP/1.1 405 Method not allowed');
        exit;
    }

    public function checkIsUserIn1cBlacklist(string $uid): bool
    {
        $data = $this->soap->generateObject(['ContragentUID' => $uid]);

        $responseData = $this->soap->requestSoap($data, 'WebSignal', 'isContragentInBlacklist');
        if (isset($responseData['response'])) {
            return (bool) $responseData['response'];
        }

        return false;
    }

    private function checkIsRegionAvailableByRegionCode(string $regionCode): bool
    {
        $this->db->query("SELECT params FROM s_scoring_types WHERE `name` = 'location'");
        $params = @unserialize($this->db->result('params'));
        return !$params
               || !is_array($params[self::EXCEPTION_REGIONS_CODE_FOR_REG_REGION] ?? null)
               || !in_array($regionCode, $params[self::EXCEPTION_REGIONS_CODE_FOR_REG_REGION]);
    }
}
