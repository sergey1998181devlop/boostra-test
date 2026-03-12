<?php

require_once('../api/Simpla.php');

use App\Core\Application\Application;
use App\Modules\Payment\Services\AddCardService;

// CSRF защита
$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!$csrfHeader || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfHeader)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
if (!$action) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No action specified']);
    exit;
}

class Best2PayAjax extends Simpla
{
    private $userId;
    private AddCardService $addCardService;

    public function __construct($userId)
    {
        parent::__construct();

        $app = Application::getInstance();
        $this->addCardService = $app->make(AddCardService::class);

        $this->userId = (int)$userId;
    }

    public function handle(string $action): array
    {
        try {
            switch ($action) {
                case 'add_card':
                    return $this->getAddCardLink([
                        'url' => '/best2pay_callback/add_card_from_vc',
                        'failurl' => '/best2pay_callback/add_card_from_vc',
                    ]);
                case 'cards':
                    return $this->getCards();
                default:
                    return ['success' => false, 'error' => 'Unknown action'];
            }
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => 'Internal server error'
            ];
        }
    }

    private function getAddCardLink(array $params): array
    {
        $order = $this->orders->get_last_order($this->userId);

        if (!$order || !isset($order->organization_id)) {
            return ['success' => false, 'error' => 'Order not found'];
        }

        $link = $this->addCardService->getAddCardLink(
            $this->userId,
            $order->organization_id,
            null,
            null,
            $params
        );

        if (!$link) {
            return ['success' => false, 'error' => 'Failed to generate add card link'];
        }

        return ['success' => true, 'link' => $link];
    }

    private function getCards(): array
    {
        $cards = $this->best2pay->get_cards([
            'user_id' => $this->userId,
            'deleted_by_client' => 0,
            'deleted' => 0,
        ]);

        return [
            'success' => true,
            'data' => $cards
        ];
    }
}

$handler = new Best2PayAjax($userId);
$result = $handler->handle($action);

header('Content-Type: application/json');
echo json_encode($result, JSON_UNESCAPED_UNICODE);
