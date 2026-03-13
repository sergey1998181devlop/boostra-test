<?php
error_reporting(0);
ini_set('display_errors', 'Off');

require_once __DIR__ . '/ajaxController.php';

class ChooseCard extends ajaxController
{
    private const LOG_FILE = 'choose_card.txt';

    public function actions(): array
    {
        return [
            'choose_card' => [
                'card_id' => 'integer',
                'order_id' => 'integer',
            ],
            'choose_sbp' => [
                'sbp_account_id' => 'integer',
                'order_id' => 'integer',
            ],
            'choose_bank' => [
                'bank_id' => 'integer',
                'order_id' => 'integer',
            ],
            'choose_default_bank' => [
                'bank_id' => 'integer',
            ]
        ];
    }

    public function actionChooseCard(): array
    {
        $userCards = $this->getUserCards();
        $selectedCard = $this->getSelectedCard($userCards);

        $userOrders = $this->getUserOrders();
        $this->checkLoanForCard($userOrders, $selectedCard);
        $currentOrder = $this->getCurrentOrder($userOrders);

        if (empty($currentOrder->have_close_credits)) {
            $this->request->json_output(['error' => 'Выбор карты невозможен']);
        }

        $oldCard = $this->getOldCard($userCards, $currentOrder);

        // Если заявка открытая, то обновляем в ней card_id и card_type
        if (in_array($currentOrder->status_1c, $this->orders::IN_PROGRESS_STATUSES)) {
            $this->updateOrderCardId($currentOrder, (int)$selectedCard->id, $this->orders::CARD_TYPE_CARD);
            $this->updateContractCardId($currentOrder, (int)$selectedCard->id, $this->orders::CARD_TYPE_CARD);
            $this->addOrderLogging($currentOrder, $selectedCard, $oldCard);
        }

        $this->addClientLogging($currentOrder, $selectedCard);

        return ['result' => 'success'];
    }

    /**
     * Проставляем шаг карты
     * @return void
     */
    private function finishedCardAddedStep()
    {
        if (!empty($this->user->card_added)) {
            return;
        }

        $this->users->update_user($this->user->id, [
            'card_added' => 1,
            'card_added_date' => date('Y-m-d H:i:s')
        ]);
    }

    public function actionChooseSbp(): array
    {
        $canAddSbpAccount = $this->best2pay->canAddSbpAccount((int)$this->user->id);
        if (!$canAddSbpAccount) {
            $this->request->json_output(['error' => 'Возникла ошибка при добавлении счета. Обратитесь в поддержку']);
        }

        $userOrders = $this->getUserOrders();
        $currentOrder = $this->getCurrentOrder($userOrders);

        $sbpAccount = $this->getSelectedSbpAccount();
        $b2pBank = $this->getSbpBank((string)$sbpAccount->member_id);

        // Если заявка открытая, то обновляем в ней card_id и card_type
        if (in_array($currentOrder->status_1c, $this->orders::IN_PROGRESS_STATUSES)) {
            $this->updateOrderCardId($currentOrder, (int)$sbpAccount->id, $this->orders::CARD_TYPE_SBP);
            $this->updateContractCardId($currentOrder, (int)$sbpAccount->id, $this->orders::CARD_TYPE_SBP);
            $this->addOrderLoggingForSbp($currentOrder, $sbpAccount, $b2pBank);

            $this->resetChosenBank((int)$currentOrder->id);
        }

        $this->addClientLoggingForSbp($currentOrder, $sbpAccount, $b2pBank);

        $this->resetDefaultChosenBank((int)$currentOrder->user_id);

        return ['result' => 'success'];
    }

    /**
     * @param array $userCards
     * @return stdClass
     */
    private function getSelectedCard(array $userCards): stdClass
    {
        foreach ($userCards as $card) {
            if ((int)$card->id === (int)$this->data['card_id']) {
                return $card;
            }
        }

        $this->request->json_output(['error' => 'Карта не найдена']);
        exit();
    }

    /**
     * @return array
     */
    private function getUserOrders(): array
    {
        $userOrders = $this->orders->get_orders([
            'user_id' => (int)$this->user->id
        ]);

        if (empty($userOrders)) {
            $this->request->json_output(['error' => 'Заявки не найдены']);
        }

        return $userOrders;
    }

    /**
     * Проверяет, относится ли карта к базовой организации и был ли ранее на данную карту выдан заем
     *
     * @param array $userOrders
     * @param stdClass $selectedCard
     * @return void
     */
    private function checkLoanForCard(array $userOrders, stdClass $selectedCard): void
    {
        foreach ($userOrders as $order) {
            if (((int)$order->status === $this->orders::STATUS_CONFIRMED && $order->card_id === $selectedCard->id) || (int)$order->status === $this->orders::STATUS_APPROVED) {
                return;
            }
        }

        $this->request->json_output(['error' => 'Карта не соответствует требуемым условиям']);
    }

    /**
     * @param array $userOrders
     * @return stdClass
     */
    private function getCurrentOrder(array $userOrders): stdClass
    {
        $currentOrder = null;
        foreach ($userOrders as $order) {
            if ((int)$order->id === (int)$this->data['order_id']) {
                $currentOrder = $order;
                break;
            }
        }

        if ($currentOrder === null) {
            $this->request->json_output(['error' => 'Заявка не найдена']);
        }

        return $currentOrder;
    }

    /**
     * @param array $userCards
     * @param stdClass $currentOrder
     * @return stdClass|null
     */
    private function getOldCard(array $userCards, stdClass $currentOrder): ?stdClass
    {
        foreach ($userCards as $card) {
            if ($card->id === $currentOrder->card_id) {
                return $card;
            }
        }

        return null;
    }

    /**
     * @param stdClass $currentOrder
     * @param int $selectedCardId
     * @param string $cardType
     * @return void
     */
    private function updateOrderCardId(stdClass $currentOrder, int $selectedCardId, string $cardType): void
    {
//        if ((int)$currentOrder->status === $this->orders::STATUS_APPROVED) {
//            $this->updateApprovedOrder($currentOrder, $selectedCardId, $cardType);
//        } else {
        $this->orders->update_order($currentOrder->id, [
            'card_id' => $selectedCardId,
            'card_type' => $cardType
        ]);
//        }
    }

    private function updateApprovedOrder(stdClass $currentOrder, ?int $selectedCardId, string $cardType): void
    {
        $this->orders->update_order($currentOrder->id, [
            'card_id' => $selectedCardId,
            'card_type' => $cardType,
            'status' => $this->orders::STATUS_NEW,
            'manager_id' => null,
        ]);

        $this->order_data->set($currentOrder->id, 'is_new_card_linked', 1);

        $this->soap->update_status_1c(
            $currentOrder->id_1c,
            $this->orders::ORDER_UPDATE_1C_STATUS_CONSIDERED,
            $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID)->name_1c,
            $currentOrder->amount,
            $currentOrder->percent,
            'Привязка новой карты к займу',
            0,
            $currentOrder->selected_period ?: $currentOrder->period
        );
    }

    /**
     * @param stdClass $currentOrder
     * @param int $selectedCardId
     * @param string $cardType
     * @return void
     */
    private function updateContractCardId(stdClass $currentOrder, int $selectedCardId, string $cardType): void
    {
        if (!empty($currentOrder->contract_id)) {
            $this->contracts->update_contract($currentOrder->contract_id, [
                'card_id' => $selectedCardId,
                'card_type' => $cardType,
            ]);
        }
    }

    /**
     * Добавляет логирования на страницу клиента
     *
     * @param stdClass $currentOrder
     * @param stdClass $selectedCard
     * @return void
     */
    private function addClientLogging(stdClass $currentOrder, stdClass $selectedCard): void
    {
        $this->comments->add_comment([
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'user_id' => $selectedCard->user_id,
            'order_id' => $currentOrder->id,
            'block' => 'card_change',
            'text' => 'Клиент выбрал карту ' . $selectedCard->pan,
            'created' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Добавляет логирование на страницу заявки
     *
     * @param stdClass $currentOrder
     * @param stdClass $selectedCard
     * @param stdClass|null $oldCard
     * @return void
     */
    private function addOrderLogging(stdClass $currentOrder, stdClass $selectedCard, ?stdClass $oldCard): void
    {
        $this->changelogs->add_changelog([
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'order_id' => $currentOrder->id,
            'user_id' => $currentOrder->user_id,
            'type' => 'card_change',
            'old_values' => json_encode(['card_id' => (int)$currentOrder->card_id, 'card_type' => $currentOrder->card_type]),
            'new_values' => 'Номер карты: ' . $selectedCard->pan,
            'created' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array
     */
    private function getUserCards(): array
    {
        $userCards = $this->best2pay->get_cards([
            'deleted' => 0,
            'deleted_by_client' => 0,
            'user_id' => (int)$this->user->id,
        ]);

        if (empty($userCards)) {
            $this->request->json_output(['error' => 'Карты не найдены']);
        }

        if (count($userCards) <= 1) {
            $this->request->json_output(['error' => 'Прикреплена только 1 карта']);
        }

        return $userCards;
    }

    private function getSelectedSbpAccount(): stdClass
    {
        $sbpAccounts = $this->users->getSbpAccounts((int)$this->user->id);

        if (empty($sbpAccounts)) {
            $this->request->json_output(['error' => 'Нет привязанных счетов СБП']);
        }

        foreach ($sbpAccounts as $sbpAccount) {
            if ((int)$sbpAccount->id === (int)$this->data['sbp_account_id']) {
                return $sbpAccount;
            }
        }

        $this->request->json_output(['error' => 'Не найден привязанный счет СБП или он был удален']);
        exit;
    }

    private function getSbpBank(string $b2pBankId): ?stdClass
    {
        $b2pBanks = $this->b2p_bank_list->get([
            'id' => $b2pBankId
        ]);

        return $b2pBanks[0] ?? null;
    }

    private function addClientLoggingForSbp(stdClass $currentOrder, stdClass $sbpAccount, ?stdClass $b2pSbpBank): void
    {
        $this->comments->add_comment([
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'user_id' => $currentOrder->user_id,
            'order_id' => $currentOrder->id,
            'block' => 'choose_sbp',
            'text' => 'Клиент выбрал СБП счет в ' . ($b2pSbpBank->title ?? '') . ' (id счета - ' . $sbpAccount->id . ')',
            'created' => date('Y-m-d H:i:s'),
        ]);
    }

    private function addOrderLoggingForSbp(stdClass $currentOrder, stdClass $sbpAccount, ?stdClass $b2pSbpBank): void
    {
        $this->changelogs->add_changelog([
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'order_id' => $currentOrder->id,
            'user_id' => $currentOrder->user_id,
            'type' => 'choose_sbp',
            'old_values' => json_encode(['card_id' => (int)$currentOrder->card_id, 'card_type' => $currentOrder->card_type]),
            'new_values' => 'Клиент выбрал СБП счет в ' . ($b2pSbpBank->title ?? '') . ' (id счета - ' . $sbpAccount->id . ')',
            'created' => date('Y-m-d H:i:s'),
        ]);
    }

    private function resetChosenBank(int $orderId)
    {
        $this->order_data->set($orderId, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE);
    }

    private function resetDefaultChosenBank(int $userId)
    {
        $this->user_data->set($userId, $this->user_data::DEFAULT_BANK_ID_FOR_SBP_ISSUANCE);
    }

    public function actionChooseBank(): array
    {
        $orderId = (int)$this->data['order_id'];

        if (empty($orderId)) {
            $this->logging(__METHOD__, '', 'Не передан ID заявки', ['order_id' => $orderId], self::LOG_FILE);
            $this->request->json_output(['error' => 'Не найдена заявка']);
        }

        $order = $this->orders->get_order($orderId);

        if (empty($order)) {
            $this->logging(__METHOD__, '', 'Заявка не найдена', ['order_id' => $orderId, 'order' => $order], self::LOG_FILE);
            $this->request->json_output(['error' => 'Заявка не найдена']);
        }

        // Если заявка НЕ принадлежит клиенту ИЛИ НЕ автозаявка
        if (
            (int)$order->user_id !== (int)$this->user->id ||
            !$this->b2p_bank_list->canShowSbpBanks()
        ) {
            $this->logging(__METHOD__, '', 'Заявка не соответствует требуемым условиям', ['order' => $order], self::LOG_FILE);
            $this->request->json_output(['error' => 'Не удалось сохранить банк']);
        }

        // Если выбрано СБП И есть card_id
        if ($order->card_type === $this->orders::CARD_TYPE_SBP && !empty((int)$order->card_id)) {
            $this->logging(__METHOD__, '', 'СБП счет уже выбран', ['order' => $order], self::LOG_FILE);
            $this->request->json_output(['error' => 'СБП счет уже выбран']);
        }

        $newBank = $this->getBank((int)$this->data['bank_id']);
        if (empty($newBank)) {
            $this->request->json_output(['error' => 'Банк не найден']);
        }

        $this->saveDefaultBankId($newBank);

        $bankIdForSbpIssuance = (int)$this->order_data->read((int)$order->id, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE);
        $oldBank = $this->getBank($bankIdForSbpIssuance);

        $this->order_data->set((int)$order->id, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE, (int)$newBank->id);

        $this->orders->update_order((int)$order->id, ['card_type' => $this->orders::CARD_TYPE_SBP, 'card_id' => 0]);

        // Проверим шаг скористы, если есть проставим шаг
        if ($this->users->skipSelectCardStep($this->user)) {
            $this->finishedCardAddedStep();
        }

        $this->logging(__METHOD__, '', 'Успешно выбран банк', ['order' => $order, 'bank' => $newBank], self::LOG_FILE);

        $this->addOrderLoggingForChooseBank($order, $newBank, $oldBank);

        if ($cross_orders = $this->orders->get_cross_orders($order->id)) {
            foreach ($cross_orders as $co) {
                $this->orders->update_order((int)$co->id, [
                    'card_type' => $this->orders::CARD_TYPE_SBP,
                    'card_id' => 0
                ]);
                $this->order_data->set((int)$co->id, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE, (int)$newBank->id);
            }
        }

        return ['result' => 'success'];
    }


    private function addOrderLoggingForChooseBank(stdClass $order, stdClass $newBank, ?stdClass $oldBank)
    {
        $this->changelogs->add_changelog([
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'type' => 'card_change',
            'old_values' => json_encode(['card_id' => (int)$order->card_id, 'card_type' => $order->card_type, 'old_bank' => $oldBank->title ?? 'Не выбран'], JSON_UNESCAPED_UNICODE),
            'new_values' => 'Выбран банк для выплаты по СБП: ' . $newBank->title,
            'created' => date('Y-m-d H:i:s'),
        ]);
    }

    private function getBank(int $bankId): ?stdClass
    {
        if (empty($bankId)) {
            $this->logging(__METHOD__, '', 'Не передан ID банка', ['user' => $this->user, 'bank_id' => $bankId], self::LOG_FILE);
            return null;
        }

        $bank = $this->b2p_bank_list->getOne([
            'id' => $bankId,
            'has_sbp' => 1
        ]);

        if (empty($bank)) {
            $this->logging(__METHOD__, '', 'Банк не найден', ['user' => $this->user, 'bank_id' => $bankId, 'bank' => $bank], self::LOG_FILE);
            return null;
        }

        return $bank;
    }

    public function actionChooseDefaultBank(): array
    {
        $bank = $this->getBank((int)$this->data['bank_id']);
        if (empty($bank)) {
            $this->request->json_output(['error' => 'Банк не найден']);
        }

        $this->saveDefaultBankId($bank);

        $result = $this->repeatIssuanceNotIssuedOrders($bank);

        return ['result' => 'success', 'need_reload' => $result['need_reload']];
    }

    private function saveDefaultBankId(stdClass $bank)
    {
        $this->user_data->set((int)$this->user->id, $this->user_data::DEFAULT_BANK_ID_FOR_SBP_ISSUANCE, (int)$bank->id);

        $this->comments->add_comment([
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'user_id' => (int)$this->user->id,
            'order_id' => 0,
            'block' => 'card_change',
            'text' => 'Клиент изменил банк по умолчанию для выплаты по СБП: ' . $bank->title,
            'created' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Повторить выдачу с новым банком, если у клиента есть заявки, которые не удалось выдать ранее
     */
    private function repeatIssuanceNotIssuedOrders(stdClass $newBank): array
    {
        $result = ['need_reload' => false];

        $ordersToCheck = [];

        // Для возможности перевыдачи проверяется последняя заявка клиента
        $lastOrder = $this->orders->get_last_order((int)$this->user->id);

        if (empty($lastOrder)) {
            return $result;
        }

        $ordersToCheck[] = $lastOrder;

        // Если последняя заявка кросс-ордер, то проверяем также по его основной заявке
        if ($this->orders->isCrossOrder($lastOrder)) {
            $mainOrder = $this->orders->get_order($lastOrder->utm_medium);

            if (!empty($mainOrder)) {
                $ordersToCheck[] = $mainOrder;
            }
        }

        foreach ($ordersToCheck as $order) {
            if (!$this->orders->canRepeatIssuanceNotIssuedOrder($order)) {
                continue;
            }

            // 1. Старый банк для выдачи
            $bankIdForSbpIssuance = (int)$this->order_data->read((int)$order->id, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE);
            $oldBank = $this->getBank($bankIdForSbpIssuance);

            // 2. Устанавливаем новый банк в заявку
            $this->order_data->set((int)$order->id, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE, (int)$newBank->id);

            // 3. Отправляем заявку на перевыдачу
            $this->orders->update_order((int)$order->id, ['status' => $this->orders::STATUS_SIGNED, 'card_id' => 0, 'card_type' => $this->orders::CARD_TYPE_SBP]);

            // 4. Увеличиваем кол-во попыток перевыдач
            $repeatIssuanceCount = (int)$this->order_data->read((int)$order->id, $this->order_data::REPEAT_ISSUANCE_COUNT);
            $this->order_data->set((int)$order->id, $this->order_data::REPEAT_ISSUANCE_COUNT, ++$repeatIssuanceCount);

            // 5. Добавляем комментарий в заявку
            $this->addOrderLoggingForRepeatIssuance($order, $newBank, $oldBank);

            // 6. Логируем
            $this->logging(__METHOD__, '', 'Заявка отправлена на перевыдачу', ['order' => $order, 'new_bank' => $newBank, 'old_bank' => $oldBank], self::LOG_FILE);

            $result['need_reload'] = true;
        }

        return $result;
    }

    private function addOrderLoggingForRepeatIssuance(stdClass $order, stdClass $newBank, ?stdClass $oldBank)
    {
        $this->changelogs->add_changelog([
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'type' => 'repeat_issuance',
            'old_values' => json_encode(['card_id' => (int)$order->card_id, 'card_type' => $order->card_type, 'old_bank' => $oldBank->title ?? 'Не выбран'], JSON_UNESCAPED_UNICODE),
            'new_values' => 'Выбран банк для перевыдачи: ' . $newBank->title,
            'created' => date('Y-m-d H:i:s'),
        ]);
    }
}

new ChooseCard();