<?php

namespace App\Services;

use App\Core\Application\Session\Session as AppSession;
use App\Dto\ExtraServiceVisibilityDto;
use App\Repositories\DoctorConditionRepository;
use App\Repositories\DoctorReturnLogRepository;
use App\Contracts\ExtraServiceInterface;
use App\Repositories\OrderRepository;
use Exception;
use Settings;
use Refinance;
use Throwable;
use UserData;
use Users;

class ReturnExtraService implements ExtraServiceInterface
{
    private AppSession $session;
    private Users $users;
    private DoctorReturnLogRepository $doctorRepo;
    private DoctorConditionRepository $conditionRepo;
    private Settings $settings;
    private UserData $userData;
    private ReturnCoefficientService $coefficientService;

    public function __construct(
        AppSession                $session,
        Users                     $users,
        UserData                  $userData,
        DoctorReturnLogRepository $doctorRepo,
        DoctorConditionRepository $conditionRepo,
        Settings                  $settings,
        ReturnCoefficientService  $coefficientService
    ) {
        $this->session             = $session;
        $this->users               = $users;
        $this->userData            = $userData;
        $this->doctorRepo          = $doctorRepo;
        $this->conditionRepo       = $conditionRepo;
        $this->settings            = $settings;
        $this->coefficientService  = $coefficientService;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function checkVisibility(int $user_id, ?int $orderId = null): array
    {
        $user = $this->users->get_user($user_id);
        if (!$user) {
            return $this->createVisibilityDto(false, false)->toArray();
        }

        $userId         = (int)$user->id;
        $whitelistValue = $this->userData->read($user_id, $this->userData::WHITELIST_DOP);

        if (($this->settings->whitelist_dop && $whitelistValue) || $this->hasRefinanceOrder($user_id)) {
            return $this->createVisibilityDto(false, false, false, false)->toArray();
        }

        // СРКВ: ФД — история возвратов + коэффициент; ВМ — только история возвратов
        $order            = $this->resolveOrder($orderId, $userId);
        $doctorBlocked    = $this->coefficientService->shouldBlockService(
            $userId,
            ReturnCoefficientService::SERVICE_CREDIT_DOCTOR,
            ReturnCoefficientService::STAGE_ISSUANCE,
            $user,
            $order
        );
        $tvMedicalBlocked = $this->coefficientService->shouldBlockService(
            $userId,
            ReturnCoefficientService::SERVICE_TV_MEDICAL,
            ReturnCoefficientService::STAGE_ISSUANCE
        );

        // Safe flow: показываем допы, но чекбоксы выключены — клиент включает сам
        // Если СРКВ заблокировала доп — не показываем даже в safe flow
        if ($this->users->isSafetyFlow($user)) {
            return $this->createVisibilityDto(!$doctorBlocked, !$tvMedicalBlocked, false, false)->toArray();
        }

        // Опасный флоу: блок всегда скрыт (show=false),
        // enable зависит от СРКВ — если не заблокирован, доп автоматически включается
        return $this->createVisibilityDto(false, false, !$doctorBlocked, !$tvMedicalBlocked)->toArray();
    }


    /**
     * Выбор цены ФД по СРКВ.
     *
     * @inheritDoc
     * @throws Exception
     */
    public function getServicePrice(int $amount, bool $isNewClient = true, $user_id = null, $order_id = null): ?object
    {
        $userId = $user_id ?? ($this->session->isActive() ? (int)$this->session->get('user_id') : null);

        if ($userId === null) {
            return $this->conditionRepo->getCreditDoctor($amount, true);
        }

        $user = $this->users->get_user($userId);
        if (!$user) {
            return $this->conditionRepo->getCreditDoctor($amount, $isNewClient);
        }

        // СРКВ: динамическое ценообразование
        if ($this->coefficientService->isStageActive(ReturnCoefficientService::STAGE_ISSUANCE)) {
            $order = $this->resolveOrder($order_id, (int)$user->id);
            return $this->getPriceBySrkv($amount, $user, $isNewClient, $order);
        }

        // Fallback: базовая сетка (СРКВ неактивна)
        return $this->conditionRepo->getCreditDoctor($amount, $isNewClient);
    }

    /**
     * Проверка возврата по услуге ФД.
     * @throws Exception
     */
    public function hasDoctorReturn(int $userId, int $days = 30): bool
    {
        try {
            return $this->doctorRepo->countByUser($userId, $days) > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    // ─── Private ────────────────────────────────────────────────────────

    /**
     * Ценообразование ФД через СРКВ.
     */
    private function getPriceBySrkv(int $amount, object $user, bool $isNewClient, ?object $order): ?object
    {
        try {
            if (!$order) {
                log_warning('SRKV: no order found, fallback to base price', ['user_id' => $user->id]);
                return $this->conditionRepo->getCreditDoctor($amount, $isNewClient);
            }

            $coefficient = $this->coefficientService->calculateReturnCoefficient($user, $order);
            $thresholds  = config('services.srkv.coefficient_thresholds', [
                'no_sale'  => 0.4,
                'discount' => 0.1,
            ]);

            log_info('SRKV: coefficient calculated', [
                'user_id'     => $user->id,
                'coefficient' => $coefficient,
                'is_new'      => $isNewClient,
            ]);

            // Коэффициент >= 0.4 → не продаём ФД
            if ($coefficient >= (float)$thresholds['no_sale']) {
                return null;
            }

            // Коэффициент > 0.1 → скидочная сетка (0.1 включительно — базовая)
            if ($coefficient > (float)$thresholds['discount']) {
                return $this->conditionRepo->getCreditDoctorByPriceGroup($amount, 'discount');
            }

            // Коэффициент <= 0.1 → базовая сетка
            return $this->conditionRepo->getCreditDoctor($amount, $isNewClient);

        } catch (Throwable $e) {
            log_error('SRKV: pricing failed, fallback to base', [
                'user_id' => $user->id ?? null,
                'error'   => $e->getMessage(),
            ]);
            return $this->conditionRepo->getCreditDoctor($amount, $isNewClient);
        }
    }

    /**
     * Получить заявку: по order_id или последнюю одобренную.
     */
    private function resolveOrder(?int $orderId, int $userId): ?object
    {
        $orderRepo = new OrderRepository();

        if ($orderId) {
            return $orderRepo->getOrderById($orderId);
        }

        return $orderRepo->getLatestOrderByUserId($userId);
    }

    /**
     * @param bool $doctorShow
     * @param bool $tvMedicalShow
     * @param ?bool $doctorChecked
     * @param ?bool $tvMedicalChecked
     * @return ExtraServiceVisibilityDto
     */
    private function createVisibilityDto(bool $doctorShow, bool $tvMedicalShow, bool $doctorChecked = null, bool $tvMedicalChecked = null): ExtraServiceVisibilityDto
    {
        return new ExtraServiceVisibilityDto($doctorShow, $tvMedicalShow, $doctorChecked, $tvMedicalChecked);
    }

    private function hasRefinanceOrder($user_id): bool
    {
        $refinance = new Refinance();
        return (bool)$refinance->getRefinanceOrder($user_id);
    }
}
