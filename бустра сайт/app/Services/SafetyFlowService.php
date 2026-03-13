<?php

namespace App\Services;

use App\Repositories\ContractRepository;

class SafetyFlowService
{
    /**
     * @var \Users
     */
    private $users;

    /**
     * @var \Database
     */
    private $db;

    /**
     * @var \OrderData
     */
    private $orderData;

    /**
     * @var ContractRepository
     */
    private $contractRepo;

    public function __construct(\Users $users, \Database $db, \OrderData $orderData, ContractRepository $contractRepo)
    {
        $this->users = $users;
        $this->db = $db;
        $this->orderData = $orderData;
        $this->contractRepo = $contractRepo;
    }

    /**
     * Проверяет, был ли первый займ получен по безопасному флоу для ПК клиентов.
     *
     * @param object $user
     * @return bool
     */
    public function isFirstLoanSafeFlow(object $user): bool
    {
        if (!$this->users->is_organic($user->utm_source)) {
            return false;
        }

        $firstContract = $this->contractRepo->getFirstContractByUserId($user->id);

        if (empty($firstContract)) {
            return false;
        }

        // Проверка, если контракт был выдан на безопасном флоу
        $safetyFlow = $this->orderData->read($firstContract->order_id, \OrderData::SAFETY_FLOW);

        if ($safetyFlow !== null) {
            return $safetyFlow == '1';
        }

        // Если safety_flow не найден, то проверяем время выдачи первого займа
        if (!empty($firstContract->issuance_date)) {
            $issueHour = (int) date('H', strtotime($firstContract->issuance_date));
            return $issueHour >= 9 && $issueHour < 17; // Проверяем, если займ выдан в рабочие часы
        }

        return false;
    }
}
