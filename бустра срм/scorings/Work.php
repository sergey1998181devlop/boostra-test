<?php

class Work extends Simpla
{
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
                    $this->handleReject($scoring);
            }

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
            return $this->returnError("Пользователь не найден");

        foreach (["Workcity", "Workstreet", "Workhousing", "workplace"] as $required_field) {
            if (empty($user->$required_field))
                return $this->returnError("Неполные данные о работе");
        }

        if (!$this->verifyWorkPlace($user))
            return $this->returnCompleted(false, "Недопустимое место работы");

        if (!$this->verifyWorkAddress($user))
            return $this->returnCompleted(false, "Недопустимый адрес работы");

        return $this->returnCompleted(true, "Проверка пройдена");
    }

    /**
     * Проверка допустимости адреса работы
     * @param $user
     * @return bool
     */
    private function verifyWorkAddress($user)
    {
        $city = mb_strtolower($user->Workcity);

        // ЦБ
        if ($city != "москва")
            return true;

        $street = mb_strtolower($user->Workstreet);
        if (strpos($street, "неглинная") !== false || strpos($street, "неглиная") !== false) {
            // Неглинная улица, проверяем дом
            $housing = (int)$user->Workhousing ?? 0;
            $building = (int)$user->Workbuilding ?? 0;
            if ($housing == 12 || $building == 12)
                return false;
        }

        return true;
    }

    /**
     * Проверка допустимости места работы
     * @param $user
     * @return bool
     */
    private function verifyWorkPlace($user)
    {
        $place = mb_strtolower($user->workplace);

        // ЦБ
        foreach (['центральный банк', 'центробанк', 'цб'] as $restricted_place) {
            if (strpos($place, $restricted_place) !== false)
                return false;
        }

        // Военный
        if ($place == 'сво')
            return false;

        return true;
    }

    /**
     * Генерация `$update` ответа об ошибке
     * @param string $string_result
     * @return array
     */
    private function returnError(string $string_result)
    {
        return [
            'status' => $this->scorings::STATUS_ERROR,
            'success' => 0,
            'string_result' => $string_result
        ];
    }

    /**
     * Генерация `$update` ответа о завершении скоринга
     * @param bool $success
     * @param string $string_result
     * @return array
     */
    private function returnCompleted(bool $success, string $string_result)
    {
        return [
            'status' => $this->scorings::STATUS_COMPLETED,
            'success' => (int)$success,
            'string_result' => $string_result
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
            'reason_id' => $this->reasons::REASON_WORK_SCORING,
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

        $reason = $this->reasons->get_reason($this->reasons::REASON_WORK_SCORING);
        $this->soap->update_status_1c($order->id_1c, $this->orders::ORDER_1C_STATUS_REJECTED, $tech_manager->name_1c, 0, 1, $reason->admin_name);

        $this->soap->send_order_manager($order->id_1c, $tech_manager->name_1c);

        $this->soap->block_order_1c($order->id_1c, 0);

        // отправляем заявку на кредитного доктора
        $this->cdoctor->send_order($order->order_id);

        // Останавливаем выполнения других скорингов по этой заявки
        $this->scorings->stopOrderScorings($order->order_id, ['string_result' => 'Причина: скоринг ' . $scoring_type->title]);
    }
}