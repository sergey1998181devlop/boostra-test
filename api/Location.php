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

    public const REG_REGION = 'reg_region';
    public const FAKT_REGION = 'fakt_region';

    /** @var DadataService */
    private $dadataService;
    /** @var RegionService */
    private $regionService;

    public function __construct()
    {
        parent::__construct();

        $this->dadataService = new DadataService();
        $this->regionService = new RegionService();
    }

    public function run($audit_id, $user_id, $order_id)
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

        $newScoring = [
            'user_id' => $user->id,
            'audit_id' =>$audit_id,
            'type' => $this->scorings::TYPE_LOCATION,
            'body' => serialize(['Regregion' => $user->Regregion, 'Faktregion' => $user->Faktregion]),
            'success' => (int)$isRegRegionAvailable && $isFaktRegionAvailable,
            'string_result' =>$this->getScoringResultText($user, $isRegRegionAvailable, $isFaktRegionAvailable)
        ];

        $this->scorings->add_scoring($newScoring);

        return $isRegRegionAvailable && $isFaktRegionAvailable;
    }

    /**
     * Проверяем, можно ли по региону регистрации и региону проживания получить займ.
     * Проверяем по коду региону (если отсутствует, то код региона получаем по названию региона)
     *
     * @param string|null $regionCode
     * @param string $regionName
     * @param stdClass|null $scoringType
     * @param string $regionType
     * @return bool
     */
    public function getRegionAvailability(?string $regionCode, string $regionName, ?stdClass $scoringType, string $regionType): bool
    {
        if ($scoringType === null) {
            return true;
        }

        if ($regionCode !== null) {
            return $this->checkIsRegionAvailableByRegionCode($regionCode, $scoringType, $regionType);
        }

        return $this->checkIsRegionAvailableByRegionName($regionName, $scoringType, $regionType);
    }

    /**
     * @param $regionName
     * @param $scoringType
     * @param $regionType
     * @return bool
     */
    private function checkIsRegionAvailableByRegionName($regionName, $scoringType, $regionType): bool
    {
        $regionCode = $this->getRegionCodeByRegionName($regionName);

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

        $dadataAddress = $this->dadataService->suggest("address", ['query' => $regionName, 'count' => 1]);

        if (empty($dadataAddress)) {
            return null;
        }

        $dadataAddress = json_decode($dadataAddress, true);

        if (empty($dadataAddress['suggestions']) || empty($dadataAddress['suggestions'][0]['data']['region'])) {
            return null;
        }

        $dadataRegionName = $dadataAddress['suggestions'][0]['data']['region'];

        $region = $this->regionService->getRegionByName($dadataRegionName);

        if ($region === null) {
            return null;
        }

        return $region->code;
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
     * @param stdClass $user
     * @param bool $isRegRegionAvailable
     * @param bool $isFaktRegionAvailable
     * @return string
     */
    public function getScoringResultText(stdClass $user, bool $isRegRegionAvailable, bool $isFaktRegionAvailable): string
    {
        if (!$isRegRegionAvailable && !$isFaktRegionAvailable) {
            $scoringResult = 'Недопустимый регион регистрации и проживания. ';
        } elseif (!$isRegRegionAvailable) {
            $scoringResult = 'Недопустимый регион регистрации. ';
        } elseif (!$isFaktRegionAvailable) {
            $scoringResult = 'Недопустимый регион проживания. ';
        } else {
            $scoringResult = 'Допустимые регионы. ';
        }

        $fullRegRegion = $user->Regregion . ($user->Regregion_shorttype ? ' ' . $user->Regregion_shorttype : '');
        $fullFaktRegion = $user->Faktregion . ($user->Faktregion_shorttype ? ' ' . $user->Faktregion_shorttype : '');

        return $scoringResult . 'Регион регистрации: ' . $fullRegRegion . '. Регион проживания: ' . $fullFaktRegion . '.';
    }
}