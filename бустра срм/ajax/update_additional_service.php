<?php

chdir('..');

require 'api/Simpla.php';

class UpdateAdditionalService extends Simpla
{
    /**
     * @return void
     */
    public function run(): void
    {
        $orderId = $this->request->post('order_id');

        $additionalServiceTvMed = $this->request->post('additional_service_tv_med');
        $additionalServiceMultipolis = $this->request->post('additional_service_multipolis');
        $additionalServiceRepayment = $this->request->post('additional_service_repayment');
        $additionalServiceHalfRepayment = $this->request->post('half_additional_service_repayment');
        $additionalServicePartialRepayment = $this->request->post('additional_service_partial_repayment');
        $additionalServiceHalfPartialRepayment = $this->request->post('half_additional_service_partial_repayment');
        $additionalServiceSOPartialRepayment = $this->request->post('additional_service_so_partial_repayment');
        $additionalServiceHalfSOPartialRepayment = $this->request->post('half_additional_service_so_partial_repayment');
        $additionalServiceSORepayment = $this->request->post('additional_service_so_repayment');
        $additionalServiceHalfSORepayment = $this->request->post('half_additional_service_so_repayment');

        $managerId = $this->request->post('manager_id');
        $userId = $this->request->post('user_id');

        $order_data = $this->order_data->readAll($orderId);

        $currentAdditionalServiceTvMed = $order_data[$this->order_data::ADDITIONAL_SERVICE_TV_MED];
        $currentAdditionalServiceMultipolis = $order_data[$this->order_data::ADDITIONAL_SERVICE_MULTIPOLIS];
        $currentAdditionalServiceRepayment = $order_data[$this->order_data::ADDITIONAL_SERVICE_REPAYMENT];
        $currentAdditionalServiceHalfRepayment = $order_data[$this->order_data::HALF_ADDITIONAL_SERVICE_REPAYMENT];
        $currentAdditionalServicePartialRepayment = $order_data[$this->order_data::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT];
        $currentAdditionalServiceHalfPartialRepayment = $order_data[$this->order_data::HALF_ADDITIONAL_SERVICE_PARTIAL_REPAYMENT];
        $currentAdditionalServiceSOPartialRepayment = $order_data[$this->order_data::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT];
        $currentAdditionalServiceHalfSOPartialRepayment = $order_data[$this->order_data::HALF_ADDITIONAL_SERVICE_PARTIAL_REPAYMENT];
        $currentAdditionalServiceSORepayment = $order_data[$this->order_data::ADDITIONAL_SERVICE_REPAYMENT];
        $currentAdditionalServiceHalfSORepayment = $order_data[$this->order_data::HALF_ADDITIONAL_SERVICE_REPAYMENT];


        if (!is_null($additionalServiceTvMed) && $currentAdditionalServiceTvMed !== (boolean)$additionalServiceTvMed) {
            $this->order_data->set($orderId, $this->order_data::ADDITIONAL_SERVICE_TV_MED, $additionalServiceTvMed);
            $this->changelogs->add_changelog([
                'manager_id' => $managerId,
                'created' => date('Y-m-d H:i:s'),
                'type' => $this->order_data::ADDITIONAL_SERVICE_TV_MED,
                'old_values' => $additionalServiceTvMed ? 'Включение' : 'Выключение',
                'new_values' => $additionalServiceTvMed ? 'Выключение' : 'Включение',
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
        }
        if (!is_null($additionalServiceMultipolis) && $currentAdditionalServiceMultipolis !== (boolean)$additionalServiceMultipolis) {
            $this->order_data->set($orderId, $this->order_data::ADDITIONAL_SERVICE_MULTIPOLIS, $additionalServiceMultipolis);
            $this->changelogs->add_changelog([
                'manager_id' => $managerId,
                'created' => date('Y-m-d H:i:s'),
                'type' => $this->order_data::ADDITIONAL_SERVICE_MULTIPOLIS,
                'old_values' => $additionalServiceMultipolis ? 'Включение' : 'Выключение',
                'new_values' => $additionalServiceMultipolis ? 'Выключение' : 'Включение',
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
        }
        if (!is_null($additionalServiceRepayment) && $currentAdditionalServiceRepayment !== (boolean)$additionalServiceRepayment) {
            $this->order_data->set($orderId, $this->order_data::ADDITIONAL_SERVICE_REPAYMENT, $additionalServiceRepayment);
            $this->changelogs->add_changelog([
                'manager_id' => $managerId,
                'created' => date('Y-m-d H:i:s'),
                'type' => $this->order_data::ADDITIONAL_SERVICE_REPAYMENT,
                'old_values' => $additionalServiceRepayment ? 'Включение' : 'Выключение',
                'new_values' => $additionalServiceRepayment ? 'Выключение' : 'Включение',
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
        }
        if (!is_null($additionalServiceHalfRepayment) && $currentAdditionalServiceHalfRepayment !== (boolean)$additionalServiceHalfRepayment) {
            $this->order_data->set($orderId, $this->order_data::HALF_ADDITIONAL_SERVICE_REPAYMENT, $additionalServiceHalfRepayment);
            $this->changelogs->add_changelog([
                'manager_id' => $managerId,
                'created' => date('Y-m-d H:i:s'),
                'type' => $this->order_data::HALF_ADDITIONAL_SERVICE_REPAYMENT,
                'old_values' => $additionalServiceHalfRepayment ? 'Включение' : 'Выключение',
                'new_values' => $additionalServiceHalfRepayment ? 'Выключение' : 'Включение',
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
        }
        if (!is_null($additionalServicePartialRepayment) && $currentAdditionalServicePartialRepayment !== (boolean)$additionalServicePartialRepayment) {
            $this->order_data->set($orderId, $this->order_data::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT, $additionalServicePartialRepayment);
            $this->changelogs->add_changelog([
                'manager_id' => $managerId,
                'created' => date('Y-m-d H:i:s'),
                'type' => $this->order_data::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT,
                'old_values' => $additionalServicePartialRepayment ? 'Включение' : 'Выключение',
                'new_values' => $additionalServicePartialRepayment ? 'Выключение' : 'Включение',
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
        }
        if (!is_null($additionalServiceHalfPartialRepayment) && $currentAdditionalServiceHalfPartialRepayment !== (boolean)$additionalServiceHalfPartialRepayment) {
            $this->order_data->set($orderId, $this->order_data::HALF_ADDITIONAL_SERVICE_PARTIAL_REPAYMENT, $additionalServiceHalfPartialRepayment);
            $this->changelogs->add_changelog([
                'manager_id' => $managerId,
                'created' => date('Y-m-d H:i:s'),
                'type' => $this->order_data::HALF_ADDITIONAL_SERVICE_PARTIAL_REPAYMENT,
                'old_values' => $additionalServiceHalfPartialRepayment ? 'Включение' : 'Выключение',
                'new_values' => $additionalServiceHalfPartialRepayment ? 'Выключение' : 'Включение',
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
        }
        
        if (!is_null($additionalServiceSOPartialRepayment) && $currentAdditionalServiceSOPartialRepayment !== (boolean)$additionalServiceSOPartialRepayment) {
            $this->order_data->set($orderId, $this->order_data::ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT, $additionalServiceSOPartialRepayment);
            $this->changelogs->add_changelog([
                'manager_id' => $managerId,
                'created' => date('Y-m-d H:i:s'),
                'type' => $this->order_data::ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
                'old_values' => $additionalServiceSOPartialRepayment ? 'Включение' : 'Выключение',
                'new_values' => $additionalServiceSOPartialRepayment ? 'Выключение' : 'Включение',
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
        }
        if (!is_null($additionalServiceHalfSOPartialRepayment) && $currentAdditionalServiceHalfSOPartialRepayment !== (boolean)$additionalServiceHalfSOPartialRepayment) {
            $this->order_data->set($orderId, $this->order_data::HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT, $additionalServiceHalfSOPartialRepayment);
            $this->changelogs->add_changelog([
                'manager_id' => $managerId,
                'created' => date('Y-m-d H:i:s'),
                'type' => $this->order_data::HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
                'old_values' => $additionalServiceHalfSOPartialRepayment ? 'Включение' : 'Выключение',
                'new_values' => $additionalServiceHalfSOPartialRepayment ? 'Выключение' : 'Включение',
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
        }
        if (!is_null($additionalServiceSORepayment) && $currentAdditionalServiceSORepayment !== (boolean)$additionalServiceSORepayment) {
            $this->order_data->set($orderId, $this->order_data::ADDITIONAL_SERVICE_SO_REPAYMENT, $additionalServiceSORepayment);
            $this->changelogs->add_changelog([
                'manager_id' => $managerId,
                'created' => date('Y-m-d H:i:s'),
                'type' => $this->order_data::ADDITIONAL_SERVICE_SO_REPAYMENT,
                'old_values' => $additionalServiceSORepayment ? 'Включение' : 'Выключение',
                'new_values' => $additionalServiceSORepayment ? 'Выключение' : 'Включение',
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
        }
        if (!is_null($additionalServiceHalfSORepayment) && $currentAdditionalServiceHalfSORepayment !== (boolean)$additionalServiceHalfSORepayment) {
            $this->order_data->set($orderId, $this->order_data::HALF_ADDITIONAL_SERVICE_SO_REPAYMENT, $additionalServiceHalfSORepayment);
            $this->changelogs->add_changelog([
                'manager_id' => $managerId,
                'created' => date('Y-m-d H:i:s'),
                'type' => $this->order_data::HALF_ADDITIONAL_SERVICE_SO_REPAYMENT,
                'old_values' => $additionalServiceHalfSORepayment ? 'Включение' : 'Выключение',
                'new_values' => $additionalServiceHalfSORepayment ? 'Выключение' : 'Включение',
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
        }
        $this->response->json_output("success");
    }
}

(new UpdateAdditionalService())->run();
