<?php

require_once('Simpla.php');
require_once('Scorings.php');

use App\Enums\OrderOrgSwitch as OrderOrgSwitchEnum;
use GuzzleHttp\Client;

class OrderOrgSwitch extends Simpla
{
    private const LOG_FILE = 'order_organization_switch.txt';

    /** @var int Минимальный баланс на Фриде для выдачи займа на Фриде по ручейку 3 */
    private const MIN_FRIDA_BALANCE_FOR_RIVER_THREE_TO_ISSUANCE_ORDER = 1_500_000; // 1.5 млн

    /** @var int Минимальный баланс на МКК для выдачи займа на этом МКК по ручейку 5 */
    private const MIN_BALANCE_FOR_RIVER_FIVE_TO_ISSUANCE_ORDER = 30000;

    /** @var int Минимальная рекомендуемая сумма скористы для выдачи ВКЛ в данной МКК */
    private const MIN_SCORISTA_AMOUNT_TO_ISSUANCE_RCL = 25000;

    private const MAX_ATTEMPTS_TO_SEND_ORDER_TO_1C = 3;

    private const MAX_PDN_TO_CREATE_RCL = 50.0;

    private const REASON_MESSAGES = [
        OrderOrgSwitchEnum::REASON_SETTING_DISABLED => 'Настройка отключена',
        OrderOrgSwitchEnum::REASON_NOT_FIRST_LOAN => 'Не первая заявка НК',
        OrderOrgSwitchEnum::REASON_CROSS_ORDER => 'Кросс-заявка',
        OrderOrgSwitchEnum::REASON_INAPPROPRIATE_STATUS => 'Неподходящий статус заявки',
        OrderOrgSwitchEnum::REASON_INAPPROPRIATE_ORGANIZATION => 'Неподходящая организация у заявки',
        OrderOrgSwitchEnum::REASON_PING3_ORDER => 'Заявка по пинг3',
        OrderOrgSwitchEnum::REASON_ORDER_AMOUNT_EXCEEDED => 'Одобренная сумма выше допустимой',
        OrderOrgSwitchEnum::REASON_ALREADY_SWITCHED => 'Заявка уже переключалась',
        OrderOrgSwitchEnum::REASON_NOT_TEST_USER => 'Пользователь не тестовый',
        OrderOrgSwitchEnum::REASON_NO_SUCCESS_AXILINK => 'Нет успешного акси',
        OrderOrgSwitchEnum::REASON_FAILED_UPRID => 'Нет успешного УПРИД',
        OrderOrgSwitchEnum::REASON_NOT_CHANCE => 'Не выпал шанс переключения',
        OrderOrgSwitchEnum::REASON_DAY_LIMIT_EXCEEDED => 'Исчерпан дневной лимит переключений',
        OrderOrgSwitchEnum::REASON_NO_PDN => 'Нет результата расчета ПДН',
        OrderOrgSwitchEnum::REASON_INCORRECT_PDN => 'Некорректный результат расчета ПДН',
        OrderOrgSwitchEnum::REASON_NEW_TERRITORY_PDN => 'ПДН с переселением на новые территории для Фриды',
        OrderOrgSwitchEnum::REASON_PDN_EXCEEDED => 'ПДН выше установленного максимума',
        OrderOrgSwitchEnum::REASON_CREATE_ORDER_FAILED => 'Не удалось создать заявку',
        OrderOrgSwitchEnum::REASON_PREVIOUSLY_REJECTED_BY_CHANCE => 'По заявке ранее не выпал шанс',
        OrderOrgSwitchEnum::REASON_UNKNOWN_ERROR => 'Фатальная ошибка',
        OrderOrgSwitchEnum::REASON_SUCCESS_WITH_ORGANIZATION_SWITCH => 'Успешное прохождение ручейка со сменой организации',
        OrderOrgSwitchEnum::REASON_SUCCESS_WITHOUT_ORGANIZATION_SWITCH => 'Успешное прохождение ручейка без смены организации',
        OrderOrgSwitchEnum::REASON_ONLY_MAIN_ORGANIZATION_IN_MPL => 'Только одна (исходная) организация входит в МПЛ',
        OrderOrgSwitchEnum::REASON_ORDER_NOT_IN_ANY_MPL => 'Заявка не попала ни в какой МПЛ',
    ];

    /**
     * Ключи из s_order_data, которые НЕ нужно скопировать в новую заявку
     */
    private const ORDER_DATA_SKIP_COPY = [
        'is_sold_to_bonon',
        'order_org_switch_result',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->initDefaultSettings();
    }

    private function initDefaultSettings()
    {
        $settings = $this->settings->organization_switch;
        if (!empty($settings)) {
            return;
        }

        $settings = [
            // Настройки из /site_settings

            // Включен ли переключатель организаций? 0/1
            'enabled' => 0,
            // Только для тестового пользователя? 0/1
            'test_user_only' => 1,
            // Шанс переключения организации (в процентах, от 0 до 100)
            'chance' => 0,
            // Максимальное количество переключаемых в день заявок (0 - без ограничений)
            'max_limit' => 0,
            // id организации, на которую будут переключаться заявки
            'new_organization_id' => 0,
            // Максимальный ПДН для переключения организации
            'max_pdn' => 0,
            // Номер включенного ручейка
            'enabled_river' => 0,
            'auto_base_organization_switch' => [
                'enabled' => 0,
                'organization_1' => [
                    'organization_id' => 0,
                    'chance' => 0,
                    'max_issuance_amount' => 0,
                    'min_balance' => 0,
                ],
                'organization_2' => [
                    'organization_id' => 0,
                    'chance' => 0,
                    'max_issuance_amount' => 0,
                    'min_balance' => 0,
                ]
            ],
            'utm_sources' => []
        ];
        $this->settings->organization_switch = $settings;
    }

    /**
     * Попытка переключить организацию заявки
     *
     * @param int $order_id ID заявки
     * @return bool true - переключение организации выполнено, false - переключение организации не произошло
     */
    public function trySwitchOrganization(int $order_id): bool
    {
        try {
            $isOrderOrgSwitched = $this->run($order_id);
        } catch (Throwable $error) {
            $this->saveErrorOrderOrgSwitch($order_id, OrderOrgSwitchEnum::REASON_UNKNOWN_ERROR, ['error' => $error]);
            return false;
        }

        return $isOrderOrgSwitched;
    }

    private function run(int $order_id): bool
    {
        $order = $this->orders->get_order($order_id);
        if (empty($order)) {
            $this->logging(__METHOD__, '', 'Заявка не найдена', ['order' => $order], self::LOG_FILE);
            return false;
        }

        $user = $this->users->get_user($order->user_id);
        if (empty($user)) {
            $this->logging(__METHOD__, '', 'Пользователь не найден', ['order' => $order, 'user' => $user], self::LOG_FILE);
            return false;
        }

        $this->settings->setSiteId($user->site_id);
        $settings = $this->settings->organization_switch;

        // Если ручеек выключен
        if (empty($settings['enabled'])) {
            $this->logging(__METHOD__, '', 'Ручеек отключен', ['order_id' => $order->order_id], self::LOG_FILE);
            return false;
        }

        if ($settings['enabled_river'] === '4') {
            return $this->runRiverFour($order);
        } else if ($settings['enabled_river'] === '5') {
            return $this->runRiverFive($order);
        }

        $this->logging(__METHOD__, '', 'Ошибка! Некорректный ручеек', ['order' => $order], self::LOG_FILE);
        return false;
    }

    private function saveErrorOrderOrgSwitch(int $orderId, string $reason, array $logContext = []): void
    {
        $message = self::REASON_MESSAGES[$reason] ?? 'Неизвестная ошибка';
        $this->order_data->set($orderId, $this->order_data::ORDER_ORG_SWITCH_RESULT, $reason);
        $this->logging(__METHOD__, '', $message, array_merge(['order_id' => $orderId], $logContext), self::LOG_FILE);
    }

    /**
     * Создание новой заявки в другой организации
     * @return int id новой заявки или 0 при ошибке
     */
    private function createNewOrder(stdClass $oldOrder, ?int $newOrganizationId = null): int
    {
        $order = $this->orders->get_order_short($oldOrder->order_id);

        if (empty($order)) {
            $this->logging(__METHOD__, '', 'Не удалось получить заявку для создания новой', [
                'old_order' => $oldOrder,
                '$order' => $order
            ], self::LOG_FILE);
            return 0;
        }

        $settings = $this->settings->organization_switch;
        $newOrder = (array)$order;

        unset($newOrder['id']);
        unset($newOrder['reason_id']);
        unset($newOrder['first_loan']);
        unset($newOrder['approve_amount']);

        $newOrganizationIdForOrder = !empty($newOrganizationId) ? $newOrganizationId : $settings['new_organization_id'];

        if ((int)$oldOrder->organization_id === (int)$newOrganizationIdForOrder) {
            $this->logging(__METHOD__, '', 'Новая заявка не создана, т.к. исходная организация и новая совпадают', [
                'old_order' => $oldOrder,
                'new_organization_id' => $newOrganizationId,
                'new_organization_id_for_order' => $newOrganizationIdForOrder,
                '$settings["new_organization_id"]' => $settings['new_organization_id']
            ], self::LOG_FILE);
            return 0;
        }

        // Статус заявки, utm метки и прочие параметры остаются из исходной заявки!
        $newOrder['organization_id'] = $newOrganizationIdForOrder;
        $newOrder['date'] = date('Y-m-d H:i:s');
        $newOrder['utm_content'] = $this->orders::UTM_SOURCE_ORGANIZATION_SWITCH;
        $newOrder['order_uid'] = exec($this->config->root_dir . 'generic/uidgen');

        $soap_zayavka = $this->addNewOrderIn1C($newOrder);
        if (empty($soap_zayavka->return->id_zayavka) || mb_strpos($soap_zayavka->return->id_zayavka, 'Не принято') !== false) {
            $this->logging(__METHOD__, '', 'Не удалось создать заявку в 1C', [
                'order_id' => (int)$oldOrder->order_id,
                'new_order' => $newOrder,
                'soap_zayavka' => $soap_zayavka
            ], self::LOG_FILE);

            return 0;
        }

        $newOrder['1c_id'] = $soap_zayavka->return->id_zayavka;
        $newOrderId = $this->orders->add_order($newOrder);

        if (empty($newOrderId)) {
            $this->logging(__METHOD__, '', 'Не удалось создать заявку в БД', [
                'order_id' => (int)$oldOrder->order_id,
                'new_order' => $newOrder,
                'new_order_id' => $newOrderId,
            ], self::LOG_FILE);

            return 0;
        }

        $this->logging(__METHOD__, '', 'Создана заявка в БД', ['old_order_id' => (int)$oldOrder->order_id, 'new_order_id' => $newOrderId], self::LOG_FILE);

        // Если автозаявка, то добавляем ее в s_orders_auto_approve, чтобы потом cron/validate_auto_approve_orders.php
        // на основании одобренной суммы аксиЛИНК подставил одобренную сумму в заявку
        if ($newOrder['utm_source'] === $this->orders::UTM_SOURCE_CRM_AUTO_APPROVE) {
            $this->orders_auto_approve->saveRecordToAutoApproveTable((int)$newOrder['user_id'], (int)$newOrderId);
        }
        // Если заявка одобрена, то одобряем в 1C
        else if ($newOrder['status'] === $this->orders::ORDER_STATUS_CRM_APPROVED) {
            $this->updateOrderStatusIn1C($newOrder, (int)$newOrder['amount']);
        }

        $this->copyOrderData((int)$oldOrder->order_id, $newOrderId);

        // В новой заявке сохраняем order_id исходной заявки
        $this->order_data->set((int)$newOrderId, $this->order_data::ORDER_ORG_SWITCH_PARENT_ORDER_ID, (int)$oldOrder->order_id);

        $this->logging(__METHOD__, '', 'Скопировали s_order_data', ['old_order_id' => (int)$oldOrder->order_id, 'new_order_id' => $newOrderId], self::LOG_FILE);

        return $newOrderId;
    }

    private function copyOrderData(int $oldOrderId, int $newOrderId)
    {
        $oldData = $this->order_data->readAll($oldOrderId);
        foreach ($oldData as $key => $val) {
            if (in_array($key, self::ORDER_DATA_SKIP_COPY)) {
                continue;
            }

            $this->order_data->set($newOrderId, $key, $val);
        }
    }

    /**
     * Отправка новой заявки в 1С
     * @param array $newOrder
     * @return Exception|stdClass|SoapFault
     */
    private function addNewOrderIn1C(array $newOrder)
    {
        $soap_zayavka_params = [
            'organization_id' => $newOrder['organization_id'],
            'order_uid' => $newOrder['order_uid'],
            'utm_source' => $newOrder['utm_source'],
            'utm_medium' => $newOrder['utm_medium'],
        ];

        $soap_zayavka = new stdClass();

        // Пробуем несколько раз создать заявку с задержкой на случай, если 1C недоступен
        for ($i = 0; $i < self::MAX_ATTEMPTS_TO_SEND_ORDER_TO_1C; $i++) {

            $this->logging(__METHOD__, '', 'Начата попытка создания заявки в 1C', ['new_order_uid' => $newOrder['order_uid'], '$i' => $i], self::LOG_FILE);

            $soap_zayavka = $this->soap->soap_repeat_zayavka(
                $newOrder['amount'],
                $newOrder['period'],
                $newOrder['user_id'],
                $newOrder['card_id'],
                NULL,
                $soap_zayavka_params
            );

            $this->logging(__METHOD__, '', 'Завершена попытка создания заявки в 1C', ['new_order_uid' => $newOrder['order_uid'], 'soap_zayavka' => $soap_zayavka, '$i' => $i], self::LOG_FILE);

            if (!empty($soap_zayavka->return->id_zayavka) && mb_strpos($soap_zayavka->return->id_zayavka, 'Не принято') === false) {
                return $soap_zayavka;
            }

            sleep(3);
        }

        return $soap_zayavka;
    }

    private function updateOrderStatusIn1C(array $newOrder, int $approveAmount): string
    {
        $updateStatus1CResult = '';

        $manager = $this->managers->get_manager($newOrder['manager_id']);

        for ($i = 0; $i < self::MAX_ATTEMPTS_TO_SEND_ORDER_TO_1C; $i++) {
            $updateStatus1CResult = $this->soap->update_status_1c(
                $newOrder['1c_id'],
                'Одобрено',
                $manager->name_1c ?? '',
                $approveAmount,
                1,
                '',
                0,
                $newOrder['period']
            );

            if ($updateStatus1CResult === 'OK') {
                return $updateStatus1CResult;
            }

            sleep(3);
        }

        return $updateStatus1CResult;
    }

    /**
     * Добавление скорингов для новой заявки
     * @param int $order_id
     * @return void
     */
    private function addScorings(int $order_id)
    {
        $order = $this->orders->get_order($order_id);

        $this->scorings->add_scoring([
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'type' => $this->axi->getAxiScoringType($order),
            'status' => $this->scorings::STATUS_NEW,
        ]);

        $this->scorings->add_scoring([
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'type' => $this->scorings::TYPE_UPRID,
            'status' => $this->scorings::STATUS_NEW,
        ]);
    }

    private function changeCrossOrderOwner(int $oldOrderId, int $userId, int $newOrderId)
    {
        $this->db->query("
            UPDATE s_orders
            SET utm_medium = ?
            WHERE user_id = ?
              AND utm_source = 'cross_order'
              AND `status` NOT IN (3, 10, 11)
              AND utm_medium = ?
            LIMIT 1
        ", $newOrderId, $userId, $oldOrderId);
    }

    /**
     * @return false|stdClass
     */
    private function calculatePdn(stdClass $order, array $flags)
    {
        $this->logging(__METHOD__, '', 'Начат расчет ПДН до выдачи', ['order_id' => $order->order_id, 'flags' => $flags], self::LOG_FILE);

        // Расчет ПДН до выдачи
        $pdnCalculationResult = $this->pdnCalculation->run($order->order_uid, $flags);
        $this->logging(__METHOD__, '', 'Завершен расчет ПДН до выдачи', ['order_id' => $order->order_id, 'flags' => $flags, 'pdn_calculation_result' => $pdnCalculationResult], self::LOG_FILE);

        // Если ПДН некорректный, то просто логируем
        // В этом случае просто считаем, что ПДН в МПЛ НЕ входит
        if (empty($pdnCalculationResult) || !isset($pdnCalculationResult->pti_percent)) {
            $this->logging(__METHOD__, '', self::REASON_MESSAGES[OrderOrgSwitchEnum::REASON_INCORRECT_PDN], ['order_id' => $order->order_id, 'pdn_calculation_result' => $pdnCalculationResult], self::LOG_FILE);
            return false;
        }

        // Проверка, чтобы у Фриды не было расчетов ПДН с переселением на новые территории
        // В этом случае просто считаем, что ПДН в МПЛ НЕ входит
        if ($pdnCalculationResult->calculation_type === 'ПДН для новых территорий' && !empty($pdnCalculationResult->new_region_id)) {
            $organizationId = !empty($flags['forced_organization_id_for_pdn_calculation'])
                ? (int)$flags['forced_organization_id_for_pdn_calculation']
                : (int)$order->organization_id;

            if ($organizationId === $this->organizations::FRIDA_ID) {
                $this->logging(__METHOD__, '', self::REASON_MESSAGES[OrderOrgSwitchEnum::REASON_NEW_TERRITORY_PDN], ['order_id' => $order->order_id, 'pdn_calculation_result' => $pdnCalculationResult], self::LOG_FILE);
                return false;
            }
        }

        return $pdnCalculationResult;
    }

    private function runRiverTwoForCrossOrders(stdClass $order): bool
    {
        // Временно отключено - в акси сейчас не предусмотрены расчёт ПДН, он считается уже после выдачи. Будет дорабатываться внутри акси в январе 2026
        $this->order_data->set((int)$order->order_id, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS, 1);

        return false;
    }

    private function trySwitchOrganizationWithCheckDocuments(stdClass $order, int $newOrganizationId, int $riverNumber, int $scenarioNumber, bool $axiWithoutCreditReports = false): int
    {
        // Сценарий 1: (если заявка изначально создавалась на целевую организацию, то не меняем организацию)
        if ((int)$order->organization_id === $newOrganizationId) {
            $this->logging(__METHOD__, '', 'Запущен сценарий ' . $scenarioNumber . ' без смены организации', ['order_id' => (int)$order->order_id], self::LOG_FILE);

            $this->order_data->set((int)$order->order_id, $this->order_data::ORDER_ORG_SWITCH_RESULT, OrderOrgSwitchEnum::REASON_SUCCESS_WITHOUT_ORGANIZATION_SWITCH);

            $this->logging(__METHOD__, '', self::REASON_MESSAGES[OrderOrgSwitchEnum::REASON_SUCCESS_WITHOUT_ORGANIZATION_SWITCH], ['order_id' => (int)$order->order_id], self::LOG_FILE);

            return 0;
        }

        // Если заявка изначально создавалась не на целевую организацию, то меняем организацию
        $this->logging(__METHOD__, '', 'Запущен сценарий ' . $scenarioNumber . ' со сменой организации', ['order_id' => (int)$order->order_id], self::LOG_FILE);
        $newOrderId = $this->switchOrganization($order, $newOrganizationId, $riverNumber, $scenarioNumber, $axiWithoutCreditReports);

        // Если успешно создали новую заявку И клиент открывал договор индивидуальных условий
        if (!empty($newOrderId) && $this->didUserOpenDocuments($order, $newOrderId)) {
            $this->order_data->set($newOrderId, $this->order_data::AUTOCONFIRM_ASP, null);
        }

        return $newOrderId;
    }

    /** Открывал ли клиент Договор индивидуальных условий */
    private function didUserOpenDocuments(stdClass $order, int $newOrderId): bool
    {
        $did_user_open_ind_usloviya_document = $this->user_data->read((int)$order->user_id, $this->user_data::DID_USER_OPEN_IND_USLOVIYA_DOCUMENT);

        $this->logging(__METHOD__, '', 'Просмотр договора индивидуальных условий', ['order_id' => (int)$order->order_id, 'did_user_open_ind_usloviya_document' => $did_user_open_ind_usloviya_document], self::LOG_FILE);

        if (!empty($did_user_open_ind_usloviya_document)) {
            // Удаляем флаг в s_user_data
            $this->user_data->set((int)$order->user_id, $this->user_data::DID_USER_OPEN_IND_USLOVIYA_DOCUMENT);

            // Добавляем флаг в s_order_data
            $this->order_data->set($newOrderId, $this->order_data::DID_USER_OPEN_IND_USLOVIYA_DOCUMENT, $did_user_open_ind_usloviya_document);

            return true;
        }

        return false;
    }

    /**
     * Расчет ПДН перед выдачей без проверки проводился ли ранее расчет ПДН
     *
     * @param stdClass $order
     * @return false|stdClass
     */
    public function calculatePdnBeforeIssuance(stdClass $order)
    {
        $flags = [
            $this->pdnCalculation::CALCULATE_PDN_BEFORE_ISSUANCE => 1,
        ];

        $pdnCalculationResult = $this->calculatePdn($order, $flags);
        if ($pdnCalculationResult === false) {
            return false;
        }

        return $pdnCalculationResult;
    }

    /**
     * Финальный расчет ПДН перед выдачей
     *
     * @param stdClass $order
     * @return false|stdClass
     */
    public function calculateFinalPdnBeforeIssuance(stdClass $order)
    {
        $flags = [
            $this->pdnCalculation::CALCULATE_PDN_BEFORE_ISSUANCE => 1,
            $this->pdnCalculation::FINAL_PDN_BEFORE_ISSUANCE => 1,
        ];

        $pdnCalculationResult = $this->calculatePdn($order, $flags);
        if ($pdnCalculationResult === false) {
            return false;
        }

        return $pdnCalculationResult;
    }

    /**
     * Расчет ПДН с принудительной установкой organization_id
     *
     * @param stdClass $order
     * @param int $organizationId
     * @return false|stdClass
     */
    private function calculatePdnWithOrganizationId(stdClass $order, int $organizationId)
    {
        $flags = [
            $this->pdnCalculation::CALCULATE_PDN_BEFORE_ISSUANCE => 1,
            $this->pdnCalculation::FORCED_ORGANIZATION_ID_FOR_PDN_CALCULATION => $organizationId
        ];

        $pdnCalculationResult = $this->calculatePdn($order, $flags);
        if ($pdnCalculationResult === false) {
            return false;
        }

        return $pdnCalculationResult;
    }

    private function switchOrganization(stdClass $order, ?int $newOrganizationId, int $riverNumber, int $scenarioNumber = 0, bool $axiWithoutCreditReports = false): int
    {
        // 1. Заново получаем заявку, т.к. статус мог измениться
        $order = $this->orders->get_order((int)$order->order_id);

        if ($this->checkIsOrderClosed($order)) {
            $this->logging(__METHOD__, '', 'Старая заявка уже в неподходящем статусе перед попыткой создать новую заявку', ['order' => $order], self::LOG_FILE);

            $this->saveErrorOrderOrgSwitch((int)$order->order_id, OrderOrgSwitchEnum::REASON_INAPPROPRIATE_STATUS);
            return 0;
        }

        // 2. Создаем новую заявку в другой организации
        $newOrderId = $this->createNewOrder($order, $newOrganizationId);

        // Если не смогли создать новую заявку (отвалилась 1C или в БД не смогли добавить новую заявку), то отказываем по текущей заявке
        if (empty($newOrderId)) {
            $this->orders->rejectOrder($order, $this->reasons::REASON_SWITCH_ORGANIZATION);
            $this->saveErrorOrderOrgSwitch((int)$order->order_id, OrderOrgSwitchEnum::REASON_CREATE_ORDER_FAILED);

            return 0;
        }

        // 3. Меняем utm_medium кросс ордера (id родительской заявки на id новой созданной заявки)
        $this->changeCrossOrderOwner((int)$order->order_id, (int)$order->user_id, $newOrderId);
        $this->logging(__METHOD__, '', 'Поменяли utm_medium кросс-ордера', ['old_order_id' => (int)$order->order_id, 'new_order_id' => $newOrderId], self::LOG_FILE);

        // 4. Отказываем по старой заявке
        $this->orders->rejectOrder($order, $this->reasons::REASON_SWITCH_ORGANIZATION);
        $this->logging(__METHOD__, '', 'Отказали по старой заявке', ['old_order_id' => (int)$order->order_id, 'new_order_id' => $newOrderId], self::LOG_FILE);

        if (!empty($axiWithoutCreditReports)) {
            // 5. Добавляем флаг, чтобы в акси не запрашивались ССП и КИ отчеты
            $this->order_data->set($newOrderId, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS, 1);
            $this->logging(__METHOD__, '', 'Установили AXI_WITHOUT_CREDIT_REPORTS новой заявке', ['old_order_id' => (int)$order->order_id, 'new_order_id' => $newOrderId], self::LOG_FILE);

            sleep(1);
        }

        // 6. Добавляем скоринги в новую заявку
        $this->addScorings($newOrderId);
        $this->logging(__METHOD__, '', 'Добавлены скоринги новой заявке', ['old_order_id' => (int)$order->order_id, 'new_order_id' => $newOrderId], self::LOG_FILE);

        // 7. Добавляем флаг об успешной смене организации
        $this->order_data->set((int)$order->order_id, $this->order_data::ORDER_ORG_SWITCH_RESULT, OrderOrgSwitchEnum::REASON_SUCCESS_WITH_ORGANIZATION_SWITCH . '_' . $riverNumber . '_' . $scenarioNumber);

        // 8. Логируем
        $this->logging(__METHOD__, '', self::REASON_MESSAGES[OrderOrgSwitchEnum::REASON_SUCCESS_WITH_ORGANIZATION_SWITCH], ['order_id' => (int)$order->order_id, 'new_order_id' => $newOrderId], self::LOG_FILE);

        return $newOrderId;
    }

    /**
     * 4-я версия ручейка (без ВКЛ)
     *
     * Условия для переключения организации:
     * 1. По заявке нет результата ручейка ИЛИ результат ручейка REASON_SETTING_DISABLED ИЛИ прошло больше 1 дня от даты получения последнего результата ручейка
     * 2. Заявка должна быть в статусе Новая ИЛИ автозаявка ИЛИ кросс-ордер
     * 3. Заявка НЕ закрыта
     * 4. Заявка не создана в результате ручейка (нет $this->order_data::ORDER_ORG_SWITCH_PARENT_ORDER_ID)
     * 6. Последний акси успешный (одобрено)
     *
     * Для кросс-ордеров:
     *  - Ручеек отключен (только добавляется флаг AXI_WITHOUT_CREDIT_REPORTS)
     *
     * Для остальных заявок:
     * - Сценарий 1: Расчет ПДН основной организации с КИ основной организации, если входим в МПЛ основной организации, то оставляем заявку в основной организации и разрешаем выдачу
     * Для Фриды дополнительно проверяем, что на балансе есть деньги и заявка НЕ инстолмент
     *
     *
     * Далее рассчитываем ПДН дополнительной организации с КИ основной автозаявка, отказываем по основном организации и создаем заявку от дополнительной.
     * Если не входим в МПЛ, то добавляем флаг $this->order_data::AXI_WITHOUT_CREDIT_REPORTS
     *
     * Дальнейшие сценарии происходят непосредственно перед выдачей (см. cron/b2p_issuance.php::isOrderPdnWithinMpl()):
     *
     * Если нет флага $this->order_data::AXI_WITHOUT_CREDIT_REPORTS:
     * - Сценарий 2: Расчет ПДН дополнительной организации с КИ дополнительной организации, если входим в МПЛ, то выдаем с дополнительной организации, иначе отказ
     *
     * Если есть флаг $this->order_data::AXI_WITHOUT_CREDIT_REPORTS:
     * - Сценарий 3: Если был запрос КИ из дополнительной организации в течение последних 7 дней, то отказ
     * - Сценарий 4: Расчет ПДН дополнительной организации без КИ, если входим в МПЛ, то выдаем с дополнительной организации, иначе отказ
     */
    private function runRiverFour(stdClass $order): bool
    {
        $this->logging(__METHOD__, '', 'Заявка проверяется по ручейку четыре', ['order_id' => $order->order_id], self::LOG_FILE);

        // 1. Проверка на соответствие заявки условиям
        if (!$this->canSwitchOrganizationRiverThree($order)) {
            return false;
        }

        $this->logging(__METHOD__, '', 'Проверка возможности прохождения ручейка четыре пройдена успешно', ['order' => $order], self::LOG_FILE);

        // 2. Кросс-ордера
        if ($this->orders->isCrossOrder($order)) {
            $this->logging(__METHOD__, '', 'Заявка проверяется по ручейку четыре для кросс-ордеров', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return $this->runRiverTwoForCrossOrders($order);
        }

        // 3. РЗС
        if ((int)$order->organization_id === $this->organizations::RZS_ID) {
            $this->logging(__METHOD__, '', 'Заявка проверяется по ручейку четыре для РЗС', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return $this->runRiverFourForOtherOrders($order, $this->organizations::RZS_ID, $this->organizations::FASTFINANCE_ID);
        }

        // 4. Фастфинанс
        $this->logging(__METHOD__, '', 'Заявка проверяется по ручейку четыре для Фриды', ['order_id' => (int)$order->order_id], self::LOG_FILE);
        return $this->runRiverFourForOtherOrders($order, $this->organizations::FASTFINANCE_ID, $this->organizations::RZS_ID);
    }

    /**
     * 5-я версия ручейка (с ВКЛ)
     *
     * Условия для запуска ручейка
     * 1. По заявке нет результата ручейка ИЛИ результат ручейка REASON_SETTING_DISABLED ИЛИ прошло больше 1 дня от даты получения последнего результата ручейка
     * 2. Заявка не создана в результате ручейка (нет $this->order_data::ORDER_ORG_SWITCH_PARENT_ORDER_ID)
     * 3. Заявка НЕ закрыта
     * 4. Последний акси успешный (одобрено)
     * 5. Пройдена проверка на возможность создания ВКЛ: подходящий s_users.utm_source и тестовый пользователь (если включено согласно настройкам) и у юзера есть UserData::ALLOW_TO_CREATE_RCL
     *
     * Для кросс-ордеров:
     * Сценарий 1: Кросс-ордер с открытым договором ВКЛ
     * Сценарий 2: Кросс-ордер без открытого договора ВКЛ
     *
     * Для остальных заявок:
     * Сценарий 3: Заявка с открытым договором ВКЛ в текущей МКК (без смены организации)
     * Сценарий 4: Заявка с открытым договором ВКЛ во второй МКК (со сменой организации)
     * Сценарий 5: Заявка без открытого договора ВКЛ с созданием ВКЛ в текущей МКК (без смены организации)
     * Сценарий 6: Заявка без открытого договора ВКЛ с созданием ВКЛ во второй МКК (со сменой организации)
     *
     * Дальнейшие сценарии происходят непосредственно перед выдачей (см. cron/b2p_issuance.php::isOrderPdnWithinMpl() и cron/check_pdn_before_issuance.php):
     */
    private function runRiverFive(stdClass $order): bool
    {
        $this->logging(__METHOD__, '', 'Заявка будет проверена по ручейку четыре или пять', ['order_id' => (int)$order->order_id], self::LOG_FILE);

        // 1. Проверка на соответствие заявки условиям
        if (!$this->canSwitchOrganizationRiverThree($order)) {
            return false;
        }

        // 2. Проверка на соответствие заявки условиям создания ВКЛ
        if (!$this->checkCanCreateRcl($order)) {
            return $this->runRiverFour($order);
        }

        $this->logging(__METHOD__, '', 'Проверка возможности прохождения ручейка пять пройдена успешно', ['order' => $order], self::LOG_FILE);

        // 3. Кросс-ордера
        if ($this->orders->isCrossOrder($order)) {
            $this->logging(__METHOD__, '', 'Заявка проверяется по ручейку пять для кросс-ордеров', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return $this->runRiverFiveForCrossOrders($order);
        }

        // 4. РЗС
        if ((int)$order->organization_id === $this->organizations::RZS_ID) {
            $this->logging(__METHOD__, '', 'Заявка проверяется по ручейку пять для РЗС', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return $this->runRiverFiveForOtherOrders($order, $this->organizations::RZS_ID, $this->organizations::FASTFINANCE_ID);
        }

        // 5. Фаст-финанс
        $this->logging(__METHOD__, '', 'Заявка проверяется по ручейку пять для Фаст-финанса', ['order_id' => (int)$order->order_id], self::LOG_FILE);
        return $this->runRiverFiveForOtherOrders($order, $this->organizations::FASTFINANCE_ID, $this->organizations::RZS_ID);
    }

    private function checkCanCreateRcl(stdClass $order): bool
    {
        $settings = $this->settings->organization_switch;

        $user = $this->users->get_user((int)$order->user_id);

        // 1. Пользователь не найден
        if (empty($user)) {
            $this->logging(__METHOD__, '', 'Пользователь не найден для ручейка пять', ['order_id' => (int)$order->order_id, 'user' => $user], self::LOG_FILE);
            return false;
        }

        $rclContract = $this->getOpenRclContract((int)$order->user_id);

        // 2. Есть открытый договор ВКЛ
        if (!empty($rclContract)) {
            $this->logging(__METHOD__, '', 'Найден договор ВКЛ, разрешаем создание повторного транша', ['order_id' => (int)$order->order_id, 'rcl_contract_id' => $rclContract->id], self::LOG_FILE);
            return true;
        }

        // 3. Подходящий лидген (используем $user->utm_source, чтобы корректно работало для кросс-ордеров и автозаявок)
        if (!empty($settings['utm_sources']) && in_array($user->utm_source ?: 'Boostra', $settings['utm_sources'])) {
            $this->logging(__METHOD__, '', 'Подходящий лидген для ручейка пять', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return true;
        }

        // 4. Если согласно настройкам должен быть тестовый пользователь
//        if (!empty($settings['test_user_only']) && !$this->user_data->isTestUser((int)$user->id)) {
//            $this->logging(__METHOD__, '', 'Не тестовый пользователь для ручейка пять', ['order_id' => (int)$order->order_id, 'user_id' => (int)$user->id], self::LOG_FILE);
//            return false;
//        }

        $allowToCreateRcl = $this->user_data->read((int)$user->id, $this->user_data::ALLOW_TO_CREATE_RCL);

        // 5. Пользователь в списке пользователей, которым можно создать ВКЛ
        if (!empty($allowToCreateRcl)) {
            $this->logging(__METHOD__, '', 'Подходящий пользователь для ручейка пять', ['order_id' => (int)$order->order_id, 'user_id' => (int)$user->id, 'allow_to_create_rcl' => $allowToCreateRcl], self::LOG_FILE);
            return true;
        }

        $this->logging(__METHOD__, '', 'Неподходящие условия для ручейка пять', ['order_id' => (int)$order->order_id], self::LOG_FILE);
        return false;
    }

    private function runRiverFiveForCrossOrders(stdClass $order): bool
    {
        $mainOrder = $this->cross_orders->getMainOrder($order);
        if (empty($mainOrder)) {
            $this->logging(__METHOD__, '', 'Не удалось получить основную заявку кросс-ордера', ['order_id' => (int)$order->order_id, 'main_order' => $mainOrder], self::LOG_FILE);
            $this->orders->rejectOrder($order, $this->reasons::REASON_SWITCH_ORGANIZATION);
            return false;
        }

        // Задержка для скористы для getApproveAmountScoring
        sleep(3);
        $approveAmountScoring = $this->scorings->getApproveAmountScoring((int)$order->user_id, (int)$mainOrder->order_id);

        // 1. Если есть открытый ВКЛ в Лорде
        $rclContract = $this->getOpenRclContract((int)$order->user_id, (int)$order->organization_id);
        if (!empty($rclContract)) {
            $this->logging(__METHOD__, '', 'Заявка проверяется по ручейку пять для кросс-ордеров с открытым ВКЛ (сценарий 1)', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            $this->saveOrderOrgSwitchResult((int)$order->order_id, 5, 1);

            $this->runRiverFiveWithOpenRcl($order, $rclContract, $approveAmountScoring);
            return false;
        }

        // 2. Если нет открытого ВКЛ
        $this->logging(__METHOD__, '', 'Заявка проверяется по ручейку пять для кросс-ордеров без открытого ВКЛ (сценарий 2)', ['order_id' => (int)$order->order_id], self::LOG_FILE);
        $this->saveOrderOrgSwitchResult((int)$order->order_id, 5, 2);

        $pdnCalculationResultForRcl = $this->calculatePdnForCreatingRcl($order, true);

        // Если не удалось рассчитать ПДН для ВКЛ без КИ, то проводим заявку по ручейку 4
        if (empty($pdnCalculationResultForRcl)) {
            $this->runRiverFour($order);
            return false;
        }

        $this->createRcl($order, $pdnCalculationResultForRcl, $approveAmountScoring, true);
        return false;
    }

    private function runRiverFiveForOtherOrders(stdClass $order, int $primaryOrganization, int $secondaryOrganization): bool
    {
        // 1. Если есть открытый ВКЛ в исходной МКК
        $rclContract = $this->getOpenRclContract((int)$order->user_id, $primaryOrganization);
        if (!empty($rclContract)) {
            $this->logging(__METHOD__, '', 'Заявка проверяется по ручейку пять для РЗС/Фриды с открытым ВКЛ без смены организации (сценарий 3)', ['order_id' => (int)$order->order_id, 'primary_organization' => $primaryOrganization], self::LOG_FILE);
            $this->saveOrderOrgSwitchResult((int)$order->order_id, 5, 3);

            $this->runRiverFiveWithOpenRcl($order, $rclContract);
            return false;
        }

        // 2. Если есть открытый ВКЛ во второй МКК
        $rclContract = $this->getOpenRclContract((int)$order->user_id, $secondaryOrganization);
        if (!empty($rclContract)) {
            $this->logging(__METHOD__, '', 'Заявка проверяется по ручейку пять для РЗС/Фриды с открытым ВКЛ со сменой организации (сценарий 4)', ['order_id' => (int)$order->order_id, 'secondary_organization' => $secondaryOrganization], self::LOG_FILE);

            return $this->runRiverFiveWithOpenRclWithOrganizationSwitch($order, $rclContract, $secondaryOrganization);
        }

        // 3. Если нет открытого ВКЛ
        return $this->runRiverFiveForOtherOrdersWithoutOpenRcl($order, $primaryOrganization, $secondaryOrganization);
    }

    private function getOpenRclContract(int $userId, ?int $organizationId = null): ?stdClass
    {
        $params = [
            'user_id' => $userId,
            'status' => $this->rcl::STATUS_APPROVED,
            'date_start' => [
                'to' => date('Y-m-d'),
            ],
            'date_end' => [
                'from' => date('Y-m-d'),
            ],
        ];

        if (!empty($organizationId)) {
            $params['organization_id'] = $organizationId;
        }

        return $this->rcl->get_contract($params) ?: null;
    }

    private function runRiverFiveWithOpenRclWithOrganizationSwitch(stdClass $order, stdClass $rclContract, int $secondaryOrganization): bool
    {
        $newOrderId = $this->switchOrganization($order, $secondaryOrganization, 5, 4, !empty($rclContract->without_ch));

        if (empty($newOrderId)) {
            $this->logging(__METHOD__, '', 'Не удалось сменить организацию', ['order_id' => (int)$order->order_id, 'new_order_id' => $newOrderId], self::LOG_FILE);
            return false;
        }

        $newOrder = $this->orders->get_order($newOrderId);

        if (empty($newOrder)) {
            $this->logging(__METHOD__, '', 'Не удалось получить заявку после смены организации', ['order_id' => (int)$order->order_id, 'new_order_id' => $newOrderId], self::LOG_FILE);
            return false;
        }

        // Задержка для скористы для getApproveAmountScoring
        sleep(3);
        $approveAmountScoring = $this->scorings->getApproveAmountScoring((int)$order->user_id, (int)$order->order_id);

        $this->runRiverFiveWithOpenRcl($newOrder, $rclContract, $approveAmountScoring);
        return true;
    }

    private function runRiverFiveWithOpenRcl(stdClass $order, stdClass $rclContract, ?int $approveAmountScoring = null): bool
    {
        if ($this->hasUserOpenTranche($order, $rclContract)) {
            $this->orders->rejectOrder($order, $this->reasons::REASON_SWITCH_ORGANIZATION);
            return false;
        }

        if (!empty($rclContract->without_ch)) {
            $this->order_data->set((int)$order->order_id, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS, 1);
        }

        if ($approveAmountScoring === null) {
            // Задержка для скористы для getApproveAmountScoring
            sleep(3);
            $approveAmountScoring = $this->scorings->getApproveAmountScoring((int)$order->user_id, (int)$order->order_id);
        }

        // Сумма допов
        $dops = $this->calculateDops((int)$order->order_id, $approveAmountScoring);
        if (empty($dops->success) || !isset($dops->extra_service_sum)) {
            $this->logging(__METHOD__, '', 'Некорректный ответ при получении суммы допов', ['order_id' => (int)$order->order_id, 'dops' => $dops], self::LOG_FILE);
        }

        $dopsAmount = $dops ? (int)$dops->extra_service_sum : 0;
        $rclMaxAmount = (int)$rclContract->max_amount;

        $this->addRclFlagsToOrder((int)$order->order_id, $approveAmountScoring, $dopsAmount, $rclMaxAmount);
        return true;
    }

    private function hasUserOpenTranche(stdClass $order, stdClass $rclContract): bool
    {
        $tranches = $this->rcl->get_tranches([
            'rcl_contract_id' => (int)$rclContract->id,
        ]);

        if (empty($tranches)) {
            $this->logging(__METHOD__, '', 'Почему-то есть открытый ВКЛ, но нет траншей, отказываем по заявке', ['order_id' => (int)$order->order_id, 'tranches' => $tranches], self::LOG_FILE);
            return true;
        }

        $ordersId = array_column($tranches, 'order_id');

        $orders = $this->orders->get_orders([
            'id' => $ordersId,
            'sort' => 'order_id_desc'
        ]);

        if (empty($orders)) {
            $this->logging(__METHOD__, '', 'Почему-то не найдены заявки, отказываем по заявке', ['order_id' => (int)$order->order_id, 'tranches' => $tranches], self::LOG_FILE);
            return true;
        }

        if (!$this->orders->checkAreOrdersClosedWithUpdate1cStatus($orders)) {
            $this->logging(__METHOD__, '', 'У клиента есть открытый транш', ['order_id' => (int)$order->order_id, 'tranches' => $tranches], self::LOG_FILE);
            return true;
        }

        return false;
    }

    private function runRiverFiveForOtherOrdersWithoutOpenRcl(stdClass $order, int $primaryOrganization, int $secondaryOrganization): bool
    {
        $currentBalanceInOrganization = 0;

        try {
            $currentBalanceInOrganization = $this->organizations->getBalance($primaryOrganization);
        } catch (RuntimeException $error) {
            $this->logging(__METHOD__, '', 'Ошибка при получении баланса в организации', ['primary_organization' => $primaryOrganization, 'error' => $error], self::LOG_FILE);
        }

        // 1. Если на МКК достаточно средств, то пробуем создать ВКЛ с КИ в текущей организации
        if ((int)$currentBalanceInOrganization >= self::MIN_BALANCE_FOR_RIVER_FIVE_TO_ISSUANCE_ORDER) {
            $pdnCalculationResultForRcl = $this->calculatePdnForCreatingRcl($order, false);

            // Если по расчету ПДН ВКЛ с КИ, можно создать ВКЛ с КИ, то создаем ВКЛ с КИ
            if (!empty($pdnCalculationResultForRcl)) {
                $this->logging(__METHOD__, '', 'Заявка проверяется по ручейку пять для РЗС/Фриды без открытого ВКЛ без смены организации (сценарий 5)', ['order_id' => (int)$order->order_id], self::LOG_FILE);
                $this->saveOrderOrgSwitchResult((int)$order->order_id, 5, 5);

                $result = $this->createRcl($order, $pdnCalculationResultForRcl, null, false);

                if ($result) {
                    return false;
                }
            }
        }

        $this->logging(__METHOD__, '', 'Заявка проверяется по ручейку пять для РЗС/Фриды без открытого ВКЛ со сменой организации (сценарий 6)', ['order_id' => (int)$order->order_id], self::LOG_FILE);

        // 2. Если на МКК недостаточно средств или не удалось создать ВКЛ, переключаем организацию и пробуем создать ВКЛ без КИ
        return $this->switchOrganizationAndCreateRcl($order, $secondaryOrganization);
    }

    private function switchOrganizationAndCreateRcl(stdClass $order, int $secondaryOrganization): bool
    {
        $newOrderId = $this->switchOrganization($order, $secondaryOrganization, 5, 6, true);
        if (empty($newOrderId)) {
            $this->logging(__METHOD__, '', 'Не удалось создать заявку', ['order_id' => $order->order_id, 'new_order_id' => $newOrderId], self::LOG_FILE);
            return false;
        }

        // Если клиент открывал договор индивидуальных условий
        if ($this->didUserOpenDocuments($order, $newOrderId)) {
            $this->order_data->set($newOrderId, $this->order_data::AUTOCONFIRM_ASP, null);
        }

        $newOrder = $this->orders->get_order($newOrderId);
        if (empty($newOrder)) {
            $this->logging(__METHOD__, '', 'Новая заявка не найдена', ['order' => $order, 'new_order_id' => $newOrderId], self::LOG_FILE);
            return false;
        }

        // Задержка для скористы для getApproveAmountScoring
        sleep(3);
        $approveAmountScoring = $this->scorings->getApproveAmountScoring((int)$order->user_id, (int)$order->order_id);

        $pdnCalculationResultForRcl = $this->calculatePdnForCreatingRcl($newOrder, true);

        // Если не удалось рассчитать ПДН для ВКЛ без КИ, то проводим заявку как бы по ручейку 4, но
        // не запускаем полноценный ручеек 4, т.к. уже была смена организации
        if (empty($pdnCalculationResultForRcl)) {
            $this->logging(__METHOD__, '', 'Не удалось рассчитать ПДН для ВКЛ, проводим заявку как бы по ручейку четыре', ['new_order_id' => $newOrderId, 'pdn_calculation_result_for_rcl' => $pdnCalculationResultForRcl], self::LOG_FILE);
            $this->saveOrderOrgSwitchResult($newOrderId, 4, 0);
            return true;
        }

        $this->createRcl($newOrder, $pdnCalculationResultForRcl, $approveAmountScoring, true);
        return true;
    }

    /**
     * @param stdClass $order
     * @param bool $withoutCh
     * @return stdClass|false
     */
    private function calculatePdnForCreatingRcl(stdClass $order, bool $withoutCh = true)
    {
        $this->logging(__METHOD__, '', 'Начат расчет ПДН для ВКЛ до выдачи', ['order_id' => (int)$order->order_id], self::LOG_FILE);

        $flags = [
            $this->pdnCalculation::CALCULATE_PDN_BEFORE_ISSUANCE => 1
        ];

        if ($withoutCh) {
            $flags[$this->pdnCalculation::WITHOUT_REPORTS] = 1;
        }

        $pdnCalculationResultForRcl = $this->pdnCalculation->calculatePdnForRcl($order->order_uid, $flags);

        $this->logging(__METHOD__, '', 'Завершен расчет ПДН для ВКЛ до выдачи', ['order_id' => (int)$order->order_id, 'pdn_calculation_result_for_rcl' => $pdnCalculationResultForRcl], self::LOG_FILE);

        // 1. Если некорректный расчет ПДН для ВКЛ
        if (!isset($pdnCalculationResultForRcl->dbi) || !isset($pdnCalculationResultForRcl->recommended_loan_amount)) {
            $this->logging(__METHOD__, '', 'Некорректный расчет ПДН для ВКЛ', ['order_id' => (int)$order->order_id, 'pdn_calculation_result_for_rcl' => $pdnCalculationResultForRcl], self::LOG_FILE);
            return false;
        }

        // 2. Если ПДН > 50%
        if ((float)$pdnCalculationResultForRcl->dbi > self::MAX_PDN_TO_CREATE_RCL) {
            $this->logging(__METHOD__, '', 'ПДН > 50%', ['order_id' => (int)$order->order_id, 'pdn_calculation_result_for_rcl' => $pdnCalculationResultForRcl], self::LOG_FILE);
            return false;
        }

        return $pdnCalculationResultForRcl;
    }

    /**
     * @param stdClass $order
     * @param stdClass $pdnCalculationResultForRcl
     * @param int|null $approveAmountScoring Рекомендуемая сумма скористы исходной заявки клиента, если текущая заявка создана в результате смены организации
     * @param bool $withoutCh Расчет ВКЛ без КИ
     * @return bool
     */
    private function createRcl(stdClass $order, stdClass $pdnCalculationResultForRcl, ?int $approveAmountScoring = null, bool $withoutCh = true): bool
    {
        if ($withoutCh) {
            $this->order_data->set((int)$order->order_id, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS, 1);
        }

        $rclMaxAmount = (int)floor($pdnCalculationResultForRcl->recommended_loan_amount);

        // Сумма скористы
        if ($approveAmountScoring === null) {
            // Задержка для скористы для getApproveAmountScoring
            sleep(3);
            $approveAmountScoring = $this->scorings->getApproveAmountScoring((int)$order->user_id, (int)$order->order_id);
        }

        // Сумма допов
        $dops = $this->calculateDops((int)$order->order_id, $approveAmountScoring);
        if (empty($dops->success) || !isset($dops->extra_service_sum)) {
            $this->logging(__METHOD__, '', 'Некорректный ответ при получении суммы допов', ['order_id' => (int)$order->order_id, 'dops' => $dops], self::LOG_FILE);
        }

        $dopsAmount = $dops ? (int)$dops->extra_service_sum : 0;

        // 3. Если создаем ВКЛ с КИ И
        // Максимальный кредитный лимит меньше установленного минимума ИЛИ
        // Рекомендуемая сумма скористы с допами меньше установленного минимума ИЛИ
        // Если максимальный кредитный лимит меньше суммы скористы с допами
        if (!$withoutCh) {

            // Сумма скористы с допами
            $approveAmountScoringWithDops = $approveAmountScoring + $dopsAmount;

            if (
                $rclMaxAmount < self::MIN_SCORISTA_AMOUNT_TO_ISSUANCE_RCL ||
                $approveAmountScoringWithDops < self::MIN_SCORISTA_AMOUNT_TO_ISSUANCE_RCL ||
                $rclMaxAmount < $approveAmountScoringWithDops
            ) {
                $this->logging(__METHOD__, '', 'Неподходящая сумма для выдачи с КИ', ['order_id' => (int)$order->order_id, 'approve_amount_scoring' => $approveAmountScoring, 'dops_amount' => $dopsAmount, 'rcl_max_amount_from_pdn' => $rclMaxAmount], self::LOG_FILE);
                return false;
            }
        }

        $this->addRclFlagsToOrder((int)$order->order_id, $approveAmountScoring, $dopsAmount, $rclMaxAmount);

        return true;
    }

    private function addRclFlagsToOrder(int $orderId, int $approveAmountScoring, int $dopsAmount, int $rclMaxAmount): void
    {
        $approveAmountScoringWithDops = $approveAmountScoring + $dopsAmount;
        $rclAmount = min($approveAmountScoring, $rclMaxAmount - $dopsAmount, $this->pdnCalculation::IL_MAX_AMOUNT);

        if ($rclAmount <= 0) {
            $this->logging(__METHOD__, '', 'Рекомендуемая сумма отрицательная', ['order_id' => $orderId, 'recommended_order_amount' => $rclAmount], self::LOG_FILE);
            $rclAmount = 0;
        }

        $this->logging(__METHOD__, '', 'Получена рекомендуемая сумма для ВКЛ', [
            'order_id' => $orderId,
            'approve_amount_scoring' => $approveAmountScoring,
            'rcl_max_amount_from_pdn' => $rclMaxAmount,
            'dops_amount' => $dopsAmount,
            'approve_amount_scoring_with_dops' => $approveAmountScoringWithDops,
            'recommended_order_amount' => $rclAmount,
        ], self::LOG_FILE);

        $this->order_data->set($orderId, $this->order_data::RCL_LOAN, 1);
        $this->order_data->set($orderId, $this->order_data::RCL_MAX_AMOUNT, $rclMaxAmount);
        $this->order_data->set($orderId, $this->order_data::RCL_AMOUNT, $rclAmount);

        $this->orders->update_order($orderId, [
            'amount' => $rclAmount,
            'approve_amount' => $rclAmount,
        ]);

        $this->logging(__METHOD__, '', 'Установлены флаги для ВКЛ', ['order_id' => $orderId], self::LOG_FILE);
    }

    /**
     * Проверка на соответствие заявки условиям переключения организации
     * @param stdClass $order
     * @return bool true - соответствует, false - не соответствует
     */
    private function canSwitchOrganizationRiverThree(stdClass $order): bool
    {
        // 1. По заявке уже есть результат ручейка И
        // Результат ручейка НЕ REASON_SETTING_DISABLED И
        // Прошло меньше 1 дня от даты получения результата ручейка
        $orderOrgSwitchResult = $this->order_data->get($order->order_id, $this->order_data::ORDER_ORG_SWITCH_RESULT);
        if (
            !empty($orderOrgSwitchResult->value) &&
            $orderOrgSwitchResult->value !== OrderOrgSwitchEnum::REASON_SETTING_DISABLED
        ) {
            $diffSeconds = time() - strtotime($orderOrgSwitchResult->updated);
            if ($diffSeconds < 86400) {
                $this->logging(__METHOD__, '', 'Ранее по заявке уже пробовали сменить организацию', ['order_id' => (int)$order->order_id], self::LOG_FILE);
                return false;
            }
        }

        // 2. Заявка закрыта
        if ($this->checkIsOrderClosed($order)) {
            $this->saveErrorOrderOrgSwitch((int)$order->order_id, OrderOrgSwitchEnum::REASON_INAPPROPRIATE_STATUS);
            return false;
        }

        $orderOrgSwitchParentOrderId = $this->order_data->read($order->order_id, $this->order_data::ORDER_ORG_SWITCH_PARENT_ORDER_ID);

        // 3. Заявка создана в результате ручейка
        if (!empty($orderOrgSwitchParentOrderId)) {
            $this->logging(__METHOD__, '', 'Заявка не проверяется по ручейку, т.к. создана в результате ручейка', ['order_id' => (int)$order->order_id, 'order_org_switch_parent_order_id' => $orderOrgSwitchParentOrderId], self::LOG_FILE);
            return false;
        }

        $axi = $this->scorings->getLastScoring([
            'order_id' => $order->order_id,
            'type' => $this->axi->getAxiScoringType($order),
        ]);

        // 4. Последний акси еще выполняется или неуспешный
        if (empty($axi) || (int)$axi->status !== $this->scorings::STATUS_COMPLETED || empty($axi->success)) {
            $this->logging(__METHOD__, '', 'Последний акси еще выполняется или неуспешный', ['order_id' => (int)$order->order_id, /*'axi' => $axi*/], self::LOG_FILE);
            return false;
        }

        return true;
    }

    private function checkIsOrderClosed(stdClass $order): bool
    {
        $orderStatutesToPreventOrgSwitch = [
            $this->orders::ORDER_STATUS_CRM_ISSUED,
            $this->orders::ORDER_STATUS_CRM_REJECT,
            $this->orders::ORDER_STATUS_SIGNED,
            $this->orders::ORDER_STATUS_CRM_AUTOCONFIRM,
        ];

        if (in_array($order->status, $orderStatutesToPreventOrgSwitch)) {
            return true;
        }

        return false;
    }

    /**
     * @param stdClass $order
     * @param int $primaryOrganization Основная (исходная) организация у заявки, на которой оставим заявку, если вошли в МПЛ основной
     * @param int $secondaryOrganization Дополнительная организация, на которую переведем заявку, если не вошли в МПЛ основной
     * @return bool Создана ли новая заявка
     */
    private function runRiverFourForOtherOrders(stdClass $order, int $primaryOrganization, int $secondaryOrganization): bool
    {
        $isIssuanceAllowed = true;
        if ($primaryOrganization === $this->organizations::FRIDA_ID) {
            $isIssuanceAllowed = $this->canIssuanceToFrida($order);
        }

        // Сценарий 1: Расчет ПДН основной организации с КИ основной организации.
        // Если входим в МПЛ основной и можно выдать на основную, то выдаем с основной
        // Если нет, то переходим к Сценарию 2
        $pdnResultOne = $this->calculatePdnWithOrganizationId($order, $primaryOrganization);

        if (!empty($pdnResultOne->is_within_mpl) && $isIssuanceAllowed) {
            return (bool)$this->trySwitchOrganizationWithCheckDocuments($order, $primaryOrganization, 4,1, false);
        }

        // Сценарий 2: Расчет ПДН дополнительной организации с КИ основной организации и создаем новую заявку
        // Если не входим в МПЛ, то добавляем флаг AXI_WITHOUT_CREDIT_REPORTS, чтобы не запрашивались отчеты
        $pdnResultTwo = $this->calculatePdnWithOrganizationId($order, $secondaryOrganization);

        $axiWithoutCreditReports = false;
        if (empty($pdnResultTwo->is_within_mpl)) {
            $axiWithoutCreditReports = true;
        }

        return (bool)$this->trySwitchOrganizationWithCheckDocuments($order, $secondaryOrganization, 4, $axiWithoutCreditReports ? 4 : 2, $axiWithoutCreditReports);

        // Дальнейшие сценарии происходят непосредственно перед выдачей (см. cron/b2p_issuance.php::isOrderPdnWithinMpl())
    }

    private function canIssuanceToFrida(stdClass $order): bool
    {
        // 1. Проверяем, что заявка не инстолмент
        if ($order->loan_type === $this->orders::LOAN_TYPE_IL) {
            $this->logging(__METHOD__, '', 'Заявка - инстоллмент, не можем выдать на Фриду', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return false;
        }

        // 2. Проверяем, что на Фриде есть деньги
        try {
            $fridaBalance = $this->organizations->getBalance($this->organizations::FRIDA_ID);
        } catch (RuntimeException $error) {
            $this->logging(__METHOD__, '', 'Ошибка при получении баланса на Фриде', ['organization_id' => $this->organizations::FRIDA_ID, 'error' => $error], self::LOG_FILE);
            return false;
        }

        if ($fridaBalance < self::MIN_FRIDA_BALANCE_FOR_RIVER_THREE_TO_ISSUANCE_ORDER) {
            $this->logging(__METHOD__, '', 'Баланс Фриды меньше установленного для выдачи на Фриду', ['order_id' => (int)$order->order_id, 'frida_balance' => $fridaBalance], self::LOG_FILE);
            return false;
        }

        return true;
    }

    /**
     * Расчет суммы допов
     * Отправляем запрос на сайт, что не переносить всю логику расчета допов из сайта в crm
     */
    private function calculateDops(int $orderId, int $amount): ?stdClass
    {
        $data = [
            'order_id' => $orderId,
            'amount' => $amount
        ];

        $url = $this->config->front_url . '/ajax/crm_extra_service_price.php';

        $client = new Client([
            'connect_timeout' => 20,
            'timeout' => 20,
        ]);

        try {
            $res = $client->request('GET', $url, [
                'query' => $data,
                'headers' => [
                    'accept' => 'application/json'
                ],
            ]);
        } catch (Throwable $error) {
            $this->logging(__METHOD__, '', 'Ошибка при получении суммы допов', ['data' => $data, 'error' => $error], self::LOG_FILE);
            return null;
        }

        $body = $res->getBody()->getContents();

        $this->logging(__METHOD__, '', 'Рекомендуемая сумма для ВКЛ', ['data' => $data, 'body' => $body], self::LOG_FILE);

        return json_decode($body) ?: null;
    }

    private function saveOrderOrgSwitchResult(int $orderId, int $riverNumber, int $scenarioNumber)
    {
        $this->order_data->set($orderId, $this->order_data::ORDER_ORG_SWITCH_RESULT, 'SUCCESS_WITH_ORGANIZATION_SWITCH_' . $riverNumber . '_' . $scenarioNumber);
    }
}
