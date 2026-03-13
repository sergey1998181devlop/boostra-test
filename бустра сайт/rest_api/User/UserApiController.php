<?php

namespace User;

use api\services\JwtService;

class UserApiController
{
	private \Simpla $simpla;
	private JwtService $jwtService;
	private int $userId;

	private const ROUTES = [
		'get_info' => 'getInfo',
		'get_card' => 'getCardById',
        'attach_card' => 'addCard',
        'handle_status_changed_event' => 'handleStatusChangedEvent',
    ];

    private const LOG_FILE = 'virt_card_hook.txt';

	public function __construct(\Simpla $simpla, JwtService $jwtService)
	{
		$this->simpla = $simpla;
		$this->jwtService = $jwtService;

        try {
            $this->userId = $jwtService->getUserId();
            $this->logDebug('HookController initialized', [
                'user_id' => $this->userId,
                'action' => '__construct'
            ]);
        } catch (\Throwable $e) {
            $this->logError('Failed to get user ID from JWT', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
	}

	public function dispatch(string $action): void
	{
        $this->logInfo('Dispatching action', [
            'action' => $action,
            'user_id' => $this->userId,
            'available_routes' => array_keys(self::ROUTES)
        ]);

		if (!isset(self::ROUTES[$action])) {
            $this->logWarning('Unknown action requested', [
                'action' => $action,
                'user_id' => $this->userId
            ]);

			$this->jsonResponse(['error' => 'Unknown action'], 400);
		}

		$method = self::ROUTES[$action];

        $this->logDebug('Executing method', [
            'action' => $action,
            'method' => $method,
            'user_id' => $this->userId
        ]);

        try {
            $this->$method();
        } catch (\Throwable $e) {
            $this->logError('Exception in dispatched method', [
                'action' => $action,
                'method' => $method,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->userId
            ]);
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
	}

	private function getInfo(): void
	{
        $this->logInfo('Getting user info', ['user_id' => $this->userId]);

        try {
            $user = $this->simpla->users->get_user($this->userId);

            if (!$user) {
                $this->logWarning('User not found', ['user_id' => $this->userId]);
                $this->jsonResponse(['error' => 'User not found'], 404);
            }

            $this->logDebug('User info retrieved successfully', [
                'user_id' => $this->userId,
                'user_email' => $user->email ?? null,
                'user_name' => ($user->name ?? '') . ' ' . ($user->last_name ?? '')
            ]);

            $this->jsonResponse((array) $user);
        } catch (\Throwable $e) {
            $this->logError('Failed to get user info', [
                'user_id' => $this->userId,
                'error' => $e->getMessage()
            ]);
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
	}

	private function getCardById(): void
	{
        try {
            $cardId = (int)$this->jwtService->getClaimFromRequest('card_id');

            $this->logInfo('Getting card by ID', [
                'user_id' => $this->userId,
                'card_id' => $cardId
            ]);

            if (!$cardId) {
                $this->logWarning('Invalid card ID provided', [
                    'user_id' => $this->userId,
                    'provided_card_id' => $cardId
                ]);
                $this->jsonResponse(['error' => 'Invalid card ID'], 400);
            }

            $this->simpla->db->query(
                "
            SELECT * 
            FROM b2p_cards 
            WHERE id = ? 
              AND user_id = ? 
              AND deleted = 0
            LIMIT 1
        ",
                $cardId,
                $this->userId
            );

            $card = $this->simpla->db->result();

            if (!$card) {
                $this->logWarning('Card not found or access denied', [
                    'user_id' => $this->userId,
                    'card_id' => $cardId
                ]);
                $this->jsonResponse(['error' => 'Card not found or access denied'], 404);
            }

            $this->logDebug('Card retrieved successfully', [
                'user_id' => $this->userId,
                'card_id' => $cardId,
                'card_pan' => substr($card->pan ?? '', -4)
            ]);

            $this->jsonResponse((array)$card);

        } catch (\Throwable $e) {
            $this->logError('Failed to get card', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
	}

    private function addCard(): void
    {
        $this->logInfo('Adding card for user', ['user_id' => $this->userId]);

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $this->logDebug('Received input data', [
                'user_id' => $this->userId,
                'input_keys' => array_keys($input ?? []),
                'has_operation' => isset($input['operation'])
            ]);

            $transaction = $input['operation'] ?? null;
            if (!is_array($transaction)) {
                $this->logWarning('Invalid transaction data', [
                    'user_id' => $this->userId,
                    'transaction_type' => gettype($transaction)
                ]);
                $this->jsonResponse(['error' => 'Невалидные данные'], 409);
            }

            $organizationId = $this->simpla->organizations::FINTEHMARKET_ID;

            $reference = (string)($transaction['reference'] ?? '');
            $pan = (string)($transaction['pan'] ?? '');
            $expdate = (string)($transaction['expdate'] ?? '');
            $approvalCode = (string)($transaction['approval_code'] ?? '');
            $token = (string)($transaction['token'] ?? '');
            $date = (string)($transaction['date'] ?? '');
            $id = (string)($transaction['id'] ?? '');
            $registerId = (string)($transaction['register_id'] ?? $id); // Если register_id нет, используем id

            $this->logDebug('Transaction data extracted', [
                'user_id' => $this->userId,
                'reference' => $reference,
                'expdate' => $expdate,
                'approval_code' => $approvalCode,
                'has_token' => !empty($token),
                'operation_id' => $id,
                'register_id' => $registerId,
                'organization_id' => $organizationId
            ]);

            $countSameCard = $this->simpla->best2pay->find_duplicates_for_user(
                $reference,
                $pan,
                $expdate,
                $organizationId
            );

            $this->logInfo('Duplicate check completed', [
                'user_id' => $this->userId,
                'duplicate_count' => $countSameCard,
                'reference' => $reference,
            ]);

            if ($countSameCard > 0) {
                $this->logWarning('Card already attached', [
                    'user_id' => $this->userId,
                    'reference' => $reference,
                    'duplicate_count' => $countSameCard
                ]);
                $this->jsonResponse(['error' => 'Карта уже привязана'], 409);
            } else {
                $card = [
                    'user_id' => (string)$this->userId,
                    'name' => 'UNKNOWN NAME',
                    'pan' => $pan,
                    'expdate' => $expdate,
                    'approval_code' => $approvalCode,
                    'token' => $token,
                    'operation_date' => str_replace('.', '-', $date),
                    'created' => date('Y-m-d H:i:s'),
                    'operation' => $id,
                    'register_id' => $registerId,
                    'transaction_id' => $id,
                    'organization_id' => $organizationId,
                ];

                $this->logDebug('Attempting to add card to database', [
                    'user_id' => $this->userId,
                    'card_data' => array_merge($card, [
                        'reference' => $reference
                    ])
                ]);

                $cardId = $this->simpla->best2pay->add_card($card);
                if (!empty($cardId)) {
                    $this->logInfo('Card added successfully', [
                        'user_id' => $this->userId,
                        'card_id' => $cardId,
                        'reference' => $reference,
                    ]);
                } else {
                    $this->logError('Failed to add card to database', [
                        'user_id' => $this->userId,
                        'card_data' => array_merge($card, [
                            'reference' => $reference
                        ])
                    ]);
                }

                $this->logDebug('Changelog entry created', [
                    'user_id' => $this->userId,
                    'card_id' => $cardId,
                    'changelog_type' => 'new_card'
                ]);

                $this->jsonResponse([
                    'message' => 'Карта успешно привязана',
                    'card_id' => $cardId,
                ]);
            }
        } catch (\Throwable $e) {
            $this->logError('Exception in addCard', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    private function handleStatusChangedEvent(): void
    {
        $this->logInfo('Handling status changed event', ['user_id' => $this->userId]);

        try {
            $status = $this->simpla->request->get('status', 'string');

            $this->logDebug('Status received', ['user_id' => $this->userId, 'status' => $status]);

            if (!in_array($status, ['active', 'deleted', 'blocked'])) {
                $this->logWarning('Invalid status value', ['user_id' => $this->userId, 'status' => $status]);
                $this->jsonResponse(['isUpdated' => false]);
            }

            if (in_array($status, ['deleted', 'blocked'])) {
                // Сбрасываем согласие на виртуальную карту
                $this->simpla->user_data->set($this->userId, "is_virtual_card_consent", 0);
                $this->logDebug('Virtual card consent reset', ['user_id' => $this->userId]);
            }

            $lastOrder = $this->simpla->orders->get_last_order($this->userId);

            $this->logDebug('Last order retrieved', [
                'user_id' => $this->userId,
                'has_order' => !empty($lastOrder),
                'order_id' => $lastOrder->id ?? null,
                'order_status' => $lastOrder->status ?? null
            ]);

            if ( // процессим только ордер, который еще в работе
                !$lastOrder
                || !isset($lastOrder->id)
                || (
                    $lastOrder->status !== $this->simpla->orders::STATUS_WAIT_VIRTUAL_CARD
                    && (int)$lastOrder->status > $this->simpla->orders::STATUS_SIGNED
                )
            ) {
                $this->logInfo('Order not eligible for status change', [
                    'user_id' => $this->userId,
                    'order_id' => $lastOrder->id ?? null,
                    'order_status' => $lastOrder->status ?? null,
                    'required_status' => $this->simpla->orders::STATUS_WAIT_VIRTUAL_CARD,
                    'status_check_result' => isset($lastOrder->status) ?
                        ($lastOrder->status === $this->simpla->orders::STATUS_WAIT_VIRTUAL_CARD ? 'equal' : 'not_equal') : 'no_order'
                ]);
                $this->jsonResponse(['isUpdated' => false]);
            }

            $orderId = $lastOrder->id;
            $this->logInfo('Processing eligible order', [
                'user_id' => $this->userId,
                'order_id' => $orderId,
                'current_status' => $lastOrder->status,
                'new_status_event' => $status
            ]);

            if ($status === 'active' && $lastOrder->status === $this->simpla->orders::STATUS_WAIT_VIRTUAL_CARD) {
                $this->logInfo('Updating order to SIGNED status', [
                    'user_id' => $this->userId,
                    'order_id' => $orderId,
                    'old_status' => $lastOrder->status,
                    'new_status' => $this->simpla->orders::STATUS_SIGNED
                ]);

                $res = $this->simpla->orders->update_order($orderId, ['status' => $this->simpla->orders::STATUS_SIGNED]);

                $this->logDebug('Order update result', [
                    'user_id' => $this->userId,
                    'order_id' => $orderId,
                    'update_success' => (bool)$res
                ]);
                $this->jsonResponse(['isUpdated' => (bool)$res]);
            }

            if (in_array($status, ['deleted', 'blocked'])) {
                $this->logInfo('Handling deleted/blocked status', [
                    'user_id' => $this->userId,
                    'order_id' => $orderId,
                    'status' => $status
                ]);

                $res = $this->simpla->orders->update_order(
                    $orderId,
                    ['card_type' => $this->simpla->orders::CARD_TYPE_SBP]
                );
                $this->logInfo('Order updated to SBP card type', [
                    'user_id' => $this->userId,
                    'order_id' => $orderId,
                    'update_success' => (bool)$res,
                    'new_card_type' => $this->simpla->orders::CARD_TYPE_SBP
                ]);

                $this->jsonResponse(['isUpdated' => (bool)$res]);
            }

            $this->logWarning('No matching condition for status change', [
                'user_id' => $this->userId,
                'order_id' => $orderId,
                'status' => $status,
                'order_status' => $lastOrder->status
            ]);

            $this->jsonResponse(['isUpdated' => false]);
        } catch (\Exception $e) {
            $this->logError('Exception in handleStatusChangedEvent', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
	{
		http_response_code($statusCode);
		echo json_encode($data);
		exit;
	}

    private function logInfo(string $message, array $context = []): void
    {
        $this->writeLog('INFO', $message, $context);
    }

    private function logWarning(string $message, array $context = []): void
    {
        $this->writeLog('WARNING', $message, $context);
    }

    private function logError(string $message, array $context = []): void
    {
        $this->writeLog('ERROR', $message, $context);
    }

    private function logDebug(string $message, array $context = []): void
    {
        $this->writeLog('DEBUG', $message, $context);
    }

    private function writeLog(string $level, string $message, array $context = []): void
    {
        try {
            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'level' => $level,
                'message' => $message,
                'context' => $context,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
            ];

            $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

            $logFile = $this->simpla->config->root_dir.'logs/'. self::LOG_FILE;

            file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
            // Если не удалось записать лог, игнорируем, чтобы не нарушить основной процесс
        }
    }
}