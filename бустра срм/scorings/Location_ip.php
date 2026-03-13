<?php

use boostra\services\DadataService;
use boostra\services\RegionService;

require_once __DIR__ . '/../lib/autoloader.php';

class Location_ip extends Simpla
{
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

        $user = $this->users->get_user((int)$scoring->user_id);
        $order = $this->orders->get_order($scoring->order_id);
        $update = $this->checkIp($user, $order);

        $update['end_date'] = date('Y-m-d H:i:s');
        $this->scorings->update_scoring($scoring_id, $update);

        return $update;
    }

    /**
     * Проверяет регион регистрации и проживания по ip
     *
     * @param stdClass $user
     * @param stdClass|false $order
     * @return array
     */
    private function checkIp(stdClass $user, $order): array
    {
        $ip = !empty($order) ? $order->ip : $user->reg_ip;
        if (empty($ip)) {
            return [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Не найден ip у заявки'
            ];
        }

        $location = $this->dadataService->getLocationByIp($ip);

        if (empty($location)) {
            return [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Ошибка при выполнении'
            ];
        }

        $location = json_decode($location);

        if (empty($location->location->data->region)) {
            return [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Не удалось определить регион подачи заявки'
            ];
        }

        $ipRegionCode = $this->getRegionCodeByRegionName($location->location->data->region);
//        $regRegionCode = $this->getRegionCodeByRegionName($user->Regregion);
        $faktRegionCode = $this->getRegionCodeByRegionName($user->Faktregion);

//        $isRegRegionCodeEqualIpRegionCode = $ipRegionCode === $regRegionCode;
        // пока не нужна сверка ip по региону регистрации
        $isRegRegionCodeEqualIpRegionCode = true;
        $isFaktRegionCodeEqualIpRegionCode = $ipRegionCode === $faktRegionCode;

        $update = [
            'status' => $this->scorings::STATUS_COMPLETED,
            'success' => (int)$isRegRegionCodeEqualIpRegionCode && $isFaktRegionCodeEqualIpRegionCode,
            'string_result' => $this->getScoringResultText(
                $user,
                $this->getFullRegionName($location->location->data->region, $location->location->data->region_type),
                $isRegRegionCodeEqualIpRegionCode ?: '',
                $isFaktRegionCodeEqualIpRegionCode ?: '',
                $ip
            )
        ];

        return $update;
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
     * @param stdClass $user
     * @param string $fullIpRegion
     * @param bool $isRegRegionCodeEqualIpRegionCode
     * @param bool $isFaktRegionCodeEqualIpRegionCode
     * @param string $ip
     * @return string
     */
    private function getScoringResultText(stdClass $user, string $fullIpRegion, bool $isRegRegionCodeEqualIpRegionCode, bool $isFaktRegionCodeEqualIpRegionCode, string $ip): string
    {
        if (!$isRegRegionCodeEqualIpRegionCode && !$isFaktRegionCodeEqualIpRegionCode) {
            $scoringResult = 'Регион подачи заявки отличается от региона регистрации и региона проживания. ';
        } elseif (!$isRegRegionCodeEqualIpRegionCode) {
            $scoringResult = 'Регион подачи заявки отличается от региона регистрации. ';
        } elseif (!$isFaktRegionCodeEqualIpRegionCode) {
            $scoringResult = 'Регион подачи заявки отличается от региона проживания. ';
        } else {
//            $scoringResult = 'Регион подачи заявки совпадает с регионом регистрации и регионом проживания. ';
            $scoringResult = 'Регион подачи заявки совпадает с регионом проживания. ';
        }

        $fullRegRegion = $this->getFullRegionName($user->Regregion, $user->Regregion_shorttype);
        $fullFaktRegion = $this->getFullRegionName($user->Faktregion, $user->Faktregion_shorttype);

//        return $scoringResult . 'Регион подачи заявки: ' . $fullIpRegion . '. Регион регистрации: ' . $fullRegRegion . '. Регион проживания: ' . $fullFaktRegion . '.';
        return $scoringResult;
    }

    private function getFullRegionName(string $regionName, ?string $regionShorttype): string
    {
        return $regionShorttype ? $regionName . ' ' . $regionShorttype : $regionName;
    }
}
