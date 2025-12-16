<?php

declare(strict_types=1);

namespace lib\VirtualCard;

use Firebase\JWT\JWT;

class VirtualCardService
{
	private \Config $config;
	private int $userId;
	private int $orderId;

	public function __construct(\Config $config, int $userId, int $orderId)
	{
		$this->config = $config;
		$this->userId = $userId;
		$this->orderId = $orderId;
	}

	public function registerVirtualCard(): array
	{
		if (!isset($_COOKIE['utm_campaign']) || $_COOKIE['utm_campaign'] !== 'vctest') {
			return [
				'status' => 400,
				'error'  => null,
				'body'   => json_encode(['message' => 'Virtual card registration skipped due to utm_source=vctest']),
			];
		}

		return $this->sendPostRequest('/virtual-cards/register', []);
	}

	public function getCardStatus(): ?array
	{
		$response = $this->sendGetRequest('/virtual-cards/status');

		if ($response['status'] === 200 && isset($response['body'])) {
			$decoded = json_decode($response['body'], true);

			return is_array($decoded) ? $decoded : null;
		}

		return null;
	}

	public function getCardDetails(): ?array
	{
		$response = $this->sendGetRequest('/virtual-cards/details');

		if ($response['status'] === 200 && isset($response['body'])) {
			$data = json_decode($response['body'], true); // decode to array
			return $data ?? null;
		}

		return null;
	}

	public function deposit(): array
	{
		return $this->sendPostRequest('/virtual-cards/deposit', []);
	}

	public function withdraw(int $cardId): array
	{
		return $this->sendPostRequest('/virtual-cards/withdraw', [
			"card_id" => $cardId
		]);
	}

	public function setPhone(string $newpassphrase, bool $resend = false): array
	{
		return $this->sendPostRequest('/virtual-cards/passphrase/change', ['newpassphrase' => $newpassphrase, 'resendSms' => $resend]);
	}

	public function verifyPhone(string $newpassphrase,string $smsCode): array
	{
		return $this->sendPostRequest('/virtual-cards/passphrase/change', ['newpassphrase' => $newpassphrase, 'smscode' => $smsCode]);
	}

	public function deleteCard($synch = false): array
	{
		return $this->sendPostRequest('/virtual-cards/block', [
			"forceDelete" => $synch,
		]);
	}

	private function sendPostRequest(string $endpoint, array $data): array
	{
		$url = rtrim($this->config->virtual_card_base_api_url ?? '', '/') . $endpoint;
		$jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);

		$payload = [
			'sub' => (string)$this->userId,
			'order_id' => (string)$this->orderId,
			'exp' => time() + 5 * 60, // 5 min
		];

		$token = JWT::encode($payload, $this->config->jwt_b2b_secret_key ?? '', 'HS256');

		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json',
				'Accept: application/json',
				'Authorization: Bearer ' . $token,
			],
			CURLOPT_POSTFIELDS => $jsonData,
		]);

		$response = curl_exec($ch);
		$error = curl_error($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return [
			'status' => $status,
			'error' => $error,
			'body' => $response,
		];
	}

	private function sendGetRequest(string $endpoint): array
	{
		$url = rtrim($this->config->virtual_card_base_api_url ?? '', '/') . $endpoint;

		$payload = [
			'sub' => (string)$this->userId,
			'order_id' => (string)$this->orderId,
			'exp' => time() + 5 * 60, // 5 min
		];

		$token = JWT::encode($payload, $this->config->jwt_b2b_secret_key ?? '', 'HS256');

		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json',
				'Accept: application/json',
				'Authorization: Bearer ' . $token,
			],
		]);

		$response = curl_exec($ch);
		$error = curl_error($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return [
			'status' => $status,
			'error' => $error,
			'body' => $response,
		];
	}
}
