<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require_once __DIR__.'/../../api/Simpla.php';
require_once __DIR__ . '/../../scorings/BoostraPTI.php';

class ScoristaImportCron_mt extends Simpla
{
    private const LOG_FILE = 'ScoristaImportCron_mt.txt';

	public const LOCKER_NAME = self::class;
	public const WORKERS_COUNT = 5;
	public const SLEEP_SECONDS = 5;
	public const SUSPEND_SECONDS = 5;
	
	private $worker_id  = '';
	private $is_reverse = false;
	private $suspended  = [];

    public function __construct()
    {
    	parent::__construct();
    }

    public function run()
    {
		$db = $this->db;
		$const = 'constant';
        $sc_type = [
            'id' => $this->scorings::TYPE_SCORISTA,
            'lock_name' => 'IMPORT_SCORISTA',
        ];
		$case_block = array_map(fn($idx) => "WHEN GET_LOCK('{$const('static::LOCKER_NAME')}_{$idx}', 0) THEN '{$idx}'"
											. " WHEN GET_LOCK('{$const('static::LOCKER_NAME')}_!{$idx}', 0) THEN '!{$idx}'"
								, array_keys(array_fill(1, static::WORKERS_COUNT, 0)));

		while(true) {
			$db->query("SELECT IS_USED_LOCK('{$const('static::LOCKER_NAME')}_EXIT') stop_all_workers");
			if($db->result('stop_all_workers')) {
				break;
			}
			$this->suspended = array_filter($this->suspended, function($_service) use ($db) {
				if($_service['last_check']->diff(new DateTime)->s >= static::SUSPEND_SECONDS) {
					$db->query("DO RELEASE_LOCK('{$_service['locker_id']}')");
					return false;
				}
				return true;
			});
			if($this->worker_id) {
				$query = "SELECT GET_LOCK('{$const('static::LOCKER_NAME')}_{$this->worker_id}', 0) lock_is_valid";
				$db->query($query);
				if(!$db->result('lock_is_valid')) {
					$this->worker_id = '';
				}
			}
			if(!$this->worker_id) {
				$query = $db->placehold('SELECT CASE ' . implode(' ', $case_block) . ' END locker');
				$db->query($query);
				if(!($this->worker_id = $db->result('locker'))) {
					break;
				}
				$this->is_reverse = strpos($this->worker_id, '!') !== false;
				$this->logging(__METHOD__, '', '', 'Начало работы крона ' . $this->worker_id, self::LOG_FILE);
			}
			
			$suspended = [0, ...array_map(fn($item) => $item['id'], $this->suspended)];
            $scoring = $this->scorings->get_import_scoring_mt($sc_type, $suspended, $this->is_reverse);
			
			if($scoring) {
				//run service
                $result = $this->handleImportedScoring($scoring);
                if ($result == $this->scorings::STATUS_IMPORT) {
                    $this->suspended[] = [
                        'id' => $scoring->id,
                        'locker_id' => $scoring->locker_id,
                        'last_check' => (new DateTime('now')),
                    ];
                } else {
                    $db->query("DO RELEASE_LOCK('{$scoring->locker_id}')");
                }
			} else {
				sleep(static::SLEEP_SECONDS);
			}
		}
    }

    public function handleImportedScoring($scoring)
    {
        $order  = $this->orders->get_order($scoring->order_id);
        $import_result = $this->scorings::STATUS_IMPORT;

        $scoristaSource = $this->order_data->read($scoring->order_id, $this->order_data::SCORISTA_SOURCE);
        if (isset($scoristaSource) && $scoristaSource === 'aksi') {
            // Скориста импортирована из акси, ответ записан в body скористы, КИ уже сохранена
            $result = $this->scorings->get_body_by_type($scoring);
        }

        if (empty($result)) {
            // Скориста проводилась в СРМ, теперь запрашиваем ответ
            $result = $this->scorista->get_result($scoring->scorista_id, $order->organization_id);

            if ($scoristaSource === 'aksi') {
                // По акси пришёл пустой ответ скористы
                $this->order_data->set($scoring->order_id, $this->order_data::SCORISTA_SOURCE, 'aksi_empty_body');
            }
        }

        if ($result->status == 'DONE') {
            $user = $this->users->get_user($scoring->user_id);
            if (isset($result->data->equifaxCH)) {
                $this->scorista->saveCreditHistory($scoring->scorista_id, $result->data->equifaxCH);
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
                if (!empty($order) && empty($order->complete)) {
                    $this->sendSmsMissing($user, $order, $update['scorista_ball']);
                }
            }

            $this->scorings->update_scoring($scoring->id, $update);
            $import_result = $this->scorings::STATUS_COMPLETED;

            // передаем в 1с agrid скористы
            if (!empty($scoring->order_id)) {
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

            if (!empty($result->data->additional->phonesBRS)) {
                $comm = $result->data->additional->phonesBRS->description.PHP_EOL;
                foreach ($result->data->additional->phonesBRS->result as $item) {
                    $item = (array)$item;
                    $comm .= $item['actuality-date'].' '.$item['phone-number'].PHP_EOL;
                }
                if (!empty($comm)) {
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
                //$this->scorings->tryAddScoristaAndAxi($scoring->order_id);
                $order = $this->orders->get_order($scoring->order_id);

                // Ищем настройку для корректировки одобренной скористы
                if ($this->leadgidScorista->isEnabled() && !$this->leadgidScorista->hasStopFactor($result->data)) {
                    $leadgid = $this->leadgidScorista->getByOrder($order, $this->leadgidScorista::TYPE_APPROVE);
                } else {
                    $leadgid = null;
                }

                if (!empty($leadgid) && $order->status == $this->orders::ORDER_STATUS_CRM_NEW) {
                    if ($leadgid->amount == 0) {
                        // Принудительный отказ по заявке
                        $this->rejectOrderByLeadgid($order, $leadgid);
                    } else {

                        // Корректируем сумму только если заявка НЕ ВКЛ
                        $isRcl = (bool)$this->order_data->read((int)$order->order_id, $this->order_data::RCL_LOAN);
                        if (!$isRcl) {
                            $update = [
                                'amount' => $leadgid->amount,
                                'approve_amount' => $leadgid->amount
                            ];

                            $update_installment = $this->installments->check_installment($scoring->id);
                            if (empty($update_installment)) {
                                $update['amount'] = min($update['amount'], $this->orders::PDL_MAX_AMOUNT);
                            }
                            $update = array_merge($update, $update_installment);

                            $this->orders->update_order($scoring->order_id, $update);
                        }
                    }

                    // Отмечаем заявку для статистики
                    $this->leadgidScorista->markOrder($scoring->order_id, $this->leadgidScorista::TYPE_APPROVE);
                }
                else {
                    // Ищем настройку для увеличения одобренной суммы
                    if ($this->approve_amount_settings->isEnabled()) {
                        $amount_setting = $this->approve_amount_settings->getByOrder($order);
                    } else {
                        $amount_setting = null;
                    }

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
                            if ($increased_amount > $amount_limit) {
                                $increased_amount = $amount_limit;
                            }

                            $update = [
                                // Увеличиваем сумму которую может взять клиент
                                'approve_amount' => $increased_amount,
                                // И ставим в калькуляторе макс.сумму по-умолчанию, чтобы он не пропустил её
                                'amount' => $increased_amount
                            ];
                            $update_installment = $this->installments->check_installment($scoring->id);
                            if (empty($update_installment)) {
                                $update['amount'] = min($update['amount'], $this->orders::PDL_MAX_AMOUNT);
                                $update['approve_amount'] = min($update['approve_amount'], $this->orders::PDL_MAX_AMOUNT);
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
                                'status' => $this->orders::ORDER_STATUS_CRM_REJECT,
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
            } elseif (!empty($scoring->order_id) && empty($success)) {
                // Скориста отказала по заявке
                $order = $this->orders->get_order($scoring->order_id);

                if (
                    !in_array($order->status, [9, 10, 12]) &&
                    $this->scorings->isScoristaAllowed($order) &&
                    !$this->scorings->isHyperEnabledForOrder($order)
                ) {
                    // Если займ не выдан ставим отказ

                    // техаккаунт System
                    $tech_manager = $this->managers->get_manager(50);

                    $update_order = array(
                        'status' => $this->orders::ORDER_STATUS_CRM_REJECT,
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

                    if (!empty($order->is_user_credit_doctor)) {
                        $this->soap1c->send_credit_doctor($order->id_1c);
                    }

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

            if (!empty($success) && !empty($result->data->additional->no_need_for_underwriter)) {
                $this->user_data->set($scoring->user_id, 'no_need_for_underwriter', 1);

                $this->check_no_need_for_underwriter($order);

            }

            if (!empty($success) && empty($order->have_close_credits)) {
                $this->autoApplyUserStep($result, $scoring, $order);
            }
        } elseif (is_null($result)) {
            $update = array(
                'status' => $this->scorings::STATUS_ERROR,
                'body' => '',
                'string_result' => 'Не возможно прочитать ответ',
                'end_date' => date('Y-m-d H:i:s'),
            );
            $this->scorings->update_scoring($scoring->id, $update);
            $import_result = $this->scorings::STATUS_ERROR;
        } elseif ($result->status == 'ERROR') {
            $update = array(
                'status' => $this->scorings::STATUS_ERROR,
                'body' => json_encode($result),
                'string_result' => 'Ошибка скористы',
                'end_date' => date('Y-m-d H:i:s'),
            );
            $this->scorings->update_scoring($scoring->id, $update);
            $import_result = $this->scorings::STATUS_ERROR;
        } elseif (!empty($result->error)) {
            $update = array(
                'status' => $this->scorings::STATUS_ERROR,
                'body' => json_encode($result),
                'string_result' => 'Ошибка скористы',
                'end_date' => date('Y-m-d H:i:s'),
            );
            $this->scorings->update_scoring($scoring->id, $update);
            $import_result = $this->scorings::STATUS_ERROR;
        }

        return $import_result;
    }

    /**
     * Проставляем автоматически шаг фото и работа при, флажке от скористы
     *
     * @param $result
     * @param $scoring
     * @param $order
     * @return void
     * @throws SoapFault
     */
    private function autoApplyUserStep($result, $scoring, $order)
    {
        if (!empty($result->data->additional->no_need_for_underwriter)) {

            if (!in_array($order->utm_source, array_map('trim', $this->settings->auto_step_no_need_for_underwriter['utm_sources'] ?? []))) {
                return;
            }

            $step_data = [];

            if (!empty($this->settings->auto_step_no_need_for_underwriter['files_added'])) {
                $step_data['files_added'] = 1;
                $step_data['file_uploaded'] = 1;
                $step_data['files_added_date'] = date('Y-m-d H:i:s');
                $this->user_data->set($scoring->user_id, $this->scorista::FLAG_STEP_FILES, 1);
            }

            if (!empty($this->settings->auto_step_no_need_for_underwriter['additional_data_added'])) {
                $step_data['additional_data_added'] = 1;
                $step_data['additional_data_added_date'] = date('Y-m-d H:i:s');
                $this->user_data->set($scoring->user_id, $this->scorista::FLAG_STEP_ADDITIONAL_DATA, 1);
                $this->soap->set_order_complete($scoring->order_id);
            }

            if (!empty($step_data)) {
                $this->users->update_user($scoring->user_id, $step_data);
            }
        }
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
     * @return void
     */
    private function sendSmsMissing(object $user, object $order, int $scorista_ball)
    {
        $domain = $this->sites->getDomainBySiteId($user->site_id);
        $sms_template = '%s, ваш скор балл %d. Вам одобрено  до 30000 р. Закончите анкету и получите деньги ' . $domain . '/s/0117';
        $message = sprintf($sms_template, $user->firstname, $scorista_ball);
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

    private function check_no_need_for_underwriter($order)
    {
        if (!$this->settings->increase_amount_for_nnu) {
            return false;
        }

        $user = $this->users->get_user($order->user_id);
        if ($user->site_id != $this->sites::SITE_BOOSTRA) {
            return false;
        }

        if ($order->amount >= $this->orders::PDL_MAX_AMOUNT) {
            return false;
        }

        $approve_amount = $this->orders::PDL_MAX_AMOUNT;
        $this->order_data->set($order->order_id, $this->order_data::INCREASE_AMOUNT_FOR_NNU, $approve_amount);
        $this->orders->update_order($order->order_id, [
            'approve_amount' => $approve_amount,
            'amount' => $approve_amount,
        ]);

        return true;
    }
}

set_time_limit(0);
$cron = new ScoristaImportCron_mt();
$cron->run();
