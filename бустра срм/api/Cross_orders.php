<?php

use App\Enums\CrossOrder;

require_once 'Simpla.php';

class Cross_orders extends Simpla
{
    /** @var stdClass Родительская заявка, для которой создаём кросс-ордер */
    private $order;
    /** @var stdClass Клиент, для которого создаём кросс-ордер */
    private $user;
    private $cross_amount;
    private $cross_organizations = [];

    private const LOG_FILE = 'cross_orders.txt';

    private const STATUS_ERROR = 'ERROR';
    private const STATUS_SUCCESS = 'SUCCESS';

    private const AMOUNT_COEF = 1;

    /** @var int Минимальный балл скроисты для создания кросс-ордера у НК */
    private const MIN_SCORISTA_BALL_NK = 500;

    /** @var int Минимальный балл скроисты для создания кросс-ордера у ПК */
    private const MIN_SCORISTA_BALL_PK = 400;

    private const MAX_ATTEMPTS_TO_SEND_ORDER_TO_1C = 3;

    private const REASON_MESSAGES = [
        CrossOrder::REASON_SETTING_DISABLED => 'Создание кросс-ордеров отключено',
        CrossOrder::REASON_NO_ORDER => 'Не найдена заявка',
        CrossOrder::REASON_NO_ORDERS => 'Не найдены заявки',
        CrossOrder::REASON_NO_USER => 'Не найден пользователь',
        CrossOrder::REASON_NO_CROSS_ORGANIZATIONS => 'Нет кросс-организаций',
        CrossOrder::REASON_INCORRECT_ORGANIZATION => 'Организация родительской заявки совпадает с текущей',
        CrossOrder::REASON_ORDER_HAS_CROSS_ORDER => 'По заявке уже был создан кросс-ордер',
        CrossOrder::REASON_ORDER_NOT_ADDED_TO_DB => 'Не удалось создать заявку в БД',
        CrossOrder::REASON_ORDER_NOT_ADDED_TO_1C => 'Заявка в 1c не создана',
        CrossOrder::REASON_UNKNOWN_REASON => 'Ошибка при создании кросс-ордера',
        CrossOrder::REASON_INSTALLMENT => 'Заявка - инстоллмент',
        CrossOrder::REASON_IGNORED_SITE => 'Заявка с недопустимого сайта',
        CrossOrder::REASON_NK_SETTING_DISABLED => 'Настройка кросс-ордеров для НК отключена',
        CrossOrder::REASON_HAS_ACTIVE_CROSS_ORDER => 'У клиента есть открытый кросс-ордер',
        CrossOrder::REASON_NO_SUCCESS_SCORING_WITH_SCORBALL => 'Нет успешного скоринга со скорбаллом',
        CrossOrder::REASON_LOW_SCORISTA_BALL_NK => 'Низкий скорбалл у НК',
        CrossOrder::REASON_LOW_SCORISTA_BALL_PK => 'Низкий скорбалл у ПК',
    ];

    public function create($order_id, bool $needSendSms = false): bool
    {
        $this->logging(__METHOD__, '', 'Начата попытка создания кросс-ордера', ['order_id' => $order_id], self::LOG_FILE);

        if (!$this->settings->cross_orders_enabled) {
            $this->saveErrorCreateCrossOrder($order_id, CrossOrder::REASON_SETTING_DISABLED);
            return false;
        }

        $this->order = $this->orders->get_order_short((int)$order_id);

        if (empty($this->order)) {
            $this->saveErrorCreateCrossOrder($order_id, CrossOrder::REASON_NO_ORDER);
            return false;
        }

        $this->user = $this->users->get_user((int)$this->order->user_id);

        if (empty($this->user)) {
            $this->saveErrorCreateCrossOrder($order_id, CrossOrder::REASON_NO_USER);
            return false;
        }

        if (!$this->canCreateCrossOrder()) {
            return false;
        }

        $this->calc_cross_amount();
        $this->init_cross_organizations();

        if (empty($this->cross_organizations)) {
            $this->saveErrorCreateCrossOrder($order_id, CrossOrder::REASON_NO_CROSS_ORGANIZATIONS);
            return false;
        }

        $isCrossOrderCreated = false;
        foreach ($this->cross_organizations as $organization) {
            try {
                if ($this->validateOrganization((int)$organization->id)) {
                    $isCrossOrderCreated = $this->create_cross_order($organization, $needSendSms);
                }
            } catch (Throwable $error) {
                $this->saveErrorCreateCrossOrder($order_id, CrossOrder::REASON_UNKNOWN_REASON, ['error' => $error]);
            }
        }

        return $isCrossOrderCreated;
    }

    private function saveErrorCreateCrossOrder(int $orderId, string $reason, array $additionalDataToLog = [])
    {
        $text = self::REASON_MESSAGES[$reason] ?: 'Некорректная ошибка';
        $text .= '. Завершена попытка создания кросс-ордера';

        $this->logging(__METHOD__, '', $text, array_merge(['order_id' => $orderId], $additionalDataToLog), self::LOG_FILE);
        $this->addCrossOrderRecord([
            'parent_order_id' => $orderId,
            'user_id' => (int)$this->user->id ?? null,
            'status' => self::STATUS_ERROR,
            'reason' => $reason
        ]);
    }

    private function saveSuccessCreateCrossOrder(int $orderId, int $crossOrderId)
    {
        $this->logging(__METHOD__, '', 'Успешно создан кросс-ордер. Завершена попытка создания кросс-ордера', ['cross_order_id' => $crossOrderId], self::LOG_FILE);
        $this->addCrossOrderRecord([
            'parent_order_id' => $orderId,
            'user_id' => (int)$this->user->id,
            'status' => self::STATUS_SUCCESS,
            'reason' => $crossOrderId
        ]);
    }

    private function create_cross_order($organization, bool $needSendSms = false): bool
    {
        $manager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);

        $new_order = $this->getNewCrossOrderData($manager, $organization);

        // 1. Сохранить кросс-ордер в 1С
        $soap_zayavka = $this->addCrossOrderTo1C($new_order);

        if (empty($soap_zayavka->return->id_zayavka) || mb_strpos($soap_zayavka->return->id_zayavka, 'Не принято') !== false) {
            $this->saveErrorCreateCrossOrder((int)$this->order->id, CrossOrder::REASON_ORDER_NOT_ADDED_TO_1C, ['soap_zayavka' => $soap_zayavka]);
            return false;
        }

        $new_order['1c_id'] = $soap_zayavka->return->id_zayavka;

        // 2. Создать кросс-ордер в БД
        $order_id = $this->orders->add_order($new_order);

        if (empty($order_id)) {
            $this->saveErrorCreateCrossOrder((int)$this->order->id, CrossOrder::REASON_ORDER_NOT_ADDED_TO_DB);
            return false;
        }

        $this->logging(__METHOD__, '', 'Успешно создан кросс-ордер', array_merge($new_order, ['cross_order_id' => $order_id]), self::LOG_FILE);

        // 3. Установить флаг complete в 1С
        try {
            $this->soap->set_order_complete($order_id);
        } catch (Throwable $error) {
            $this->logging(__METHOD__, '', 'Не удалось установить флаг complete в 1С', ['order_id' => $order_id, 'error' => $error], self::LOG_FILE);
        }

        // 4. Установить кросс-ордеру выбранный банк
        if ($bankId = (int)$this->order_data->read((int)$this->order->id, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE)) {
            $this->order_data->set((int)$order_id, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE, $bankId);
            $this->orders->update_order($order_id, ['card_id' => 0, 'card_type' => $this->orders::CARD_TYPE_SBP]);
        }

        sleep(2);

        // 5. Одобряем заявку в 1C
        $this->updateOrderStatusIn1C($new_order, $manager);

        // 6. Если согласно настройкам необходимо проверить актуальность ССП и КИ отчетов, то добавляем скоринг проверки отчетов
        if ($this->report->needCheckReports((int)$order_id)) {
            $this->scorings->add_scoring([
                'user_id' => $new_order['user_id'],
                'order_id' => $order_id,
                'type' => $this->scorings::TYPE_REPORT,
                'status' => $this->scorings::STATUS_NEW,
            ]);
        }

        // 7. Добавляем УПРИД
        $this->scorings->add_scoring([
            'type' => $this->scorings::TYPE_UPRID,
            'user_id' => $new_order['user_id'],
            'order_id' => $order_id,
        ]);

        $this->order_data->set($order_id, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS, 1);
        sleep(1);

        // 8. добавляем аксинбки
        $this->scorings->add_scoring([
            'user_id' => $new_order['user_id'],
            'order_id' => $order_id,
            'type' => $this->scorings::TYPE_AXILINK_2,
            'status' => $this->scorings::STATUS_NEW,
        ]);

        // 9. Сохранить запись о кросс-ордере
        $this->saveSuccessCreateCrossOrder((int)$this->order->id, $order_id);

        // 10. Отправить смс, если нужно
        if ($needSendSms) {
            $this->smssender->sendApprovedSms($this->user, (int)$order_id, (int)$new_order['approve_amount']);
        }

        $crossOrganizationId = (int)($this->settings->cross_organization_id ?? 0);
        $crossAsp = $this->order_data->read((int)$this->order->id, $this->order_data::AUTOCONFIRM_ASP_CROSS);

        // 11. Если есть АСП и организация для выдачи кросс-ордер - копируем его в кросс-ордер и меняем статус
        if (!empty($crossAsp) && (int)$new_order['organization_id'] === $crossOrganizationId) {
            $this->order_data->set((int)$order_id, $this->order_data::AUTOCONFIRM_ASP, $crossAsp);
            $this->orders->update_order($order_id, ['status' => $this->orders::ORDER_STATUS_CRM_AUTOCONFIRM]);
            $this->logging(__METHOD__, '', ' Кросс-ордер переведён в статус «Автоподписание»', ['cross_order_id' => $order_id, 'asp' => $crossAsp], self::LOG_FILE);
        }

        return true;
    }

    private function getNewCrossOrderData(stdClass $manager, stdClass $organization): array
    {
        $new_order = (array)$this->order;

        unset($new_order['id']);
        unset($new_order['utm_source']);
        unset($new_order['utm_campaign']);
        unset($new_order['utm_content']);
        unset($new_order['utm_term']);
        unset($new_order['webmaster_id']);
        unset($new_order['click_hash']);
        unset($new_order['max_amount']);
        unset($new_order['max_period']);

        $new_order['organization_id'] = $organization->id;
        $new_order['manager_id'] = $manager->id;
        $new_order['loan_type'] = 'PDL';
        $new_order['status'] = $this->orders::ORDER_STATUS_CRM_APPROVED;
        $new_order['amount'] = $this->cross_amount;
        $new_order['period'] = $this->getPeriod();
        $new_order['percent'] = $this->orders::BASE_PERCENTS;
        $new_order['approve_amount'] = $this->cross_amount;
        $new_order['date'] = date('Y-m-d H:i:s');
        $new_order['utm_source'] = $this->orders::UTM_SOURCE_CROSS_ORDER;
        $new_order['utm_medium'] = $this->order->id;
        $new_order['1c_id'] = '';
        $new_order['order_uid'] = exec($this->config->root_dir . 'generic/uidgen');
        $new_order['card_type'] = $this->order->card_type;

        if ($new_order['card_type'] === $this->orders::CARD_TYPE_SBP) {
            $new_order['card_id'] = $this->order->card_id;
        } elseif ($new_order['card_type'] === $this->orders::CARD_TYPE_CARD) {
            $mainOrderCard = $this->best2pay->get_card($this->order->card_id);

            // Если кросс ордер на финлаб, и id орагнизации карты основной заявки не совпадает с id финлаба
            // То ищем карту с тем же токеном но для финлаба
            if ($organization->id == $this->organizations::FINLAB_ID && $mainOrderCard->organization_id != $organization->id) {
                $params = [
                    'user_id' => $mainOrderCard->user_id,
                    'token' => $mainOrderCard->token,
                    'organization_id' => $organization->id,
                    'deleted' => 0,
                    'deleted_by_client' => 0
                ];

                if ($crossOrderCard = $this->best2pay->get_card_by_params($params)) {
                    $new_order['card_id'] = $crossOrderCard->id;
                }
            }
        }

        return $new_order;
    }

    /**
     * Получает период для кросса
     * @return int
     */
    private function getPeriod(): int
    {
        return $this->orders::PDL_CROSS_ORDER_MAX_PERIOD;
    }

    private function updateOrderStatusIn1C(array $new_order, stdClass $manager): string
    {
        $updateStatus1CResult = '';

        for ($i = 0; $i < self::MAX_ATTEMPTS_TO_SEND_ORDER_TO_1C; $i++) {
            $updateStatus1CResult = $this->soap->update_status_1c(
                $new_order['1c_id'],
                'Одобрено',
                $manager->name_1c,
                $new_order['amount'],
                $new_order['percent'],
                '',
                0,
                $new_order['period']
            );

            if ($updateStatus1CResult === 'OK') {
                return $updateStatus1CResult;
            }

            sleep(3);
        }

        return $updateStatus1CResult;
    }

    private function init_cross_organizations()
    {
        $cross2_enabled = $this->settings->cross2_enabled;
        foreach ($this->organizations->getList() as $org) {
            if (
                $this->settings->cross_organization_id  == $org->id ||
                (
                    $this->settings->cross_organization_id2 == $org->id && !empty($cross2_enabled)
                )
            ) {
                $this->cross_organizations[$org->id] = $org;
            }
        }
    }

    private function addCrossOrderTo1C(array $new_order)
    {
        $soap_zayavka_params = [
            'organization_id' => $new_order['organization_id'],
            'order_uid' => $new_order['order_uid'],
            'utm_source' => $new_order['utm_source'],
            'utm_medium' => $new_order['utm_medium'],
        ];

        $soap_zayavka = $this->soap->soap_repeat_zayavka(
            $new_order['amount'],
            $new_order['period'],
            $new_order['user_id'],
            $new_order['card_id'],
            NULL,
            $soap_zayavka_params
        );

        if (!empty($soap_zayavka->return->id_zayavka) && mb_strpos($soap_zayavka->return->id_zayavka, 'Не принято') === false) {
            return $soap_zayavka;
        }

        // Если 1С был недоступен, пробуем еще несколько раз создать заявку с задержкой
        for ($i = 0; $i < self::MAX_ATTEMPTS_TO_SEND_ORDER_TO_1C; $i++) {
            $soap_zayavka = $this->soap->soap_repeat_zayavka(
                $new_order['amount'],
                $new_order['period'],
                $new_order['user_id'],
                $new_order['card_id'],
                NULL,
                $soap_zayavka_params
            );

            if (!empty($soap_zayavka->return->id_zayavka) && mb_strpos($soap_zayavka->return->id_zayavka, 'Не принято') === false) {
                return $soap_zayavka;
            }

            sleep(3);
        }

        return $soap_zayavka;
    }

    /**
     * Cross_orders::check()
     *
     * по условиям спр
     *
     * первичка
     * 1 Если у клиента нет стоп-факторов аксинбки
     * 2 Если балл 0,25 и меньше по акси ИЛИ 500 и выше по скористе - то можно  одобрить кросс-выдачу.
     *
     * повторка
     * 1 Если у клиента нет стоп-факторов аксинбки
     * 2 Если балл 0,25 и меньше по акси ИЛИ 500 и выше по скористе
     * 3 Если клиент не ушёл ранее более чем на 7 дней в просрочку по предыдущим займам любой МКК.
     * 4 Если клиент не имеет действующей просрочки более 5- дней.
     * @return bool
     */
    private function canCreateCrossOrder(): bool
    {
        // 1. Если инстоллмент и ПК, пропускаем
//        if ($this->order->loan_type === 'IL' && (!empty($this->order->have_close_credits) || $this->order->first_loan == 0)) {
//            $this->saveErrorCreateCrossOrder((int)$this->order->id, CrossOrder::REASON_INSTALLMENT);
//            return false;
//        }

        $ignore_sites = [
            'akvariusmkk.ru',
            'soyaplace.ru',
        ];

        // 2. Если заявка с сайтов
        if (in_array($this->order->utm_term, $ignore_sites)) {
            $this->saveErrorCreateCrossOrder((int)$this->order->id, CrossOrder::REASON_IGNORED_SITE);
            return false;
        }

        // 3. Если НК и нельзя создавать кросс-ордер НК согласно настройкам
        if (empty($this->order->have_close_credits) && !$this->settings->cross_orders_nk_enabled) {
            $this->saveErrorCreateCrossOrder((int)$this->order->id, CrossOrder::REASON_NK_SETTING_DISABLED);
            return false;
        }

        $scoristaBall = $this->getLastScoristaBall();

        // 4. По родительской заявке нет успешного скоринга
        if (empty($scoristaBall)) {
            $this->saveErrorCreateCrossOrder((int)$this->order->id, CrossOrder::REASON_NO_SUCCESS_SCORING_WITH_SCORBALL);
            return false;
        }

        if (empty($this->order->have_close_credits)) {

            // 5. Скорбалл по родительской заявке НК ниже порога
            if ($scoristaBall < self::MIN_SCORISTA_BALL_NK) {
                $this->saveErrorCreateCrossOrder((int)$this->order->id, CrossOrder::REASON_LOW_SCORISTA_BALL_NK, ['scorista_ball' => $scoristaBall]);
                return false;
            }
        } else {

            // 5. Скорбалл по родительской заявке ПК ниже порога
            if ($scoristaBall < self::MIN_SCORISTA_BALL_PK) {
                $this->saveErrorCreateCrossOrder((int)$this->order->id, CrossOrder::REASON_LOW_SCORISTA_BALL_PK, ['scorista_ball' => $scoristaBall]);
                return false;
            }
        }

        return true;
    }

    private function getLastScoristaBall(): int
    {
        if ($this->order->utm_source === $this->orders::UTM_SOURCE_CRM_AUTO_APPROVE) {
            $necessaryScoringType = $this->scorings::TYPE_AXILINK;
        } else  {
            $necessaryScoringType = $this->scorings::TYPE_SCORISTA;
        }

        $scoring = $this->scorings->getLastScoring([
            'order_id' => (int)$this->order->id,
            'type' => $necessaryScoringType,
            'status' => $this->scorings::STATUS_COMPLETED,
            'success' => 1
        ]);

        if (!empty((int)$scoring->scorista_ball)) {
            return (int)$scoring->scorista_ball;
        }

        // После смены организации без КИ в ответе из акси нет балла скористы,
        // поэтому берем балл скористы из исходной заявки клиента
        $parentOrderId = $this->order_data->read((int)$this->order->id, $this->order_data::ORDER_ORG_SWITCH_PARENT_ORDER_ID);

        $this->logging(__METHOD__, '', 'Взята родительская заявка для получения балла скористы', ['order_id' => $this->order->id, 'parent_order_id' => $parentOrderId], self::LOG_FILE);

        if (!empty($parentOrderId)) {
            $scoring = $this->scorings->getLastScoring([
                'order_id' => $parentOrderId,
                'type' => $necessaryScoringType,
                'status' => $this->scorings::STATUS_COMPLETED,
                'success' => 1
            ]);

            if (!empty((int)$scoring->scorista_ball)) {
                return (int)$scoring->scorista_ball;
            }
        }

        return 0;
    }

    /**
     * Cross_orders::calc_cross_amount()
     * лимит 100% от суммы на аквариусе. Округляем в сторону увеличения до тысяч.
     * @return void
     */
    private function calc_cross_amount()
    {
        if ($this->order->loan_type == Orders::LOAN_TYPE_IL) {
            $this->cross_amount = Orders::IL_BASE_AMOUNT;
        } else {
            $this->cross_amount = min(ceil($this->order->amount * self::AMOUNT_COEF / 1000) * 1000, Orders::PDL_MAX_AMOUNT);
        }
    }

    /**
     * Создает записи о попытке создания кросс-ордера
     * @param $data
     * @return mixed
     */
    public function addCrossOrderRecord($data)
    {
        $query = $this->db->placehold("INSERT INTO __cross_orders SET ?%", $data);
        $this->db->query($query);
        $id = $this->db->insert_id();

        if (empty($id)) {
            $this->logging(__METHOD__, '', 'Ошибка при создании записи кросс-ордера', ['data' => $data, 'id' => $id], self::LOG_FILE);
        }

        return $id;
    }

    private function validateOrganization(int $organizationId): bool
    {
        $orders = $this->orders->get_orders([
            'user_id' => (int)$this->user->id
        ]);

        if (empty($orders)) {
            $this->saveErrorCreateCrossOrder((int)$this->order->id, CrossOrder::REASON_NO_ORDERS);
            return false;
        }

        // 6. Есть открытый кросс-ордер в данном МКК
        foreach ($orders as $order) {
            if ((int)$order->organization_id === $organizationId && !$this->orders->isOrderClosed($order)) {

                // 7. Если кросс-ордер одобрен, то отказываем по нему, чтобы создался новый,
                // иначе (если выдан) запрещаем создание кросс-ордера
                if ((int)$order->status === $this->orders::ORDER_STATUS_CRM_APPROVED) {
                    $this->logging(__METHOD__, '', 'Отказываем по одобренному кросс-ордеру, чтобы создать новый', ['order_id' => (int)$this->order->id, 'old_cross_order_id' => (int)$order->order_id], self::LOG_FILE);
                    $this->orders->rejectOrder($order, $this->reasons::REASON_END_TIME);
                } else {
                    $this->saveErrorCreateCrossOrder((int)$this->order->id, CrossOrder::REASON_HAS_ACTIVE_CROSS_ORDER, ['active_cross_order' => $order]);
                    return false;
                }
            }
        }

        // 8. Организация родительской заявки совпадает с организацией создаваемого кросс-ордера
        if ((int)$this->order->organization_id === $organizationId) {
            $this->saveErrorCreateCrossOrder((int)$this->order->id, CrossOrder::REASON_INCORRECT_ORGANIZATION);
            return false;
        }

        // 9. По родительской заявке в нужном МКК уже создавался кросс-ордер
        if ($all_orders = $this->orders->get_orders(['user_id' => $this->order->user_id])) {
            foreach ($all_orders as $order_item) {
                if ((int)$order_item->organization_id === $organizationId && $order_item->utm_medium == $this->order->id) {
                    $this->saveErrorCreateCrossOrder((int)$this->order->id, CrossOrder::REASON_ORDER_HAS_CROSS_ORDER);
                    return false;
                }
            }
        }

        return true;
    }

    public function getMainOrderContractNumber($cross_order)
    {
        $contract = $this->getMainOrderContract($cross_order);

        if ($contract) {
            return $contract->number;
        }

        return '';
    }

    public function getMainOrderContract($cross_order)
    {
        if ($cross_order->utm_source == 'cross_order' && !empty($cross_order->utm_medium)) {
            $order = $this->orders->get_order($cross_order->utm_medium);
            if ($order && !empty($order->contract_id)) {
                return $this->contracts->get_contract($order->contract_id);
            }
        }

        return false;
    }

    public function getMainOrder(stdClass $crossOrder): ?stdClass
    {
        if ($this->orders->isCrossOrder($crossOrder)) {
            return $this->orders->get_order($crossOrder->utm_medium) ?: null;
        }

        return null;
    }
}