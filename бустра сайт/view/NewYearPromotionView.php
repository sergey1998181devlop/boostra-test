<?php

use App\Core\Application\Application;
use App\Modules\NewYearPromotion\Services\NewYearPromotionService;

require_once('View.php');

class NewYearPromotionView extends View
{
    private NewYearPromotionService $promoService;

    public function __construct()
    {
        parent::__construct();

        $app = Application::getInstance();
        $this->promoService = $app->make(NewYearPromotionService::class);
    }

    public function fetch()
    {
        $this->handleAction();
        // Если handleAction() не завершил выполнение (не должно произойти),
        // возвращаем пустую строку, чтобы IndexView не вернул false
        return '';
    }

    private function handleAction()
    {
        // Проверяем, включена ли новогодняя акция в настройках
        if (empty((int)$this->settings->newyear_promotion_enabled)) {
            $this->jsonError('promo_disabled');
            return;
        }

        if (empty($this->user) || empty($this->user->id)) {
            $this->jsonError('unauthorized');
            return;
        }

        $action = $this->request->post('action', 'string');
        $orderId = $this->request->post('order_id', 'integer');
        $userId = (int)$this->user->id;

        if (empty($action) || empty($orderId)) {
            $this->jsonError('missing_parameters');
            return;
        }

        try {
            switch ($action) {
                case 'get_discount':
                    $this->getDiscount($userId, $orderId);
                    return;
                    
                case 'pay_click':
                    $this->payClick($userId, $orderId);
                    return;
                    
                case 'get_banner_html':
                    $this->getBannerHtml($userId, $orderId);
                    return;
                    
                default:
                    $this->jsonError('unknown_action');
                    return;
            }
        } catch (\Throwable $e) {
            logger('newyear_promo')->error('Error in NewYearPromotionView', [
                'action' => $action,
                'user_id' => $userId,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->jsonError('internal_error');
            return;
        }
    }

    /**
     * Проверяет активность акции
     * Единственный источник истины - баланс из 1С
     * НЕ проверяет таблицу s_newyear_promotion_2026 для определения участия
     * При ошибках вызывает jsonError и завершает выполнение
     *
     * @param int $userId
     * @param int $orderId
     * @return void Метод завершает выполнение через jsonError при ошибках
     */
    private function checkPromoActive(int $userId, int $orderId): void
    {
        // Получаем баланс из 1С для проверки активности акции
        $userBalance = $this->getUserBalanceFrom1C($userId, $orderId);
        if (empty($userBalance)) {
            $this->jsonError('balance_not_found');
            return;
        }

        // единственный источник истины - баланс из 1С
        if (!$this->promoService->isUserInPromo($userId, $orderId, $userBalance)) {
            $this->jsonError('promo_not_found');
            return;
        }

        // Проверяем, активна ли скидка (использует discount_date из 1С)
        $isActive = $this->promoService->isDiscountActive($userBalance);

        // Если акция не активна, возвращаем ошибку
        if (!$isActive) {
            $this->jsonError('promo_expired');
            return;
        }
    }

    private function getDiscount(int $userId, int $orderId)
    {
        // Проверяем активность акции (метод сам вызовет jsonError при ошибках)
        // Метод проверяет участие через баланс из 1С, не через таблицу
        $this->checkPromoActive($userId, $orderId);
        
        // Логируем нажатие на кнопку получения скидки (акция уже проверена на активность)
        // Если записи в таблице нет - создастся автоматически при логировании
        $this->promoService->logDiscountButtonClicked($userId, $orderId);
        
        $this->json([
            'success' => true,
            'message' => 'Скидка активирована'
        ]);
    }

    private function payClick(int $userId, int $orderId)
    {
        // Проверяем активность акции (метод сам вызовет jsonError при ошибках)
        // Метод проверяет участие через баланс из 1С, не через таблицу
        $this->checkPromoActive($userId, $orderId);
        
        // Логируем нажатие на кнопку оплаты (акция уже проверена на активность)
        // Если записи в таблице нет - создастся автоматически при логировании
        try {
            $this->promoService->logPayButtonClicked($userId, $orderId);
        } catch (\Throwable $e) {
            logger('newyear_promo')->error('payClick error: ' . $e->getMessage());
        }
        
        $this->json([
            'success' => true,
            'message' => 'Переход на оплату'
        ]);
    }

    private function json(array $data)
    {
        // Очищаем буфер вывода, если он был начат
        if (ob_get_level()) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Получает баланс пользователя из 1С по order_id
     *
     * @param int $userId
     * @param int $orderId
     * @return object|null
     */
    private function getUserBalanceFrom1C(int $userId, int $orderId): ?object
    {
        try {
            // Получаем заказ для получения zaim_number или id_1c
            $order = $this->orders->get_crm_order($orderId);
            if (empty($order)) {
                return null;
            }

            // Получаем балансы из 1С
            $response_balances = $this->soap->get_user_balances_array_1c($this->user->uid);
            if (empty($response_balances) || isset($response_balances['errors'])) {
                return null;
            }

            // Получаем контракт для получения номера займа
            $contract = $this->contracts->get_contract_by_params(['order_id' => $order->id, 'user_id' => $userId]);
            
            // Ищем баланс по номеру займа или по id_1c заказа
            $balance_1c = null;
            foreach ($response_balances as $response_balance) {
                // Проверяем по номеру займа из контракта
                if (!empty($contract->number) && $response_balance['НомерЗайма'] == $contract->number) {
                    $balance_1c = (object)$response_balance;
                    break;
                }
                // Проверяем по id_1c заказа
                if (!empty($order->id_1c) && $response_balance['Заявка'] == $order->id_1c) {
                    $balance_1c = (object)$response_balance;
                    break;
                }
            }

            if (empty($balance_1c)) {
                return null;
            }

            // Нормализуем баланс
            return $this->users->make_up_user_balance($userId, $balance_1c);
        } catch (\Throwable $e) {
            logger('newyear_promo')->error('Error getting balance from 1C', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Возвращает HTML баннера для указанного заказа
     *
     * @param int $userId
     * @param int $orderId
     * @return void
     */
    private function getBannerHtml(int $userId, int $orderId)
    {
        try {
            // Получаем заказ
            $order = $this->orders->get_crm_order($orderId);
            if (empty($order)) {
                $this->jsonError('order_not_found');
                return;
            }

            // Получаем баланс из 1С
            $userBalance = $this->getUserBalanceFrom1C($userId, $orderId);
            if (empty($userBalance)) {
                $this->jsonError('balance_not_found');
                return;
            }

            // Формируем структуру orderData, как в UserView
            $orderData = (object)[
                'order' => $order,
                'balance' => $userBalance,
            ];
            
            // Убеждаемся, что user_id установлен в balance
            if (empty($orderData->balance->user_id)) {
                $orderData->balance->user_id = $userId;
            }

            // Получаем промо-данные (копируем логику из UserView::getNewYearPromo)
            $promo = $this->getNewYearPromoData($orderData);

            if (empty($promo)) {
                $this->jsonError('promo_not_found');
                return;
            }

            // Подготавливаем данные для шаблона
            $orderData->newyear_promo = $promo;

            // Рендерим шаблон баннера
            $this->design->assign('orderData', $orderData);
            $bannerHtml = $this->design->fetch('partials/newyear_promo_banner.tpl');

            // Возвращаем HTML
            $this->json([
                'success' => true,
                'html' => $bannerHtml
            ]);
        } catch (\Throwable $e) {
            logger('newyear_promo')->error('Error getting banner HTML', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->jsonError('internal_error');
        }
    }

    /**
     * Получает данные новогодней акции для заказа (копия логики из UserView::getNewYearPromo)
     *
     * @param object $order
     * @return object|null
     */
    private function getNewYearPromoData($order): ?object
    {
        if ((empty($order->order->order_id)) || empty($order->balance)) {
            logger('newyear_promo_check')->warning('Не полные данные заявки/пользователя для Новогодней акции', [
                'user_id' => $this->user->id ?? null,
                'order' => $order,
            ]);
            return null;
        }

        $userId = $this->user->id;
        $orderId = $order->order->order_id ?? null;

        if (empty($userId) || empty($orderId)) {
            logger('newyear_promo_check')->warning('Не полные данные заявки/пользователя для Новогодней акции', [
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
            return null;
        }

        // Проверяем, участвует ли пользователь в акции (единственный источник истины - баланс из 1С)
        if (!$this->promoService->isUserInPromo($userId, $orderId, $order->balance)) {
            logger('newyear_promo_check')->warning('Не полные данные заявки/пользователя для Новогодней акции', [
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
            return null;
        }

        // Проверяем, активна ли скидка (использует discount_date из 1С как единственный источник истины)
        $isDiscountActive = $this->promoService->isDiscountActive($order->balance);

        // Если время акции недействительно - не показываем баннер вообще
        if (!$isDiscountActive) {
            logger('newyear_promo_check')->info('Новогодняя акция не активна для пользователя', [
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
            return null;
        }

        // Проверяем, была ли активирована скидка (для отображения состояния баннера)
        $discountActivated = $this->promoService->hasEvent($userId, $orderId, $this->promoService::EVENT_DISCOUNT_BUTTON_CLICKED);

        // Рассчитываем скидку и сумму со скидкой
        // Для расчёта скидки используем полную сумму долга (включая доп. услуги)
        $totalDebt = (float)($order->balance->ostatok_od ?? 0)
            + (float)($order->balance->ostatok_percents ?? 0)
            + (float)($order->balance->ostatok_peni ?? 0)
            + (float)($order->balance->penalty ?? 0);
        $discountAmount = $this->promoService->getDiscountAmount($order->balance);
        $discountCalculation = $this->promoService->calculateTotalWithDiscount($order->balance);

        $totalWithDiscount = $discountCalculation['total_with_discount'];

        // Получаем оставшееся время (использует discount_date из 1С, если есть, иначе возвращает 0)
        $remainingTime = $this->promoService->getRemainingTime($order->balance);

        // Получаем время активации скидки
        $activationEvent = $this->promoService->getLastEvent($userId, $orderId, $this->promoService::EVENT_DISCOUNT_BUTTON_CLICKED);
        $discountStartedAt = $activationEvent ? $activationEvent->created_at : null;

        // Определяем ссылку на PDF файл в зависимости от организации
        $pdfUrl = null;
        $organizationId = (int)($order->order->organization_id ?? 0);
        
        switch ($organizationId) {
            case $this->organizations::AKVARIUS_ID:
                $pdfUrl = '/files/docs/newyear_promo/Условия акции_Аквариус_2025.pdf';
                break;
            case $this->organizations::LORD_ID:
                $pdfUrl = '/files/docs/newyear_promo/Условия акции_Лорд_2025.pdf';
                break;
            case $this->organizations::RZS_ID:
                $pdfUrl = '/files/docs/newyear_promo/Условия акции_РЗС_2025.pdf';
                break;
            case $this->organizations::FINLAB_ID:
                $pdfUrl = '/files/docs/newyear_promo/Условия акции_Финлаб_2025.pdf';
                break;
        }

        logger('newyear_promo_check')->info('Новогодняя акция активна для пользователя', [
            'user_id' => $userId,
            'order_id' => $orderId,
            'is_discount_active' => $isDiscountActive,
            'discount_activated' => $discountActivated,
            'discount_amount' => $discountAmount,
            'total_debt' => $totalDebt,
            'total_with_discount' => $totalWithDiscount,
            'remaining_time' => $remainingTime,
            'discount_started_at' => $discountStartedAt,
            'pdf_url' => $pdfUrl,
            'discount_date' => $order->balance->discount_date ?? null
        ]);

        // Передаем данные в шаблон
        return (object)[
            'is_active' => $isDiscountActive,
            'discount_amount' => $discountAmount,
            'total_debt' => $totalDebt,
            'total_with_discount' => $totalWithDiscount,
            'remaining_time' => $remainingTime,
            'discount_activated' => $discountActivated,
            'discount_started_at' => $discountStartedAt,
            'pdf_url' => $pdfUrl,
        ];
    }

    private function jsonError(string $code)
    {
        // Очищаем буфер вывода, если он был начат
        if (ob_get_level()) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode(['success' => false, 'error' => $code], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

