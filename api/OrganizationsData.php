<?php

require_once 'Simpla.php';

class OrganizationsData extends Simpla
{
    private const TABLE_NAME = 's_organizations_data';

    public function get_data(int $organization_id, string $key)
    {
        $this->db->query(
            'SELECT `value` FROM `' . self::TABLE_NAME . '` WHERE `organization_id` = ? AND `key` = ?',
            $organization_id,
            $key
        );

        $result = $this->db->result('value');

        $object = json_decode($result);

        if (!empty($object)) {
            return $object;
        }

        return $result;
    }

    /**
     * Получить конфиг Axi для организации
     * @param int $organizationId
     * @param bool $isCross
     * @return object|null
     */
    public function getAxiConfig(int $organizationId, bool $isCross = false): ?object
    {
        $config = $this->get_data($organizationId, 'axi_config');

        if (empty($config)) {
            return null;
        }

        $variant = $isCross && isset($config->cross) ? 'cross' : 'base';

        return $config->{$variant} ?? null;
    }

    /**
     * Получить конфиг Pixel для организации
     * @param int $organizationId
     * @return object|null
     */
    public function getPixelConfig(int $organizationId): ?object
    {
        $config = $this->get_data($organizationId, 'pixel_config');

        if (empty($config)) {
            return null;
        }

        return $config;
    }
}
