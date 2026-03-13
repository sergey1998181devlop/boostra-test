<?php
/*
Alter for promocode's table

CREATE TABLE `s_promocodes` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(250) NULL DEFAULT '0' COMMENT 'Promocode\'s short description' COLLATE 'utf8_general_ci',
	`promocode` VARCHAR(6) NOT NULL DEFAULT '0' COMMENT 'Promocode' COLLATE 'utf8_general_ci',
	`date_start` DATE NOT NULL COMMENT 'Promocode\'s start date',
	`date_end` DATE NOT NULL COMMENT 'Promocode\'s end date',
	`rate` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT 'Promocode\'s rate',
	`quantity` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Total number of uses',
	`phone` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Phone number for personal promocodes' COLLATE 'utf8_general_ci',
	`limit_sum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Loan\'s sum limit',
	`limit_term` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Loan\'s term limit',
	`manager_id` INT(11) NOT NULL COMMENT 'Promocode\'s creator',
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `promocode` (`promocode`) USING BTREE,
	INDEX `FK_s_promocodes_s_managers` (`manager_id`) USING BTREE,
	CONSTRAINT `FK_s_promocodes_s_managers` FOREIGN KEY (`manager_id`) REFERENCES `s_managers` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COMMENT='Client\'s promocodes'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

*/

require_once 'Simpla.php';

class Promocodes extends Simpla
{
    public const PAGE_SIZE = 20;
    public const ACCEPTED_CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    public const CODE_PATTERN = '__';

    public function getList(array $filters = []) {

        $start_page = $filters['start_page'] ?? 1;
        
		$query = $this->db->placehold("
            SELECT p.*, m.name AS manager
            FROM __promocodes p
            LEFT JOIN __managers m
                ON m.id = p.manager_id
            ORDER BY id DESC
            LIMIT ?,?
        ", ($start_page - 1) * static::PAGE_SIZE, static::PAGE_SIZE);
        $this->db->query($query);
        $results = $this->db->results();
        return $results;
    }

    public function getOne($where) {

        $conditions = [];
        foreach ($where as $condition => $value) {
            $conditions[] = $this->db->placehold("`$condition` IN(?@)", $value);
        }

        $conditions = implode(' AND ', $conditions);
        $this->db->query("SELECT * FROM __promocodes WHERE 1 AND $conditions ORDER BY id DESC");
        return $this->db->result();
    }

    public function getPromocode(string $promocode = '') {
        
		$query = $this->db->placehold("
            SELECT p.*, m.name AS manager
            FROM __promocodes p
            LEFT JOIN __managers m
                ON m.id = p.manager_id
            WHERE promocode = ?
        ", $promocode);
        $this->db->query($query);
        return $this->db->result();
    }

    public function getLastSettings() {
        
		$query = $this->db->placehold("
            SELECT rate, quantity, limit_sum, limit_term
            FROM __promocodes p
            ORDER BY id DESC
            LIMIT 1
        ");
        $this->db->query($query);
        return $this->db->result();
    }

    public function count() {
		$query = $this->db->placehold("
            SELECT COUNT(*)
            FROM __promocodes");
        $this->db->query($query);
        return $this->db->result();
    }

    public static function randomStringGenerator() {
        $source = static::ACCEPTED_CHARS;
        $patternLength = strlen(static::CODE_PATTERN);

        $result = [];

        // Генерация основных символов
        for ($i = 0; $i < $patternLength; $i++) {
            $result[] = $source[random_int(0, strlen($source) - 1)];
        }

        // Добавление двух случайных цифр
        $result[] = random_int(0, 9);
        $result[] = random_int(0, 9);

        // Перемешивание результата
        shuffle($result);

        return implode('', $result);
    }

    /**
     * @throws Exception
     */
    public function generate(float $rate, string $phone = '') {
        do {
            $promocodeBase = static::randomStringGenerator();
            $promocode = $promocodeBase . (trim($phone) ? 'P' : 'M') . ((int)($rate * 10) % 10);

            if ($this->db->query('SELECT COUNT(*) as count FROM __promocodes WHERE promocode = ?', $promocode)) {
                $result = $this->db->result();
                $exists = $result->count > 0;
            } else {
                throw new Exception('Ошибка при проверке существования промокода.');
            }
        } while ($exists);

        return $promocode;
    }

    /**
     * @throws Exception
     */
    public function create(array $fields = []) {
        if (!empty($fields)) {
            $fields['promocode']  = $this->generate((float)$fields['rate'], (string)$fields['phone']);
            $fields['manager_id'] = (int)$_SESSION['manager_id'];

            if ($this->db->query('INSERT INTO __promocodes SET ?%', $fields)) {
                return $fields;
            } else {
                throw new Exception('Ошибка при сохранении промокода в базу данных.');
            }
        } else {
            throw new InvalidArgumentException('Данные для создания промокода не могут быть пустыми.');
        }
    }

    /**
     * Get all promocodes with activations and orders count.
     *
     * @return array
     */
    public function getListWithOrdersUsersCount(): array
    {
        $query = $this->db->placehold("
            SELECT s_promocodes.promocode, s_promocodes.date_start, s_promocodes.date_end, COUNT(s_orders.user_id) AS activation_count, 
            COUNT(s_orders.confirm_date) AS order_count FROM s_promocodes 
            LEFT JOIN s_orders ON s_orders.promocode = s_promocodes.id 
            GROUP BY `s_promocodes`.`promocode`
            ORDER BY promocode 
        ");
        $this->db->query($query);

        return $this->db->results() ?: [];
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

    /**
     * @param $order
     * @param $promocode
     * @return bool
     */
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
                $this->order_data->set($order->order_id, OrderData::ADDITIONAL_SERVICE_TV_MED, 1);
                $this->order_data->set($order->order_id, OrderData::ADDITIONAL_SERVICE_MULTIPOLIS, 1);
                $this->order_data->set($order->order_id, OrderData::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT, 1);
                $this->order_data->set($order->order_id, OrderData::ADDITIONAL_SERVICE_REPAYMENT, 1);
            }

            // Помечаем займ как обязательный к выдаче
            if ($promocode->is_mandatory_issue) {
                $this->order_data->set($order->order_id, 'MANDATORY_ISSUE', 1);
            }

            if ($order->first_loan && ($promocode->disable_additional_services || $promocode->is_mandatory_issue)) {
                return false;
            }

            $object = $this->soap->generateObject(
                [
                    'НомерЗаявки' => $order->id_1c,
                    'Ставка' => $order_fields['percent'],
                ]
            );
            $soap_result = $this->soap->requestSoap($object, 'WebOtvetZayavki', 'ChangeRate');

            if (!empty($soap_result['response'])) {
                $this->orders->update_order($order->order_id, $order_fields);
            }

            return true;
        }

        return false;
    }
}