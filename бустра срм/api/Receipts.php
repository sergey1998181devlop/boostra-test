<?php

require_once 'Simpla.php';

class Receipts extends Simpla
{
    /**
     * id организации бустра в таблице s_organizations
     */
    const ORGANIZATION_BOOSTRA = 1;
    const ORGANIZATION_AKVARIUS = 6;
    const ORGANIZATION_FINTEHMARKET = 8;
    const ORGANIZATION_MOREDENEG = 17;

    const ORGANIZATION_RUBL = 21;

    /**
     * Кредитный Доктор
     */
    public const PAYMENT_TYPE_CREDIT_DOCTOR = 'credit_doctor';
    public const PAYMENT_TYPE_STAR_ORACLE = 'star_oracle';
    public const PAYMENT_TYPE_TV_MEDICAL = 'tv_medical';
    public const PAYMENT_TYPE_SAFE_DEAL = 'safe_deal';

    /**
     * Возврат по Кредитному Доктору
     */
    public const PAYMENT_TYPE_RETURN_CREDIT_DOCTOR = 'return_credit_doctor';
    public const PAYMENT_TYPE_RETURN_PENALTY_CREDIT_DOCTOR = 'return_penalty_credit_doctor';

    public const PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_CHEQUE = 'RECOMPENSE_CREDIT_DOCTOR';
    public const PAYMENT_TYPE_RETURN_PENALTY_CREDIT_DOCTOR_CHEQUE = 'RECOMPENSE_PENALTY_CREDIT_DOCTOR';
    public const PAYMENT_TYPE_RETURN_MULTIPOLIS_CHEQUE = 'RECOMPENSE_MULTIPOLIS';
    public const PAYMENT_TYPE_RETURN_TV_MEDICAL_CHEQUE = 'RECOMPENSE_TV_MEDICAL';
    public const PAYMENT_TYPE_RETURN_STAR_ORACLE_CHEQUE = 'RECOMPENSE_STAR_ORACLE';
    public const PAYMENT_TYPE_RETURN_SAFE_DEAL_CHEQUE = 'RECOMPENSE_SAFE_DEAL';

    /**
     * Возврат по Мультиполису
     */
    public const PAYMENT_TYPE_RETURN_MULTIPOLIS = 'return_multipolis';

    /**
     * Возврат по Телемедецине
     */
    public const PAYMENT_TYPE_RETURN_TV_MEDICAL = 'return_tv_medical';
    /**
     * Возврат по Звездный Оракул
     */
    public const PAYMENT_TYPE_RETURN_STAR_ORACLE = 'return_star_oracle';
    /**
     * Возврат по Безопасной сделке
     */
    public const PAYMENT_TYPE_RETURN_SAFE_DEAL = 'return_safe_deal';

    /**
     * Возврат по реквизитам
     */
    public const PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_REQUISITES = 'REFUND_CREDIT_DOCTOR_REQUISITES';
    public const PAYMENT_TYPE_RETURN_STAR_ORACLE_REQUISITES = 'REFUND_STAR_ORACLE_REQUISITES';
    public const PAYMENT_TYPE_RETURN_SAFE_DEAL_REQUISITES = 'REFUND_SAFE_DEAL_REQUISITES';
    public const PAYMENT_TYPE_RETURN_MULTIPOLIS_REQUISITES = 'REFUND_MULTIPOLIS_REQUISITES';
    public const PAYMENT_TYPE_RETURN_TV_MEDICAL_REQUISITES = 'REFUND_TV_MEDICAL_REQUISITES';
    public const PAYMENT_TYPE_RETURN_OVERPAYMENT_REQUISITES = 'REFUND_OVERPAYMENT_REQUISITES';

    /**
     * Ссылка на скачивание чека
     * последним параметром необходимо подставить id чека
     * https://receipts.ru/Home/Download/aS42yQn
     */
    public const DOWNLOAD_RECEIPT_URL = 'https://receipts.ru/Home/Download/';

    /**
     * Описания услуг
     */
    public const PAYMENT_DESCRIPTIONS = [
        self::PAYMENT_TYPE_CREDIT_DOCTOR => 'ПО «Финансовый Доктор»',
        self::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR => 'Возврат за ПО «Финансовый Доктор»',
        self::PAYMENT_TYPE_RETURN_PENALTY_CREDIT_DOCTOR => 'Возврат по услуге Кредитный Доктор',
        self::PAYMENT_TYPE_TV_MEDICAL => 'ПО «ВитаМед»',
        self::PAYMENT_TYPE_RETURN_MULTIPOLIS => 'Возврат за ПО «Boostra Concierge»',
        self::PAYMENT_TYPE_RETURN_TV_MEDICAL => 'Возврат за ПО «ВитаМед»',
        self::PAYMENT_TYPE_STAR_ORACLE => 'ПО «Звездный Оракул»',
        self::PAYMENT_TYPE_SAFE_DEAL => 'ПО «Безопасная сделка»',
        self::PAYMENT_TYPE_RETURN_STAR_ORACLE => 'Возврат за «Звездный Оракул»',
        self::PAYMENT_TYPE_RETURN_SAFE_DEAL => 'Возврат за «Безопасная сделка»',
        self::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_REQUISITES => 'Возврат по реквизитам за ПО «Финансовый Доктор»',
        self::PAYMENT_TYPE_RETURN_STAR_ORACLE_REQUISITES => 'Возврат по реквизитам за «Звездный Оракул»',
        self::PAYMENT_TYPE_RETURN_SAFE_DEAL_REQUISITES => 'Возврат по реквизитам за «Безопасная сделка»',
        self::PAYMENT_TYPE_RETURN_MULTIPOLIS_REQUISITES => 'Возврат по реквизитам за ПО «Boostra Concierge»',
        self::PAYMENT_TYPE_RETURN_TV_MEDICAL_REQUISITES => 'Возврат по реквизитам за ПО «ВитаМед»',
        self::PAYMENT_TYPE_RETURN_OVERPAYMENT_REQUISITES => 'Возврат переплаты по реквизитам',
    ];

    /**
     * Добавляет новый чек
     * @param array $data
     * @return mixed
     */
    public function addItem(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_receipts SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Получает ссылку на скачивание чека по возвращенному КД
     * @param int $transaction_id
     * @return string|null
     */
    public function getReceiptUrlDownloadReturnCD(int $transaction_id): ?string
    {
        $query = $this->db->placehold(
            "SELECT receipt_id FROM s_receipts WHERE transaction_id = ?",
            $transaction_id
        );
        $this->db->query($query);
        $receiptId = $this->db->result('receipt_id');
        return !empty($receiptId) ? self::DOWNLOAD_RECEIPT_URL . $receiptId : null;
    }

    /**
     * @param int $order_id
     * @param array $types
     * @return array|false
     */
    public function getReturnedReceipts(int $order_id,array $types)
    {
        $query = $this->db->placehold(
            'SELECT r.*,r.payment_type as type FROM s_receipts r WHERE r.order_id = ? and  r.payment_type in (?@)',
            $order_id,
            array_map('strval', $types)
        );
        $this->db->query($query);
        return $this->db->results() ?? [];
    }
    
    
    /**
     * @param int $order_id
     * @param int $payment_id
     * @param array $types
     * @return array|false
     */
    public function getReturnedReceiptsByPayment(int $order_id,int $payment_id ,array $types)
    {
        $query = $this->db->placehold(
            'SELECT r.*,t.type FROM s_receipts r
                LEFT JOIN b2p_transactions t ON t.id = r.transaction_id 
                  WHERE  r.order_id = ? and
                         t.reference = ? and
                        r.payment_type in (?@)',
            $order_id,
            $payment_id,
            array_map('strval', $types)
        );
        $this->db->query($query);
        return $this->db->results() ?? [];
    }
}
