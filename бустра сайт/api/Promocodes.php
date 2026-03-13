<?php

require_once(__DIR__ . '/Simpla.php');
require_once(__DIR__ . '/CreditDoctor.php');
require_once(__DIR__ . '/Helpers.php');

class Promocodes extends Simpla
{

    public const MODE_NONE   = 0;
    public const MODE_BANNER = 1;
    public const MODE_FORM   = 2;
    public const MODE_APPLY  = 3;

    public const PROMO_LENGTH = 6;

    public function promocodeMode($user_id) {
        $last_order = (array)$this->orders->get_last_order($user_id);
        $order_is_approved = Helpers::isApproved($last_order); // одобрен ли займ
        $order_is_worked   = $last_order['1c_status'] === $this->orders::ORDER_1C_STATUS_CONFIRMED
                                && $last_order['status'] == $this->orders::STATUS_APPROVED; //проверка на действующий займ
        $order_is_rejected = ($last_order['1c_status'] === $this->orders::ORDER_1C_STATUS_REJECTED
                                || $last_order['1c_status'] === $this->orders::ORDER_1C_STATUS_REJECTED_TECH)
                                && $last_order['status'] == $this->orders::STATUS_REJECTED; //проверка на отказ
        if($order_is_rejected) {
            return static::MODE_BANNER;
        } elseif (!$order_is_worked) {
            if($order_is_approved) {
                if($last_order['promocode']) {
                    return static::MODE_APPLY;
                } else {
                    return static::MODE_FORM;
                }
            } else {
                return static::MODE_BANNER;
            }
        }
        return static::MODE_NONE;
    }
    
    private function getInfo(string $code = '', int $id = 0)
    {
        if (trim($code)) {
            $param = $code;
            $cond = 'p.promocode = ?';
        } elseif ($id) {
            $param = $id;
            $cond = 'p.id = ?';
        } else {
            return null;
        }

        $this->db->query('SET group_concat_max_len = 1000000;');

        $query = $this->db->placehold("
            SELECT
                p.*,
                GROUP_CONCAT(DISTINCT TRIM(u.phone_mobile)) AS used_phones,
                COUNT(DISTINCT o.id) AS total_usage
            FROM __promocodes p
            LEFT JOIN __orders o ON o.promocode = p.id
            LEFT JOIN __users u ON o.user_id = u.id
            WHERE {$cond}
            GROUP BY p.id
        ", $param);

        $this->db->query($query);
        return $this->db->result();
    }

    public function getInfoById(int $id)
    {
        return $this->getInfo('', $id);
    }

    public function getInfoByCode(string $code)
    {
        return $this->getInfo($code);
    }

    public function checkAvailability($promocode, string $phone, \Datetime $check_date = null)
    {
        if ($promocode) {
            $check_date = $check_date ?? new \DateTime('now');
            $check_date = $check_date->format('Y-m-d');
            if ($check_date > $promocode->date_end || $check_date < $promocode->date_start || !$phone) {
                return false;
            } elseif ($promocode->quantity && $promocode->quantity <= $promocode->total_usage) {
                return false;
            } elseif ($promocode->phone && ($promocode->total_usage > 0 || $promocode->phone != $phone)) {
                return false;
            } elseif (strpos($promocode->used_phones, $phone) !== false) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }
    
    public function getLastUnusedPromoCode($phoneMobile)
    {
        $this->db->query("
            SELECT p.*
            FROM __promocodes p
            WHERE p.phone = ?
              AND p.date_start <= CURDATE()
              AND p.date_end >= CURDATE()
              AND NOT EXISTS (
                  SELECT 1 
                  FROM __orders o 
                  WHERE o.promocode = p.id
              )
            ORDER BY p.id DESC
            LIMIT 1;
        ", $phoneMobile);
        
        return $this->db->result();
    }

    public function apply($order, $promocode): bool
    {
        if ($order && $promocode) {
            $order_fields = [];
            $order_fields['percent'] = $promocode->rate;
            $order_fields['promocode'] = $promocode->id;

            // Устанавливаем срок займа, если указан в промокоде
            if ($promocode->limit_term) {
                $order_fields['period'] = $promocode->limit_term;
            }

            // Отключаем дополнительные услуги
            if ($promocode->disable_additional_services) {
                $this->order_data->set($order->id, OrderData::ADDITIONAL_SERVICE_TV_MED, 1);
                $this->order_data->set($order->id, OrderData::ADDITIONAL_SERVICE_MULTIPOLIS, 1);
                $this->order_data->set($order->id, OrderData::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT, 1);
                $this->order_data->set($order->id, OrderData::ADDITIONAL_SERVICE_REPAYMENT, 1);
            }

            // Помечаем займ как обязательный к выдаче
            if ($promocode->is_mandatory_issue) {
                $this->order_data->set($order->id, 'MANDATORY_ISSUE', 1);
            }
            
            if ($order->first_loan && ($promocode->disable_additional_services || $promocode->is_mandatory_issue)) {
                return false;
            }

            $object = $this->soap->generateObject([
                'НомерЗаявки' => $order->{'1c_id'},
                'Ставка' => $order_fields['percent'],
            ]);
            $soap_result = $this->soap->requestSoap($object, 'WebOtvetZayavki', 'ChangeRate');

            if (!empty($soap_result['response'])) {
                $this->orders->update_order($order->id, $order_fields);

                return true;
            } else {
                error_log("SOAP ChangeRate failed. Order ID: {$order->id}, Promocode ID: {$promocode->id}, Rate: {$promocode->rate}");

                return false;
            }
        }
        
        return false;
    }

    /**
     * Получаем отображение промокода, для новых клиентов в атовыдачи
     * @param $last_order
     * @return int
     */
    public function promoCodeModeAutoConfirmNewUser($last_order): int
    {
        if (!empty($last_order->promocode)) {
            return static::MODE_APPLY;
        } else {
            return static::MODE_FORM;
        }
    }

    /**
     * @param $order
     * @param $promocode
     * @return bool
     */
    public function addFirstLoanPromocode($order, $promocode): bool
    {
        $this->order_data->set($order->id, 'promocode_id', $promocode->id);
        return true;
    }
}
