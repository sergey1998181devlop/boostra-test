<?php

namespace api\helpers;
use boostra\services\RegionService;

/**
 * Helper для определения временной зоны по региону
 */
class TimeZoneHelper
{
    /**
     * Определение временной зоны по коду региона (из users_addresses.region_code или regions.code).
     * @param string|null $regionCode
     * @return string
     */
    public static function getTimezoneByRegionCode(?string $regionCode): string
    {
        if ($regionCode === null || $regionCode === '') {
            return 'Europe/Moscow';
        }

        $region = (new RegionService())->getRegionByCode(trim($regionCode));
        return $region && $region->timezone !== null ? $region->timezone : 'Europe/Moscow';
    }

    /**
     * @param string|null $region
     * @return array
     */
    public static function parseRegion(?string $region): array
    {
        if (!$region) {
            return [];
        }

        $searchRegion = trim(mb_strtolower(
            self::cutShortDistrictName($region)
        ));

        foreach ((new RegionService())->getRegions() as $region) {
            if (mb_strpos(mb_strtolower($region->name), mb_strtolower($searchRegion)) !== false) {
                return [$region->name, $region->timezone ?? 'Europe/Moscow'];
            }
        }

        return [];
    }

    /**
     * @param string $region
     * @return string
     */
    private static function cutShortDistrictName(string $region): string
    {
        $shorts = ['область', 'обл', 'край', 'г. ', 'г ', 'республика', 'респ'];
        $region = str_replace($shorts, '', mb_strtolower($region));

        return trim($region);
    }
}