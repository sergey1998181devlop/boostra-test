<?php

use api\helpers\JWTHelper;

require_once 'View.php';

/**
 * Авторизация по токену
 */
class AuthByTokenView extends View
{
    public function fetch()
    {
        unset($_SESSION['user_id']);
        $token = $this->request->get('token');
        $jwt = JWTHelper::decodeToken($token, $this->config->jwt_secret_key);

        if (!empty($jwt)) {
            $user_id = (int)$jwt->sub;
            $this->ping3Process($user_id);
            $this->users->authUserById($user_id);
        } else {
            $this->request->redirect($this->config->root_url . '/user/login');
        }
    }

    /**
     * Обработка трафика по ping3
     * @param int $user_id
     * @return void
     */
    private function ping3Process(int $user_id)
    {
        if ($this->request->get('utm_term') === $this->rest_api_partner::UTM_TERM) {
            $order_id = $this->request->get('order_id', 'integer');
            $this->order_data->set($order_id, $this->user_data::PING3_VISIT, $user_id);

            // Тут для статистики по партнерам учтем все переходы по пользователю
            $this->user_data->set($user_id, $this->user_data::PING3_VISIT, trim($this->request->get('partner')));

            // Проверим и обработаем процесс изменения автозаявки ping3
            $crm_auto_approve_order_id = $this->ping3_data->getPing3Data($this->ping3_data::REPEAT_HAS_CRM_AUTO_APPROVE, $user_id);
            if (!empty($crm_auto_approve_order_id)) {
                $this->checkPing3CrmAutoApprove($user_id, $crm_auto_approve_order_id);
                return;
            }

            // Проверим и обработаем процесс изменения cross_order ping3
            $cross_order_order_id = $this->ping3_data->getPing3Data($this->ping3_data::REPEAT_HAS_CRM_CROSS_ORDER, $user_id);
            if (!empty($cross_order_order_id)) {
                $this->checkPing3CrossOrder($user_id, $cross_order_order_id);
            }
        }
    }

    /**
     * Проверяем, признак того что мы отдавали crm_auto_approve в ping3
     * @param int $user_id
     * @param int $crm_auto_approve_order_id
     * @return void
     */
    private function checkPing3CrmAutoApprove(int $user_id, int $crm_auto_approve_order_id)
    {
        // Если заявка crm_auto_approve найдена обработаем ее
        if ($crm_auto_approve_order_id) {
            $this->open_search_logger->create('Переписываем utm_term у заявки crm_auto_approve', [
                'user_id' => $user_id,
                'order_id' => $crm_auto_approve_order_id,
            ], 'user_auth_lk', \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            $this->orders->update_order($crm_auto_approve_order_id, ['utm_term' => $this->rest_api_partner::UTM_TERM]);
            $this->order_data->set($crm_auto_approve_order_id, $this->ping3_data::PING3_CRM_AUTO_APPROVE, 1);
        }
    }

    /**
     * Проверяем, признак того что мы отдавали cross_order в ping3
     * @param int $user_id
     * @param int $cross_order_order_id
     * @return void
     */
    private function checkPing3CrossOrder(int $user_id, int $cross_order_order_id)
    {
        // Если заявка crm_auto_approve найдена обработаем ее
        if ($cross_order_order_id) {
            $this->open_search_logger->create('Переписываем utm_term у заявки cross_order', [
                'user_id' => $user_id,
                'order_id' => $cross_order_order_id,
            ], 'user_auth_lk', \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            $this->orders->update_order($cross_order_order_id, ['utm_term' => $this->rest_api_partner::UTM_TERM]);
            $this->order_data->set($cross_order_order_id, $this->ping3_data::PING3_CRM_CROSS_ORDER, 1);
        }
    }
}
