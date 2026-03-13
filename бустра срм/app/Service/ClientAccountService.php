<?php

namespace App\Service;

use App\Repositories\BlockedAdvSmsRepository;
use App\Repositories\OrdersRepository;
use App\Repositories\UserRepository;

class ClientAccountService
{
    public const ERR_USER_NOT_FOUND = 'Пользователь не найден';
    public const ERR_ALREADY_BLOCKED = 'Личный кабинет уже заблокирован';
    public const ERR_ALREADY_UNBLOCKED = 'Личный кабинет не заблокирован';
    public const ERR_HAS_ACTIVE_CONTRACTS = 'У клиента есть активные контракты';

    private UserRepository $users;
    private OrdersRepository $orders;
    private BlockedAdvSmsRepository $advSms;

    public function __construct(UserRepository $users, OrdersRepository $orders, BlockedAdvSmsRepository $advSms)
    {
        $this->users = $users;
        $this->orders = $orders;
        $this->advSms = $advSms;
    }

    public function tryBlock(int $userId): ?string
    {
        $user = $this->users->getById($userId);
        if (!$user) {
            return self::ERR_USER_NOT_FOUND;
        }
        if (!empty($user->blocked)) {
            return self::ERR_ALREADY_BLOCKED;
        }
        if ($this->orders->hasActiveIssuedContracts($userId)) {
            return self::ERR_HAS_ACTIVE_CONTRACTS;
        }

        $this->users->updateBlocked($userId, 1);

        // отписка от рекламных СМС при блокировке ЛК
        $this->advSms->blockAdvForUser($userId, $user->phone_mobile ?? null);
        return null;
    }

    public function tryUnblock(int $userId): ?string
    {
        $user = $this->users->getById($userId);
        if (!$user) {
            return self::ERR_USER_NOT_FOUND;
        }
        if (empty($user->blocked)) {
            return self::ERR_ALREADY_UNBLOCKED;
        }

        $this->users->updateBlocked($userId, 0);
        $this->users->resetPasswordIncorrectTotal($userId);
        $this->orders->resetOrdersAcceptTry($userId);

        // убрать из игнора рекламных СМС
        $this->advSms->unblockAdvForUser($userId);
        return null;
    }
}


