<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/api/Simpla.php';

class ApiException extends \RuntimeException
{
    private int $statusCode;

    public function __construct(string $message, int $statusCode = 400, \Throwable $prev = null)
    {
        parent::__construct($message, 0, $prev);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}

/**
 * @api
 * /api/actual-info-transaction
 *
 * Вход:
 * {
 *   "payments": [
 *     {"operation_id": "...", "order_id": "..."},
 *     ...
 *   ],
 *   "is_operation_action" : true
 * }
 *
 */
class ApiActualInfoTransaction extends Simpla
{
    private const JSON_FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    public function fetch(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {

            $this->checkApiKey();  // проверка токена

            $req      = $this->decodeRequest();
            $payments = $this->normalizePayments($req);

            if (!$payments) {
                $this->respond([
                    'status' => 'fail',
                ]);
                return;
            }

            $response = $this->processOperations($payments, $req["is_operation_action"]);

            $this->respond($response);

        } catch (ApiException $e) {
            http_response_code($e->getStatusCode());
            $this->respond(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            $this->respond(['error' => 'Internal server error']);
        }
    }

    private function decodeRequest(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        if ($raw === '') {
            throw new ApiException('Empty request body', 400);
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new ApiException('Invalid JSON body', 400);
        }

        if (!isset($data['payments']) || !is_array($data['payments'])) {
            throw new ApiException('Field "payments" must be an array', 400);
        }

        if (!isset($data['is_operation_action'])) {
            throw new ApiException('Need to initialize type action', 400);
        }

        return $data;
    }

    private function normalizePayments(array $req): array
    {
        $out = [];

        foreach ($req['payments'] as $p) {
            if (!is_array($p)) continue;

            $op  = isset($p['operation_id']) ? trim((string)$p['operation_id']) : '';
            $ord = isset($p['order_id']) ? trim((string)$p['order_id']) : '';

            if ($op === '' || $ord === '') continue;

            $ord = ltrim($ord, '0') ?: '0';

            $out[] = [
                'operation_id' => $op,
                'order_id'     => $ord,
            ];
        }

        return $out;
    }


    private function processOperations(array $payments, bool $is_operation = true): array
    {
        foreach ($payments as $payment) {
            $is_operation ? $this->pingOperationStatus($payment) : $this->p2pCreditAction($payment);
        }

        return ['status' => 'ok'];
    }

    private function p2pCreditAction($payment){
        $p2p_credit = $this->best2pay->getP2PCreditBy('register_id', $payment['order_id']);
        if (!$p2p_credit) {
            return;
        }

        if ($p2p_credit->status === Best2pay::STATUS_APPROVED && !empty($p2p_credit->operation_id)) {
            $this->best2pay->update_p2pcredit($p2p_credit->id, [
                'send' => 0
            ]);
            return;
        }

        $response = $this->best2pay->get_operation_info($p2p_credit->body['sector'], $payment['order_id'], $payment['operation_id'] );
        $operationXml = simplexml_load_string($response);

        $order = $this->orders->get_order($p2p_credit->order_id);
        if ($order && $operationXml->state == Best2pay::STATUS_APPROVED && $operationXml->order_state == Best2pay::STATUS_ORDER_COMPLETED) {
            $operation_date = date_create_from_format('Y.m.d H:i:s', (string)$operationXml->date);
            $this->best2pay->update_p2pcredit($p2p_credit->id, [
                'response' => $response,
                'status' => $operationXml->state,
                'operation_id' => $operationXml->id,
                'complete_date' => $operation_date->format('Y-m-d H:i:s'),
                'send' => 0
            ]);

            $this->issuance->issuanceByStatus($operationXml->state, $order, $response);
        } else {
            //На случай если где-то теряется заявка, можно будет поискать
            $this->logging(__METHOD__, 'Best2payCallback_CREDIT_NO_ORDER', $_REQUEST, $response, 'change_b2p_statuses_credit.txt');
        }
    }

    private function pingOperationStatus($p)
    {
        $operation_id = $p['operation_id'] ?? null;
        $order_id = $p['order_id'];

        $payment = $this->best2pay->get_payment($order_id);
        if (!$payment) {
            return;
        }

        if (!empty($payment->operation_id) && $payment->reason_code == 1) {
            $this->best2pay->update_payment($payment->id, [
                'sent' => 0,
            ]);
            return;
        }

        $callbackLink = "{$this->config->front_url}/best2pay_callback/payment?id={$payment->register_id}";
        if (!empty($operation_id)) {
            $callbackLink .= "&operation={$operation_id}";
        }
        file_get_contents($callbackLink);
    }

    private function respond(array $payload): void
    {
        echo json_encode($payload, self::JSON_FLAGS) ?: '{"error":"JSON encode error"}';
    }

    private function checkApiKey(): void
    {
        $headers = getallheaders();
        $token = $headers['X-Api-Key'] ?? null;

        $expected = $this->config->api_transactions_token_1c ?? null;

        if (!$expected) {
            throw new ApiException('Server token not configured', 500);
        }

        if ($token !== $expected) {
            throw new ApiException('Unauthorized: invalid API token', 401);
        }
    }

}

(new ApiActualInfoTransaction())->fetch();
