<?php

namespace App\Handlers;

use App\Contracts\ToggleAutodebitHandlerContract;
use App\Repositories\B2PCardRepository;
use App\Repositories\UserRepository;
use App\Modules\Card\Services\CardService;

class ToggleCardAutodebitHandler implements ToggleAutodebitHandlerContract
{
    private B2PCardRepository $cardRepository;
    private UserRepository $userRepository;
    private CardService $cardService;

    public function __construct(
        B2PCardRepository $cardRepository,
        UserRepository $userRepository,
        CardService $cardService
    ) {
        $this->cardRepository = $cardRepository;
        $this->userRepository = $userRepository;
        $this->cardService = $cardService;
    }

    /**
     * Переключает автодебет для всех карт пользователя
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
        $cards = $this->cardRepository->getAllByUserId($userId);

        if (empty($cards)) {
            return [
                'success' => false,
                'message' => 'У пользователя нет карт'
            ];
        }

        $cardAutodebitParams = [];
        foreach ($cards as $card) {
            $cardAutodebitParams[(int)$card->id] = $value;
        }

        // uid пользователя для RC
        $userUid = $this->userRepository->getUidById($userId);
        
        if (empty($userUid)) {
            return [
                'success' => false,
                'message' => 'Пользователь не найден'
            ];
        }

        $this->cardService->changeAutodebitParam(
            $cardAutodebitParams,
            $userId,
            $orderId,
            $managerId,
            $userUid
        );

        return [
            'success' => true,
            'message' => 'Карты: автодебет ' . ($value ? 'включен' : 'выключен')
        ];
    }
}
