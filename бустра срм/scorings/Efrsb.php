<?php

require_once( __DIR__ . '/../api/Simpla.php');

class Efrsb extends Simpla
{
    public function run_scoring($scoring_id)
    {
        $scoringType = $this->scorings->get_type($this->scorings::TYPE_EFRSB);
        if (empty($scoringType->active)) {
            return $this->scorings->update_scoring($scoring_id, [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Проверка на стороне СРМ отключена',
                'end_date' => date('Y-m-d H:i:s')
            ]);
        }

        $scoring = $this->scorings->get_scoring($scoring_id);
        if (empty($scoring)) {
            return null;
        }

        // Пытаемся достать ИНН из заявки
        $order_id = (int)$scoring->order_id;
        if (!empty($order_id) && $order = $this->orders->get_order($order_id)) {
            $inn = $order->inn;
        }

        // Достаём ИНН из пользователя (Для автозаявок)
        if (empty($inn) && $user = $this->users->get_user((int)$scoring->user_id)) {
            $inn = $user->inn;
        }

        if (empty($inn)) {
            // Пройдены ли скоринги которые могут найти ИНН? Если нет, то ждём их завершения
            $inn_scorings = $this->scorings->get_scorings([
                'type' => [
                    $this->scorings::TYPE_FNS,
                    $this->scorings::TYPE_AXILINK_2
                ],
                'status' => [
                    $this->scorings::STATUS_NEW,
                    $this->scorings::STATUS_PROCESS
                ],
                'user_id' => $scoring->user_id,
            ]);
            if (empty($inn_scorings)) {
                $update = [
                    'status' => $this->scorings::STATUS_ERROR,
                    'string_result' => 'не найден ИНН'
                ];
            } else {
                return null;
            }
        } else {
            try {
                $this->logging(__METHOD__, '', 'До запроса в инфосферу', ['scoring_id' => $scoring_id], 'efrsb.txt');

                $response = $this->infosphere->check_efrsb(['inn' => $inn]);

                $this->logging(__METHOD__, '', 'После запроса в инфосферу', ['scoring_id' => $scoring_id, 'response' => $response], 'efrsb.txt');

                if (empty($response)) {
                    $update = [
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'При запросе произошла ошибка. Истекло время ожидания.'
                    ];
                } else {
                    $isBankrupt = (bool)$response['Source']['ResultsCount'];
                    $update = [
                        'status' => $this->scorings::STATUS_COMPLETED,
                        'body' => $inn,
                        'success' => (int)!$isBankrupt,
                        'string_result' => $isBankrupt ? 'банкротства найдены' : 'банкротства не найдены'
                    ];
                }
            } catch (Exception $e) {
                $update = [
                    'status' => $this->scorings::STATUS_ERROR,
                    'body' => $e->getMessage(),
                    'string_result' => 'При запросе произошла ошибка'
                ];
            }
        }

        $this->logging(__METHOD__, '', '', ['scoring_id' => $scoring_id, 'update' => $update], 'efrsb.txt');

        if (!empty($update)) {
            $update['end_date'] = date('Y-m-d H:i:s');
            $this->scorings->update_scoring($scoring_id, $update);

            if (empty($update['success']) && $update['status'] == $this->scorings::STATUS_COMPLETED) {
                // Отказ по заявке
                if (!empty($scoring->order_id)) {
                    $tech_manager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);
                    $order = $this->orders->get_order($scoring->order_id);

                    $update_order = [
                        'status' => 3,
                        'manager_id' => $tech_manager->id,
                        'reason_id' => 22, // Отказ по банкротству
                        'reject_date' => date('Y-m-d H:i:s'),
                    ];
                    $this->orders->update_order($scoring->order_id, $update_order);

                    $this->virtualCard->forUser($order->user_id)->delete();

	                $this->leadgid->reject_actions($scoring->order_id);
                    if (!empty($order->is_user_credit_doctor)) {
                        $this->soap->send_credit_doctor($order->id_1c);
                    }

                    $changeLogs = Helpers::getChangeLogs($update_order, $order);
                    $this->changelogs->add_changelog(array(
                        'manager_id' => $tech_manager->id,
                        'created' => date('Y-m-d H:i:s'),
                        'type' => 'status',
                        'old_values' => serialize($changeLogs['old']),
                        'new_values' => serialize($changeLogs['new']),
                        'order_id' => $scoring->order_id,
                        'user_id' => $order->user_id,
                    ));

                    $this->soap->update_status_1c($order->id_1c, $this->orders::ORDER_1C_STATUS_REJECTED, $tech_manager->name_1c);
                    $this->soap->send_order_manager($order->id_1c, $tech_manager->name_1c);

                    // отправляем заявку на кредитного доктора
                    $this->cdoctor->send_order($scoring->order_id);

                    $scoring_type = $this->scorings->get_type($this->scorings::TYPE_EFRSB);
                    $this->scorings->stopOrderScorings($scoring->order_id, ['string_result' => 'Причина: скоринг ' . $scoring_type->title]);
                }
            }
            return $update;
        }
    }
}