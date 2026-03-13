<?php

namespace App\Repositories;

use App\Dto\ServicePurchaseDto;
use App\Enums\LicenseServiceType;

class ExtraServicePurchaseRepository
{
    /** @var \Database */
    private $db;

    public function __construct(\Database $db)
    {
        $this->db = $db;
    }

    public function getLastNotFullyReturnedByOrderAndType(int $orderId, string $type): ?ServicePurchaseDto
    {
        $table = LicenseServiceType::getServiceTable($type);
        if (!$table) {
            return null;
        }

        $query = "SELECT order_id, user_id, date_added, amount, amount_total_returned
                  FROM {$table}
                  WHERE order_id = ? AND amount_total_returned < amount
                  ORDER BY date_added DESC
                  LIMIT 1";
        $this->db->query($query, $orderId);
        $row = $this->db->result();
        return $row ? ServicePurchaseDto::fromDbRow($row) : null;
    }
}


