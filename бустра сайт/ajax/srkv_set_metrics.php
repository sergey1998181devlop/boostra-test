<?php

/**
 * Ручка СРКВ — управление метриками в кеше.
 *
 * GET  ?action=get                              — текущие метрики из кеша
 * POST ?action=set                              — записать метрики в кеш (тело: JSON)
 * POST ?action=clear                            — очистить все ключи СРКВ из кеша
 * GET  ?action=coefficient&user_id=X&order_id=Y — рассчитать коэффициент для клиента
 *
 */

use App\Core\Application\Application;
use App\Core\Cache\CacheInterface;
use App\Dto\ReturnCoefficientDto;
use App\Repositories\OrderRepository;
use App\Services\ReturnCoefficientService;

require_once dirname(__DIR__) . '/api/Simpla.php';

header('Content-Type: application/json; charset=utf-8');

$simpla = new Simpla();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? ($method === 'GET' ? 'get' : 'set');

$cacheKeyMetrics  = 'srkv:metrics';
$cacheKeyFallback = 'srkv:metrics:fallback';
$cacheKeyRisky    = 'srkv:risky_values';
$cacheKeyMaxScore = 'srkv:max_score';

try {
    /** @var CacheInterface $cache */
    $cache = Application::getInstance()->make(CacheInterface::class);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Cache unavailable: ' . $e->getMessage()]);
    exit;
}

// ─── GET: текущие метрики из кеша ────────────────────────────────────────────
if ($action === 'get') {
    try {
        $dto = $cache->get($cacheKeyMetrics);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Cache get failed: ' . $e->getMessage()]);
        exit;
    }

    if (!$dto instanceof ReturnCoefficientDto) {
        echo json_encode([
            'success' => true,
            'cached'  => false,
            'metrics' => null,
            '_debug'  => 'got type: ' . (is_object($dto) ? get_class($dto) : gettype($dto)),
        ]);
        exit;
    }

    $riskyValues = $cache->get($cacheKeyRisky);
    $maxScore    = $cache->get($cacheKeyMaxScore);

    echo json_encode([
        'success'      => true,
        'cached'       => true,
        'metrics'      => dtoToArray($dto),
        'risky_values' => is_array($riskyValues) ? $riskyValues : null,
        'max_score'    => is_float($maxScore) ? $maxScore : null,
    ]);
    exit;
}

// ─── POST ?action=clear: очистить кеш ────────────────────────────────────────
if ($action === 'clear') {
    $cache->delete($cacheKeyMetrics);
    $cache->delete($cacheKeyFallback);
    $cache->delete($cacheKeyRisky);
    $cache->delete($cacheKeyMaxScore);

    echo json_encode(['success' => true, 'message' => 'SRKV cache cleared']);
    exit;
}

// ─── GET ?action=coefficient: рассчитать коэффициент для user_id ─────────────
if ($action === 'coefficient') {
    $userId  = (int)($_GET['user_id']  ?? 0);
    $orderId = (int)($_GET['order_id'] ?? 0) ?: null;

    if ($userId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'user_id is required']);
        exit;
    }

    try {
        $app     = Application::getInstance();
        $service = $app->make(ReturnCoefficientService::class);

        $user = $simpla->users->get_user($userId);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => "User $userId not found"]);
            exit;
        }

        $orderRepo = new OrderRepository();
        $order     = $orderId
            ? $orderRepo->getOrderById($orderId)
            : $orderRepo->getLatestOrderByUserId($userId);

        if (!$order) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Order not found']);
            exit;
        }

        $debug = $service->getDebugInfo($user, $order);

        echo json_encode([
            'success'  => true,
            'user_id'  => $userId,
            'order_id' => (int)$order->id,
            'debug'    => $debug,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ─── POST ?action=set: записать метрики ──────────────────────────────────────
$body = file_get_contents('php://input');
$data = json_decode($body, true);

// Fallback: если JSON body не пришёл, берём из POST (form data)
if (!is_array($data) && !empty($_POST)) {
    $data = $_POST;
    // POST передаёт строки — конвертируем вложенные JSON-строки в массивы
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $data[$key] = $decoded;
            }
        }
    }
}

if (!is_array($data) || empty($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No data provided. Send JSON body or POST form data']);
    exit;
}


try {
    $dto = new ReturnCoefficientDto($data);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid metrics data: ' . $e->getMessage()]);
    exit;
}

try {
    /** @var ReturnCoefficientService $service */
    $service = Application::getInstance()->make(ReturnCoefficientService::class);
    $service->applyMetrics($dto);
    $service->recalculateRiskyValues();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Apply metrics failed: ' . $e->getMessage()]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'SRKV metrics and risky values saved to cache',
    'metrics' => dtoToArray($dto),
]);
exit;

// ─── Helpers ─────────────────────────────────────────────────────────────────

function dtoToArray(ReturnCoefficientDto $dto): array
{
    return [
        'conversion_on_issuance' => $dto->conversionOnIssuance,
        'conversion_on_payment'  => $dto->conversionOnPayment,
        'overall_return_pct'     => $dto->overallReturnPct,
        'client_type'  => $dto->clientType,
        'loan_type'    => $dto->loanType,
        'platform'     => $dto->platform,
        'gender'       => $dto->gender,
        'score'        => $dto->score,
        'source'       => $dto->source,
    ];
}
