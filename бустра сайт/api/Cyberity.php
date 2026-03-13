<?php

require_once 'Simpla.php';

class Cyberity extends Simpla
{
    /** @var string Статус новой верификации (перед открытием self_verification.tpl) */
    public const STATUS_NEW = 'NEW';

    /** @var string Статус после загрузки iframe cyberity верификации */
    public const STATUS_STARTED_APPLICATION = 'STARTED_APPLICATION';

    /** @var string Статус после добавления фото паспорта */
    public const STATUS_ADDED_PASSPORT = 'ADDED_PASSPORT';

    /** @var string Статус после добавления скана лица */
    public const STATUS_ADDED_SELFIE = 'ADDED_SELFIE';

    /** @var string Статус после отправки фотографии паспорта и скана лица */
    public const STATUS_PROGRESS = 'PROGRESS';

    /** @var string Статус завершенной верификации (после получения коллбека с результатом верификации на /ajax/cyberity_callback.php) */
    public const STATUS_COMPLETED = 'COMPLETED';

    /** @var string Уровень проверки (уровни проверок позволяют детально настроить процесс верификации Ваших пользователей) */
    public const CHECK_LEVEL = 'basic-kyc-level';
    public const PASSPORT_LEVEL = 'passport-kyc-level';
    public const SELFIE_LEVEL = 'selfy-kys-level';

    /** @var string Токен */
    private $token;

    /** @var string Секретный ключ */
    private $secretKey;

    /** @var bool Включен ли функционал */
    private $isEnabled;

    public function __construct()
    {
        parent::__construct();

        $this->token = $this->settings->apikeys['cyberity']['token'];
        $this->secretKey = $this->settings->apikeys['cyberity']['secret_key'];
        $this->isEnabled = (bool)$this->settings->apikeys['cyberity']['enabled'];
    }

    /**
     * Включен ли функционал антифрод верификации Cyberitu
     *
     * @return bool
     */
    public function isCyberityVerificationEnabled(): bool
    {
        if (!empty($this->token) && !empty($this->secretKey) && !empty($this->isEnabled)) {
            return true;
        }

        return false;
    }

    /**
     * @param array $where
     * @return stdClass|null|bool
     */
    public function getUserVerification(array $where)
    {
        $conditions = [];
        foreach ($where as $condition => $value) {
            $conditions[] = $this->db->placehold("`$condition` = ?", $value);
        }

        $conditions = implode(' AND ', $conditions);
        $this->db->query("SELECT * FROM __cyberity_verifications WHERE $conditions ORDER BY id DESC LIMIT 1");

        return $this->db->result();
    }

    /**
     * @param array $data
     * @return int
     */
    public function insertUserVerification(array $data): int
    {
        $this->db->query("INSERT INTO __cyberity_verifications SET ?%", $data);
        return $this->db->insert_id();
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUserVerification(int $id, array $data): bool
    {
        $query = $this->db->placehold("UPDATE __cyberity_verifications SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }
}