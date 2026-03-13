<?php

namespace App\Repositories;

use App\Dto\ExtraServicesInformDto;

class ExtraServicesInformRepository
{
    /** @var \Database */
    private $db;

    public function __construct(\Database $db)
    {
        $this->db = $db;
    }

    /**
     * Сохранение информации об отправке SMS
     *
     * @param ExtraServicesInformDto $dto
     * @return int|null
     */
    public function insert(ExtraServicesInformDto $dto): ?int
    {
        $query = $this->db->placehold("INSERT INTO s_extra_services_informs SET ?%", $dto->toDbArray());
        $this->db->query($query);
        return $this->db->insert_id();
    }
}
