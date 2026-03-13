<?php

require_once('Simpla.php');

class Organizations extends Simpla
{
    /** @var string boostra.ru */
    public const SITE_BOOSTRA = 'boostra';

    /** @var string soyaplace.ru */
    public const SITE_SOYA = 'soyaplace';

    /** @var string credit.neomani.ru */
    public const SITE_NEOMANI = 'neomani';

    public const BOOSTRA_ID = 1;
    public const AKVARIUS_ID = 6;
    public const AKADO_ID = 7;
    public const FINTEHMARKET_ID = 8;
    public const FINLAB_ID = 11;
    public const VIPZAIM_ID = 12;
    public const RZS_ID = 13;
    public const FORINT_ID = 14;
    public const LORD_ID = 15;
    public const MOREDENEG_ID = 17;
    public const FINVOLNA_ID = 18;
    public const FRIDA_ID = 20;
    public const FASTFINANCE_ID = 22;
    public const RUBL_ID = 21;

    private const LOG_FILE = 'organizations.txt';

    // Выбранная базовая организация согласно настройкам смены организации и site_id ['site_id' => organization_id]
    private array $selectedBaseOrganizationIdBySiteId = [];

    public function get_organizations_by_ids(array $ids)
    {
        $query = $this->db->placehold("SELECT id, short_name  FROM s_organizations where id IN (?@) ORDER BY id ASC", array_map('intval', $ids));

        $this->db->query($query);
        return $this->db->results();
    }

    public function get_base_organization($params = [])
    {
        return $this->get_organization($this->get_base_organization_id($params));
    }

    public function get_base_organization_id($params = []): int
    {
        $siteId = self::SITE_BOOSTRA;

        if (!empty($params['user_id'])) {
            $user = $this->users->get_user((int)$params['user_id']);
            if (!empty($user->site_id)) {
                $siteId = $user->site_id;
            }
        }

        $this->settings->setSiteId($siteId);

        // Не кешируем базовую организацию, чтобы у каждой автозаявки в кроне бралась своя
//        if (!empty($this->selectedBaseOrganizationIdBySiteId[$siteId])) {
//            return $this->selectedBaseOrganizationIdBySiteId[$siteId];
//        }

        try {
            $this->selectedBaseOrganizationIdBySiteId[$siteId] = $this->getBaseOrganizationAccordingToOrderOrgSwitchSettings($params);
        } catch (Throwable $e) {
            $error = [
                'Ошибка: ' . $e->getMessage(),
                'Файл: ' . $e->getFile(),
                'Строка: ' . $e->getLine(),
                'Подробности: ' . $e->getTraceAsString()
            ];
            $this->logging(__METHOD__, '', 'Ошибка при получении базовой организации, возвращаем базовую организацию из настроек', ['error' => $error], self::LOG_FILE);
            $this->selectedBaseOrganizationIdBySiteId[$siteId] =  (int)$this->settings->base_organization_id;
        }

        return $this->selectedBaseOrganizationIdBySiteId[$siteId];
    }

    /**
     * Organizations::get_organizations_for_issuance()
     * Возвращает массив организаций, по которым могут выдаваться займы
     *
     * @return array
     */
    public function get_organizations_for_issuance()
    {
        return [
            'base' => [
                self::RZS_ID,
                self::FRIDA_ID,
                self::FASTFINANCE_ID,
            ],
            'cross' => [
                self::LORD_ID,
                self::RZS_ID,
                self::FINVOLNA_ID,
                self::FRIDA_ID,
                self::FASTFINANCE_ID,
            ],
            'other' => [
                self::FORINT_ID,
                self::MOREDENEG_ID,
                self::RUBL_ID,
            ]
        ];
    }

    /**
     * Organizations::get_boostra_organizations_for_filter()
     * Возвращает список организаций для фильтра в листинге
     * @return array
     */
    public function get_boostra_organizations_for_filter()
    {
        return [
            self::AKVARIUS_ID,
            self::FINLAB_ID,
            self::RZS_ID,
            self::LORD_ID,
            self::FORINT_ID,
            self::FRIDA_ID,
            self::FASTFINANCE_ID,
        ];
    }

    /**
     * Organizations::get_soyaplace_organizations_for_filter()
     * Возвращает список организаций для фильтра в листинге
     * @return array
     */
    public function get_soyaplace_organizations_for_filter()
    {
        return [
            self::MOREDENEG_ID,
            self::FINVOLNA_ID,
        ];
    }

    /**
     * Проверяет, является ли организация активной для выдачи займов
     * @param int $organizationId
     * @return bool
     */
    public function isActiveOrganization(int $organizationId): bool
    {
        $activeOrganizations = $this->get_organizations_for_issuance();
        return in_array($organizationId, $activeOrganizations['base'])
            || in_array($organizationId, $activeOrganizations['cross'])
            || in_array($organizationId, $activeOrganizations['other']);
    }

    /**
     * Возвращает ссылку на сайт по его id
     * @param string $siteId
     * @return string Например https://boostra.ru
     */
    public function getSiteUrl(string $siteId): string
    {
        switch ($siteId) {
            case self::MOREDENEG_ID:
            case self::FINVOLNA_ID:
                return 'https://soyaplace.ru';

            case self::RUBL_ID:
                return 'https://rubl.ru';

            default:
                return $this->config->front_url;
        }
    }

    /**
     * Organizations::get_inn_for_recurrents()
     * Метод возвращает список ИНН организаций, по которым нужно списывать реккуренты
     * Также используется для проверки наличия выданных займов в Soap1c::DebtForFIO
     * @return array
     */
    public function get_inn_for_recurrents()
    {
        $organizations_map = [
            self::BOOSTRA_ID,
            self::AKVARIUS_ID,
            self::AKADO_ID,
            self::FINLAB_ID,
            self::RZS_ID,
            self::LORD_ID,
            self::MOREDENEG_ID,
            self::FINVOLNA_ID,
            self::FRIDA_ID,
            self::FASTFINANCE_ID,
            self::RUBL_ID,
        ];

        $inn = [];
        foreach ($this->getList() as $org) {
            if (in_array($org->id, $organizations_map)) {
                $inn[] = $org->inn;
            }
        }

        return $inn;
    }

    public function get_organization($id)
    {
        return $this->caches->wrap("organization:{$id}", 3600, function () use ($id) {
            $query = $this->db->placehold("
                SELECT * FROM __organizations WHERE id = ?
            ", (int)$id);
            $this->db->query($query);
            return $this->db->result();
        });
    }

    public function get_organization_id_by_inn($inn)
    {
        return $this->caches->wrap("organization_id_by_inn:{$inn}", 3600, function () use ($inn) {
            $this->db->query("
                SELECT id FROM s_organizations
                WHERE inn = ?
            ", (int)$inn);
            return $this->db->result('id');
        });
    }

    /**
     * Get list organizations
     *
     * @return array
     */
    public function getList(): array
    {

        $query = $this->db->placehold("SELECT * FROM s_organizations ORDER BY id ASC ");

        $this->db->query($query);
        $result = $this->db->results();

        return is_array($result) ? $result : [];

    }

    /**
     * Add new organization
     *
     * @param $post
     * @return void
     */
    public function addOrganizations($post): array
    {
        // string to array
        mb_parse_str($post, $dataForm);

        $query = $this->db->placehold("
            INSERT INTO __organizations SET ?%
        ", $dataForm);

        $this->db->query($query);


        return [
            'id' => $this->db->insert_id(),
            'data' => $dataForm
        ];
    }

    /**
     * Delete organization
     *
     * @param $id
     * @return void
     */
    public function delete($id)
    {
        $query = $this->db->placehold("
            DELETE FROM __organizations WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    /**
     * Update organization
     *
     * @param $id
     * @param $data
     * @return array
     */
    public function update($id, $data): array
    {
        $dataForm = [];

        foreach ($data as $item) {
            $dataForm[$item[0]["name"]] = $item[0]["value"];
        }

        $query = $this->db->placehold("
            UPDATE __organizations SET ?% WHERE id = ?
        ", (array)$dataForm, (int)$id);
        $this->db->query($query);
        return $dataForm;
    }

    public function isFinlab(int $organizationId): bool
    {
        return $organizationId === $this->organizations::FINLAB_ID;
    }

    /**
     * Вернуть массив ИНН по строковому site_id (напр. 'main').
     * Учитывает множественные связи в s_sites_organizations.
     *
     * @param string $site_id // например, 'boostra'
     * @return array            // ['123456789', '987654321', ...]
     */
    public function get_inns_by_site_id(string $site_id): array
    {
        return $this->caches->wrap("inns_by_site_id:{$site_id}", 3600, function () use ($site_id) {
            $query = $this->db->placehold("
                SELECT DISTINCT o.inn
                FROM s_sites_organizations so
                INNER JOIN s_organizations o ON o.id = so.organization_id
                WHERE so.site_id = ?
                  AND o.inn <> ''
            ", $site_id);

            $this->db->query($query);
            return $this->db->results('inn') ?: [];
        });
    }

    public function get_inns_by_order_1c_id($order_1c_id): array
    {
        return $this->caches->wrap("inns_by_order_1c_id:{$order_1c_id}", 3600, function () use ($order_1c_id) {
            $query = $this->db->placehold("
            SELECT DISTINCT o.inn
            FROM s_orders ord
            INNER JOIN __users u ON u.id = ord.user_id
            INNER JOIN s_sites_organizations so ON so.site_id = u.site_id
            INNER JOIN s_organizations o ON o.id = so.organization_id
            WHERE ord.`1c_id` = ?
              AND o.inn <> ''
        ", $order_1c_id);

            $this->db->query($query);
            return $this->db->results('inn') ?: [];
        });
    }

    public function get_inns_by_user_id($user_id): array
    {
        return $this->caches->wrap("inns_by_user_id:{$user_id}", 3600, function () use ($user_id) {
            $query = $this->db->placehold("
            SELECT DISTINCT o.inn
            FROM __users u
            INNER JOIN s_sites_organizations so ON so.site_id = u.site_id
            INNER JOIN s_organizations o ON o.id = so.organization_id
            WHERE u.id = ?
              AND o.inn <> ''
        ", $user_id);

            $this->db->query($query);
            return $this->db->results('inn') ?: [];
        });
    }

    public function get_site_organization($org_id)
    {
        return $this->caches->wrap("site_organization:{$org_id}", 3600, function () use ($org_id) {
            $query = $this->db->placehold("
            SELECT *
            FROM s_sites_organizations
            WHERE organization_id = ?
        ", $org_id);

            $this->db->query($query);
            return $this->db->result('site_id');
        });
    }


    /**
     * Получить site_id по номеру горячей линии
     *
     * @param string $phone Номер телефона горячей линии (phone_a из Voximplant)
     * @return string|null site_id или null если не найдено
     */
    public function getSiteIdByHotlinePhone(string $phone): ?string
    {
        $phonePrepared = formatPhoneNumber($phone);

        if ($phonePrepared === false) {
            return null;
        }

        // Сравниваем последние 10 цифр (без кода страны 7/8)
        $last10Digits = substr($phonePrepared, -10);

        $query = $this->db->placehold("
            SELECT so.site_id
            FROM s_organizations o
            INNER JOIN s_sites_organizations so ON so.organization_id = o.id
            WHERE RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(o.phone, ' ', ''), '-', ''), '+', ''), '(', ''), ')', ''), 10) = ?
            LIMIT 1
        ", $last10Digits);

        $this->db->query($query);
        $result = $this->db->result('site_id');

        return $result ?: null;
    }

    private function getBaseOrganizationAccordingToOrderOrgSwitchSettings(array $params): int
    {
        $settings = $this->settings->organization_switch;
        $this->logging(__METHOD__, '', 'На получение базовой организации взята заявка', [], self::LOG_FILE);

        // 1. Если ручеек выключен, то возвращаем settings->base_organization_id
        if (
            empty($settings['enabled']) ||
            empty($settings['auto_base_organization_switch']['enabled']) ||
            empty($settings['auto_base_organization_switch']['organization_1']['organization_id']) ||
            empty($settings['auto_base_organization_switch']['organization_2']['organization_id'])
        ) {
            $this->logging(__METHOD__, '', 'Автоматическое переключение базовой организации отключено, возвращаем базовую организацию из настроек', [], self::LOG_FILE);
            return (int)$this->settings->base_organization_id;
        }

        $organization1 = $settings['auto_base_organization_switch']['organization_1'];
        $organization2 = $settings['auto_base_organization_switch']['organization_2'];

        $organizationId1 = (int)$organization1['organization_id'];
        $organizationId2 = (int)$organization2['organization_id'];

        $maxIssuanceAmountInOrganization1 = (int)$organization1['max_issuance_amount'];
        $maxIssuanceAmountInOrganization2 = (int)$organization2['max_issuance_amount'];

        $minBalanceInOrganization1 = (int)$organization1['min_balance'];
        $minBalanceInOrganization2 = (int)$organization2['min_balance'];

        // 2. Возвращаем организацию открытого ВКЛ (если есть)
        if (!empty($params['order_id'])) {
            $openRclOrganizationId = $this->checkOpenRclOrganizationId((int)$params['order_id']);
            if (!empty($openRclOrganizationId)) {
                $this->logging(__METHOD__, '', 'Получена организация согласно открытому ВКЛ', ['order_id' => $params['order_id'], 'organization_id' => $openRclOrganizationId], self::LOG_FILE);
                return $openRclOrganizationId;
            }
        }

        // 3. Проверяем, запрашивали ли у клиента недавно ССП/КИ отчеты
        if (!empty($params['check_last_report_date']) && !empty($params['order_id'])) {
            $this->logging(__METHOD__, '', 'Проверяем недавние запросы отчетов по заявке', ['order_id' => $params['order_id']], self::LOG_FILE);

            $hasRecentlyInquiredReportsInOrganization1 = $this->axi->checkHasRecentlyInquiredReports((int)$params['order_id'], $organizationId1);
            $hasRecentlyInquiredReportsInOrganization2 = $this->axi->checkHasRecentlyInquiredReports((int)$params['order_id'], $organizationId2);

            $this->logging(__METHOD__, '', 'Запросы отчетов по заявке', [
                'order_id' => $params['order_id'],
                'organization_id_1' => $organizationId1,
                'organization_id_2' => $organizationId2,
                'has_recently_inquired_reports_in_organization_1' => $hasRecentlyInquiredReportsInOrganization1,
                'has_recently_inquired_reports_in_organization_2' => $hasRecentlyInquiredReportsInOrganization2,
            ], self::LOG_FILE);

            // Если недавно запросили отчет в первой организации, то ставим ее базовой, чтобы с большой вероятностью в ручейке OrderOrgSwitch.php поменяли на вторую организацию и смогли выдать без КИ
            if ($hasRecentlyInquiredReportsInOrganization1 && !$hasRecentlyInquiredReportsInOrganization2) {
                $this->logging(__METHOD__, '', 'Был запрошен отчет в Организация 1, возвращаем первую', ['order_id' => $params['order_id']], self::LOG_FILE);
                return $organizationId1;
            }

            // Если недавно запросили отчет во второй организации, то ставим ее базовой, чтобы с большой вероятностью в ручейке OrderOrgSwitch.php поменяли на первую организацию и смогли выдать без КИ
            if (!$hasRecentlyInquiredReportsInOrganization1 && $hasRecentlyInquiredReportsInOrganization2) {
                $this->logging(__METHOD__, '', 'Был запрошен отчет в Организация 2, возвращаем вторую', ['order_id' => $params['order_id']], self::LOG_FILE);
                return $organizationId2;
            }
        }

        // 4. Проверяем можно ли выплатить на данную организацию
        $canIssuanceToOrganization1 = $this->checkCanIssuanceToOrganization($organizationId1, $maxIssuanceAmountInOrganization1, $minBalanceInOrganization1);
        $canIssuanceToOrganization2 = $this->checkCanIssuanceToOrganization($organizationId2, $maxIssuanceAmountInOrganization2, $minBalanceInOrganization2);

        $this->logging(__METHOD__, '', 'Дневной лимит для организаций', [
            'organization_id_1' => $organizationId1,
            'organization_id_2' => $organizationId2,
            'max_issuance_amount_in_organization_1' => $maxIssuanceAmountInOrganization1,
            'max_issuance_amount_in_organization_2' => $maxIssuanceAmountInOrganization2,
            'min_balance_in_organization_1' => $minBalanceInOrganization1,
            'min_balance_in_organization_2' => $minBalanceInOrganization2,
        ], self::LOG_FILE);

        // 5. Если в обе организации нельзя выплатить, то возвращаем settings->base_organization_id
        if (!$canIssuanceToOrganization1 && !$canIssuanceToOrganization2) {
            $this->logging(__METHOD__, '', 'Обе организации превысили дневной лимит, возвращаем базовую организацию из настроек', [], self::LOG_FILE);
            return (int)$this->settings->base_organization_id;
        }

        // 6. Если первая организация превысила лимит, то ставим ее базовой, чтобы основной поток пошел в ручейке OrderOrgSwitch.php на вторую организацию
        if (!$canIssuanceToOrganization1 && $canIssuanceToOrganization2) {
            $this->logging(__METHOD__, '', 'Организация 1 превысила лимит, возвращаем первую', [], self::LOG_FILE);
            return $organizationId1;
        }

        // 7. Если вторая организация превысила лимит, то ставим ее базовой, чтобы основной поток пошел в ручейке OrderOrgSwitch.php на первую организацию
        if ($canIssuanceToOrganization1 && !$canIssuanceToOrganization2) {
            $this->logging(__METHOD__, '', 'Организация 2 превысила лимит, возвращаем вторую', [], self::LOG_FILE);
            return $organizationId2;
        }

        $chance1 = (int)$organization1['chance'];
        $chance2 = (int)$organization2['chance'];
        $random = mt_rand(1, 100);

        $this->logging(__METHOD__, '', 'Проверяем шанс', [
            'organization_id_1' => $organizationId1,
            'organization_id_2' => $organizationId2,
            'chance_1' => $chance1,
            'chance_2' => $chance2,
            'random' => $random,
        ], self::LOG_FILE);

        // 8. Обе организации не превысили лимит, выбираем по шансу
        if ($random <= $chance1) {
            $this->logging(__METHOD__, '', 'По шансу выбрана организация 1', [], self::LOG_FILE);
            return $organizationId1;
        }

        $this->logging(__METHOD__, '', 'По шансу выбрана организация 2', [], self::LOG_FILE);
        return $organizationId2;
    }

    private function checkOpenRclOrganizationId(int $orderId): ?int
    {
        $order = $this->orders->get_order($orderId);
        if (empty($order)) {
            $this->logging(__METHOD__, '', 'Заявка не найдена для проверки на открытый ВКЛ', ['order_id' => $orderId], self::LOG_FILE);
            return null;
        }

        $rclContract = $this->rcl->get_contract([
            'user_id' => (int)$order->user_id,
            'status' => $this->rcl::STATUS_APPROVED,
            'organization_id' => [$this->organizations::RZS_ID, $this->organizations::FRIDA_ID],
            'date_start' => [
                'to' => date('Y-m-d'),
            ],
            'date_end' => [
                'from' => date('Y-m-d'),
            ],
        ]);

        if (!empty($rclContract)) {
            return (int)$rclContract->organization_id;
        }

        return null;
    }

    private function checkCanIssuanceToOrganization(int $organizationId, int $maxIssuanceAmountInOrganization, int $minBalanceInOrganization): bool
    {
        // 1. Если дневной лимит в данной МКК исчерпан
        if (!empty($maxIssuanceAmountInOrganization)) {
            $dateStart = date('Y-m-d 00:00:00');
            $dateEnd = date('Y-m-d H:i:s');

            $currentIssuanceAmountInOrganization = $this->contracts->getIssuanceAmountForPeriod($dateStart, $dateEnd, $organizationId);

            $this->logging(__METHOD__, '', 'Текущая сумма выдачи в организации', ['organization_id' => $organizationId, 'current_issuance_amount_in_organization' => $currentIssuanceAmountInOrganization], self::LOG_FILE);

            if ($currentIssuanceAmountInOrganization > $maxIssuanceAmountInOrganization) {
                return false;
            }
        }

        // 2. Если в МКК осталось баланса меньше минимально допустимого
        if (!empty($minBalanceInOrganization)) {
            try {
                $currentBalanceInOrganization = $this->getBalance($organizationId);
            } catch (RuntimeException $error) {
                $this->logging(__METHOD__, '', 'Ошибка при получении баланса в организации', ['organization_id' => $organizationId, 'error' => $error], self::LOG_FILE);
                return false;
            }

            if ((int)$currentBalanceInOrganization < $minBalanceInOrganization) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $organizationId
     * @return int|null
     * @throws RuntimeException
     */
    public function getBalance(int $organizationId): ?int
    {
        switch ($organizationId) {
            case self::RZS_ID:
                $sector = 'RZS_PAY_CREDIT';
                break;
            case self::FRIDA_ID:
                $sector = 'FRIDA_PAY_CREDIT';
                break;
            case self::FASTFINANCE_ID:
                $sector = 'FASTFINANCE_CREDIT';
                break;
            default:
                $this->logging(__METHOD__, '', 'Неподходящий organization_id', ['organization_id' => $organizationId], self::LOG_FILE);
                throw new RuntimeException('Неподходящий organization_id ' . $organizationId);
        }

        $currentBalanceInOrganization = $this->caches->wrap("organization:balance:{$organizationId}", 300, function () use ($sector) {
            $response_b2p = $this->best2pay->getBalance($sector);
            $xml = simplexml_load_string($response_b2p);

            if (empty($xml) || !isset($xml->amount)) {
                $this->logging(__METHOD__, '', 'Не удалось получить баланс', ['sector' => $sector, '$xml' => $xml], self::LOG_FILE);

                // Выбрасываем исключение, чтобы не закешировалось некорректное значение
                throw new RuntimeException('Не удалось получить баланс: ' . $sector);
            }

            $this->logging(__METHOD__, '', 'Баланс в организации в копейках', ['sector' => $sector, '$xml->amount' => $xml->amount], self::LOG_FILE);

            // Баланс в рублях
            return (int)((int)$xml->amount / 100);
        });

        $this->logging(__METHOD__, '', 'Баланс в организации', ['organization_id' => $organizationId, 'current_balance_in_organization' => $currentBalanceInOrganization], self::LOG_FILE);

        return $currentBalanceInOrganization;
    }
}