<?php
require_once 'View.php';

class PromocodeView extends View
{
    /**
     * @throws Exception
     */
    public function fetch()
    {
        if ($this->request->method('post')) {
            $action = $this->request->post('action', 'string');
            if ($action === 'create') {
                return $this->create();
            } else {
                $this->response->json_output(['error' => 'Invalid action']);
            }
        } else {
            return $this->view();
        }
    }

    /**
     * @throws Exception
     */
    private function view(): string
    {
        $code = $this->request->get('id', 'string');
        $promocode = empty($code) ? $this->promocodes->getLastSettings() : $this->promocodes->getPromocode($code);

        if (!empty($promocode->date_start)) {
            $promocode->date_start = (new DateTime($promocode->date_start))->format('d.m.Y');
        }
        if (!empty($promocode->date_end)) {
            $promocode->date_end = (new DateTime($promocode->date_end))->format('d.m.Y');
        }

        $this->design->assign('promocode', $promocode);
        
        return $this->design->fetch('promocode.tpl');
    }

    /**
     * Create promocode
     */
    private function create()
    {
        $errors = [];
        $date_start = $this->request->post('date_start', 'string');
        $date_end = $this->request->post('date_end', 'string');
        $user_id = (int)$this->request->post('user_id', 'integer');
        $user = null;

        if ($user_id) {
            $user = $this->users->get_user($user_id);
            
            if (empty($user)) {
                $this->response->json_output(['errors' => ['user_not_found']]);
            }
        }
        
        try {
            $date_start_parsed = (new DateTime($date_start))->format('Y-m-d');
            $date_end_parsed = (new DateTime($date_end))->format('Y-m-d');

            // Проверка, что конечная дата больше начальной
            if (new DateTime($date_end) < new DateTime($date_start)) {
                $errors[] = 'end_date_before_start_date';
            }
        } catch (Exception $e) {
            $errors[] = 'invalid_date_format';
        }
        
        $promocode = [
            'date_start' => $date_start_parsed ?? null,
            'date_end'   => $date_end_parsed ?? null,
            'rate'       => (float)$this->request->post('rate', 'string'),
            'title'      => trim($this->request->post('title', 'string')),
            'quantity'   => max(0, (int)$this->request->post('quantity', 'integer')),
            'phone'      => $user ? $user->phone_mobile : '',
            'disable_additional_services' => $this->request->post('disable_additional_services') !== null,
            'is_mandatory_issue'         => $this->request->post('is_mandatory_issue') !== null
        ];

        if (empty($date_start)) {
            $errors[] = 'empty_date_start';
        }
        if (empty($date_end)) {
            $errors[] = 'empty_date_end';
        }
        if (empty($promocode['title'])) {
            $errors[] = 'empty_title';
        }

        if (!empty($errors)) {
            $this->response->json_output(['errors' => $errors]);
        }

        // Обработка обязательной выдачи
        if ($promocode['is_mandatory_issue']) {
            $lastOrder = $this->orders->get_user_last_order($user->id);
            
            $this->blacklist->delete($user->id);

            if ($lastOrder && $lastOrder->reason_id != $this->reasons::REASON_REMOVED_FROM_BLACKLIST) {
                $this->orders->update_order($lastOrder->id, [
                    'reason_id' => $this->reasons::REASON_REMOVED_FROM_BLACKLIST
                ]);
            }

            $reason = 'Для клиента сгенерирован промокод с обязательной выдачей займа';
            $this->blacklist->sendDeleteUserFromBlacklist1c(
                $user->UID,
                $reason,
                $reason,
                $this->manager->name_1c
            );
        }

        $result = $this->promocodes->create($promocode);
        if (!$result) {
            $this->response->json_output(['errors' => ['promocode_creation_failed']]);
        }

        $this->response->json_output(['promocode' => $result]);
    }
}
