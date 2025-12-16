<?php

require_once 'Simpla.php';

class Organizations extends Simpla
{
    public const BOOSTRA_ID = 1;
    public const AKVARIUS_ID = 6;
    public const ACADO_ID = 7;
    public const FINTEHMARKET_ID = 8;
    public const FINLAB_ID = 11;
    public const VIPZAIM_ID = 12;
    public const RZS_ID = 13;
    public const LORD_ID = 15;
    public const MOREDENEG_ID = 17;
    public const FRIDA_ID = 20;

    public function get_base_organization($params = [])
    {
        return $this->get_organization($this->get_base_organization_id($params));
    }
    
    public function get_base_organization_id($params = []): int
    {
        if (!empty($_COOKIE['test_frida']) || (!empty($params['organization_id']) && $params['organization_id'] == self::FRIDA_ID)) {
            return self::FRIDA_ID;
        } else {
            return (int)$this->settings->base_organization_id;
        }
    }
    
    /**
     * Organizations::get_inn_for_recurrents()
     * Метод возвращает список ИНН организаций, по которым нужно списывать реккуренты
     * Также используется для проверки наличия выданных займов в Soap1c::DebtForFIO
     * @return array
     */
    public function get_inn_for_recurrents(bool $only_values = true)
    {
        $organizations_map = [
            self::BOOSTRA_ID,
            self::AKVARIUS_ID,
            self::ACADO_ID,
            self::FINLAB_ID,
            self::LORD_ID,
            self::RZS_ID,
            self::MOREDENEG_ID,
            self::FRIDA_ID,
        ];

        $inn = [];
        foreach ($this->getList() as $org) {
            if (in_array($org->id, $organizations_map)) {
                $inn[$org->id] = $org->inn;
            }
        }
        
        return $only_values ? array_values($inn) : $inn;
    }

    /**
     * Get list organizations
     *
     * @return array
     */
    public function getList(): array
    {

        $query = $this->db->placehold("SELECT * FROM s_organizations ORDER BY id ASC ");

        $this->db->query($query);
        return $this->db->results();

    }

    public function get_organization($id)
    {
    	$this->db->query("
            SELECT * FROM s_organizations
            WHERE id = ?
        ", (int)$id);

        $organization = $this->db->result();

        if (!empty($organization)) {
            $organization->params = json_decode($organization->params, true);
        }

        return $organization;
    }
    
    public function get_organization_id_by_inn($inn)
    {
    	$this->db->query("
            SELECT id FROM s_organizations
            WHERE inn = ?
        ", (int)$inn);
        return $this->db->result('id');        
    }

    /**
     * Считается ли нашей (не требует перепривязки) карта с этой организации?
     * @return bool
     */
    public function is_our_card($organization_id)
    {
        return $organization_id != self::BOOSTRA_ID;
    }

    public function isFinlab(int $organizationId): bool
    {
        return $organizationId === $this->organizations::FINLAB_ID;
    }

    public function isAkvarius(int $organizationId): bool
    {
        return $organizationId === $this->organizations::AKVARIUS_ID;
    }

    public function isCrossOrderOrganizationId(int $organizationId): bool
    {
        $crossOrderOrganizationIdFromSettings = $this->settings->cross_organization_id;

        if ($organizationId === (int)$crossOrderOrganizationIdFromSettings) {
            return true;
        }

        $organizations = $this->organizations->getList();

        foreach ($organizations as $organization) {
            if (!empty($organization->cross_orders) && $organizationId === (int)$organization->id) {
                return true;
            }
        }

        return false;
    }

    public function assign_to_design()
    {
        $this->design->assign('ORGANIZATION_FINLAB', self::FINLAB_ID);
        $this->design->assign('ORGANIZATION_VIPZAIM', self::VIPZAIM_ID);
        $this->design->assign('ORGANIZATION_AKVARIUS', self::AKVARIUS_ID);
        $this->design->assign('ORGANIZATION_RZS', self::RZS_ID);
        $this->design->assign('ORGANIZATION_LORD', self::LORD_ID);
        $this->design->assign('ORGANIZATION_MOREDENEG', self::MOREDENEG_ID);
        $this->design->assign('ORGANIZATION_FRIDA', self::FRIDA_ID);
    }

    /**
     * Вернуть массив ИНН по строковому site_id (напр. 'main').
     * Учитывает множественные связи в s_sites_organizations.
     *
     * @return array            // ['123456789', '987654321', ...]
     */
    public function get_site_inns(): array
    {
        $query = $this->db->placehold("
            SELECT DISTINCT o.inn
            FROM s_sites_organizations so
            INNER JOIN s_organizations o ON o.id = so.organization_id
            WHERE so.site_id = ?
              AND o.inn <> ''
        ", $this->config->site_id);

        $this->db->query($query);
        return array_map(fn($r) => $r->inn, (array)$this->db->results());
    }
}