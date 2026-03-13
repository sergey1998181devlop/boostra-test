<?php

require_once 'View.php';

class VitamedBaseTariffView extends View
{
    public function fetch()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            $id = $this->request->post('id');
            $from_amount = $this->request->post('from_amount');
            $to_amount = $this->request->post('to_amount');
            $price = $this->request->post('price');
            $licenseKeyDays = $this->request->post('license_key_days');

            if (empty($from_amount) || empty($to_amount) || empty($price) || empty($licenseKeyDays) ) {
                $this->response->json_output(['success' => false, 'error' => 'Все поля обязательны']);
                return;
            }

            $data = [
                'from_amount' => intval($from_amount),
                'to_amount'   => intval($to_amount),
                'price'       => floatval($price),
                'license_key_days'      => intval($licenseKeyDays),
                'is_new'      => 1,
            ];

            if (!empty($id)) {
                $this->tv_medical->updateVitamedCondition($id, $data);
            } else {
                $this->tv_medical->createVitamedCondition($data);
            }

            $this->response->json_output(['success' => true]);
        } elseif ($method === 'DELETE') {
            parse_str(file_get_contents("php://input"), $deleteData);
            $id = isset($deleteData['id']) ? intval($deleteData['id']) : null;

            if (!$id) {
                $this->response->json_output(['success' => false, 'error' => 'ID не указан']);
                return;
            }

            $this->tv_medical->deleteVitamedCondition($id);
            $this->response->json_output(['success' => true]);
        } else {
            $this->response->json_output(['success' => false, 'error' => 'Неподдерживаемый метод']);
        }
    }
}