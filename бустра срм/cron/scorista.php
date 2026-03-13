<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once __DIR__.'/../api/Simpla.php';
require_once __DIR__ . '/../scorings/BoostraPTI.php';

class ScoristaCron extends Simpla
{
    // время жизни(кеша) платных скорингов
    private $toll_scoring_lifetime = 2592000; // 30 дней
    
    public function __construct()
    {
    	parent::__construct();
    }
    
    public function run()
    {
        
        // скоринги scorista загруженные из 1с
        $i = 30;
        while ($i > 0)
        {
            if ($scoring = $this->scorings->get_import_scoring($this->scorings::TYPE_SCORISTA, true))
            {
                if (!empty($scoring->order_id)) {
                    $order = $this->orders->get_order($scoring->order_id);
                }

                $this->scorings->update_scoring($scoring->id, array(
                    'status' => $this->scorings::STATUS_WAIT,
                ));
                
                $result = $this->scorista->get_result($scoring->scorista_id, $order->organization_id);
                
                if ($result->status == 'DONE')
                {
                    $user = $this->users->get_user($scoring->user_id);
                    if (isset($result->data->equifaxCH)) {
                        $zip = new ZipArchive();
                        $zip->open($this->config->root_dir . 'files/equifax_zipped/' . $scoring->scorista_id . '.zip', ZipArchive::CREATE);
                        $zip->addFromString($scoring->scorista_id . '.xml', base64_decode($result->data->equifaxCH, true));
                        $zip->setCompressionIndex(0, ZipArchive::CM_LZMA, 9);
                        $zip->close();
                        unset($result->data->equifaxCH);
                    }

                    $success = (int)($result->data->decision->decisionName === $this->scorings::SCORISTA_STATUS_RESULT_SUCCESS);
                    $string_result = !empty($success) ? 'Проверка пройдена' : 'Проверка не пройдена';

                    $update = array(
                        'status' => $this->scorings::STATUS_COMPLETED,
                        'body' => json_encode($result->data),
                        'success' => $success,
                        'scorista_status' => $result->data->decision->decisionName,
                        'scorista_ball' => $result->data->additional->summary->score,
                        'string_result' => $string_result,
                        'end_date' => date('Y-m-d H:i:s'),
                    );

                    // добавляет проверку на купленный рейтинг и пустое значение в документе
                    if (!empty($success)) {
                        $document_credit_rating = $this->documents->getLastDocumentCreditRating((int)$scoring->user_id);
                        if (!empty($document_credit_rating) && !isset($document_credit_rating->params->score)) {
                            $document_credit_rating->params['score'] = $result->data->additional->summary->score;
                            $this->documents->update_document($document_credit_rating->id, ['params' => $document_credit_rating->params]);
                        }
                        $this->orders->priorApprove($scoring->order_id);
//                        if (!empty($order) && empty($order->complete)) {
//                            $amount = min($result->data->additional->decisionSum ?? $order->amount + 1000, 30000);
//                            $this->sendSmsMissing($user, $order, $update['scorista_ball'], $amount);
//                        }
                    }

                    $this->scorings->update_scoring($scoring->id, $update);
    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($update);echo '</pre><hr />';
    
                    // передаем в 1с agrid скористы
                    if (!empty($scoring->order_id))
                    {
                        if (!isset($order)) {
                            $order = $this->orders->get_order($scoring->order_id);
                        }

                        $calculatorPTI = new BoostraPTI($order);
                        $calculatorPTI->setSource();
                        $calculatorPTI->toggleDetails(true);
                        $dataPTI = $calculatorPTI->getPTIData();
                        $this->orders->update_order($scoring->order_id, [
                            'scorista_ball' => $result->data->additional->summary->score,
                            'pti_order' => $dataPTI['rosstat_pti'] ?? 0
                        ]);
                        $this->soap->send_scorista_id($user->UID, $order->id_1c, $scoring->scorista_id, $order->organization_id);
                    }
    
                    if (!empty($result->data->additional->phonesBRS))
                    {
                        $comm = $result->data->additional->phonesBRS->description.PHP_EOL;
                        foreach ($result->data->additional->phonesBRS->result as $item)
                        {
                            $item = (array)$item;
                            $comm .= $item['actuality-date'].' '.$item['phone-number'].PHP_EOL;
                        }
                        if (!empty($comm))
                        {
                            $this->comments->add_comment(array(
                                'manager_id' => 0,
                                'user_id' => $scoring->user_id,
                                'order_id' => $scoring->order_id,
                                'block' => 'scorista',
                                'text' => $comm,
                                'created' => date('Y-m-d H:i:s'),
                            ));
                        }
                    }

                    if (!empty($scoring->order_id) && !empty($success)) {
                        // Скориста одобрила заявку
                        $this->finkarta_api->addScoring($scoring->user_id, $scoring->order_id);
                        $this->scorings->tryAddScoristaAndAxi($scoring->order_id);
                        $order = $this->orders->get_order($scoring->order_id);

                        // Ищем настройку для корректировки одобренной скористы
                        if ($this->leadgidScorista->isEnabled() && !$this->leadgidScorista->hasStopFactor($result->data))
                            $leadgid = $this->leadgidScorista->getByOrder($order, $this->leadgidScorista::TYPE_APPROVE);
                        else
                            $leadgid = null;

                        if (!empty($leadgid) && $order->status == $this->orders::ORDER_STATUS_CRM_NEW) {
                            if ($leadgid->amount == 0) {
                                // Принудительный отказ по заявке
                                $this->rejectOrderByLeadgid($order, $leadgid);
                            }
                            elseif ($this->scorings->isScoristaAllowed($order)) {
                                // Корректируем сумму
                                $update = [
                                    'amount' => $leadgid->amount
                                ];
                                $update_installment = $this->installments->check_installment($scoring->id);
                                if (empty($update_installment)) {
                                    $update = $this->installments->update_empty_installment($update);
                                }
                                $update = array_merge($update, $update_installment);

                                $this->orders->update_order($scoring->order_id, $update);
                            }

                            // Отмечаем заявку для статистики
                            $this->leadgidScorista->markOrder($scoring->order_id, $this->leadgidScorista::TYPE_APPROVE);
                        }
                        else {
                            // Ищем настройку для увеличения одобренной суммы
                            if ($this->approve_amount_settings->isEnabled())
                                $amount_setting = $this->approve_amount_settings->getByOrder($order);
                            else
                                $amount_setting = null;

                            if (!empty($amount_setting) && in_array($order->status, [
                                    $this->orders::ORDER_STATUS_CRM_NEW,
                                    $this->orders::ORDER_STATUS_CRM_APPROVED
                                ])) {
                                // Увеличиваем одобренную сумму
                                if (!empty($result->data->additional->decisionSum) && $this->scorings->isScoristaAllowed($order)) {
                                    $order_amount = $result->data->additional->decisionSum;
                                    $increased_amount = $order_amount + $amount_setting->amount;
                                    $amount_limit = empty($order->have_close_credits) ? 15000 : 30000;
                                    // Сумма не должна выходить за лимит
                                    if ($increased_amount > $amount_limit)
                                        $increased_amount = $amount_limit;

                                    $update = [
                                        // Увеличиваем сумму которую может взять клиент
                                        'approve_amount' => $increased_amount,
                                        // И ставим в калькуляторе макс.сумму по-умолчанию, чтобы он не пропустил её
                                        'amount' => $increased_amount
                                    ];
                                    $update_installment = $this->installments->check_installment($scoring->id);
                                    if (empty($update_installment)) {
                                        $update = $this->installments->update_empty_installment($update);
                                    }
                                    $update = array_merge($update, $update_installment);
                                    
                                    $this->orders->update_order($scoring->order_id, $update);

                                    // Отмечаем заявку для статистики
                                    $this->approve_amount_settings->markOrder(
                                        $scoring->order_id,
                                        ($increased_amount - $order_amount), // На сколько увеличили на самом деле
                                        $amount_setting->amount              // На сколько надо было увеличить по настройке
                                    );
                                }
                            }
                        }

                        // Проверка на зависящие от скористы стоп-факторы акси
                        $failed_axi = $this->scorings->getLastScoring([
                            'order_id' => $scoring->order_id,
                            'type' => $this->scorings::TYPE_AXILINK_2,
                            'status' => $this->scorings::STATUS_COMPLETED,
                            'success' => 0,
                        ]);
                        if (!empty($failed_axi) && $axi_body = (array)$this->scorings->get_body_by_type($failed_axi)) {
                            $axi_reject_reason = $this->dbrainAxi->getRejectReason($scoring->user_id, $axi_body['message'] ?? '');
                            if (!empty($axi_reject_reason)) {
                                // Есть стоп-фактор
                                $order = $this->orders->get_order($scoring->order_id);
                                if ($order->status == $this->orders::ORDER_STATUS_CRM_NEW && $this->scorings->isScoristaAllowed($order)) {
                                    // Отказ по заявке
                                    $tech_manager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);

                                    $update_order = [
                                        'status' => 3,
                                        'manager_id' => $tech_manager->id,
                                        'reason_id' => $axi_reject_reason,
                                        'reject_date' => date('Y-m-d H:i:s'),
                                    ];
                                    $this->orders->update_order($scoring->order_id, $update_order);

                                    $this->leadgid->reject_actions($scoring->order_id);

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

                                    $reason = $this->reasons->get_reason($axi_reject_reason);
                                    $this->soap->update_status_1c($order->id_1c, $this->orders::ORDER_1C_STATUS_REJECTED, $tech_manager->name_1c, 0, 1, $reason->admin_name);
                                }
                            }
                        }
                    }
                    else if (!empty($scoring->order_id) && empty($success)) {
                        // Скориста отказала по заявке
                        $order = $this->orders->get_order($scoring->order_id);

                        // Ищем настройку для принудительного одобрения
                        if ($this->leadgidScorista->isEnabled() && !$this->leadgidScorista->hasStopFactor($result->data))
                            $leadgid = $this->leadgidScorista->getByOrder($order);
                        else
                            $leadgid = null;

                        if (!empty($leadgid) && $order->status == $this->orders::ORDER_STATUS_CRM_NEW) {
                            if ($leadgid->amount == 0) {
                                // Принудительный отказ по заявке
                                $this->rejectOrderByLeadgid($order, $leadgid);
                            }
                            elseif ($this->scorings->isScoristaAllowed($order)) {
                                // Принудительно одобряем скористу
                                $this->scorings->update_scoring($scoring->id, [
                                    'success' => 1,
                                    'scorista_status' => $this->scorings::SCORISTA_STATUS_RESULT_SUCCESS,
                                    'string_result' => 'Проверка пройдена',
                                ]);

                                $update = [
                                    'amount' => $leadgid->amount
                                ];
                                $update_installment = $this->installments->check_installment($scoring->id);
                                if (empty($update_installment)) {
                                    $update = $this->installments->update_empty_installment($update);
                                }
                                $update = array_merge($update, $update_installment);
                                
                                $this->orders->update_order($scoring->order_id, $update);

                                $this->finkarta_api->addScoring($scoring->user_id, $scoring->order_id);
                                $this->scorings->tryAddScoristaAndAxi($scoring->order_id);
                            }

                            // Отмечаем заявку для статистики
                            $this->leadgidScorista->markOrder($scoring->order_id, $this->leadgidScorista::TYPE_REJECT, $leadgid->id, $order->scorista_ball);
                        }

                        if (empty($leadgid) && !in_array($order->status, [9, 10, 12]) && $this->scorings->isScoristaAllowed($order)) {
                            // Если займ не выдан ставим отказ

                            // техаккаунт System
                            $tech_manager = $this->managers->get_manager(50);

                            $update_order = array(
                                'status' => 3,
                                'manager_id' => $tech_manager->id,
                                'reason_id' => 5, // Отказ Скористы
                                'reject_date' => date('Y-m-d H:i:s'),

                            );
                            $this->orders->update_order($scoring->order_id, $update_order);
                            $this->leadgid->reject_actions($scoring->order_id);

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
                            $reason = $this->reasons->get_reason(5);
                            $this->soap->update_status_1c($order->id_1c, $this->orders::ORDER_1C_STATUS_REJECTED, $tech_manager->name_1c, 0, 1, $reason->admin_name);

                            if (!empty($order->is_user_credit_doctor))
                                $this->soap1c->send_credit_doctor($order->id_1c);

                            $this->soap->send_order_manager($order->id_1c, $tech_manager->name_1c);

                            $resp = $this->soap->block_order_1c($order->id_1c, 0);

                            // отправляем заявку на кредитного доктора
                            $this->cdoctor->send_order($order->order_id);

                            // Останавливаем выполнения других скорингов по этой заявки
                            $update['status'] = $this->scorings::STATUS_STOPPED;

                            $scoring_type = $this->scorings->get_type($this->scorings::TYPE_SCORISTA);
                            $this->scorings->stopOrderScorings($order->order_id, ['string_result' => 'Причина: скоринг ' . $scoring_type->title]);
                        }
                    }
                }
                elseif (is_null($result))
                {
                    $update = array(
                        'status' => $this->scorings::STATUS_ERROR,
                        'body' => '',
                        'string_result' => 'Не возможно прочитать ответ',
                        'end_date' => date('Y-m-d H:i:s'),
                    );
                    $this->scorings->update_scoring($scoring->id, $update);
                }
                elseif ($result->status == 'ERROR')
                {
                    $update = array(
                        'status' => $this->scorings::STATUS_ERROR,
                        'body' => json_encode($result),
                        'string_result' => 'Ошибка скористы',
                        'end_date' => date('Y-m-d H:i:s'),
                    );
                    $this->scorings->update_scoring($scoring->id, $update);
                }
                elseif (!empty($result->error))
                {
                    $update = array(
                        'status' => $this->scorings::STATUS_ERROR,
                        'body' => json_encode($result),
                        'string_result' => 'Ошибка скористы',
                        'end_date' => date('Y-m-d H:i:s'),
                    );
                    $this->scorings->update_scoring($scoring->id, $update);
                }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($result->data->additional->phonesBRS);echo '</pre><hr />';                
                
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($scoring);echo '</pre><hr />';          
            }                  

            $i--;
        }
        
                
        // новые скоринги
        $i = 30;
        $scoring = 1;
        while ($i > 0 && !empty($scoring))
        {
            if ($scoring = $this->scorings->get_new_scoring(array($this->scorings::TYPE_SCORISTA)))
            {
                // Для обработки получаем последний скоринг скористы по заявке, так как возможны дубликаты
                $scoring = $this->scorings->getLastScoring([
                    'order_id' => $scoring->order_id,
                    'type' => $this->scorings::TYPE_SCORISTA,
                    'status' => $this->scorings::STATUS_NEW
                ]);

                if (empty($scoring)) {
                    continue;
                }

                $this->scorings->update_scoring($scoring->id, array(
                    'status' => $this->scorings::STATUS_PROCESS,
                    'start_date' => date('Y-m-d H:i:s')
                ));

                $scoringType = $this->scorings->get_type((int)$scoring->type);
                $classname = $scoringType->name;
                $scoring_result = $this->{$classname}->run_scoring($scoring->id);

                // Останавливаем дубликаты скористы по этой заявке
                $this->scorings->stopOrderScoringsByType($scoring->order_id, ['string_result' => 'Дубликат'], Scorings::TYPE_SCORISTA, $scoring->id);
            }
            $i--;
        }
        
        $query = $this->db->placehold("
            UPDATE s_scorings SET status = ?
            WHERE status = ? and type = ?
        ", $this->scorings::STATUS_IMPORT, $this->scorings::STATUS_WAIT, $this->scorings::TYPE_SCORISTA);
        $this->db->query($query);
    }

    /**
     * Принудительный отказ по заявке из-за настройки лидгена
     * @param stdClass $order
     * @param stdClass $leadgid
     * @return void
     */
    function rejectOrderByLeadgid($order, $leadgid)
    {
        if (!$this->scorings->isScoristaAllowed($order))
            return;

        $system_manager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);
        $update_order = [
            'status' => $this->orders::ORDER_STATUS_CRM_REJECT,
            'manager_id' => $system_manager->id,
            'reason_id' => 5, // Отказ Скористы
            'reject_date' => date('Y-m-d H:i:s'),
        ];

        $this->orders->update_order($order->order_id, $update_order);
        $this->leadgid->reject_actions($order->order_id);

        $changeLogs = Helpers::getChangeLogs($update_order, $order);
        $this->changelogs->add_changelog(array(
            'manager_id' => $system_manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status',
            'old_values' => serialize($changeLogs['old']),
            'new_values' => serialize($changeLogs['new']),
            'order_id' => $order->order_id,
            'user_id' => $order->user_id,
        ));

        $reason = $this->reasons->get_reason(5);
        $this->soap->update_status_1c($order->id_1c, $this->orders::ORDER_1C_STATUS_REJECTED, $system_manager->name_1c, 0, 1, $reason->admin_name);

        if (!empty($order->is_user_credit_doctor))
            $this->soap1c->send_credit_doctor($order->id_1c);

        $this->soap->send_order_manager($order->id_1c, $system_manager->name_1c);
        $this->soap->block_order_1c($order->id_1c, 0);

        // отправляем заявку на кредитного доктора
        $this->cdoctor->send_order($order->order_id);

        // Останавливаем другие скоринги
        $scoring_type = $this->scorings->get_type($this->scorings::TYPE_SCORISTA);
        $this->scorings->stopOrderScorings($order->order_id, ['string_result' => 'Причина: скоринг ' . $scoring_type->title]);

        // Помечаем заявку как отказанную настройкой лидгена
        $this->leadgidScorista->markOrderAsRejected($order->order_id, $leadgid->id);
    }

    /**
     * Отправляет смс после одобрения скористы новым клиентам
     * @param object $user
     * @param object $order
     * @param int $scorista_ball
     * @param int $amount
     * @return void
     */
    private function sendSmsMissing(object $user, object $order, int $scorista_ball, int $amount)
    {
        $sms_template = '%s, ваш скор балл %d. Вам одобрено %d. Закончите анкету и получите деньги ' . $this->config->main_domain . '/s/0117';
        $message = sprintf($sms_template, $user->firstname, $scorista_ball, $amount);
        $resp = $this->smssender->send_sms($user->phone_mobile, $message, $user->site_id);

        $this->sms->add_message(
            [
                'user_id' => $user->id,
                'order_id' => $order->order_id,
                'phone' => $user->phone_mobile,
                'message' => $message,
                'created' => date('Y-m-d H:i:s'),
                'send_status' => $resp[1] ?? null,
                'delivery_status' => '',
                'send_id' => $resp[0] ?? null,
                'type' => $this->smssender::TYPE_AFTER_APPROVE_SCORISTA,
            ]
        );
    }
}

$cron = new ScoristaCron();
$cron->run();
