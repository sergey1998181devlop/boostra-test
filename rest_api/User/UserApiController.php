<?php

namespace User;

use api\services\JwtService;

class UserApiController
{
	private \Simpla $simpla;
	private JwtService $jwtService;
	private int $userId;

	private const ROUTES = [
		'get_info'          => 'getInfo',
		'get_order_amount'  => 'getOrderAmount',
		'get_card'          => 'getCardById',
		'mark_failed_order' => 'markFailedOrder',
		'user_activation' => 'userActivation',
	];

	public function __construct(\Simpla $simpla, JwtService $jwtService)
	{
		$this->simpla = $simpla;
		$this->jwtService = $jwtService;
		$this->userId = $jwtService->getUserId();
	}

	public function dispatch(string $action): void
	{
		if (!isset(self::ROUTES[$action])) {
			$this->jsonResponse(['error' => 'Unknown action'], 400);
		}

		$method = self::ROUTES[$action];
		$this->$method();
	}

	private function getInfo(): void
	{
		$user = $this->simpla->users->get_user($this->userId);

		if (!$user) {
			$this->jsonResponse(['error' => 'User not found'], 404);
		}

		$this->jsonResponse((array) $user);
	}

	private function getOrderAmount(): void
	{
		$orderId = (int) $this->jwtService->getClaimFromRequest('order_id');
		$order = $this->simpla->orders->get_order($orderId);

		if (!$order || (int)$order->user_id !== $this->userId) {
			$this->jsonResponse(['error' => 'Order not found or access denied'], 404);
		}

		$params = array('order_id' => $orderId, 'user_id' => $this->userId);
		$contract = $this->simpla->contracts->get_contract_by_params($params);

		if (!$contract) {
			$this->jsonResponse(['error' => 'Contract not found'], 404);
		}

		$description = 'Выдача займа по договору №'.$contract->number.' (userID: '.$this->userId.')';

		$contract_amount = $contract->amount;

		$this->jsonResponse([
			'amount' => $contract_amount,
			'contract_number' => $contract->number,
			'description' => $description,
		]);
	}

	private function getCardById(): void
	{
		$cardId = (int) $this->jwtService->getClaimFromRequest('card_id');

		$this->simpla->db->query("
            SELECT * 
            FROM b2p_cards 
            WHERE id = ? 
              AND user_id = ? 
              AND deleted = 0
            LIMIT 1
        ", $cardId, $this->userId);

		$card = $this->simpla->db->result();

		if (!$card) {
			$this->jsonResponse(['error' => 'Card not found or access denied'], 404);
		}

		$this->jsonResponse((array) $card);
	}

	private function markFailedOrder(): void
	{
		$orderId = (int) $this->jwtService->getClaimFromRequest('order_id');
		$order = $this->simpla->orders->get_order($orderId);

		if (!$order || (int)$order->user_id !== $this->userId) {
			$this->jsonResponse(['error' => 'Order not found or access denied'], 404);
		}

		$res = $this->simpla->orders->update_order($order->id, [
			'status' => 11, 'pay_result'=>'Ошибка выдачи: перевод через виртуальную карту не прошел'
		]);

		$this->jsonResponse(['isUpdated' => (bool)$res]);
	}

	private function userActivation(): void
	{
		$res = $this->simpla->orders->update_orders_waiting_virtual_cards_by_user($this->userId);
		$this->jsonResponse(['isUpdated' => (bool)$res]);
	}

	private function jsonResponse(array $data, int $statusCode = 200): void
	{
		http_response_code($statusCode);
		echo json_encode($data);
		exit;
	}
}