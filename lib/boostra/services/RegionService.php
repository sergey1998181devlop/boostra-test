<?php

namespace boostra\services;

use boostra\domains\Region;

class RegionService extends Core
{
    /**
     * @return Region[]
     * */
    public function getRegions(): array
    {
        $query = $this->db->placehold(sprintf('SELECT * FROM %s ORDER BY `name` ASC', Region::table()));
        $this->db->query($query);

        $regions = $this->db->results();

        if (empty($regions)) {
            return [];
        }

        $regionsCollection = [];
        foreach ($regions as $region) {
            $region->short_name = trim(preg_replace('/[Аа]втономная область|[Аа]втономный округ$|[Оо]бласть|[Кк]рай|[Рр]еспублика/ui', '', $region->name));
            $regionsCollection[] = $region;
        }

        return $regionsCollection;
    }

    /**
     * @param string|null $code
     * @return Region|null
     */
    public function getRegionByCode(?string $code): ?Region
    {
        if ($code === null) {
            return null;
        }

        $query = $this->db->placehold(sprintf('SELECT * FROM %s WHERE `code` = ? LIMIT 1', Region::table()), $code);
        $this->db->query($query);

        $region = $this->db->result();

        if (empty($region)) {
            return null;
        }

        return new Region($region);
    }

    /**
     * @param string|null $regionName
     * @return Region|null
     */
    public function getRegionByName(?string $regionName): ?Region
    {
        if ($regionName === null) {
            return null;
        }

        $query = $this->db->placehold(sprintf('SELECT * FROM %s WHERE `name` LIKE "%%' . $this->db->escape($regionName) . '%%" LIMIT 1', Region::table()));
        $this->db->query($query);

        $region = $this->db->result();

        if (empty($region)) {
            return null;
        }

        return new Region($region);
    }
}
