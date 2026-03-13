<?php

chdir(dirname(__FILE__) . '/../');
require_once 'api/Simpla.php';

class UpdateReturnByRequisitesStatuses extends Simpla
{
    private const STATUSES_TO_SYNC = ['new', 'sent'];

    public function __construct()
    {
        parent::__construct();
        $this->process();
    }

    /**
     * Обработка обновления статусов заявок
     * @return void
     */
    private function process(): void
    {
        $requests = $this->getPendingRequests();

        if (empty($requests)) {
            return;
        }

        foreach ($requests as $request) {
            $response = $this->soap->getStatusRequestReturnService([
                'operation_id' => $request->operation_id,
                'service_type' => $request->service_type,
            ]);

            if (isset($response['error'])) {
                $this->logging(__METHOD__, 'return_by_requisites_status', [
                    'request_id' => $request->id,
                    'error' => $response['error'],
                ], []);
                continue;
            }

            if (empty($response)) {
                continue;
            }

            $first = $response[0] ?? null;

            if (!is_array($first)) {
                continue;
            }

            $newStatus = $this->mapStatusFrom1C($first['Статус'] ?? '');
            $errorText = $first['ОписаниеОшибки'] ?? null;

            if ($newStatus === $request->status && $errorText === $request->error_text) {
                continue;
            }

            $this->serviceReturnRequests->updateStatus($request->id, $newStatus, $errorText);
        }
    }

    /**
     * Получить заявки требующие синхронизации статуса
     * @return array
     */
    private function getPendingRequests(): array
    {
        $this->db->query(
            'SELECT * FROM __service_return_requests 
             WHERE status IN (?@) 
               AND service_type != ? 
             ORDER BY updated ASC 
             LIMIT 100',
            self::STATUSES_TO_SYNC,
            'overpayment'
        );

        return (array) $this->db->results();
    }
    
    /**
     * Маппинг статуса из 1С в CRM
     * @param string $status
     * @return string
     */
    private function mapStatusFrom1C(string $status): string
    {
        $map = [
            'Новая' => 'new',
            'Отправлена' => 'sent',
            'Исполнена' => 'approved',
            'Ошибка' => 'error',
            'Ошибка / сбой' => 'error',
        ];

        return $map[$status] ?? 'sent';
    }
}

new UpdateReturnByRequisitesStatuses();
