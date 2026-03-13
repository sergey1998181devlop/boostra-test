<?php
require_once 'AService.php';

/**
 * Обновляет информацию по заявке статус и т.д...
 * Class UpdateOrderService
 */
class UpdateOrderService extends AService
{
    public function run()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json);

        $id_1c = $data->ИД;
        $status_1c = $data->Статус ?? '';
        $comment = $data->Комментарий ?? '';
        $official_response = $data->ОфициальныйОтвет ?? '';

        if ($this->config->update_status_order_1c) {
            $this->logging('update_status_order', 'update_status_order.php', $data, [], 'update_status_order_1c.txt');
        }

        if (empty($id_1c)) {
            http_response_code(404);
            $this->request->json_output(['error' => "1c_id is empty..."]);
        }

        $order_id = $this->orders->get_order_1cid($id_1c);
        $order = $this->orders->get_order($order_id);
        $update = $this->getUpdateStatusInfo([$status_1c, $comment, $official_response]);

        if ($order->status_1c != $status_1c) {
            $this->orders->update_order($order_id, $update);
        }

        $this->response = true;
        $this->json_output();
    }

    /**
     * @param array $data
     * @return array
     */
    private function getUpdateStatusInfo(array $data): array
    {
        list($stat, $comment, $official_response) = $data;

        if (empty($stat)) {
            http_response_code(404);
            $this->request->json_output(['error' => "status is empty..."]);
        }

        $result = [
            '1c_status' => $stat,
        ];

        switch ($stat):
            case 'Новая':
            case '1.Рассматривается':
            case '3.Одобрено':
            case '4.Готов к выдаче':
            case '5.Выдан':
            case '6.Закрыт':
                $result['comment'] = $comment;
                break;
            case '2.Отказано':
            case '7.Технический отказ':
            case 'Не определено':
                $result['comment'] = $comment;
                $result['official_response'] = $official_response;
                $result['status'] = Orders::ORDER_STATUS_CRM_REJECT;
                $result['reject_date'] = date('Y-m-d H:i:s');
                break;
        endswitch;

        return $result;
    }
}

(new UpdateOrderService())->run();
