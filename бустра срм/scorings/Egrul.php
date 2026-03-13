<?php

/**
 * TODO: Запускать скоринг только для Юр.заявок
 * TODO: Добавить скоринг в отображение в срм
 * TODO (optional): Если скоринг отказал - скрывать ИП кнопку в течении Х времени, чтобы не жали ещё раз
 */

class Egrul extends Simpla
{
    const RESULT_SUCCESS = 'Проверка пройдена';
    const RESULT_UNSUCCESS = 'Проверка не пройдена';
    const RESULT_WAIT_FNS = 'Ждём ФНС, ИНН пока нет';
    const RESULT_ERROR_RESPONSE = 'Ошибка при запросе';
    const RESULT_ERROR_EMPTY_INN = 'Пустой ИНН';
    const RESULT_ERROR_EMPTY_USER = 'Клиент не найден';

    /** @var string[] Список полей для проверки статуса активности юр.лица */
    const FIELD_NAMES = [
        'OrgStatus', 'IPStatus'
    ];
    /** @var string[] Список значений подтверждающих статус активности юр.лица */
    const FIELD_VALUES = [
        'Действующий', 'Действующая', 'Действующее'
    ];

    public function run_scoring($scoring_id)
    {
        $scoring = $this->scorings->get_scoring($scoring_id);
        if (empty($scoring))
            return null;

        $result = $this->run($scoring);
        if (!empty($result)) {
            if (!empty($result['status'])) {
                // Если скоринг закончился - ставим дату окончания
                if ($result['status'] == $this->scorings::STATUS_COMPLETED || $result['status'] == $this->scorings::STATUS_ERROR)
                    $result['end_date'] = date('Y-m-d H:i:s');

                // Если закончился без ошибок, но не прошёл проверку - пытаемся отклонить заявку
                if ($result['status'] == $this->scorings::STATUS_COMPLETED && $result['success'] == 0)
                    $this->handleRejectCompanyOrder($scoring);
            }

            if (!empty($result['body']))
                $result['body'] = serialize((array)$result['body']);

            $this->generateCompanyOrder($scoring, $result);

            $this->scorings->update_scoring($scoring_id, $result);
        }
        return $result;
    }

    /**
     * Обработка скоринга
     * @param object $scoring
     * @return array
     */
    private function run(object $scoring)
    {
        $user = $this->users->get_user((int)$scoring->user_id);
        if (empty($user))
            return $this->returnError(self::RESULT_ERROR_EMPTY_USER);

        if (empty($user->inn)) {
            // ФНС скоринг мог не успеть получить ИНН, тогда надо его подождать
            $fns = $this->scorings->get_scorings([
                'type' => $this->scorings::TYPE_FNS,
                'status' => $this->scorings::STATUS_COMPLETED,
                'user_id' => $scoring->user_id,
            ]);
            if (empty($fns))
                return ['string_result' => self::RESULT_WAIT_FNS];
            else
                return $this->returnError(self::RESULT_ERROR_EMPTY_INN);
        }

        // Запрос в инфосферу
        try {
            $response = $this->Infosphere->check_egrul(['inn' => $user->inn]);
            if (!isset($response['Source']))
                return $this->returnError(self::RESULT_ERROR_RESPONSE, $user->inn);
        }
        catch (Exception $e) {
            return $this->returnError(self::RESULT_ERROR_RESPONSE, $e->getMessage());
        }

        // Разбор ответа
        $sources = $response['Source'];
        if (isset($sources['ResultsCount']))
            $sources = [$sources];

        $active_org = false;
        $body = [];

        foreach ($sources as $source) {
            if ((int)$source['ResultsCount'] == 0)
                continue;

            $records = $source['Record'];
            if (isset($records['Field']))
                $records = [$records];

            foreach ($records as $record) {
                $fields = $record['Field'];
                foreach ($fields as $field) {
                    if (in_array($field['FieldName'], self::FIELD_NAMES) &&
                        in_array($field['FieldValue'], self::FIELD_VALUES)) {
                        $active_org = true;
                        break;
                    }
                }
                $body[] = $fields;
            }
        }

        return [
            'status' => $this->scorings::STATUS_COMPLETED,
            'success' => (int)$active_org,
            'string_result' => $active_org ? self::RESULT_SUCCESS : self::RESULT_UNSUCCESS,
            'body' => $body
        ];
    }

    /**
     * Генерация `$update` ответа об ошибке
     * @param string $string_result
     * @param string|null $body
     * @return array
     */
    private function returnError(string $string_result, $body = null)
    {
        if (empty($body))
            return [
                'status' => $this->scorings::STATUS_ERROR,
                'success' => 0,
                'string_result' => $string_result
            ];

        return [
            'status' => $this->scorings::STATUS_ERROR,
            'success' => 0,
            'string_result' => $string_result,
            'body' => $body
        ];
    }

    /**
     * Делаем отказ по заявке, **если скоринг может делать отказы**
     * @param $scoring
     * @return void
     */
    private function handleReject($scoring)
    {
        $scoring_type = $this->scorings->get_type($scoring->type);
        if ($scoring_type->negative_action != 'stop')
            return;

        $order = $this->orders->get_order($scoring->order_id);
        if (empty($order))
            return;

        // техаккаунт System
        $tech_manager = $this->managers->get_manager(50);

        $update_order = [
            'status' => $this->orders::ORDER_STATUS_CRM_REJECT,
            'manager_id' => $tech_manager->id,
            'reason_id' => $this->reasons::REASON_EGRUL_SCORING,
            'reject_date' => date('Y-m-d H:i:s'),
        ];
        $this->orders->update_order($scoring->order_id, $update_order);
        $this->leadgid->reject_actions($scoring->order_id);
        if (!empty($order->is_user_credit_doctor)) {
            $this->soap1c->send_credit_doctor($order->id_1c);
        }

        $changeLogs = Helpers::getChangeLogs($update_order, $order);

        $this->changelogs->add_changelog(array(
            'manager_id' => $tech_manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status',
            'old_values' => serialize($changeLogs['old']),
            'new_values' => serialize($changeLogs['new']),
            'order_id' => $order->order_id,
            'user_id' => $order->user_id,
        ));

        $reason = $this->reasons->get_reason($this->reasons::REASON_EGRUL_SCORING);
        $this->soap->update_status_1c($order->id_1c, $this->orders::ORDER_1C_STATUS_REJECTED, $tech_manager->name_1c, 0, 1, $reason->admin_name);

        $this->soap->send_order_manager($order->id_1c, $tech_manager->name_1c);

        $this->soap->block_order_1c($order->id_1c, 0);

        // отправляем заявку на кредитного доктора
        $this->cdoctor->send_order($order->order_id);

        // Останавливаем выполнения других скорингов по этой заявки
        $this->scorings->stopOrderScorings($order->order_id, ['string_result' => 'Причина: скоринг ' . $scoring_type->title]);
    }

    /**
     * Делаем отказ по заявке из таблицы **s_company_orders**
     * @param $scoring
     * @return void
     */
    public function handleRejectCompanyOrder($scoring)
    {
        $this->company_orders->updateItem($scoring->scorista_id, [
            'status' => $this->company_orders::STATUS_REJECT,
        ]);
    }

    /**
     * Проверяет и создает заявку для ИП
     * @param $scoring
     * @param array $result
     * @return void
     */
    private function generateCompanyOrder($scoring, array $result)
    {
        $company_order_id = (int)$scoring->scorista_id;
        $companyOrder = $this->company_orders->getItem($company_order_id);
        if (empty($companyOrder)) {
            return;
        }

        $status = !empty($result['success']) ? CompanyOrders::STATUS_APPROVED : CompanyOrders::STATUS_REJECT;
        $this->company_orders->updateItem($company_order_id, compact('status'));

//        if (!empty($result['success'])) {
//            $user = $this->users->get_user((int)$companyOrder->user_id);
//
//            // отправляем заявку в 1с
//            $params = [
//                'utm_source' => $this->orders::UTM_SOURCE_COMPANY_FORM,
//                'utm_medium' => 'CRM',
//                'organization_id' => $this->organizations::VIPZAIM_ID,
//                'order_uid' => exec($this->config->root_dir.'generic/uidgen'),
//                'utm_campaign' => $company_order_id,
//            ];
//
//            $soap_zayavka = $this->soap->soap_repeat_zayavka($companyOrder->amount, 90, $companyOrder->user_id, '', null, $params);
//            if (!empty($soap_zayavka->return->id_zayavka) && $soap_zayavka->return->id_zayavka !== 'Не принято') {
//                $params['1c_id'] = $soap_zayavka->return->id_zayavka;
//            }
//
//            // создаем в базе order
//            $date_create = date('Y-m-d H:i:s');
//            $order = array_merge($params, [
//                'card_id' => '',
//                'amount' => $companyOrder->amount,
//                'period' => 90,
//                'user_id' => $companyOrder->user_id,
//                'ip' => '127.0.0.1',
//                'first_loan' => 0,
//                'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
//                'is_user_credit_doctor' => 0,
//                'date' => $date_create,
//                'accept_date' => $date_create,
//                'local_time' => $date_create,
//                'utm_content' => '',
//                'utm_term' => '',
//                'webmaster_id' => '',
//                'click_hash' => '',
//                'percent' => 200,
//                'complete' => 1,
//                'status' => $this->orders::ORDER_STATUS_CRM_NEW,
//                'have_close_credits' => 1,
//                'b2p' => $user->b2p,
//            ]);
//
//            $order_id = $this->orders->add_order($order);
//            $this->scorings->update_scoring((int)$scoring->id, ['order_id' => $order_id]);

            // Создаем поручение на перечисление микрозайма
//            $this->documents->create_document(
//                [
//                    'type' => $this->documents::PREVIEW_PORUCHENIE_NA_PERECHISLENIE_MIKROZAJMA,
//                    'user_id' => $companyOrder->user_id,
//                    'order_id' => $order_id,
//                    'params' => $this->documents->getCompanyOrderAssignmentParams($company_order_id),
//                ]
//            );
        // Отключаем дополнительные услуги
//            $this->order_data->set($order_id, OrderData::ADDITIONAL_SERVICE_TV_MED, 1);
//            $this->order_data->set($order_id, OrderData::ADDITIONAL_SERVICE_MULTIPOLIS, 1);
//            $this->order_data->set($order_id, OrderData::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT, 1);
//            $this->order_data->set($order_id, OrderData::ADDITIONAL_SERVICE_REPAYMENT, 1);
//        }
    }
}