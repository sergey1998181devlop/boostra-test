<?php

use boostra\services\UsersAddressService;

require_once 'View.php';

class IndividualOrderView extends View
{
    /**
     * Список причин скрытых от верификаторов
     */
    const SKIP_REASONS_FOR_VERIFICATORS = [
        1, // Долг по ФССП - 1
        21, //Антифрод
        24, // ФССП
        25, //Локальное время
        28, // Возраст 65+
        38 // Отказ по совпадению паспортных данных
    ];

    public function fetch()
    {
        if ($this->request->method('post'))
        {
            $order_id = $this->request->post('order_id', 'integer');
            $action = $this->request->post('action', 'string');

            switch($action):

                case 'change_manager':
                    $this->change_manager_action();
                break;

                case 'amount':
                    $this->action_amount();
                break;

                case 'personal':
                    $this->action_personal();
                break;

                case 'passport':
                    $this->action_passport();
                break;

                case 'reg_address':
                    $this->reg_address_action();
                break;

                case 'fakt_address':
                    $this->fakt_address_action();
                break;

                case 'contacts':
                    $this->contacts_action();
                break;

                case 'workdata':
                    $this->workdata_action();
                break;

                case 'work_address':
                    $this->work_address_action();
                break;

                case 'socials':
                    $this->socials_action();
                break;

                case 'images':
                    $this->action_images();
                break;

                case 'services':
                    $this->action_services();
                break;

                case 'accept':
                    $this->action_accept();
                break;

                case 'approve':
                    $this->action_approve();
                break;

                case 'reject':
                    $this->action_reject();
                break;

                case 'waiting':
                    $this->action_waiting();
                break;

                case 'add_maratorium':
                    $this->action_add_maratorium();
                break;

                case 'add_comment':
                    $this->action_add_comment();
                break;

                case 'send_sms':
                    $this->action_send_sms();
                break;

            endswitch;

        }
        else
        {


            $scoring_types = $this->scorings->get_types();
            $this->design->assign('scoring_types', $scoring_types);

            $order_id = $this->request->get('id', 'integer');

            if (!empty($order_id))
            {
                if (!($individual_order = $this->individuals->get_individual_order($order_id)))
                    return false;
                $this->design->assign('individual_order', $individual_order);

                if ($order = $this->orders->get_order($order_id))
                {
                    if (in_array($this->manager->role, ['verificator', 'edit_verificator']) && empty($order->manager_id))
                    {
                        return $this->design->fetch('403.tpl');
                    }

                    if (!empty($order->pay_result))
                    {
                        $pay_result = @unserialize($order->pay_result);
                        if (isset($pay_result['return']))
                            $order->pay_result = $pay_result['return'];
                    }

                    if (!empty($order->reason_id))
                    {
                        $order->reason = $this->reasons->get_reason($order->reason_id);
                    }
                    if (!empty($order->maratorium_date))
                    {
                        $order->maratorium_valid = strtotime($order->maratorium_date) > time();
                    }

                    $contactpersons = $this->contactpersons->get_contactpersons(array('user_id'=>$order->user_id));
                    $this->design->assign('contactpersons', $contactpersons);

                    $get_files_filters = [
                        'user_id' => $order->user_id,
                    ];

                    if ($order->first_loan) {
                        $get_files_filters['without_types'] = ['face1', 'face2'];
                    }

                    $files = $this->users->get_files($get_files_filters);
                    $this->design->assign('files', $files);

                    $need_update_scorings = 0;
                    $inactive_run_scorings = 0;
                    $scorings = array();
                    if ($result_scorings = $this->scorings->get_scorings(array('user_id'=>$order->user_id)))
                    {
                        foreach ($result_scorings as $scoring)
                        {

                            $scorings[$scoring->type] = $scoring;

                            if ($scoring->status == $this->scorings::STATUS_NEW || $scoring->status == $this->scorings::STATUS_PROCESS || $scoring->status == $this->scorings::STATUS_IMPORT)
                            {
                                $need_update_scorings = 1;
                                if ($scoring_types[$scoring->type]->type == 'first')
                                    $inactive_run_scorings = 1;
                            }
                        }

                        foreach ($scorings as $scoring_type => $scoring) {
                            if (in_array($scoring->type, [
                                $this->scorings::TYPE_JUICESCORE,
                                $this->scorings::TYPE_FSSP,
                                $this->scorings::TYPE_BLACKLIST,
                                $this->scorings::TYPE_EFRSB
                            ])) {
                                $scoring->body = $this->scorings->get_scoring_body($scoring->id);
                                $scoring->body = unserialize($scoring->body);
                            }

                            if (in_array($scoring->type, [
                                $this->scorings::TYPE_AXILINK,
                                $this->scorings::TYPE_SCORISTA
                            ])) {
                                $scoring->body = $this->scorings->get_scoring_body($scoring->id);
                                $scoring->body = json_decode($scoring->body);
                                if (!empty($scoring->body->equifaxCH)) {
                                    $scoring->body->equifaxCH = iconv('cp1251', 'utf8', base64_decode($scoring->body->equifaxCH));
                                }
                            }
                        }
                    }
                    $this->design->assign('scorings', $scorings);
                    $this->design->assign('need_update_scorings', $need_update_scorings);
                    $this->design->assign('inactive_run_scorings', $inactive_run_scorings);

                    $user = $this->users->get_user((int)$order->user_id);
                    $order->user = $user;
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($order->user);echo '</pre><hr />';
                    $card_list = $this->soap->get_card_list($user->UID);
                    $this->design->assign('card_list', $card_list);

                    $user_balance_1c = $this->soap->get_user_balance_1c($user->UID, $user->site_id);
                    $user_balance_1c = $this->users->make_up_user_balance($user->id, $user_balance_1c->return);
                    $user_balance = $this->users->get_user_balance($user->id);
//    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($user->UID);echo '</pre><hr />';
        			if(empty($user_balance))
        				$balance_id = $this->users->add_user_balance($user_balance_1c);
        			else
        				$balance_id = $this->users->update_user_balance($user_balance->id, $user_balance_1c);

                    $user_balance = $this->users->get_user_balance($user->id);
                    $this->design->assign('user_balance', $user_balance);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($user_balance);echo '</pre><hr />';

                    $comments = array();
                    $commentsData = $this->comments->get_comments(array('order_id'=>$order_id));

                    foreach ($commentsData as $key => $comment) {
                        if (in_array($comment->block, ChangelogsView::LOGS_TYPE_TO_HIDE_LOGS) && in_array($comment->manager_id, ChangelogsView::MANAGERS_TO_HIDE_LOGS)) {
                            unset($commentsData[$key]);
                        }
                    }

                    foreach ($commentsData as $com)
                    {
                        if (!isset($comments[$com->block]))
                            $comments[$com->block] = array();
                        $comments[$com->block][] = $com;
                    }
                    $this->design->assign('comments', $comments);


					// получаем комменты из 1С
					if ($comments_1c_response = $this->soap->get_comments($user->UID, $user->site_id))
					{
						$comments_1c = array();
						if (!empty($comments_1c_response->Комментарии))
						{
							foreach ($comments_1c_response->Комментарии as $comm)
							{
								$comment_1c_item = new StdClass();

								$comment_1c_item->created = date('Y-m-d H:i:s', strtotime($comm->Дата));
								$comment_1c_item->text = $comm->Комментарий;
								$comment_1c_item->block = $comm->Блок;

								$comments_1c[] = $comment_1c_item;
							}
						}

						usort($comments_1c, function($a, $b){
							return strtotime($b->created) - strtotime($a->created);
						});

						$this->design->assign('comments_1c', $comments_1c);

						$blacklist_comments = array();
						if (!empty($comments_1c_response->ЧС))
						{
							foreach ($comments_1c_response->ЧС as $comm)
							{
								$blacklist_comment = new StdClass();

								$blacklist_comment->created = date('Y-m-d H:i:s', strtotime($comm->Дата));
								$blacklist_comment->text = $comm->Комментарий;
								$blacklist_comment->block = $comm->Блок;

								$blacklist_comments[] = $blacklist_comment;
							}
						}

						usort($blacklist_comments, function($a, $b){
							return strtotime($b->created) - strtotime($a->created);
						});

						$this->design->assign('blacklist_comments', $blacklist_comments);
		//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($comments_1c_response);echo '</pre><hr />';
					}

					$comment_blocks = $this->comments->get_blocks();
					$this->design->assign('comment_blocks', $comment_blocks);


                    $user_orders = $this->orders->get_orders(array('user_id'=>$user->id));
                    $repeat = array_filter($user_orders, function($item) {return $item->have_close_credits;});
                    $this->design->assign('user_orders', $user_orders);

                    $user_scorings = $this->scorings->get_scorings(array('user_id' => $order->user_id, 'sort'=>'date_desc'));
                    foreach ($user_scorings as $user_scoring) {
                        if (in_array($user_scoring->type, [$this->scorings::TYPE_JUICESCORE, $this->scorings::TYPE_FSSP, $this->scorings::TYPE_EFRSB])) {
                            $user_scoring->body = $this->scorings->get_scoring_body($user_scoring->id);
                            $user_scoring->body = unserialize($user_scoring->body);
                        }

                        if (in_array($user_scoring->type, [$this->scorings::TYPE_AXILINK, $this->scorings::TYPE_SCORISTA])) {
                            $user_scoring->body = $this->scorings->get_scoring_body($user_scoring->id);
                            $user_scoring->body = json_decode($user_scoring->body);
                            if (isset($user_scoring->body->equifaxCH)) {
                                unset($user_scoring->body->equifaxCH);
                            }
//                            if (!empty($user_scoring->body->equifaxCH))
//                                $user_scoring->body->equifaxCH = iconv('cp1251', 'utf8', base64_decode($user_scoring->body->equifaxCH));
                        }

                        $user_scoring->type = $this->scorings->get_type($user_scoring->type);
                    }

                    $this->design->assign('user_scorings', $user_scorings);

                    $order->eventlogs = $this->eventlogs->get_logs(array('order_id' => $order_id));
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($order->user->loan_history);echo '</pre><hr />';
                    $this->design->assign('order', $order);

                    $passport_error = array();

                    if(empty($repeat)) {
                        $passport_user_id = $this->users->get_passport_user($user->passport_serial, $user->site_id,  (int)$order->user_id);
                        if (!empty($passport_user_id)) {
                            $passport_error[(int)$order->user_id] = $passport_user_id;
                        }
                    }

                    $this->design->assign('passport_error', $passport_error);

                    $eventlogs = $this->eventlogs->get_events(array('order_id' => $order_id));
                    $this->design->assign('eventlogs', $eventlogs);

                    $changelog_types = $this->changelogs->get_types();
                    $this->design->assign('changelog_types', $changelog_types);
                    $order_statuses = $this->orders->get_statuses();
                    $this->design->assign('order_statuses', $order_statuses);

                    $filter = [
                        'user_id' => $order->user_id
                    ];
                    if ($search = $this->request->get('search'))
                    {
                        $filter['search'] = array_filter($search);
                        $this->design->assign('search', array_filter($search));
                    }
                    $changelogs = $this->changelogs->get_changelogs($filter);

                    $managers = [];
                    foreach ($this->managers->get_managers() as $manager)
                        $managers[$manager->id] = $manager;

                    foreach ($changelogs as $key => $changelog)
                    {
                        if (in_array($changelog->type, ChangelogsView::LOGS_TYPE_TO_HIDE_LOGS) && in_array($changelog->manager_id, ChangelogsView::MANAGERS_TO_HIDE_LOGS)) {
                            unset($changelogs[$key]);
                            continue;
                        }

                        if (!empty($changelog->manager_id) && !empty($managers[$changelog->manager_id]))
                            $changelog->manager = $managers[$changelog->manager_id];
                    }

                    $this->design->assign('changelogs', $changelogs);
                }
                else
                {
                    $this->design->assign('error', 'undefined_order');
                }
            }

            $this->design->assign('open_scorings', $this->request->get('open_scorings', 'integer'));
        }

        $maratoriums = array();
        foreach ($this->maratoriums->get_maratoriums() as $m)
            $maratoriums[$m->id] = $m;
        $this->design->assign('maratoriums', $maratoriums);


        $reject_reasons = array();
        $waiting_reasons = array();
        foreach ($this->reasons->get_reasons() as $reason)
        {
            if (in_array($this->manager->role, ['verificator', 'edit_verificator']) && (in_array($reason->id, self::SKIP_REASONS_FOR_VERIFICATORS)) ) {
                continue;
            }
            if ($reason->type == 'reject')
                $reject_reasons[] = $reason;
            if ($reason->type == 'waiting')
                $waiting_reasons[] = $reason;
        }
        $this->design->assign('reject_reasons', $reject_reasons);
        $this->design->assign('waiting_reasons', $waiting_reasons);

        $sms_templates = $this->sms->get_templates(array('type' => 'order'));
        $this->design->assign('sms_templates', $sms_templates);

        if (!empty($order))
        {
            $sms_messages = $this->sms->get_messages(array('order_id' => $order->order_id));
            $this->design->assign('sms_messages', $sms_messages);
        }

        $body = $this->design->fetch('individual_order.tpl');

        if ($this->request->get('ajax', 'integer'))
        {
            echo $body;
            exit;
        }

        return $body;
    }

    private function change_manager_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $manager_id = $this->request->post('manager_id', 'integer');

        if (!($order = $this->orders->get_order((int)$order_id)))
            return array('error'=>'Неизвестный ордер');

        if (!in_array($this->manager->role, array('admin', 'developer', 'chief_verificator', 'opr', 'ts_operator')))
            return array('error'=>'Не хватает прав для выполнения операции', 'manager_id'=>$order->manager_id);

        if (!empty($order->id_1c))
        {
            $check_block = $this->soap1c->check_block_order_1c($order->id_1c);

            if ($check_block == 'Block_1c')
            {
                return array('error'=>'Заявка заблокирована в 1С', 'check_block'=>$check_block);
            }
            elseif ($check_block == 'Block_CRM' && empty($manager_id))
            {
                $this->soap1c->block_order_1c($order->id_1c, 0);
            }
            elseif ($check_block != 'Block_CRM' && !empty($manager_id))
            {
                $this->soap1c->block_order_1c($order->id_1c, 1);
            }
        }

        $update = array(
            'manager_id' => $manager_id,
            'uid' => exec($this->config->root_dir.'generic/uidgen')
        );
        $this->orders->update_order($order_id, $update);

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status_order',
            'old_values' => serialize(array('status' => $order->status, 'manager_id' => $order->manager_id)),
            'new_values' => serialize($update),
            'order_id' => $order_id,
            'user_id' => $order->user_id,
        ));

        $output = array('success' => 1, 'status' => 1, 'manager' => $this->manager->name);

        $this->json_output($output);
    }

    private function action_add_comment()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $order_id = $this->request->post('order_id', 'integer');
        $block = $this->request->post('block', 'string');
        $text = $this->request->post('text');

        if (empty($text))
        {
            $this->json_output(array('error'=>'Напишите комментарий!'));
        }
        else
        {

            $comment = array(
                'manager_id' => $this->manager->id,
                'user_id' => $user_id,
                'order_id' => $order_id,
                'block' => $block,
                'text' => $text,
                'created' => date('Y-m-d H:i:s'),
            );

            if ($comment_id = $this->comments->add_comment($comment))
            {

                if ($order = $this->orders->get_order((int)$order_id))
                {
                    $manager = $this->managers->get_manager((int)$this->manager->id);
                    $this->soap->send_comment(array(
                        'manager' => $manager->name_1c,
                        'text' => $text,
                        'created' => date('Y-m-d H:i:s'),
                        'number' => $order->id_1c
                    ));
                }

                $this->json_output(array(
                    'success' => 1,
                    'created' => date('d.m.Y H:i:s'),
                    'text' => $text,
                    'manager_name' => $this->manager->name,
                ));
            }
            else
            {
                $this->json_output(array('error'=>'Не удалось добавить!'));
            }
        }
    }

    private function action_add_maratorium()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $maratorium_id = $this->request->post('maratorium_id', 'integer');

        if (empty($maratorium_id))
        {
            $this->json_output(array('error'=>'Выберите мораторий!'));
        }
        else
        {
            if ($maratorium = $this->maratoriums->get_maratorium($maratorium_id))
            {
                $maratorium_date = time() + $maratorium->period;
                $this->users->update_user($user_id, array(
                    'maratorium_id' => $maratorium_id,
                    'maratorium_date' => date('Y-m-d H:i:s', $maratorium_date),
                ));

                $this->json_output(array(
                    'success' => 1,
                    'date' => date('d.m.Y H:i:s', $maratorium_date),
                ));
            }
            else
            {
                $this->json_output(array('error'=>'Неизвестный мораторий!'));
            }
        }
    }

    private function action_accept()
    {
        $order_id = $this->request->post('order_id', 'integer');

        if (!($individual_order = $this->individuals->get_individual_order($order_id)))
            $this->json_output(array('error'=>'Неизвестная заявка!'));

        if (!empty($order->manager_id))
            $this->json_output(array('error'=>'Заявка уже принята другим менеджером!'));

        $order = $this->orders->get_order((int)$order_id);
        // проверяем в 1с заблокирована ли заявка
        $check_block = $this->soap->check_block_order_1c($order->id_1c);
        if ($check_block == 'Block_1c')
        {
            $this->json_output(array('error'=>'Заявка заблокирована в 1С', 'check_block'=>$check_block));
        }
        elseif ($check_block == 'Block_CRM')
        {
            $this->json_output(array('error'=>'Заявка уже заблокирована в CRM', 'check_block'=>$check_block));
        }
        elseif ($check_block == 'OK')
        {
            $resp = $this->soap->block_order_1c($order->id_1c, 1);
            if ($resp = 'OK')
            {
                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'individual_status',
                    'old_values' => serialize(array('manager_id'=>null)),
                    'new_values' => serialize(array('manager_id'=>$this->manager->id)),
                    'order_id' => $order_id,
                ));


                $this->individuals->update_order($individual_order->id, array('manager_id' => $this->manager->id, 'status' => 4));

                $this->json_output(array('success'=>'Заявка принята!'));

            }
            else
            {
                $this->json_output(array('error'=>'Ошибка при блокировке в 1с', 'response'=>$resp));
            }

        }
        else
        {
            $this->json_output(array('error'=>'Ошибка при проверке заявки на блокировку в 1с', 'check_block'=>$check_block));
        }
    }

    private function send_order_1c($order_id)
    {
        $order = $this->orders->get_order($order_id);

        //Отправка заявки в 1с со всеми заполнеными клиентом данными на 1, 2, 3 этапах
        $loan = array(
            'lastname' => (string)$order->lastname,
            'firstname' => (string)$order->firstname,
            'patronymic' => (string)$order->patronymic,
            'birth' => (string)$order->birth,
            'phone_mobile' => (string)$order->phone_mobile,
            'email' => (string)$order->email,
            'passport_serial' => (string)$order->passport_serial,
            'passport_date' => (string)$order->passport_date,
            'subdivision_code' => (string)$order->subdivision_code,
            'passport_issued' => (string)$order->passport_issued,

            'АдресРегистрацииИндекс' => (string)$order->Regindex,
            'Regregion' => (string)trim($order->Regregion.' '.$order->Regregion_shorttype),
            'Regdistrict' => (string)$order->Regdistrict,
            'Regcity' => (string)trim($order->Regcity.' '.$order->Regcity_shorttype),
            'Reglocality' => '',
            'Regstreet' => (string)trim($order->Regstreet.' '.$order->Regstreet_shorttype),
            'Regbuilding' => (string)$order->Regbuilding,
            'Reghousing' => (string)$order->Reghousing,
            'Regroom' => (string)$order->Regroom,

            'АдресФактическогоПроживанияИндекс' => (string)$order->Faktindex,
            'Faktregion' => (string)trim($order->Faktregion.' '.$order->Faktregion_shorttype),
            'Faktdistrict' => (string)$order->Faktdistrict,
            'Faktcity' => (string)trim($order->Faktcity.' '.$order->Faktcity_shorttype),
            'Faktlocality' => '',
            'Faktstreet' => (string)trim($order->Faktstreet.' '.$order->Faktstreet_shorttype),
            'Faktbuilding' => (string)$order->Faktbuilding,
            'Fakthousing' => (string)$order->Fakthousing,
            'Faktroom' => (string)$order->Faktroom,

			'site_id' => $order->order_id,
			'partner_id' => '',
			'partner_name' => 'Boostra',

			'amount' => (string)$order->amount,
			'period' => (string)$order->period,

            'utm_source' => $order->utm_source,
			'utm_medium' => $order->utm_medium,
			'utm_campaign' => $order->utm_campaign,
			'utm_content' => $order->utm_content,
			'utm_term' => $order->utm_term,
			'webmaster_id' => $order->webmaster_id,
			'click_hash' => $order->click_hash,

            'id' => '',
            'Car' => '',

            'МестоРождения' => (string)$order->birth_place,
            'ГородскойТелефон' => isset($order->landline_phone) ? (string)$order->landline_phone : '',
            'Пол' => (string)$order->gender,

            'СфераРаботы' => (string)$order->work_scope,

            'ДоходОсновной' => isset($order->income_base) ? (string)$order->income_base : '',
            'ДоходДополнительный' => isset($order->income_additional) ? (string)$order->income_additional : '',
            'ДоходСемейный' => isset($order->income_family) ? (string)$order->income_family : '',
            'ФинансовыеОбязательства' => isset($order->obligation) ? (string)$order->obligation : '',
            'ПлатежиПоКредитамВМесяц' => isset($order->other_loan_month) ? (string)$order->other_loan_month : '',
            'СколькоКредитов' => isset($order->other_loan_count) ? (string)$order->other_loan_count : '',
            'КредитнаяИстория' => isset($order->credit_history) ? (string)$order->credit_history : '',
            'МаксимальноОдобренныйРанееКредит' => isset($order->other_max_amount) ? (string)$order->other_max_amount : '',
            'ПоследнийОдобренныйРанееКредит' => isset($order->other_last_amount) ? (string)$order->other_last_amount : '',
            'БылоЛиБанкротство' => isset($order->bankrupt) ? (string)$order->bankrupt : '',
            'Образование' => isset($order->education) ? (string)$order->education : '',
            'СемейноеПоложение' => isset($order->marital_status) ? (string)$order->marital_status : '',
            'КоличествоДетей' => isset($order->childs_count) ? (string)$order->childs_count : '',
            'НаличиеАвтомобиля' => isset($order->have_car) ? (string)$order->have_car : '',
            'ВК' => isset($order->social_vk) ? (string)$order->social_vk : '',
            'Инст' => isset($order->social_inst) ? (string)$order->social_inst : '',
            'Фейсбук' => isset($order->social_fb) ? (string)$order->social_fb : '',
            'ОК' => isset($order->social_ok) ? (string)$order->social_ok : '',

            'ServicesSMS' => $order->service_sms,
            'ServicesInsure' => $order->service_insurance,
            'ServicesReason' => $order->service_reason,
        );

        /** Доделать эти параметры **/
        $loan['ОтказНаСайте'] = '';
        $loan['ПричинаОтказаНаСайте'] = '';

        $contact_person_name = array();
        $contact_person_phone = array();
        $contact_person_relation = array();
        if ($contactpersons = $this->contactpersons->get_contactpersons(array('user_id'=>$order->user_id)))
        {
            foreach ($contactpersons as $contactperson)
            {
                $contact_person_name[] = (string)$contactperson->name;
                $contact_person_phone[] = (string)$contactperson->phone;
                $contact_person_relation[] = (string)$contactperson->relation;
            }
        }


        $loan['КонтактноеЛицоФИО'] = json_encode($contact_person_name);
        $loan['КонтактноеЛицоТелефон'] = json_encode($contact_person_phone);
        $loan['КонтактноеЛицоРодство'] = json_encode($contact_person_relation);

        if ($order->work_scope == 'Пенсионер')
        {
            $loan['Занятость'] = '';
            $loan['Профессия'] = '';
            $loan['МестоРаботы'] = '';
            $loan['СтажРаботы'] = '';
            $loan['ШтатРаботы'] = '';
            $loan['ТелефонОрганизации'] = '';
            $loan['ФИОРуководителя'] = '';

            $loan['АдресРаботы'] = '';
        }
        else
        {
            $loan['Занятость'] = isset($order->employment) ? (string)$order->employment : '';
            $loan['Профессия'] = isset($order->profession) ? (string)$order->profession : '';
            $loan['МестоРаботы'] = isset($order->workplace) ? (string)$order->workplace : '';
            $loan['СтажРаботы'] = isset($order->experience) ? (string)$order->experience : '';
            $loan['ШтатРаботы'] = isset($order->work_staff) ? (string)$order->work_staff : '';
            $loan['ТелефонОрганизации'] = isset($order->work_phone) ? (string)$order->work_phone : '';
            $loan['ФИОРуководителя'] = isset($order->workdirector_name) ? (string)$order->workdirector_name : '';

            $loan['АдресРаботы'] = $order->Workindex.' '.$order->Workregion.', '.$order->Workcity.', ул.'.$order->Workstreet.', д.'.$order->Workhousing;
            if (!empty($order->Workbuilding))
                $loan['АдресРаботы'] .= '/'.$order->Workbuilding;
            if (!empty($order->Workroom))
                $loan['АдресРаботы'] .= ', оф.'.$order->Workroom;
        }

        switch ($order->status):

            case '2':
                $loan['СтатусCRM'] = 'Одобрена';
            break;

            case '3':
                $loan['СтатусCRM'] = 'Отказ';
            break;

        endswitch;

        $loan['СуммаCRM'] = $order->amount;
        $loan['УИД_CRM'] = $order->tinkoff_id;

        $loan = (object)$loan;

        $resp = $this->soap->send_loan($loan);

        if (!empty($resp->return->id_zayavka))
        {
            $this->orders->update_order($order_id, array('1c_id'=>$resp->return->id_zayavka));

            $soap = $this->soap->get_uid_by_phone($order->phone_mobile);
            if (!empty($soap->result) && !empty($soap->uid))
            {
                $this->users->update_user($order->user_id, array('UID'=>$soap->uid));
            }
        }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($loan, $resp);echo '</pre><hr />';
    }

    private function action_approve()
    {
        $order_id = $this->request->post('order_id', 'integer');

        $individual_order = $this->individuals->get_individual_order($order_id);

        $update = array(
            'status' => 2,
            'manager_id' => $this->manager->id,
        );

        $old_values = array();
        foreach ($update as $key => $val)
            if ($individual_order->$key != $update[$key])
                $old_values[$key] = $individual_order->$key;

        $log_update = array();
        foreach ($update as $k => $u)
            if (isset($old_values[$k]))
                $log_update[$k] = $u;

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'individual_status',
            'old_values' => serialize($old_values),
            'new_values' => serialize($log_update),
            'order_id' => $order_id,
        ));


        $this->individuals->update_order($individual_order->id, $update);

        $old_order = $this->orders->get_order((int)$order_id);

        $new_order = array(
			'user_id' => $old_order->user_id,
            'card_id' => $old_order->card_id,
			'amount' => $old_order->amount,
			'period' => $old_order->period,
            'percent' => 1,
            'date' => date('Y-m-d H:i:s'),
			'comment' => $individual_order->id,
            'ip' => '',
            'juicescore_session_id' => '',
            'local_time' => '',
            'utm_source' => 'ind',
            'max_amount' => 0,
            'razgon' => 0,
            'manager_id' => $this->manager->id,
        );
        if ($new_order_id = $this->orders->add_order($new_order))
        {
            // отправляем ее в 1с
            $soap_zayavka = $this->soap->soap_repeat_zayavka($old_order->amount, $old_order->period, $old_order->user_id, $old_order->card_id);
            if (empty($soap_zayavka->return->id_zayavka))
            {
                $this->orders->update_order($order_id, array('status'=>3, 'note' => strval($soap_zayavka->return->Error)));
                $this->leadgid->reject_actions($order_id);
            }
            else
            {
                // задержка для 1с между подачей заявки и одобрением
                sleep(2);

                // ставим статус одобрена и отправляем статус в 1с
                $this->orders->update_order($new_order_id, array(
                    'status'=>2,
                    '1c_id' => $soap_zayavka->return->id_zayavka,
                    'approve_date' => date('Y-m-d H:i:s'),
                ));

                $this->soap->update_status_1c($soap_zayavka->return->id_zayavka, 'Одобрено', $this->manager->name_1c, $old_order->amount, 1);


        		//отправка смс
                $site_id = $this->users->get_site_id_by_user_id($old_order->user_id);
                $template = $this->sms->get_template($this->sms::SMS_TEMPLATE_APPROVE_OTHER, $site_id);
                $message = strtr($template->template, [
                    '{{amount}}' => $old_order->amount,
                ]);
        		$status = $this->smssender->send_sms($old_order->phone_mobile, $message, $site_id);
        		if($status){
        			$this->db->query("INSERT INTO sms_log SET phone='".$old_order->phone_mobile."', status='".$status[1]."', dates='".date("Y-m-d H:i:s")."', sms_id='".$status[0]."'");
        		}

            }
        }

        $resp = $this->soap->block_order_1c($old_order->id_1c, 0);

        $this->design->assign('order', $old_order);
    }

    private function action_reject()
    {
        $order_id = $this->request->post('order_id', 'integer');

        $individual_order = $this->individuals->get_individual_order($order_id);

        $update = array(
            'status' => 3,
            'manager_id' => $this->manager->id,
        );

        $old_values = array();
        foreach ($update as $key => $val)
            if ($individual_order->$key != $update[$key])
                $old_values[$key] = $individual_order->$key;

        $log_update = array();
        foreach ($update as $k => $u)
            if (isset($old_values[$k]))
                $log_update[$k] = $u;

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'individual_status',
            'old_values' => serialize($old_values),
            'new_values' => serialize($log_update),
            'order_id' => $order_id,
            'user_id' => $individual_order->user_id,
        ));

        $this->individuals->update_order($individual_order->id, $update);

        $order = $this->orders->get_order((int)$order_id);
        $resp = $this->soap->block_order_1c($order->id_1c, 0);


        $this->design->assign('order', $order);
    }

    private function action_waiting()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $reason_id = $this->request->post('reason_id', 'integer');

        $update = array(
            'status' => 7,
            'manager_id' => $this->manager->id,
            'reason_id' => $reason_id,
        );

        $old_order = $this->orders->get_order($order_id);
        $old_values = array();
        foreach ($update as $key => $val)
            if ($old_order->$key != $update[$key])
                $old_values[$key] = $old_order->$key;

        $log_update = array();
        foreach ($update as $k => $u)
            if (isset($old_values[$k]))
                $log_update[$k] = $u;

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status',
            'old_values' => serialize($old_values),
            'new_values' => serialize($log_update),
            'order_id' => $order_id,
        ));

		//LOGGING
		$this->db->query("
			INSERT INTO 
				verification_stats 
			SET 
				order_id='".$order_id."', 
				dates='".date("Y-m-d H:i:s")."',  
				manager_id='".$this->manager->id."',
				start_status='1'
		");

        $this->orders->update_order($order_id, $update);

        $order = $this->orders->get_order((int)$order_id);

        $this->design->assign('order', $order);
    }

    private function action_send_sms()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $template_id = $this->request->post('template_id', 'integer');

        $order = $this->orders->get_order((int)$order_id);
        $site_id = $this->users->get_site_id_by_user_id($order->user_id);

        $template = $this->sms->get_template($template_id, $site_id);

        $resp = $this->smssender->send_sms(
            $order->phone_mobile,
            $template->template,
            $site_id,
            1
        );

        $this->sms->add_message(array(
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'phone' => $order->phone_mobile,
            'message' => $template->template,
            'created' => date('Y-m-d H:i:s'),
            'send_status' => $resp[1],
            'delivery_status' => '',
            'send_id' => $resp[0],
        ));

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'send_sms',
            'old_values' => '',
            'new_values' => $template->template,
            'order_id' => $order_id,
        ));




        $this->design->assign('order', $order);
    }

    private function action_amount()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');
    	$amount = $this->request->post('amount', 'integer');
    	$period = $this->request->post('period', 'integer');
        $card_id = $this->request->post('card_id', 'integer');

        $order = new StdClass();
        $order->id = $order_id;
        $order->amount = $amount;
        $order->period = $period;
        $order->card_id = $card_id;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;

        $this->design->assign('order', $order);

        $amount_error = array();

        if (empty($amount))
            $amount_error[] = 'empty_amount';
        if (empty($period))
            $amount_error[] = 'empty_period';

        if (empty($amount_error))
        {
            $user = $this->users->get_user((int)$user_id);
            $card_list = $this->soap->get_card_list($user->UID);
            $this->design->assign('card_list', $card_list);


            $update = array(
                'amount' => $amount,
                'period' => $period,
                'card_id' => $card_id,
            );

            $old_order = $this->orders->get_order($order_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_order->$key != $update[$key])
                    $old_values[$key] = $old_order->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'period_amount',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->orders->update_order($order_id, $update);

        }
        $this->design->assign('amount_error', $amount_error);
    }

    private function action_personal()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->lastname = trim($this->request->post('lastname'));
        $order->firstname = trim($this->request->post('firstname'));
        $order->patronymic = trim($this->request->post('patronymic'));
        $order->gender = trim($this->request->post('gender'));
        $order->birth = trim($this->request->post('birth'));
        $order->birth_place = trim($this->request->post('birth_place'));
        $order->email = trim($this->request->post('email'));

        $personal_error = array();

        if (empty($order->lastname))
            $personal_error[] = 'empty_lastname';
        if (empty($order->firstname))
            $personal_error[] = 'empty_firstname';
        if (empty($order->patronymic))
            $personal_error[] = 'empty_patronymic';
        if (empty($order->gender))
            $personal_error[] = 'empty_gender';
        if (empty($order->birth))
            $personal_error[] = 'empty_birth';

        if (empty($personal_error))
        {
            $update = array(
                'lastname' => $order->lastname,
                'firstname' => $order->firstname,
                'patronymic' => $order->patronymic,
                'gender' => $order->gender,
                'birth' => $order->birth,
                'birth_place' => $order->birth_place,
                'email' => $order->email,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'personal',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);

            // обновляем в 1с
            $ord = $this->orders->get_order((int)$order_id);
            $this->soap->update_fields($ord->id_1c, $update);

        }

        $this->design->assign('personal_error', $personal_error);

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->phone_mobile = $isset_order->phone_mobile;
        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);

    }

    private function action_passport()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->passport_serial = trim($this->request->post('passport_serial'));
        $order->passport_date = trim($this->request->post('passport_date'));
        $order->subdivision_code = trim($this->request->post('subdivision_code'));
        $order->passport_issued = trim($this->request->post('passport_issued'));

        $passport_error = array();

        $site_id = $this->users->get_site_id_by_user_id($user_id);
        $passport_user_id = $this->users->get_passport_user($order->passport_serial, $site_id);
        if (!empty($passport_user_id) && $passport_user_id != $user_id) {
            $passport_error[$user_id] = $passport_user_id;
        }
        if (empty($order->passport_serial))
            $passport_error[] = 'empty_passport_serial';
        if (empty($order->passport_date))
            $passport_error[] = 'empty_passport_date';
        if (empty($order->subdivision_code))
            $passport_error[] = 'empty_subdivision_code';
        if (empty($order->passport_issued))
            $passport_error[] = 'empty_passport_issued';

        if (empty($passport_error))
        {
            $update = array(
                'passport_serial' => $order->passport_serial,
                'passport_date' => $order->passport_date,
                'subdivision_code' => $order->subdivision_code,
                'passport_issued' => $order->passport_issued
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'passport',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('passport_error', $passport_error);

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);


        // обновляем в 1с
        $ord = $this->orders->get_order((int)$order_id);
        $this->soap->update_fields($ord->id_1c, $log_update);
    }

    private function reg_address_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->Regregion = trim($this->request->post('Regregion'));
        $order->Regcity = trim($this->request->post('Regcity'));
        $order->Regstreet = trim($this->request->post('Regstreet'));
        $order->Reghousing = trim($this->request->post('Reghousing'));
        $order->Regbuilding = trim($this->request->post('Regbuilding'));
        $order->Regroom = trim($this->request->post('Regroom'));

        $regaddress_error = array();

        if (empty($order->Regregion))
            $regaddress_error[] = 'empty_regregion';
        if (empty($order->Regcity))
            $regaddress_error[] = 'empty_regcity';
        if (empty($order->Regstreet))
            $regaddress_error[] = 'empty_regstreet';
        if (empty($order->Reghousing))
            $regaddress_error[] = 'empty_reghousing';

        if (empty($regaddress_error))
        {
            $timezone_id = $this->users->getTimezoneId($order->Regregion);

            $update = array(
                'Regregion' => $order->Regregion,
                'Regcity' => $order->Regcity,
                'Regstreet' => $order->Regstreet,
                'Reghousing' => $order->Reghousing,
                'Regbuilding' => $order->Regbuilding,
                'Regroom' => $order->Regroom,
            );

            if (!empty($timezone_id)) {
                $update['timezone_id'] = $timezone_id;
            }

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'regaddress',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            if (!empty($old_user->registration_address_id) && !empty($this->request->safe_post('Regregion'))) {
                (new UsersAddressService())->updateRegistrationAddress($old_user->registration_address_id, $this->request);
            }

            $this->users->update_user($user_id, $update);

            // обновляем в 1с
            $ord = $this->orders->get_order((int)$order_id);
            $this->soap->update_fields($ord->id_1c, $log_update);
        }

        $this->design->assign('regaddress_error', $regaddress_error);

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);
    }

    private function fakt_address_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->Faktregion = trim($this->request->post('Faktregion'));
        $order->Faktcity = trim($this->request->post('Faktcity'));
        $order->Faktstreet = trim($this->request->post('Faktstreet'));
        $order->Fakthousing = trim($this->request->post('Fakthousing'));
        $order->Faktbuilding = trim($this->request->post('Faktbuilding'));
        $order->Faktroom = trim($this->request->post('Faktroom'));

        $faktaddress_error = array();

        if (empty($order->Faktregion))
            $faktaddress_error[] = 'empty_faktregion';
        if (empty($order->Faktcity))
            $faktaddress_error[] = 'empty_faktcity';
        if (empty($order->Faktstreet))
            $faktaddress_error[] = 'empty_faktstreet';
        if (empty($order->Fakthousing))
            $faktaddress_error[] = 'empty_fakthousing';

        if (empty($faktaddress_error))
        {
            $update = array(
                'Faktregion' => $order->Faktregion,
                'Faktcity' => $order->Faktcity,
                'Faktstreet' => $order->Faktstreet,
                'Fakthousing' => $order->Fakthousing,
                'Faktbuilding' => $order->Faktbuilding,
                'Faktroom' => $order->Faktroom,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'faktaddress',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            if (!empty($old_user->factual_address_id) && !empty($this->request->safe_post('Faktregion'))) {
                (new UsersAddressService())->updateFactualAddress($old_user->factual_address_id, $this->request);
            }

            $this->users->update_user($user_id, $update);

            // обновляем в 1с
            $ord = $this->orders->get_order((int)$order_id);
            $this->soap->update_fields($ord->id_1c, $log_update);
        }

        $this->design->assign('faktaddress_error', $faktaddress_error);

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);
    }

    private function contacts_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $contact_person_ids = array_map('trim', (array)$this->request->post('contact_person_id'));
        $contact_person_names = array_map('trim', (array)$this->request->post('contact_person_name'));
        $contact_person_phones = array_map('trim', (array)$this->request->post('contact_person_phone'));
        $contact_person_relations = array_map('trim', (array)$this->request->post('contact_person_relation'));
        $contact_person_comments = array_map('trim', (array)$this->request->post('contact_person_comment'));

//        $this->contactpersons->delete_user_contactpersons($user_id);
        foreach ($contact_person_ids as $i => $contact_person_id)
        {
            $contactperson = array(
                'user_id' => $user_id,
                'name' => $contact_person_names[$i],
                'phone' => $contact_person_phones[$i],
                'relation' => $contact_person_relations[$i],
                'comment' => $contact_person_comments[$i],
            );
            if (empty($contact_person_id))
            {
                $this->contactpersons->add_contactperson($contactperson);

                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'contacts',
                    'old_values' => serialize(array()),
                    'new_values' => serialize($contactperson),
                    'order_id' => $order_id,
                    'user_id' => $user_id,
                ));
            }
            else
            {
                $this->contactpersons->update_contactperson($contact_person_id, $contactperson);
            }

        }

        $contactpersons = $this->contactpersons->get_contactpersons(array('user_id'=>$user_id));
        $this->design->assign('contactpersons', $contactpersons);


        $order = new StdClass();
        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);

        // обновляем в 1с
        $contactpersons_1c = array();
        foreach ($contactpersons as $contactperson)
        {
            $contactperson_1c = new StdClass();

            $ex = explode(' ', $contactperson->name);

            $contactperson_1c->Фамилия = empty($ex[0]) ? '' : $ex[0];
            $contactperson_1c->Имя = empty($ex[1]) ? '' : $ex[1];
            $contactperson_1c->Отчество = empty($ex[2]) ? '' : $ex[2];
            $contactperson_1c->СтепеньРодства = $contactperson->relation;
            $contactperson_1c->ТелефонМобильный = $this->soap->format_phone($contactperson->phone);

            $contactpersons_1c[] = $contactperson_1c;
        }
        $this->soap->update_fields($isset_order->id_1c, array('contactpersons' => $contactpersons_1c));

    }

    private function workdata_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->work_scope = trim($this->request->post('work_scope'));
        $order->profession = trim($this->request->post('profession'));
        $order->work_phone = trim($this->request->post('work_phone'));
        $order->workplace = trim($this->request->post('workplace'));
        $order->workdirector_name = trim($this->request->post('workdirector_name'));
        $order->income_base = trim($this->request->post('income_base'));

        $workdata_error = array();

        if (empty($order->work_scope))
            $workaddress_error[] = 'empty_work_scope';
        if (empty($order->income_base))
            $workaddress_error[] = 'empty_income_base';

        if (empty($workdata_error))
        {
            $update = array(
                'work_scope' => $order->work_scope,
                'profession' => $order->profession,
                'work_phone' => $order->work_phone,
                'workplace' => $order->workplace,
                'workdirector_name' => $order->workdirector_name,
                'income_base' => $order->income_base,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'workdata',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('workdata_error', $workdata_error);

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);

        // обновляем в 1с
        $ord = $this->orders->get_order((int)$order_id);
        $this->soap->update_fields($ord->id_1c, $log_update);
    }


    private function work_address_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->Workregion = trim($this->request->post('Workregion'));
        $order->Workcity = trim($this->request->post('Workcity'));
        $order->Workstreet = trim($this->request->post('Workstreet'));
        $order->Workhousing = trim($this->request->post('Workhousing'));
        $order->Workbuilding = trim($this->request->post('Workbuilding'));
        $order->Workroom = trim($this->request->post('Workroom'));

        $workaddress_error = array();

        if (empty($order->Workregion))
            $workaddress_error[] = 'empty_workregion';
        if (empty($order->Workcity))
            $workaddress_error[] = 'empty_workcity';
        if (empty($order->Workstreet))
            $workaddress_error[] = 'empty_workstreet';
        if (empty($order->Workhousing))
            $workaddress_error[] = 'empty_workhousing';

        if (empty($workaddress_error))
        {
            $update = array(
                'Workregion' => $order->Workregion,
                'Workcity' => $order->Workcity,
                'Workstreet' => $order->Workstreet,
                'Workhousing' => $order->Workhousing,
                'Workbuilding' => $order->Workbuilding,
                'Workroom' => $order->Workroom,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'workaddress',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('workaddress_error', $workaddress_error);

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);

        // обновляем в 1с
        $ord = $this->orders->get_order((int)$order_id);
        $workaddress = $ord->Workregion.', '.$ord->Workcity.', '.$ord->Workstreet.', д'.$ord->Workhousing;
        if (!empty($ord->Workbuilding))
            $workaddress .= ', стр.'.$ord->Workbuilding;
        if (!empty($ord->Workroom))
            $workaddress .= ', оф.'.$ord->Workroom;
        $this->soap->update_fields($ord->id_1c, array(
            'workaddress' => $workaddress
        ));
    }

    private function socials_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->social_fb = trim($this->request->post('social_fb'));
        $order->social_inst = trim($this->request->post('social_inst'));
        $order->social_vk = trim($this->request->post('social_vk'));
        $order->social_ok = trim($this->request->post('social_ok'));

        $socials_error = array();

        if (empty($socials_error))
        {
            $update = array(
                'social_fb' => $order->social_fb,
                'social_inst' => $order->social_inst,
                'social_vk' => $order->social_vk,
                'social_ok' => $order->social_ok,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'socials',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('socials_error', $socials_error);

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);
    }


    private function action_images()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $statuses = $this->request->post('status');
        foreach ($statuses as $file_id => $status)
        {
            $update = array(
                'status' => $status,
                'id' => $file_id
            );

            $old_files = $this->users->get_file($file_id);
            $old_values = array();
            foreach ($update as $key => $val)
                $old_values[$key] = $old_files->$key;
            if ($old_values['status'] != $update['status'])
            {
                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'images',
                    'old_values' => serialize($old_values),
                    'new_values' => serialize($update),
                    'user_id' => $user_id,
                    'order_id' => $order_id,
                    'file_id' => $file_id,
                ));
            }

            $this->users->update_file($file_id, array('status' => $status));
        }

        $have_reject = 0;
        if ($files = $this->users->get_files(array('user_id' => $user_id)))
        {
            foreach ($files as $f)
                if ($f->status == 3)
                    $have_reject = 1;
        }
        if (empty($have_reject))
        {
            $this->orders->update_order($order_id, array('status' => 1));
        }
        else
        {
            $this->orders->update_order($order_id, array('status' => 5));
        }

        $order = new StdClass();
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $this->design->assign('order', $order);

        $files = $this->users->get_files(array('user_id'=>$user_id));
        $this->design->assign('files', $files);
    }

    private function action_services()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->service_sms = (int)$this->request->post('service_sms');
        $order->service_insurance = (int)$this->request->post('service_insurance');
        $order->service_reason = (int)$this->request->post('service_reason');

        $services_error = array();

        if (empty($services_error))
        {
            $update = array(
                'service_sms' => $order->service_sms,
                'service_insurance' => $order->service_insurance,
                'service_reason' => $order->service_reason,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'services',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('services_error', $services_error);

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);
    }
}
