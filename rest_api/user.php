<?php

use api\services\JwtService;
use api\exceptions\JwtException;
use User\UserApiController;

include_once 'inc/header.php';

header('Content-Type: application/json');

try {
	$simpla = new Simpla();
	$jwtService = new JwtService($simpla->config->jwt_b2b_secret_key);

	$controller = new UserApiController($simpla, $jwtService);
	$action = $simpla->request->get('user');

	$controller->dispatch($action);
} catch (JwtException $e) {
	http_response_code(401);
	echo json_encode(['error' => 'Unauthorized', 'message' => $e->getMessage()]);
	exit;
} catch (Throwable $e) {
	http_response_code(500);
	echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
	exit;
}