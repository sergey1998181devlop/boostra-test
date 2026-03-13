<?php

require_once 'Simpla.php';

/**
 * Класс для работы с доп.почтами пользователя.
 */
class UserEmails extends Simpla
{
    const SOURCE_NBKI_EMAIL = 'NBKI_EMAIL';

    /**
     * Обрабатывает XML строку из АКСИ НБКИ и возвращает найденные почты
     * @param string $xmlString
     * @return SimpleXMLElement
     */
    public function parseXml(string $xmlString)
    {
        $xmlString = mb_convert_encoding($xmlString, 'windows-1251', 'utf-8');
        $xml = simplexml_load_string($xmlString);
        return $xml->preply2->report->ContactReply;
    }

    /**
     * Загружает XML строку и синхронизирует найденные почты
     * @param int $orderId
     * @param string $xmlString
     */
    public function loadXml(int $orderId, string $xmlString)
    {
        $parse_result = $this->parseXml($xmlString);

        $order = $this->orders->get_order($orderId);
        $user = $this->users->get_user($order->user_id);

        foreach ($parse_result as $result) {
            if (!empty($result->email)) {
                $this->syncEmail($order->user_id, (string)$result->email, self::SOURCE_NBKI_EMAIL, $user->UID);
            }
        }
    }

    /**
     * Синхронизирует почты с CRM и 1C
     * @param string $userId
     * @param string $email
     * @param string $source
     * @param string $user1cUid
     */
    public function syncEmail(string $userId, string $email, string $source, string $user1cUid = '')
    {
        $user = $this->users->get_user($userId);
        $user1cUid = !empty($user1cUid) ? $user1cUid : $user->UID;

        $this->syncCrm($user, $email, $source);
        $this->sync1c($user1cUid, $email);
    }

    /**
     * Отправляет почту в бд CRM
     * @param object $user
     * @param string $email
     * @param string $source
     * @return bool false - почта уже добавлена к этому пользователю, иначе true
     */
    private function syncCrm(object $user, string $email, string $source): bool
    {
        if ($user->email === $email) {
            // Это основной email
            return false;
        }

        $usersWithSameEmail = $this->getUsersWithSameEmail($email);
        foreach ($usersWithSameEmail as $otherUserId) {
            if ((int) $otherUserId === (int) $user->id) {
                // Этот доп.email уже добавлен
                return false;
            }
        }

        $this->add([
            'user_id' => $user->id,
            'email' => $email,
            'source' => $source
        ]);
        return true;
    }

    /**
     * Отправляет email в 1С и помечает его как синхронизированный.
     *
     * @param string $userUid UID пользователя в 1С
     * @param string $email Email-адрес
     * @return void
     */
    private function sync1c(string $userUid, string $email): void
    {
        try {
            $response = $this->soap->sendAdditionalEmail($userUid, [$email]);
            $payload = $response['response'] ?? $response;

            $isSuccess = !array_key_exists('КонтрагентНайден', $payload) || $payload['КонтрагентНайден'] === true;

            if ($isSuccess) {
                $userId = $this->users->get_uid_user_id($userUid);
                if ($userId) {
                    $this->markSynced((int) $userId, $email);
                }
                return;
            }

            $this->logging(
                __METHOD__,
                'SendAdditionalEmail failed',
                ['uid' => $userUid, 'email' => $email],
                ['response' => $payload],
                'sync_user_emails.txt'
            );
        } catch (\Throwable $e) {
            $this->logging(
                __METHOD__,
                'SendAdditionalEmail exception',
                ['uid' => $userUid, 'email' => $email],
                ['error' => $e->getMessage()],
                'sync_user_emails.txt'
            );
        }
    }

    /**
     * Поиск пользователей с указанной доп.почтой
     * @param string $email
     * @return array
     */
    public function getUsersWithSameEmail(string $email): array
    {
        $this->db->query($this->db->placehold('SELECT user_id FROM __user_emails WHERE email = ?', $email));

        return $this->db->results('user_id') ?? [];
    }

    /**
     * Добавление доп.почты в бд CRM
     * @param array $row
     * @return int
     */
    public function add(array $row): int
    {
        $this->db->query($this->db->placehold('INSERT INTO __user_emails SET ?%', $row));

        return $this->db->insert_id();
    }

    /**
     * Получение конкретной почты по её Id
     * @param int $id
     * @return array
     */
    public function get(int $id): array
    {
        $this->db->query($this->db->placehold('SELECT * FROM __user_emails WHERE id = ?', $id));

        return $this->db->result() ?? [];
    }

    /**
     * Все доп.почты пользователя
     * @param int $userId
     * @return array
     */
    public function getUserEmails(int $userId): array
    {
        $this->db->query(
            $this->db->placehold('SELECT * FROM __user_emails WHERE user_id = ? AND is_active = 1', $userId)
        );

        return $this->db->results() ?? [];
    }

    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->query($this->db->placehold("UPDATE __user_emails SET ?% WHERE id = ?", $data, $id));
    }

    /**
     * Отправка email по SMTP
     * @param string $subject Тема письма
     * @param string $message Текст сообщения (поддерживает HTML)
     * @param string|null $toEmail Email получателя (если null, берется из профиля пользователя)
     * @return bool true - письмо отправлено, false - ошибка
     */
    public function sendEmail(string $subject, string $message, string $toEmail = null): bool
    {
        try {
            if (empty($toEmail)) {
                return false;
            }

            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host       = $this->config->notify_email_smtp_host;     // SMTP сервер
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->config->notify_email_smtp_user;     // SMTP логин
            $mail->Password   = $this->config->notify_email_smtp_password; // SMTP пароль
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // или PHPMailer::ENCRYPTION_SMTPS для SSL
            $mail->Port       = $this->config->notify_email_smtp_port;     // 587 для TLS, 465 для SSL
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($this->config->from_email_name, 'Компания Boostra.ru');

            $mail->addAddress($toEmail, $user->name ?? '');

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message);

            $mail->send();

            return true;

        } catch (Exception $e) {
            error_log("Email sending failed: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Помечает email как синхронизированный с 1С.
     *
     * @param int $userId ID пользователя
     * @param string $email Email-адрес
     *
     * @return void
     */
    public function markSynced(int $userId, string $email): void
    {
        $this->db->query(
            $this->db->placehold(
                'UPDATE __user_emails SET synced_at = NOW(), updated_at = NOW() WHERE user_id = ? AND email = ?',
                $userId,
                $email
            )
        );
    }

    /**
     * Несинхронизированные доп.почты пользователя
     *
     * @param int $userId
     * @return array
     */
    public function getUnsyncedUserEmails(int $userId): array
    {
        $this->db->query(
            $this->db->placehold(
                'SELECT * FROM __user_emails WHERE user_id = ? AND is_active = 1 AND (synced_at IS NULL OR synced_at = "0000-00-00 00:00:00")',
                $userId
            )
        );

        return $this->db->results() ?? [];
    }

    /**
     * Отправляет в 1С все несинхронизированные доп.почты пользователя,
     *
     * @param int $userId
     * @return array ['total' => int, 'processed' => int]
     */
    public function syncUnsyncedForUser(int $userId): array
    {
        $result = [
            'total'     => 0,
            'processed' => 0,
        ];

        $user = $this->users->get_user($userId);
        if (!$user || empty($user->UID)) {
            return $result;
        }

        $emails = $this->getUnsyncedUserEmails($userId);
        if (empty($emails)) {
            return $result;
        }

        $result['total'] = count($emails);

        foreach ($emails as $row) {
            $this->sync1c($user->UID, $row->email);
            $result['processed']++;
        }

        return $result;
    }
}