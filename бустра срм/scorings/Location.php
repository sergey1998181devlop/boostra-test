<?php

use boostra\services\DadataService;
use boostra\services\RegionService;

require_once __DIR__ . '/../lib/autoloader.php';

class Location extends Simpla
{
    /** @var string[] Ключ в scoringType с кодами регионов из таблицы regions для отказа региону регистрации */
    private const EXCEPTION_REGIONS_CODE_FOR_REG_REGION = 'exception_regions_code_for_reg_region';

    /** @var string[] Ключ в scoringType с кодами регионов из таблицы regions для отказа по региону проживания */
    private const EXCEPTION_REGIONS_CODE_FOR_FAKT_REGION = 'exception_regions_code_for_fakt_region';

    /** @var string[] Коды регионов, для которых нужно делать авто-отказы для ПК */
    private const EXCEPTION_REGIONS_CODE_FOR_PK = [
        '46' // Курская область
    ];

    private const REG_REGION = 'reg_region';
    private const FAKT_REGION = 'fakt_region';

    private DadataService $dadataService;
    private RegionService $regionService;

    public function __construct()
    {
        parent::__construct();

        $this->dadataService = new DadataService();
        $this->regionService = new RegionService();
    }

    /**
     * @param int $scoring_id
     * @return array|null
     */
    public function run_scoring(int $scoring_id): ?array
    {
        $scoring = $this->scorings->get_scoring($scoring_id);

        if (!$scoring) {
            return null;
        }

        $user = $this->users->get_user($scoring->user_id);
        if ($user) {
            $update = $this->handleUser($user, $scoring);
        } else {
            $update = [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'не найдена пользователь'
            ];
        }

        $update['end_date'] = date('Y-m-d H:i:s');
        $this->scorings->update_scoring($scoring_id, $update);

        return $update;
    }

    /**
     * @param stdClass $user
     * @param stdClass $scoring
     * @return array
     */
    private function handleUser(stdClass $user, stdClass $scoring): array
    {
        $scoringType = $this->scorings->get_type($this->scorings::TYPE_LOCATION);

        if (empty($user->Regregion)) {
            return [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'в заявке не указан регион регистрации'
            ];
        }

        $regRegionCode = $user->Regregion_code;

        if ($regRegionCode === null) {
            $regRegionCode = $this->getRegionCodeByRegionName($user->Regregion);
        }

        $faktRegionCode = $user->Faktregion_code;

        if ($faktRegionCode === null) {
            $faktRegionCode = $this->getRegionCodeByRegionName($user->Faktregion);
        }

        $isRegRegionAvailable = $this->getRegionAvailability($regRegionCode, $scoringType, self::REG_REGION);
        $isFaktRegionAvailable = $this->getRegionAvailability($faktRegionCode, $scoringType, self::FAKT_REGION);

        $update = [
            'status' => $this->scorings::STATUS_COMPLETED,
            'body' => serialize(['Regregion' => $user->Regregion, 'Faktregion' => $user->Faktregion]),
            'success' => (int)$isRegRegionAvailable && $isFaktRegionAvailable,
            'string_result' => $this->getScoringResultText($user, $isRegRegionAvailable, $isFaktRegionAvailable)
        ];

        if (!empty($scoring->order_id)) {
            $order = $this->orders->get_order((int)$scoring->order_id);
            if ($this->needStopOrderScorings($order, $regRegionCode, $faktRegionCode, $isRegRegionAvailable, $isFaktRegionAvailable)) {
                $this->stopOrderScorings($order, $scoring);
            }
        }

        return $update;
    }

    /**
     * Проверяем, можно ли по региону регистрации и региону проживания получить займ.
     * Проверяем по коду региону (если отсутствует, то код региона получаем по названию региона)
     *
     * @param string|null $regionCode
     * @param stdClass|null $scoringType
     * @param string $regionType
     * @return bool
     */
    private function getRegionAvailability(?string $regionCode, ?stdClass $scoringType, string $regionType): bool
    {
        if ($scoringType === null) {
            return true;
        }

        if ($regionCode === null) {
            return true;
        }

        return $this->checkIsRegionAvailableByRegionCode($regionCode, $scoringType, $regionType);
    }

    /**
     * @param string|null $regionName
     * @return string|null
     */
    private function getRegionCodeByRegionName(?string $regionName): ?string
    {
        if ($regionName === null) {
            return null;
        }

        $region = $this->regionService->getRegionByName($regionName);

        if ($region !== null) {
            return $region->code;
        }

        return $this->dadataService->getRegionCodeByRegionName($regionName);
    }

    /**
     * @param string $regionCode
     * @param stdClass|null $scoringType
     * @param string $regionType
     * @return bool
     */
    private function checkIsRegionAvailableByRegionCode(string $regionCode, ?stdClass $scoringType, string $regionType): bool
    {
        if ($regionType === self::REG_REGION) {
            return
                !is_array($scoringType->params[self::EXCEPTION_REGIONS_CODE_FOR_REG_REGION]) ||
                !in_array($regionCode, $scoringType->params[self::EXCEPTION_REGIONS_CODE_FOR_REG_REGION]);
        }

        if ($regionType === self::FAKT_REGION) {
            return
                !is_array($scoringType->params[self::EXCEPTION_REGIONS_CODE_FOR_FAKT_REGION]) ||
                !in_array($regionCode, $scoringType->params[self::EXCEPTION_REGIONS_CODE_FOR_FAKT_REGION]);
        }

        return true;
    }

    /**
     * @param stdClass $userOrOrder
     * @param bool $isRegRegionAvailable
     * @param bool $isFaktRegionAvailable
     * @return string
     */
    private function getScoringResultText(stdClass $userOrOrder, bool $isRegRegionAvailable, bool $isFaktRegionAvailable): string
    {
        $fullRegRegion = $userOrOrder->Regregion . ($userOrOrder->Regregion_shorttype ? ' ' . $userOrOrder->Regregion_shorttype : '');
        $fullFaktRegion = $userOrOrder->Faktregion . ($userOrOrder->Faktregion_shorttype ? ' ' . $userOrOrder->Faktregion_shorttype : '');

        if (!$isRegRegionAvailable && !$isFaktRegionAvailable) {
            $scoringResult = 'Недопустимый регион регистрации и проживания. ';
        } elseif (!$isRegRegionAvailable) {
            $scoringResult = 'Недопустимый регион регистрации. ';
        } elseif (!$isFaktRegionAvailable) {
            $scoringResult = 'Недопустимый регион проживания. ';
        } else {
            return 'Допустимые регионы.';
        }

        return $scoringResult . 'Регион регистрации: ' . $fullRegRegion . '. Регион проживания: ' . $fullFaktRegion . '.';
    }

    /**
     * Проверяет нужно ли останавливать другие скоринги
     *
     * @param stdClass $order
     * @param string|null $regRegionCode
     * @param string|null $faktRegionCode
     * @param bool $isRegRegionAvailable
     * @param bool $isFaktRegionAvailable
     * @return bool
     */
    private function needStopOrderScorings(stdClass $order, ?string $regRegionCode, ?string $faktRegionCode, bool $isRegRegionAvailable, bool $isFaktRegionAvailable): bool
    {
        // Если НК И недопустим регион регистрации ИЛИ регион проживания, то останавливаем остальные скоринги
        if (empty($order->have_close_credits) && (!$isRegRegionAvailable || !$isFaktRegionAvailable)) {
            return true;
        }

        // Если ПК И недопустим регион регистрации ИЛИ регион проживания, то останавливаем остальные скоринги
        if (
            !empty($order->have_close_credits) &&
            (
            ($regRegionCode !== null && in_array($regRegionCode, self::EXCEPTION_REGIONS_CODE_FOR_PK)) ||
            ($faktRegionCode !== null && in_array($faktRegionCode, self::EXCEPTION_REGIONS_CODE_FOR_PK))
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Останавливает другие скоринги
     *
     * @param stdClass $order
     * @param stdClass $scoring
     * @return void
     */
    private function stopOrderScorings(stdClass $order, stdClass $scoring): void
    {
        // техаккаунт System
        $tech_manager = $this->managers->get_manager(50);

        $update_order = array(
            'status' => 3,
            'manager_id' => $tech_manager->id,
            'reason_id' => 14, // Отказ по региону
            'reject_date' => date('Y-m-d H:i:s'),
        );
        $this->orders->update_order($scoring->order_id, $update_order);
        $this->leadgid->reject_actions($scoring->order_id);
        if (!empty($order->is_user_credit_doctor)) {
            $this->soap1c->send_credit_doctor($order->id_1c);
        }

	    $changeLogs = Helpers::getChangeLogs($update_order, $order);

        $this->changelogs->add_changelog(array(
            'manager_id' => $tech_manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status',
            'old_values' => serialize($changeLogs['old']),
            'new_values' => serialize($changeLogs['new']),
            'order_id' => $order->order_id,
            'user_id' => $order->user_id,
        ));

        $this->soap->update_status_1c($order->id_1c, $this->orders::ORDER_1C_STATUS_REJECTED, $tech_manager->name_1c);

        $this->soap->send_order_manager($order->id_1c, $tech_manager->name_1c);

        $this->soap->block_order_1c($order->id_1c, 0);

        // отправляем заявку на кредитного доктора
        $this->cdoctor->send_order($order->order_id);

        // Останавливаем выполнения других скорингов по этой заявки
        $scoring_type = $this->scorings->get_type($this->scorings::TYPE_LOCATION);
        $this->scorings->stopOrderScorings($order->order_id, ['string_result' => 'Причина: скоринг ' . $scoring_type->title]);
    }

    /**
     * @param $audit_id
     * @param $user_id
     * @return bool
     */
    public function run($audit_id, $user_id): bool
    {
        $user = $this->users->get_user((int)$user_id);

        return $this->scoring($user, $audit_id);
    }

    /**
     * @param stdClass $user
     * @param $audit_id
     * @return bool
     */
    private function scoring(stdClass $user, $audit_id): bool
    {
        $scoringType = $this->scorings->get_type($this->scorings::TYPE_LOCATION);

        $isRegRegionAvailable = $this->getRegionAvailability($user->Regregion_code, $user->Regregion, $scoringType, self::REG_REGION);
        $isFaktRegionAvailable = $this->getRegionAvailability($user->Faktregion_code, $user->Faktregion, $scoringType, self::FAKT_REGION);

        $this->scorings->add_scoring([
            'user_id' => $user->id,
            'audit_id' => $audit_id,
            'type' => $this->scorings::TYPE_LOCATION,
            'body' => serialize(['Regregion' => $user->Regregion, 'Faktregion' => $user->Faktregion]),
            'success' => (int)$isRegRegionAvailable && $isFaktRegionAvailable,
            'string_result' => $this->getScoringResultText($user, $isRegRegionAvailable, $isFaktRegionAvailable)
        ]);

        return $isRegRegionAvailable && $isFaktRegionAvailable;
    }
}
