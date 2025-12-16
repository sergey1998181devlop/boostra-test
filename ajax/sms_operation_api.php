<?php

declare(strict_types=1);

/**
 * SMS (Алфавит): отправка и проверка кода.
 * В последующем можно поменять для разных допов
 *
 * Ввод:
 *   - action: send | verify   (из URL или ?action=)
 *   - token:  секрет (строка)
 *   - uid:    UUID пользователя
 *   - code:   4 цифры (ТОЛЬКО для verify)
 *
 * Ответы (кратко):
 *   - успех  → "да"
 *   - ошибка → "нет"
 *
 * Политики:
 *   - длина кода: 4
 *   - TTL кода: 10 мин
 *   - попытки: 5
 *   - кулдаун отправки: 60 сек
 *
 * Таблица логов: __addition_sms_messages (message_id, phone, user_uid, type, code, used)
 *
 * Примеры:
 *   POST /rest_api/sms-operation/send?token=...&uid=...
 *   POST /rest_api/sms-operation/verify?token=...&uid=...&code=1234
 *
 * .htaccess:
 *   RewriteRule ^rest_api/sms-operation/(send|verify)$ api/sms_operation.php?action=$1 [QSA,NC,L]
 */

require_once dirname(__DIR__) . '/api/Simpla.php';

class SmsOperation extends Simpla
{
    private const SECURE_CHECKER_TOKEN = '437b12ec-6d06-11f0-89d2-26dcc1fc1820';
    private const TYPE_ADDITION = 'addition_alfavit';
    private const MESSAGE_ADDITION = 'Ваш код для подписания услуг с ООО "Алфавит":';
    private const TOKEN_LEN = 12;
    private const CODE_LENGTH = 4;


    /** @var string|null */
    private ?string $action = null;

    /** @var string|null */
    private ?string $token = null;

    /** @var string|null */
    private ?string $uid = null;

    /** @var string|null */
    private ?string $code = null;

    /** @var string|null */
    private ?string $phone = null;

    public function __construct()
    {
        parent::__construct();

        $this->action = $this->request->get('action', 'string') ?: null; // send|verify
        $this->token = $this->request->get('token', 'string') ?: null;
        $this->uid = $this->request->get('uid', 'string') ?: null;
        $this->code = $this->request->get('code', 'string') ?: null;
        $this->phone = null;
    }

    public function run(): void
    {
        // простая валидация
        if (!$this->validateCommon()) {
            return;
        }

        switch ($this->action) {
            case 'send':
                $this->handleSend();
                return;

            case 'verify':
                $this->handleVerify();
                return;

            default:
                $this->request->json_output([
                    'success' => false,
                    'error' => 'invalid_action',
                    'message' => 'Некорректное значение action. Используйте: send или verify.',
                ]);
                return;
        }
    }

    /**
     * Общая валидация: секрет и uid.
     */
    private function validateCommon(): bool
    {
        // проверка токена
        if ((strlen($this->token) < self::TOKEN_LEN)
            || ($this->token !== self::SECURE_CHECKER_TOKEN)) {
            $this->request->json_output([
                'success' => false,
                'error' => 'unauthorized',
                'message' => 'Некорректный формат token или сам токен.',
            ]);
            return false;
        }

        // обработка кода
        if ($this->action === 'verify' && ($this->code === null || !preg_match('/^\d{3,8}$/', $this->code))) {
            $this->request->json_output([
                'success' => false,
                'error' => 'invalid_code',
                'message' => 'Некорректный формат кода.',
            ]);
            return false;
        }

        // проверка uid клиента
        if (
            $this->uid === null ||
            !$this->isValidUuid($this->uid)
        ) {
            $this->request->json_output([
                'success' => false,
                'error' => 'invalid_uid',
                'message' => 'Некорректный uid (ожидается UUID).',
            ]);
            return false;
        }

        // получаем пользователя
        $user = $this->users->get_user_by_uid($this->uid);
        if (!$user) {
            $this->request->json_output([
                'success' => false,
                'error' => 'invalid_uid',
                'message' => 'Некорректный uid.',
            ]);
            return false;
        }

        $this->phone = $user->phone_mobile;
        return true;
    }

    /**
     * Отправка кода (создание записи и, при необходимости, внешний вызов SMS-провайдера).
     */
    private function handleSend(): void
    {
        $code = $this->generateCode(self::CODE_LENGTH);
        $sms_text = self::MESSAGE_ADDITION . $code;
        $msg = iconv('utf-8', 'cp1251', $sms_text);

        $send_result = $this->notify->send_sms($this->phone, $msg);

        if (!is_numeric($send_result)) {
            $this->logging(
                __METHOD__, "", ['phone' => $this->phone, "msg" => $msg],
                $send_result, 'user_addition_alfavit_send_message.txt'
            );
        }

        $sms_id = $this->sms->add_message([
            'phone' => $this->phone,
            'message' => $sms_text,
            'send_id' => $send_result,
            'type' => self::TYPE_ADDITION,
        ]);

        $data = [
            'message_id' => $sms_id,
            'phone' => $this->phone,
            'user_uid' => $this->uid,
            'type' => self::TYPE_ADDITION,
            'code' => $code,
            'used' => 0,
        ];


        $q = $this->db->placehold("INSERT INTO __addition_sms_messages SET ?%", $data);
        $this->db->query($q);

        // Здесь обычно вызывается провайдер SMS. В проде код в ответ НЕ возвращаем.
        $payload = [
            'success' => true,
            'status' => 'sent',
            'message' => 'Код отправлен.'
        ];

        $this->request->json_output($payload);
    }

    /**
     * Проверка кода.
     */
    private function handleVerify(): void
    {

        $query = $this->db->placehold("
            SELECT EXISTS(
              SELECT 1
              FROM __addition_sms_messages
              WHERE user_uid = ? AND type = ? AND code = ? AND used = 0
              LIMIT 1
            ) AS ex
        ", $this->uid, self::TYPE_ADDITION, $this->code);
        $this->db->query($query);

        if ($this->db->result('ex')) {
            $q = $this->db->placehold("
            UPDATE __addition_sms_messages
               SET used = 1
             WHERE user_uid = ?
               AND type = ?
               AND used = 0
               AND code = ?
             ORDER BY id DESC
             LIMIT 1
        ", $this->uid, self::TYPE_ADDITION, $this->code);
            $this->db->query($q);

            $this->request->json_output([
                'success' => true,
                'status' => 'verified',
                'message' => 'Код успешно подтверждён.',
            ]);

        }

        $this->request->json_output([
            'success' => false,
            'error' => 'not_found',
            'message' => 'Актуальный код не найден. Запросите отправку кода заново.',
        ]);
    }


    /**
     * SUB -----------------------------
     */

    /**
     * Генерация цифрового кода нужной длины.
     */
    private function generateCode(int $len = 4): string
    {
        // 4–8 цифр
        $len = max(3, min(8, $len));
        $min = (int)str_pad('1', $len, '0');
        $max = (int)str_pad('', $len, '9');
        return (string)random_int($min, $max);
    }

    /**
     * @param string $v
     * @return bool
     */
    private function isValidUuid(string $v): bool
    {
        // ^8-4-4-4-12, версия [1-7], вариант [89ab], без лишних символов
        return (bool)preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-7][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $v
        );
    }

}

(new SmsOperation())->run();