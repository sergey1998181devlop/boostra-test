<?php

namespace Api\Services;

use UserData;

class UserDataService
{
    private const INN_NOT_FOUND = 'inn_not_found';
    /** @var UserData */
    private UserData $userData;

    public function __construct(UserData $userData)
    {
        $this->userData = $userData;
    }

    public function checkInn(int $userId, $inn): int
    {
        $innNotFound = $this->getByKey($userId, self::INN_NOT_FOUND);
        if (!empty($innNotFound)) {
            if (empty($inn)) {
                return 1;
            }

            $this->clearInnNotFound($userId);
        }

        return 0;
    }

    public function getAll(int $userId): array
    {
        $userData = $this->userData->getAll($userId);

        return $userData ? array_column($userData, 'value', 'key') : [];
    }

    public function getByKey(int $userId, string $key)
    {
        return $this->userData->read($userId, $key);
    }

    public function clearInnNotFound(int $userId): void
    {
        $this->userData->set($userId, self::INN_NOT_FOUND, 0);
    }
}