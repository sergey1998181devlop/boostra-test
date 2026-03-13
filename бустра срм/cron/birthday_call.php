<?php

error_reporting(-1);
ini_set('display_errors', 'On');

date_default_timezone_set('Europe/Moscow');

chdir(dirname(__FILE__) . '/../');
require_once 'api/Simpla.php';

class BirthdayCron extends Simpla
{
    private const ALLOWED_HOUR_FROM = 10;
    private const ALLOWED_HOUR_TO = 18;
    private const BIRTHDAY_MAN_SCENARIO_ID = 54612;
    private const BIRTHDAY_WOMAN_SCENARIO_ID = 54611;
    private const BIRTHDAY_VOXIMPLANT_OUTCOIMNG_PHONE_NUMBER_ID = 15669;
    private const LOG_FILE = 'birthday_calls.txt';

    public function run()
    {
        $this->logging(__METHOD__, '', "Работа крона начата", ['status' => 'started'], self::LOG_FILE);
        $totalProcessed = 0;
        $totalCalls = 0;

        $users = $this->users->fetchBirthdayClients();
        $processedEmails = array();

        foreach ($users as $user) {
            $totalProcessed++;

            // Проверка временных ограничений
            $timeCheck = $this->users->checkTimeRestrictions($user->timezone_id, $user->timezone_offset, self::ALLOWED_HOUR_FROM, self::ALLOWED_HOUR_TO);

            if (!$timeCheck['can_call']) {
                $this->logging(__METHOD__, '', "Звонок по номеру заблокирован",
                    ['status' => 'blocked',
                        'phone' => $user->phone_mobile,
                        'error' => $timeCheck['error']
                    ], self::LOG_FILE);
                continue;
            }

            // Создаем запись о звонке
            $this->voxCalls->setNewCall($user, "birthday");

            // Отправляем звонок
            $resultVoxImplant = $this->voximplant->sendVoximplantCall(
                (string)$user->phone_mobile,
                $user->gender == "male" ? self::BIRTHDAY_MAN_SCENARIO_ID : self::BIRTHDAY_WOMAN_SCENARIO_ID,
                self::BIRTHDAY_VOXIMPLANT_OUTCOIMNG_PHONE_NUMBER_ID
            );

            $base64_user_id = base64_encode($user->id);
            $hmac_hash = hash_hmac('sha256', $base64_user_id, $this->config->email_secret_key);
            $encrypted_user_id = $base64_user_id . '.' . $hmac_hash;
            $this->design->assign('encrypted_user_id', $encrypted_user_id);
            $email_body = $this->design->fetch('birthday_email.tpl');

            $result_email = false;
            if (!array_key_exists($user->email, $processedEmails) && $this->user_data->read($user->id, 'email_is_unsubscribed') == null && $user->email != "" && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                $result_email = $this->emails->sendEmail("С Днём Рождения от Бустры!", $email_body, $user->email);
                $processedEmails[$user->email] = $user->id;
            }

            $email_result = [
                'success' => $result_email,
                'email' => $user->email,
            ];

            $totalCalls++;
            $this->logging(__METHOD__, '', "Номер обработан", ['result_voximplant' => $resultVoxImplant, 'result_email' => $email_result], self::LOG_FILE);
        }

        $this->logging(__METHOD__, '', "Работа крона завершена", [
            'status' => 'completed',
            'message' => 'All users processed',
            'total_processed' => $totalProcessed,
            'total_calls' => $totalCalls
        ], self::LOG_FILE);
    }
}

$cron = new BirthdayCron();
$cron->run();