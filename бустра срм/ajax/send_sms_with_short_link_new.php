<?php
error_reporting(0);
ini_set('display_errors', 'off');

header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");
define('ROOT', dirname(__DIR__));

session_start();
chdir('..');

require 'api/Simpla.php';

$simpla = new Simpla();

class SendSms extends Simpla
{
    private const SERVICES = [
        "doctor-sms-inform" => ['id' => 33, 'name' => 'Финансовый доктор', 'policy' => 'CREDIT_DOCTOR_POLICY'],
        "oracle-sms-inform" => ['id' => 64, 'name' => 'Звёздный оракул', 'policy' => 'STAR_ORACLE_POLICY'],
        "vita-sms-inform" => ['id' => 65, 'name' => 'Вита-мед', 'policy' => 'ACCEPT_TELEMEDICINE'],
        "concierge-sms-inform" => ['id' => 68, 'name' => 'Консьерж сервис', 'policy' => 'DOC_MULTIPOLIS'],
    ];

    function __construct()
    {
        parent::__construct();

        $type = $this->request->post('type');
        $policyId = $this->request->post('policyId');
        $orderId = $this->request->post('orderId');
        $manager = $this->request->post('manager');
        $phone = $this->request->post('phone');
        $whoseNumber = $this->request->post('whoseNumber');
        $clientPhone = $this->request->post('clientPhone');

        if (empty($type) || empty($orderId) || empty($manager) || empty($phone)) {
            $this->response->json_output(['error' => 'Неправильные параметры']);
            exit();
        }

        $user = $this->users->getUserByOrderId($orderId);
        $zaim = $this->users->get_user_balance($user->id);

        if (empty($zaim->zaim_number) || $zaim->zaim_summ == 0 || empty($zaim->zaim_summ)) {
            $this->response->json_output(['error' => 'Займ не найден']);
            exit();
        }

        // Информационные SMS по доп.услугам
        if (strstr($type, '-sms-inform')) {
            try {
                $template = $this->sms->get_templates(['id' => self::SERVICES[$type]['id']]);

                if (empty($template)) {
                    $this->response->json_output(['error' => 'SMS шаблон не найден для типа: ' . $type]);
                }

                $documents = $this->documents->get_documents([
                    'order_id' => $orderId,
                    'type' => self::SERVICES[$type]['policy'],
                ]);

                if (empty($documents[0])) {
                    $this->response->json_output(['error' => 'Не найден Полис лицензионный ' . self::SERVICES[$type]['name']]);
                }

                $smsText = $template[0]->template;

                $this->smssender->send_sms(
                    $phone,
                    $smsText,
                    $user->site_id,
                    1
                );

                $text = 'Отправлено SMS по возврату доп.услуги ' . self::SERVICES[$type]['name'] . ' на номер ' . $phone;
                $block = 'services';
            } catch (Exception $e) {
                error_log('Ошибка отправки SMS: ' . $e->getMessage());
                $this->response->json_output(['error' => 'Ошибка отправки SMS: ' . $e->getMessage()]);
            }

            $this->addSmsInform($type, $orderId, $manager, $phone, $user->id, $zaim->zaim_number);

        // SMS с лицензионным ключом
        } elseif (strstr($type, '-sms-key')) {
            $licenseKey = null;

            try {
                $typeInform = str_replace('key', 'inform', $type);

                $policy = $this->documents->get_document($policyId);

                if (empty($policy)) {
                    $this->response->json_output(['error' => 'Не найден Полис лицензионный ' . self::SERVICES[$typeInform]['name']]);
                }

                if (!empty($policy->params)) {
                    if (is_object($policy->params)) {
                        $licenseKey = $policy->params->license_key ?? null;
                    } elseif (is_array($policy->params)) {
                        $licenseKey = $policy->params['license_key'] ?? null;
                    }
                }

                if (empty($licenseKey)) {
                    $this->response->json_output(['error' => 'Лицензионный ключ для услуги ' . self::SERVICES[$typeInform]['name'] . ' отсутствует']);
                }

                $smsText = 'Ваш лицензионный ключ ' . self::SERVICES[$typeInform]['name'] . ': ' . $licenseKey;

                $this->smssender->send_sms(
                    $phone,
                    $smsText,
                    $user->site_id,
                    1
                );

                $text = 'Отправлено SMS с лицензионным ключом по доп.услуге ' . self::SERVICES[$typeInform]['name'] . ' на номер ' . $phone;
                $block = 'services';
            } catch (Exception $e) {
                error_log('Ошибка отправки SMS: ' . $e->getMessage());
                $this->response->json_output(['error' => 'Ошибка отправки SMS: ' . $e->getMessage()]);
            }

            $this->addSmsInform($type, $orderId, $manager, $phone, $user->id, $zaim->zaim_number, $licenseKey);

            // Остальные типы SMS
        } else {
            $this->smsShortLink->run($user, $zaim, $type, $orderId, $manager, false, $phone);

            $commentText = ' отправлено смс клиенту с типом ';
            if (!empty($whoseNumber)) {
                $commentText = ' отправлено смс не клиенту с типом ';
            }
            $text = $phone . $commentText . $type .' '.$whoseNumber;

            $block = 'collection';
        }

        $this->comments->add_comment([
            'manager_id' => $manager,
            'user_id' => $user->id,
            'order_id' => $orderId,
            'block' => $block,
            'text' => $text,
            'created' => date('Y-m-d H:i:s'),
        ]);

        $this->response->json_output(['success' => 'Успешно','text' => $text]);
    }

    private function addSmsInform(string $type, int $orderId, int $managerId, string $smsPhone, int $userId, string $contract, string $licenseKey = null): int
    {
        $typeInform = str_replace('key', 'inform', $type);
        $serviceName = self::SERVICES[$typeInform]['name'];
        $smsTemplateId = $typeInform === $type ? self::SERVICES[$type]['id'] : null;

        $smsType = null;
        if (strpos($type, 'inform')) {
            $smsType = 'Inform';
        } elseif (strpos($type, 'key')) {
            $smsType = 'Key';
        }

        $smsInform = [
            'user_id' => $userId,
            'contract' => $contract,
            'order_id' => $orderId,
            'manager_id' => $managerId,
            'service_name' => $serviceName,
            'sms_phone' => $smsPhone,
            'sms_template_id' => $smsTemplateId,
            'sms_type' => $smsType,
            'license_key' => $licenseKey,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $query = $this->db->placehold("
            INSERT INTO s_extra_services_informs SET ?%
        ", $smsInform);

            $this->db->query($query);
            return $this->db->insert_id();
        } catch (Exception $e) {
            error_log('Ошибка сохранения SMS: ' . $e->getMessage());
            return 0;
        }
    }
}

new SendSms();