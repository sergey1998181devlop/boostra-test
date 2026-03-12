<?php

require_once dirname(__DIR__) . '/api/Simpla.php';

error_reporting(E_ERROR);

/**
 * Класс отправляющий чеки
 * class CronSendReceipts
 */
class CronGetLicenseKeys extends Simpla
{
    /**
     * @throws Exception
     */
    public function run(): void
    {
        $isNullKeys = $this->request->get('is_null', 'integer');
        $order_id = $this->request->get('order_id', 'integer');
        $type = $this->request->get('type', 'string');
        $limit = $this->request->get('limit', 'integer') ?: 10;

        if ($isNullKeys) {
            $this->resendErrors($limit, $type, $order_id);
            return;
        }

        $this->setOracleDocumentLicenseKey($limit);
        $this->setTvmedDocumentLicenseKey($limit);
        $this->setMultipolisDocumentLicenseKey($limit);
    }

    /**
     * @throws Exception
     */
    public function setOracleDocumentLicenseKey($limit = 10): void
    {
        $filter_data = [
            'ready' => 0,
            'type' => [Documents::STAR_ORACLE_POLICY],
            'limit' => $limit,
            'created' => '2024-01-01 01:01:01'
        ];

        $allDocuments = $this->documents->get_documents($filter_data);

        foreach ($allDocuments as $document) {
            $params = $document->params;

            if ($params->license_key) {
                continue;
            }

            $starOracle = $this->star_oracle->getStarOracle($document->order_id, $document->user_id, 'SUCCESS');

            $license_key = $this->dop_license->createLicenseWithKey(
                $this->dop_license::SERVICE_STAR_ORACLE,
                [
                    'user_id' => $document->user_id,
                    'order_id' => $document->order_id,
                    'service_id' => $starOracle->id ?? 1,
                    'organization_id' => $document->organization_id,
                    'amount' => $params->amount,
                ]
            );

            if ($license_key) {
                $params->license_key = $license_key;
                $this->documents->update_document(
                    $document->id,
                    [
                        'params' => $params,
                        'ready' => 1,
                    ]
                );
            } else {
                $this->documents->update_document(
                    $document->id,
                    [
                        'ready' => 2,
                    ]
                );
            }
        }
    }

    /**
     * @param int $limit
     * @return void
     * @throws Exception
     */
    public function setTvmedDocumentLicenseKey(int $limit = 10): void
    {
        $filter_data = [
            'ready' => 0,
            'type' => [Documents::ACCEPT_TELEMEDICINE],
            'limit' => $limit,
            'created' => '2024-01-01 01:01:01'
        ];

        $allDocuments = $this->documents->get_documents($filter_data);

        foreach ($allDocuments as $document) {
            $params = $document->params;

            if ($params['license_key']) {
                $this->documents->update_document(
                    $document->id,
                    [
                        'ready' => 1,
                    ]
                );
                continue;
            }

            if (!$amount = $params['products'][0]['price'] ?? 0) {
                $this->logging(
                    __METHOD__,
                    'no amount in document params vitamed - ' . $document->id,
                    $params['products'][0] ?? '--',
                    $params['products'] ?? '--',
                    'dop_api.txt'
                );
                $this->documents->update_document(
                    $document->id,
                    [
                        'ready' => 2,
                    ]
                );
                continue;
            }

            $filter_tvmed = [
                'filter_user_id' => $document->user_id,
                'filter_order_id' => $document->order_id,
                'filter_status' => 'SUCCESS',
            ];

            $tvmed = $this->tv_medical->selectPayments($filter_tvmed, false);

            $license_key = $this->dop_license->createLicenseWithKey(
                $this->dop_license::SERVICE_VITAMED,
                [
                    'user_id' => $document->user_id,
                    'order_id' => $document->order_id,
                    'service_id' => $tvmed->id ?? 1,
                    'organization_id' => $document->organization_id,
                    'amount' => $amount,
                ]
            );

            if ($license_key) {
                $params['license_key'] = $license_key;
                $this->documents->update_document(
                    $document->id,
                    [
                        'params' => $params,
                        'ready' => 1,
                    ]
                );
            } else {
                $this->documents->update_document(
                    $document->id,
                    [
                        'ready' => 2,
                    ]
                );
            }
        }
    }

    /**
     * @param int $limit
     * @return void
     * @throws Exception
     */
    public function setMultipolisDocumentLicenseKey(int $limit = 10): void
    {
        $filter_data = [
            'ready' => 0,
            'type' => [Documents::DOC_MULTIPOLIS],
            'limit' => $limit,
            'created' => '2024-01-01 01:01:01'
        ];

        $allDocuments = $this->documents->get_documents($filter_data);

        foreach ($allDocuments as $document) {
            $params = $document->params;

            if ($params['license_key']) {
                $this->documents->update_document(
                    $document->id,
                    [
                        'ready' => 1,
                    ]
                );
                continue;
            }
            if (!($params['amount'] ?? 0)) {
                $this->logging(
                    __METHOD__,
                    'no amount in document params multipolis - ' . $document->id,
                    $params['amount'] ?? '--',
                    $params,
                    'dop_api.txt'
                );
                $this->documents->update_document(
                    $document->id,
                    [
                        'ready' => 2,
                    ]
                );
                continue;
            }

            $filter_multipolis = [
                'filter_user_id' => $document->user_id,
                'filter_order_id' => $document->order_id,
                'filter_status' => 'SUCCESS',
            ];

            $multipolis = $this->multipolis->selectAll($filter_multipolis, false);

            $license_key = $this->dop_license->createLicenseWithKey(
                $this->dop_license::SERVICE_CONCIERGE,
                [
                    'user_id' => $document->user_id,
                    'order_id' => $document->order_id,
                    'service_id' => $multipolis->id ?? 1,
                    'organization_id' => $document->organization_id,
                    'amount' => $params['amount'],
                ]
            );

            if ($license_key) {
                $params['license_key'] = $license_key;
                $this->documents->update_document(
                    $document->id,
                    [
                        'params' => $params,
                        'ready' => 1,
                    ]
                );
            } else {
                $this->documents->update_document(
                    $document->id,
                    [
                        'ready' => 2,
                    ]
                );
            }
        }
    }


    /**
     * @param $limit
     * @param string|null $type
     * @param int|null $filter_order_id
     * @return void
     */
    public function resendErrors($limit, string $type = null, int $filter_order_id = null): void
    {
        $filter_data = [
            'filter_empty_license_key' => 1,
            'limit' => $limit
        ];

        if ($type) {
            $filter_data['filter_service_type'] = $type;
        }

        if ($filter_order_id) {
            $filter_data['filter_order_id'] = $filter_order_id;
            unset($filter_data['filter_empty_license_key']);
        }

        $allLicenseKeys = $this->dop_license->getAllLicenseKeys($filter_data);

        foreach ($allLicenseKeys as $licenseKey) {
            $this->dop_license->getLicenseKey($licenseKey);
        }
    }
}

$start = microtime(true);
(new CronGetLicenseKeys())->run();
$end = microtime(true);

$time_worked = microtime(true) - $start;
exit(date('c', $start) . ' - ' . date('c', $end) . ' :: script ' . __FILE__ . ' work ' . $time_worked . '  s.');
