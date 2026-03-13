<?php

require_once 'Simpla.php';

class ServiceReturnRequests extends Simpla
{
    /**
     * Получить заявку на возврат по ID
     * @param int $id
     * @return object|false
     */
    public function getById(int $id)
    {
        $this->db->query('SELECT * FROM __service_return_requests WHERE id = ?', $id);
        return $this->db->result();
    }
    
    /**
     * Получить последнюю заявку на возврат по типу услуги и ID
     * @param string $serviceType
     * @param int $servicePk
     * @return object|false
     */
    public function getLastByService(string $serviceType, int $servicePk)
    {
        $this->db->query(
            'SELECT * FROM __service_return_requests 
             WHERE service_type = ? AND service_pk = ? 
             ORDER BY created DESC LIMIT 1',
            $serviceType,
            $servicePk
        );
        return $this->db->result();
    }
    
    /**
     * Создать заявку на возврат
     * @param array $data
     * @return int ID созданной заявки
     */
    public function create(array $data): int
    {
        $this->db->query("INSERT INTO __service_return_requests SET ?%", $data);
        return $this->db->insert_id();
    }
    
    /**
     * Обновить статус заявки на возврат
     * @param int $id
     * @param string $status
     * @param string|null $errorText
     * @return bool
     */
    public function updateStatus(int $id, string $status, ?string $errorText = null): bool
    {
        $data = [
            'status' => $status,
            'updated' => 'NOW()'
        ];

        if ($errorText !== null) {
            $data['error_text'] = $errorText;
        }

        $this->db->query('UPDATE __service_return_requests SET ?% WHERE id = ?', $data, $id);
        return (bool)$this->db->affected_rows();
    }
    
    /**
     * Получить заявки на возврат для UI с расширенными данными
     * @param int $orderId
     * @return array
     */
    public function getReturnRequestsForUI(int $orderId): array
    {
        $this->db->query(
            "SELECT r.*, m.name AS manager_name
             FROM __service_return_requests r
             LEFT JOIN __managers m ON m.id = r.manager_id
             WHERE r.order_id = ?
             ORDER BY r.created DESC",
            $orderId
        );
        
        $requests = [];
        $results = $this->db->results();
        if (is_array($results)) {
            foreach ($results as $row) {
                $payload = json_decode($row->requisites_payload, true) ?: [];
                
                $statusMeta = $this->getReturnRequestStatusMeta($row->status);
                
                $requests[] = (object) [
                    'id' => (int)$row->id,
                    'created' => $row->created,
                    'updated' => $row->updated,
                    'service_type' => $row->service_type,
                    'service_pk' => (int)$row->service_pk,
                    'amount' => $row->amount,
                    'status' => $row->status,
                    'status_text' => $statusMeta['text'],
                    'status_badge' => $statusMeta['badge'],
                    'operation_id' => $row->operation_id,
                    'account_number' => $payload['account_number'] ?? '',
                    'bik' => $payload['bik'] ?? '',
                    'bank_name' => $payload['bank_name'] ?? '',
                    'error_text' => $row->error_text,
                    'manager_name' => $row->manager_name,
                ];
            }
        }
        
        return $requests;
    }

    /**
     * Получить заявки на возврат по заказу и типу услуги
     * @param int $orderId
     * @param string $serviceType
     * @return array
     */
    public function getByOrderAndType(int $orderId, string $serviceType): array
    {
        $this->db->query(
            "SELECT r.*, m.name AS manager_name
             FROM __service_return_requests r
             LEFT JOIN __managers m ON m.id = r.manager_id
             WHERE r.order_id = ? AND r.service_type = ?
             ORDER BY r.created DESC",
            $orderId,
            $serviceType
        );

        $results = $this->db->results();
        $requests = [];
        if (is_array($results)) {
            foreach ($results as $row) {
                $payload = json_decode($row->requisites_payload, true) ?: [];
    
                $statusMeta = $this->getReturnRequestStatusMeta($row->status);
    
                $requests[] = (object) [
                    'id' => (int)$row->id,
                    'created' => $row->created,
                    'updated' => $row->updated,
                    'amount' => $row->amount,
                    'status' => $row->status,
                    'status_text' => $statusMeta['text'],
                    'status_badge' => $statusMeta['badge'],
                    'account_number' => $payload['account_number'] ?? '',
                    'bik' => $payload['bik'] ?? '',
                    'bank_name' => $payload['bank_name'] ?? '',
                    'error_text' => $row->error_text,
                    'manager_name' => $row->manager_name,
                ];
            }
        }

        return $requests;
    }

    /**
     * Получить метаданные статуса (текст + badge)
     * @param string|null $status
     * @return array{text:string,badge:string}
     */
    private function getReturnRequestStatusMeta(?string $status): array
    {
        $map = [
            'new' => ['text' => 'Новая', 'badge' => 'secondary'],
            'sent' => ['text' => 'Отправлена', 'badge' => 'info'],
            'approved' => ['text' => 'Исполнена', 'badge' => 'success'],
            'error' => ['text' => 'Ошибка', 'badge' => 'danger'],
        ];

        return $map[$status] ?? ['text' => $status ?? '', 'badge' => 'light'];
    }
}
