<?php

session_start();

chdir('..');
require 'api/Simpla.php';

/**
 * Контроллер возврата дополнительных услуг по реквизитам
 * Обрабатывает отправку заявок на возврат в 1С через банковский перевод
 */
class RefundByRequisites extends Simpla
{
    private $manager;
    private $response = ['status' => false];
    
    public function __construct()
    {
        parent::__construct();
        
        if ($this->request->isJson()) {
            if (!$this->request->verifyBearerToken()) {
                $this->response['message'] = 'Invalid or missing token';
                http_response_code(403);
                $this->request->json_output($this->response);
            }
        }
        
        $manager_id = $_SESSION['manager_id'] ?? null;
        if (!$manager_id) {
            $this->response['message'] = 'Менеджер не найден';
            $this->request->json_output($this->response);
        }
        
        $this->manager = $this->managers->get_manager($manager_id);
        if (!$this->manager || $this->manager->blocked == 1) {
            $this->response['message'] = 'Менеджер заблокирован';
            $this->request->json_output($this->response);
        }

        $permissions = $this->managers->get_permissions($this->manager->role);
        $hasAccessToRefunds = in_array('insures', $permissions ?? []);

        if (!$hasAccessToRefunds) {
            $this->response['message'] = 'Нет прав доступа к возвратам';
            http_response_code(403);
            $this->request->json_output($this->response);
        }

        $action = $this->request->post('action') ?? $this->request->get('action');
        
        switch ($action) {
            case 'GetAdditionalData':
                $this->response = $this->actionGetAdditionalData();
                break;
            case 'GetOverpaymentAmount':
                $this->response = $this->actionGetOverpaymentAmount();
                break;
            case 'Send':
                $this->response = $this->actionSend();
                break;
            case 'RefreshStatus':
                $this->response = $this->actionRefreshStatus();
                break;
            default:
                $this->response['message'] = 'Неизвестное действие';
        }
        
        $this->request->json_output($this->response);
    }

    /**
     * Получить дополнительные данные для модального окна возврата
     * @return array
     */
    private function actionGetAdditionalData(): array
    {
        $serviceType = $this->request->post('service_type', 'string');
        $serviceId = $this->request->post('service_id', 'integer');
        
        if (!$serviceType || !$serviceId) {
            return ['status' => false, 'message' => 'Недостаточно параметров'];
        }
        
        try {
            $service = $this->getService($serviceType, $serviceId);
            
            if (!$this->isValidService($service, $serviceType)) {
                return ['status' => false, 'message' => 'Услуга не найдена'];
            }

            $operationId = $this->getOperationId($service, $serviceType);
            $userId = $this->getServiceUserId($service);

            $savedRequisites = $this->userBankRequisites->getByUserId($userId);
            $lastRequest = $this->serviceReturnRequests->getLastByService($serviceType, $serviceId);
            
            return [
                'status' => true,
                'data' => [
                    'operation_id' => $operationId,
                    'saved_requisites' => $savedRequisites,
                    'return_request' => $this->formatReturnRequest($lastRequest),
                ]
            ];
            
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Получить сумму переплаты из 1С
     * @return array
     */
    private function actionGetOverpaymentAmount(): array
    {
        $orderId = $this->request->post('order_id', 'integer');

        if (!$orderId) {
            return ['status' => false, 'message' => 'Не передан ID заказа'];
        }

        try {
            $contractNumber = $this->getContractNumberByOrderId($orderId);
            
            if (empty($contractNumber)) {
                return ['status' => false, 'message' => 'Не найден номер контракта для заказа'];
            }

            $result = $this->soap->getOverpaymentAmount($contractNumber);

            if (isset($result['error'])) {
                return [
                    'status' => false,
                    'message' => 'Сервер 1С не отвечает, попробуйте позже',
                    'error' => $result['error']
                ];
            }

            $amount = (float)$result;

            if ($amount <= 0) {
                return ['status' => false, 'message' => 'У клиента нет переплаты'];
            }

            return [
                'status' => true,
                'amount' => $amount
            ];

        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Сервер 1С не отвечает, попробуйте позже',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Отправка заявки на возврат по реквизитам в 1С
     * @return array
     */
    private function actionSend(): array
    {
        $data = [
            'service_type' => $this->request->post('service_type', 'string'),
            'service_id' => $this->request->post('service_id', 'integer'),
            'amount' => $this->request->post('amount'),
            'recipient_fio' => trim(($this->request->post('recipient_fio', 'string') ?? '')),
            'requisites_mode' => $this->request->post('requisites_mode', 'string'),
            'requisites_id' => $this->request->post('requisites_id', 'integer'),
            'account_number' => $this->request->post('account_number', 'string'),
            'bik' => $this->request->post('bik', 'string'),
            'bank_name' => $this->request->post('bank_name', 'string'),
            'save_requisites' => $this->request->post('save_requisites', 'boolean'),
            'set_default' => $this->request->post('set_default', 'boolean'),
        ];

        if (empty($data['service_type']) || empty($data['service_id']) || empty($data['amount'])) {
            return ['status' => false, 'message' => 'Заполните все обязательные поля'];
        }

        if (empty($data['recipient_fio'])) {
            return ['status' => false, 'message' => 'Укажите ФИО получателя'];
        }
        
        if (empty($data['account_number']) || empty($data['bik'])) {
            return ['status' => false, 'message' => 'Укажите номер счета и БИК банка'];
        }
        
        try {
            $service = $this->getService($data['service_type'], $data['service_id']);
            
            if (!$this->isValidService($service, $data['service_type'])) {
                return ['status' => false, 'message' => 'Услуга не найдена'];
            }

            $validationError = $this->validateReturnAmount($data['amount'], $service, $data['service_type']);
            if ($validationError) {
                return $validationError;
            }

            $userId = $this->getServiceUserId($service);
            $orderId = $this->getServiceOrderId($service);

            $shouldSaveRequisites = false;
            $requisitesData = null;

            if ($data['requisites_mode'] === 'new' && !empty($data['save_requisites'])) {
                $shouldSaveRequisites = true;
                $requisitesData = [
                    'user_id' => $userId,
                    'account_number' => $data['account_number'],
                    'bik' => $data['bik'],
                    'bank_name' => $data['bank_name'] ?? '',
                    'recipient_fio' => $data['recipient_fio'],
                    'is_default' => !empty($data['set_default']) ? 1 : 0,
                ];
            }

            $order = null;
            if ($data['service_type'] === 'overpayment') {
                $order = $this->orders->get_order($orderId);
                $response = $this->soap->addRequestReturnOverpayment([
                    'loan_number' => $order->contract_number ?? $order->id_1c,
                    'amount' => $data['amount'],
                    'account_number' => $data['account_number'],
                    'bik' => $data['bik'],
                    'recipient_fio' => $data['recipient_fio'],
                ]);
                $operationId = null;
            } else {
                $operationId = $this->getOperationId($service, $data['service_type']);
                if (!$operationId) {
                    return ['status' => false, 'message' => 'Не найден OperationID для услуги (отсутствует транзакция/платеж покупки)'];
                }

                $response = $this->soap->addRequestReturnService([
                    'service_type' => $data['service_type'],
                    'amount' => $data['amount'],
                    'operation_id' => $operationId,
                    'account_number' => $data['account_number'],
                    'bik' => $data['bik'],
                    'recipient_fio' => $data['recipient_fio'],
                ]);
            }

            $responseText = isset($response->return) ? (string)$response->return : null;
            
            if ($responseText !== 'ОК') {
                $errorText = $responseText ?? ($response->faultstring ?? 'Неизвестная ошибка 1С');
                return [
                    'status' => false,
                    'error_text' => $errorText,
                    'message' => "Ошибка 1С: {$errorText}",
                ];
            }

            $this->db->begin_transaction();

            try {
                $requisitesId = null;
                if ($shouldSaveRequisites && $requisitesData) {
                    $requisitesId = $this->userBankRequisites->create($requisitesData);
                } elseif ($data['requisites_mode'] === 'saved' && !empty($data['requisites_id'])) {
                    $requisitesId = $data['requisites_id'];
                }

                $reference = $this->getReturnReference($service, $data['service_type']);

                $description = $data['service_type'] === 'overpayment'
                    ? "Возврат переплаты по реквизитам"
                    : "Возврат по реквизитам '" . $this->getServiceName($data['service_type']) . "' от {$service->date_added}";

                $returnTransactionId = $this->best2pay->add_transaction([
                    'user_id' => $userId,
                    'order_id' => $orderId,
                    'type' => $this->getReturnTransactionType($data['service_type']),
                    'amount' => $data['amount'] * 100,
                    'sector' => 0,
                    'register_id' => 0,
                    'contract_number' => $this->getContractNumberByOrderId($orderId),
                    'reference' => $reference,
                    'description' => $description,
                    'created' => date('Y-m-d H:i:s'),
                    'operation' => $operationId,
                    'reason_code' => 1,
                    'state' => 'APPROVED',
                    'body' => json_encode(['return_by_requisites' => true, 'original_operation_id' => $operationId]),
                    'operation_date' => date('Y-m-d H:i:s'),
                    'callback_response' => 'return_by_requisites',
                ]);

                $requestId = $this->serviceReturnRequests->create([
                    'user_id' => $userId,
                    'order_id' => $orderId,
                    'service_type' => $data['service_type'],
                    'service_pk' => $data['service_id'],
                    'operation_id' => $operationId,
                    'amount' => $data['amount'],
                    'requisites_id' => $requisitesId,
                    'requisites_payload' => json_encode([
                        'account_number' => $data['account_number'],
                        'bik' => $data['bik'],
                        'bank_name' => $data['bank_name'] ?? '',
                        'recipient_fio' => $data['recipient_fio'],
                    ]),
                    'status' => 'new',
                    'manager_id' => $this->manager->id,
                    'return_transaction_id' => $returnTransactionId,
                    'created' => date('Y-m-d H:i:s'),
                    'updated' => date('Y-m-d H:i:s'),
                ]);

                if ($data['service_type'] !== 'overpayment') {
                    $statusResponse = $this->soap->getStatusRequestReturnService([
                        'operation_id' => $operationId,
                        'service_type' => $data['service_type'],
                    ]);

                    if (!empty($statusResponse)) {
                        $first = $statusResponse[0] ?? null;

                        if (is_array($first)) {
                            $newStatus = $this->mapStatusFrom1C($first['Статус'] ?? '');
                            $errorText = $first['ОписаниеОшибки'] ?? null;
                            $this->serviceReturnRequests->updateStatus($requestId, $newStatus, $errorText);
                        }
                    }
                }

                $this->updateService($data['service_type'], $data['service_id'], [
                    'return_status' => 2,
                    'amount_total_returned' => ($service->amount_total_returned ?? 0) + $data['amount'],
                    'return_date' => date('Y-m-d H:i:s'),
                    'return_amount' => $data['amount'],
                    'return_transaction_id' => $returnTransactionId,
                    'return_sent' => 2,
                    'return_by_manager_id' => $this->manager->id,
                ]);

                $organizationId = $service->organization_id;
                $paymentType = $this->getReceiptPaymentTypeForRequisites($data['service_type']);

                if ($paymentType === $this->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_REQUISITES) {
                    $organizationId = $this->receipts::ORGANIZATION_FINTEHMARKET;
                }
                
                $this->receipts->addItem([
                    'user_id' => $userId,
                    'order_id' => $orderId,
                    'amount' => $data['amount'],
                    'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                    'payment_type' => $paymentType,
                    'organization_id' => $organizationId,
                    'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$paymentType] ?? 'Возврат услуги по реквизитам',
                    'transaction_id' => $returnTransactionId,
                ]);

                if ($data['service_type'] === 'overpayment') {
                    if (!$order) {
                        $order = $this->orders->get_order($orderId);
                    }
                    $commentText = "Возврат переплаты по Договору {$order->contract_number} в сумме {$data['amount']} руб.";
                } else {
                    $secondReturnText = ($service->return_status ?? 0) === 2 ? ' оставшейся части' : '';
                    $commentText = "Возврат по реквизитам{$secondReturnText} '{$service->title}' от {$service->date_added} (Дата услуги). Сумма: {$data['amount']} руб.";
                }
                
                $this->comments->add_comment([
                    'manager_id' => $this->manager->id,
                    'user_id' => $userId,
                    'order_id' => $orderId,
                    'block' => 'return_by_requisites',
                    'text' => $commentText,
                    'created' => date('Y-m-d H:i:s'),
                ]);
                
                $orderNumber = $data['service_type'] === 'overpayment'
                    ? ($order->id_1c ?? '')
                    : ($service->order->id_1c ?? '');
                
                $this->soap->send_comment([
                    'manager' => $this->manager->name_1c,
                    'text' => $commentText,
                    'created' => date('Y-m-d H:i:s'),
                    'number' => $orderNumber,
                ]);

                $this->changelogs->add_changelog([
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'return_by_requisites_' . $data['service_type'],
                    'old_values' => $data['service_type'] === 'overpayment' ? $orderId : $service->id,
                    'new_values' => serialize(['amount' => $data['amount'], 'operation_id' => $operationId]),
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'file_id' => $returnTransactionId,
                ]);

                $this->db->commit();

                return [
                    'status' => true,
                    'message' => 'Успешно отправлено',
                    'data' => [
                        'request_id' => $requestId,
                        'request' => $this->formatReturnRequest($this->serviceReturnRequests->getById($requestId)),
                    ],
                ];
                
            } catch (Exception $e) {
                $this->db->rollback();
                return ['status' => false, 'message' => 'Ошибка БД: ' . $e->getMessage()];
            }
            
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }
    
    /**
     * Получить OperationID покупки услуги для отправки в 1С
     * @param object $service
     * @param string $serviceType
     * @return int|null
     */
    private function getOperationId(object $service, string $serviceType): ?int
    {
        switch ($serviceType) {
            case 'credit_doctor':
                if (!$service->transaction_id) return null;
                
                if (!empty($service->is_penalty)) {
                    $payment = $this->best2pay->get_payment($service->transaction_id);
                    return $payment->operation_id ?? null;
                }
                
                $transaction = $this->best2pay->get_transaction($service->transaction_id);
                return $transaction->operation ?? null;
                
            case 'star_oracle':
                if (!$service->transaction_id) return null;
                
                if (empty($service->action_type) || $service->action_type === 'issuance') {
                    $transaction = $this->best2pay->get_transaction($service->transaction_id);
                    return $transaction->operation ?? null;
                }
                
                $payment = $this->best2pay->get_payment($service->transaction_id);
                return $payment->operation_id ?? null;


            case 'safe_deal':
                if (!$service->transaction_id) return null;

                $transaction = $this->best2pay->get_transaction($service->transaction_id);
                return $transaction->operation ?? null;
            case 'multipolis':
            case 'tv_medical':
            if (!$service->payment_id) {
                return null;
            }
            // Определяем по action_type: issuance -> transaction, остальное -> payment
            if ($service->action_type === 'issuance') {
                $transaction = $this->best2pay->get_transaction($service->payment_id);
                return $transaction->operation ?? null;
            }

                $payment = $this->best2pay->get_payment($service->payment_id);
                return $payment->operation_id ?? null;

            default:
                return null;
        }
    }
    
    /**
     * Загрузить услугу по типу и ID
     * @param string $serviceType
     * @param int $serviceId
     * @return object|null
     */
    private function getService(string $serviceType, int $serviceId): ?object
    {
        switch ($serviceType) {
            case 'credit_doctor':
                return $this->credit_doctor->getCreditDoctor($serviceId);
            case 'star_oracle':
                return $this->star_oracle->getStarOracleById($serviceId);
            case 'safe_deal':
                return $this->safe_deal->getById($serviceId);
            case 'multipolis':
                return $this->multipolis->get_multipolis($serviceId);
            case 'tv_medical':
                return $this->tv_medical->getPaymentById($serviceId);
            case 'overpayment':
                return $this->orders->get_order($serviceId);
            default:
                return null;
        }
    }
    
    /**
     * Обновить данные услуги
     * @param string $serviceType
     * @param int $serviceId
     * @param array $data
     * @return void
     */
    private function updateService(string $serviceType, int $serviceId, array $data): void
    {
        $updateMap = [
            'credit_doctor' => [$this->credit_doctor, 'updateUserCreditDoctorData'],
            'multipolis' => [$this->multipolis, 'update_multipolis'],
            'tv_medical' => [$this->tv_medical, 'updatePayment'],
            'star_oracle' => [$this->star_oracle, 'updateStarOracleData'],
            'safe_deal' => [$this->safe_deal, 'update'],
        ];
        
        if ($serviceType === 'overpayment') {
            return;
        }
        
        if (isset($updateMap[$serviceType])) {
            call_user_func($updateMap[$serviceType], $serviceId, $data);
        }
    }
    
    /**
     * Обновить статус заявки на возврат из 1С
     * @return array
     */
    private function actionRefreshStatus(): array
    {
        $requestId = $this->request->post('request_id', 'integer');

        if (empty($requestId)) {
            return ['status' => false, 'message' => 'Не передан идентификатор заявки'];
        }

        $request = $this->serviceReturnRequests->getById($requestId);

        if (!$request) {
            return ['status' => false, 'message' => 'Заявка не найдена'];
        }

        try {
            $statusResponse = $this->soap->getStatusRequestReturnService([
                'operation_id' => $request->operation_id,
                'service_type' => $request->service_type,
            ]);

            if (isset($statusResponse['error'])) {
                return ['status' => false, 'message' => $statusResponse['error']];
            }

            $newStatus = $request->status;
            $errorText = null;

            if (!empty($statusResponse)) {
                $first = $statusResponse[0] ?? null;

                if (is_array($first)) {
                    $newStatus = $this->mapStatusFrom1C($first['Статус'] ?? '');
                    $errorText = $first['ОписаниеОшибки'] ?? null;
                }
            }

            if (!$this->serviceReturnRequests->updateStatus($requestId, $newStatus, $errorText)) {
                return ['status' => false, 'message' => 'Не удалось обновить статус'];
            }

            return [
                'status' => true,
                'data' => $this->formatReturnRequest($this->serviceReturnRequests->getById($requestId)),
            ];

        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Получить тип транзакции возврата
     * @param string $serviceType
     * @return string
     */
    private function getReturnTransactionType(string $serviceType): string
    {
        $map = [
            'credit_doctor' => 'REFUND_CREDIT_DOCTOR_REQUISITES',
            'multipolis' => 'REFUND_MULTIPOLIS_REQUISITES',
            'tv_medical' => 'REFUND_TV_MEDICAL_REQUISITES',
            'star_oracle' => 'REFUND_STAR_ORACLE_REQUISITES',
            'safe_deal' => 'REFUND_SAFE_DEAL_REQUISITES',
            'overpayment' => 'REFUND_OVERPAYMENT_REQUISITES',
        ];
        return $map[$serviceType] ?? 'REFUND_CREDIT_DOCTOR_REQUISITES';
    }
    
    /**
     * Получить идентификатор оригинальной транзакции/платежа для привязки возврата
     * @param object $service
     * @param string $serviceType
     * @return mixed
     */
    private function getReturnReference(object $service, string $serviceType)
    {
        if (empty($service)) {
            return null;
        }

        switch ($serviceType) {
            case 'credit_doctor':
                // Для credit_doctor: is_penalty -> payment_id, иначе -> transaction_id
                if (!empty($service->is_penalty)) {
                    return $service->payment_id ?? $service->transaction_id;
                }
                return $service->transaction_id ?? $service->payment_id;

            case 'safe_deal':
                return $service->transaction_id;
            case 'star_oracle':
                // Определяем по action_type: issuance -> transaction_id, остальное -> payment_id
                if (empty($service->action_type) || $service->action_type === 'issuance') {
                    return $service->transaction_id ?? $service->payment_id;
                }
                return $service->payment_id ?? $service->transaction_id;

            case 'multipolis':
            case 'tv_medical':
            return $service->payment_id ?? $service->transaction_id;

            case 'overpayment':
                return $service->order_id;
        }

        return $service->id ?? null;
    }
    
    /**
     * Получить тип чека для возврата по реквизитам
     * @param string $serviceType
     * @return string
     */
    private function getReceiptPaymentTypeForRequisites(string $serviceType): string
    {
        $map = [
            'credit_doctor' => $this->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_REQUISITES,
            'multipolis' => $this->receipts::PAYMENT_TYPE_RETURN_MULTIPOLIS_REQUISITES,
            'tv_medical' => $this->receipts::PAYMENT_TYPE_RETURN_TV_MEDICAL_REQUISITES,
            'star_oracle' => $this->receipts::PAYMENT_TYPE_RETURN_STAR_ORACLE_REQUISITES,
            'safe_deal' => $this->receipts::PAYMENT_TYPE_RETURN_SAFE_DEAL_REQUISITES,
            'overpayment' => $this->receipts::PAYMENT_TYPE_RETURN_OVERPAYMENT_REQUISITES,
        ];
        return $map[$serviceType] ?? $this->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_REQUISITES;
    }
    
    /**
     * Получить название услуги по типу
     * @param string $serviceType
     * @return string
     */
    private function getServiceName(string $serviceType): string
    {
        $map = [
            'credit_doctor' => 'Финансовый Доктор',
            'multipolis' => 'Консьерж-сервис',
            'tv_medical' => 'Вита-мед',
            'star_oracle' => 'Звездный Оракул',
            'safe_deal' => 'Безопасная Сделка',
            'overpayment' => 'Переплата',
        ];
        return $map[$serviceType] ?? $serviceType;
    }
    
    /**
     * Получить номер контракта по order_id
     * @param int $orderId
     * @return string
     */
    private function getContractNumberByOrderId(int $orderId): string
    {
        $order = $this->orders->get_order($orderId);
        if (!empty($order->contract_id)) {
            $contract = $this->contracts->get_contract($order->contract_id);
            return !empty($contract) ? $contract->number : '';
        }
        return '';
    }

    /**
     * Форматировать заявку для JSON ответа
     * @param object|false $request
     * @return array|null
     */
    private function formatReturnRequest($request): ?array
    {
        if (!$request) {
            return null;
        }

        return [
            'id' => (int)$request->id,
            'status' => $request->status,
            'status_text' => $this->getStatusText($request->status),
            'status_badge' => $this->getStatusBadge($request->status),
            'updated' => $request->updated,
            'error_text' => $request->error_text,
            'service_type' => $request->service_type,
            'service_id' => (int)$request->service_pk,
            'operation_id' => $request->operation_id,
            'amount' => $request->amount,
        ];
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

    /**
     * Получить текст статуса
     * @param string $status
     * @return string
     */
    private function getStatusText(string $status): string
    {
        $map = [
            'new' => 'Новая',
            'sent' => 'Отправлена',
            'approved' => 'Исполнена',
            'error' => 'Ошибка',
        ];

        return $map[$status] ?? $status;
    }

    /**
     * Получить CSS класс бейджа для статуса
     * @param string $status
     * @return string
     */
    private function getStatusBadge(string $status): string
    {
        $map = [
            'new' => 'secondary',
            'sent' => 'info',
            'approved' => 'success',
            'error' => 'danger',
        ];

        return $map[$status] ?? 'light';
    }

    /**
     * Проверить валидность сущности
     * @param object|null $service
     * @param string $serviceType
     * @return bool
     */
    private function isValidService(?object $service, string $serviceType): bool
    {
        if (!$service) {
            return false;
        }
        
        return $serviceType === 'overpayment' 
            ? !empty($service->order_id)
            : !empty($service->id);
    }

    /**
     * Получить ID пользователя из сущности
     * @param object $service
     * @return int
     */
    private function getServiceUserId(object $service): int
    {
        return (int)$service->user_id;
    }

    /**
     * Получить ID заказа из сущности
     * @param object $service
     * @return int
     */
    private function getServiceOrderId(object $service): int
    {
        return (int)$service->order_id;
    }

    /**
     * Получить оставшуюся сумму для возврата
     * @param object $service
     * @return float
     */
    private function getServiceAmountLeft(object $service): float
    {
        return (float)($service->amount - ($service->amount_total_returned ?? 0));
    }

    /**
     * Проверить лимит суммы возврата
     * @param float $amount
     * @param object $service
     * @param string $serviceType
     * @return array|null Null если всё ок, массив с ошибкой если превышен лимит
     */
    private function validateReturnAmount(float $amount, object $service, string $serviceType): ?array
    {
        if ($amount <= 0) {
            return ['status' => false, 'message' => 'Сумма должна быть больше нуля'];
        }
        
        if ($serviceType === 'overpayment') {
            return null;
        }
        
        $amountLeft = $this->getServiceAmountLeft($service);
        
        if ($amount > $amountLeft) {
            return ['status' => false, 'message' => "Сумма превышает остаток. Максимум: {$amountLeft} руб"];
        }
        
        return null;
    }
}

new RefundByRequisites();

