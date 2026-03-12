<?php

namespace api\services;

use api\exceptions\JwtException;
use api\helpers\JWTHelper;

class JwtService
{
	private string $jwtSecret;
	private ?object $decodedPayload = null;

	public function __construct(string $jwtSecret)
	{
		$this->jwtSecret = $jwtSecret;
	}

	public function getBearerToken(): string
	{
		$header = null;

		if (!empty($_SERVER['HTTP_X_AUTH_TOKEN'])) {
			$header = trim($_SERVER['HTTP_X_AUTH_TOKEN']);
		}

		if (!$header && function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			if (!empty($headers['X-Auth-Token'])) {
				$header = trim($headers['X-Auth-Token']);
			}
		}

		if (empty($header)) {
			throw new JwtException('X-Auth-Token header not found');
		}

		return $header;
	}


	public function decodeTokenFromRequest(): object
	{
		if ($this->decodedPayload !== null) {
			return $this->decodedPayload;
		}

		$token = $this->getBearerToken();
		$decoded = JWTHelper::decodeToken($token, $this->jwtSecret);

		if (!is_object($decoded)) {
			throw new JwtException('Invalid token payload format');
		}

		$this->decodedPayload = $decoded;

		return $this->decodedPayload;
	}

	public function getClaimFromRequest(string $claimName)
	{
		$decoded = $this->decodeTokenFromRequest();

		if (!property_exists($decoded, $claimName)) {
			throw new JwtException("Claim '{$claimName}' not found in token");
		}

		return $decoded->{$claimName};
	}

	public function getUserId(): int
	{
		return (int) $this->getClaimFromRequest('sub');
	}

	public function getOrderId(): int
	{
		return (int) $this->getClaimFromRequest('order_id');
	}
}
