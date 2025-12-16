<?php

namespace App\Services;

use App\Core\Application\Session\Session as AppSession;
use App\Dto\ExtraServiceVisibilityDto;
use App\Repositories\DoctorConditionRepository;
use App\Repositories\DoctorReturnLogRepository;
use App\Repositories\OracleReturnLogRepository;
use App\Contracts\ExtraServiceInterface;
use App\Repositories\ReturnRepository;
use Exception;
use Settings;
use Refinance;
use UserData;
use Users;

class ReturnExtraService implements ExtraServiceInterface
{
    private AppSession $session;
    private Users $users;
    private SafetyFlowService $safetyFlowService;
    private DoctorReturnLogRepository $doctorRepo;
    private OracleReturnLogRepository $oracleRepo;
    private DoctorConditionRepository $conditionRepo;
    private RiskGroupService $riskGroupService;
    private Settings $settings;
    private UserData $userData;
    private ScoringService $scoringService;
    private ReturnRepository $returnRepository;

    /**
     * @param AppSession $session
     * @param Users $users
     * @param UserData $userData
     * @param SafetyFlowService $safetyFlowService
     * @param DoctorReturnLogRepository $doctorRepo
     * @param OracleReturnLogRepository $oracleRepo
     * @param DoctorConditionRepository $conditionRepo
     * @param RiskGroupService $riskGroupService
     * @param ScoringService $scoringService
     * @param Settings $settings
     * @param ReturnRepository $returnRepository;
     */
    public function __construct(
        AppSession                $session,
        Users $users,
        UserData $userData,
        SafetyFlowService         $safetyFlowService,
        DoctorReturnLogRepository $doctorRepo,
        OracleReturnLogRepository $oracleRepo,
        DoctorConditionRepository $conditionRepo,
        RiskGroupService          $riskGroupService,
        ScoringService $scoringService,
        Settings $settings,
        ReturnRepository $returnRepository
    ) {
        $this->session = $session;
        $this->users = $users;
        $this->userData = $userData;
        $this->safetyFlowService = $safetyFlowService;
        $this->doctorRepo = $doctorRepo;
        $this->oracleRepo = $oracleRepo;
        $this->conditionRepo = $conditionRepo;
        $this->riskGroupService = $riskGroupService;
        $this->scoringService = $scoringService;
        $this->settings = $settings;
        $this->returnRepository = $returnRepository;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function checkVisibility(int $user_id): array
    {
        $userId = $user_id ?? ($this->session->isActive() ? (int)$this->session->get('user_id') : null);

        $user = $this->users->get_user($userId);
        if (!$user) {
            return $this->createVisibilityDto(false, false)->toArray();
        }

        $hasRefinanceOrder = $this->hasRefinanceOrder($user_id);

        if (($this->settings->whitelist_dop && $this->userData->read($user_id, $this->userData::WHITELIST_DOP)) || $hasRefinanceOrder) {
            log_info($user_id, ['whitelist_dop' => $this->settings->whitelist_dop, 'user_data_dop' => $this->userData->read($user_id, $this->userData::WHITELIST_DOP)]);
            return $this->createVisibilityDto(false, false, false, false)->toArray();
        }

        $cfg = config('services.extra_service');

        $isNew = empty($user->loan_history);

        $isFirstLoanSafe = $this->safetyFlowService->isFirstLoanSafeFlow($user);
        $isUnderSafePeriod = $this->users->isSafetyFlow($user);

        $thresholdZODays = (int)($this->settings->return_threshold_days_zo ?? $cfg['return_threshold_days']['star_oracle']);
        $thresholdFDDays = (int)($this->settings->return_threshold_days_fd ?? $cfg['return_threshold_days']['financial_doctor']);

        $returnsZO = $this->oracleRepo->countByUser($userId, $thresholdZODays);
        $returnsFD = $this->doctorRepo->countByUser($userId, $thresholdFDDays);

        // Новый клиент
        if ($isNew) {
            if (!$isUnderSafePeriod) {
                log_info('new_client not safe', ['user_id' => $userId, 'is_under_safe_period' => $isUnderSafePeriod]);
                return $this->createVisibilityDto(false, false)->toArray();
            }
            log_info('new_client safe', ['user_id' => $userId, 'is_under_safe_period' => $isUnderSafePeriod]);
            return $this->createVisibilityDto(true, true)->toArray();
        }

        // Возвраты по любой услуге за 30 дней (ФД или Оракул)
        if ($returnsZO > 0 or $returnsFD > 0) {
            log_info('old_client', ['returnZO' => (int)$returnsZO, 'returnFD' => (int)$returnsFD, 'user_id' => $userId]);
            return $this->createVisibilityDto(true, false)->toArray();
        }

        // Первый займ по опасному флоу (ПК)
        if (!$isFirstLoanSafe) {
            log_info('old_client first loan not safe', ['isFirstLoanSafe' => false, 'user_id' => $userId]);
            return $this->createVisibilityDto(false, false)->toArray();
        }

        // Первый займ по безопасному флоу (ПК)
        if ($isUnderSafePeriod) {
            log_info('old_client safe', ['isFirstLoanSafe' => $isUnderSafePeriod, 'user_id' => $userId]);
            return $this->createVisibilityDto(false, true)->toArray();
        }

        log_info('old_client not safe', ['isFirstLoanSafe' => $isUnderSafePeriod, 'user_id' => $userId]);
        return $this->createVisibilityDto(false, false)->toArray();
    }


    /**
     * Выбор цены по ТЗ.
     *
     * @inheritDoc
     * @throws Exception
     */
    public function getServicePrice(int $amount, bool $isNewClient = true, $user_id = null): ?object
    {
        $userId = $user_id ?? ($this->session->isActive() ? (int)$this->session->get('user_id') : null);

        if ($userId === null) {
            return $this->conditionRepo->getCreditDoctor($amount, true);
        }

        $user = $this->users->get_user($userId);

        $isUnderSafePeriod = $this->users->isSafetyFlow($user);

        // новый клиент
        if ($isNewClient) {
            if ($this->scoringService->isUserScoreSufficient($user->id)) {
                return $this->conditionRepo->getCreditDoctorByPriceGroup($amount, 'discount');
            }
            
            return $this->conditionRepo->getCreditDoctor($amount, true);
        }

        if (
            $this->scoringService->isUserScoreSufficient($user->id)
            && (
                $this->returnRepository->getProlongationCount($user->id) > 1
                || !$this->returnRepository->checkIsLastOrderOverdue($user->id)
            )
        ) {
            return $this->conditionRepo->getCreditDoctorByPriceGroup($amount, 'discount');
        }
        
        // группа риска, цены из risk_group_prices
        if ($this->riskGroupService->isInRiskGroup($user)) {
            return $this->conditionRepo->getCreditDoctorByPriceGroup($amount, 'risk_group_prices');
        }

        // постоянный клиент, первый займ по безопасному флоу, цены из safety_flow_prices
        if ($isUnderSafePeriod && $this->safetyFlowService->isFirstLoanSafeFlow($user)) {
            return $this->conditionRepo->getCreditDoctorByPriceGroup($amount, 'safety_flow_prices');
        }

        // остальные пк
        return $this->conditionRepo->getCreditDoctor($amount, false);
    }

    /**
     * Проверка возврата по услуге ФД.
     * @throws Exception
     */
    public function hasDoctorReturn(int $userId, int $days = 30): bool
    {
        try {
            return $this->doctorRepo->countByUser($userId, $days) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param bool $doctorShow
     * @param bool $oracleShow
     * @param ?bool $doctorChecked
     * @param ?bool $oracleChecked
     * @return ExtraServiceVisibilityDto
     */
    private function createVisibilityDto(bool $doctorShow, bool $oracleShow, bool $doctorChecked = null, bool $oracleChecked = null): ExtraServiceVisibilityDto
    {
        return new ExtraServiceVisibilityDto($doctorShow, $oracleShow, $doctorChecked, $oracleChecked);
    }

    private function hasRefinanceOrder($user_id): bool
    {
        $refinance = new Refinance();
        // Проверяем, что есть заявка на рефинанс со статусом NEW
        return (bool)$refinance->getRefinanceOrder($user_id);
    }
}
