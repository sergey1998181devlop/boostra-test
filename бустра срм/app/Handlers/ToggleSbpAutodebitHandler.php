<?php

namespace App\Handlers;

use App\Contracts\ToggleAutodebitHandlerContract;
use App\Repositories\B2PSbpAccountRepository;
use App\Repositories\UserRepository;
use App\Modules\SbpAccount\Services\SbpAccountService;

class ToggleSbpAutodebitHandler implements ToggleAutodebitHandlerContract
{
    private B2PSbpAccountRepository $sbpRepository;
    private UserRepository $userRepository;
    private SbpAccountService $sbpService;

    public function __construct(
        B2PSbpAccountRepository $sbpRepository,
        UserRepository $userRepository,
        SbpAccountService $sbpService
    ) {
        $this->sbpRepository = $sbpRepository;
        $this->userRepository = $userRepository;
        $this->sbpService = $sbpService;
    }

    /**
     * Переключает автодебет для всех СБП-счетов пользователя
     *
     * @param int $userId
     * @param int $orderId
     * @param int $value 0|1
     * @param int $managerId
     * @return array
     * @throws \Exception
     */
    public function handle(int $userId, int $orderId, int $value, int $managerId): array
    {
        $sbpAccounts = $this->sbpRepository->getAllByUserId($userId);

        if (empty($sbpAccounts)) {
            return [
                'success' => false,
                'message' => 'У пользователя нет СБП-счетов'
            ];
        }

        $sbpAutodebitParams = [];
        foreach ($sbpAccounts as $sbpAccount) {
            $sbpAutodebitParams[(int)$sbpAccount->id] = $value;
        }

        // Получаем uid пользователя для RC
        $userUid = $this->userRepository->getUidById($userId);
        
        if (empty($userUid)) {
            return [
                'success' => false,
                'message' => 'Пользователь не найден'
            ];
        }

        $this->sbpService->changeAutodebitParam(
            $sbpAutodebitParams,
            $userId,
            $orderId,
            $managerId,
            $userUid
        );

        return [
            'success' => true,
            'message' => 'СБП: автодебет ' . ($value ? 'включен' : 'выключен')
        ];
    }
}
