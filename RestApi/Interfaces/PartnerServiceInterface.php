<?php

namespace RestApi\Interfaces;

/**
 * Реализация основных методов
 */
interface PartnerServiceInterface
{
    /**
     * Генерирует токен доступа
     * @return array
     */
    public function getToken(): array;

    /**
     * Отправить запрос на проверку повтоного клиента, а также проверки черных списков и проверки клиента на готовность выдачи займа
     * @return array
     */
    public function checkDouble(): array;

    /**
     * Отправить анкету клиента для получения решения по займуслушай у
     * @return array
     */
    public function applicationForDecisions(): array;

    /**
     * Отправка запроса для получения решения по займу
     * @return array
     */
    public function checkDecisions(): array;

    /**
     * Получаем поля пользователя из запроса
     * @param bool $new_client Нужно ли добавлять стандартные сервисные поля
     * @return array
     */
    public function getUserFields(bool $new_client = true): array;

    /**
     * Получаем поля для заявки
     * @param int $user_id
     * @return array
     */
    public function getOrderFields(int $user_id): array;

    /**
     * Получаем id образования из базы по значению
     *
     * @param string $value
     * @return int
     */
    public function getEducationId(string $value): int;

    /**
     * Получаем id семейного положения из базы по значению
     *
     * @param string $value
     * @return string|null
     */
    public function getMaritalStatusId(string $value): ?string;

    /**
     * Получаем статус занятости
     *
     * @param string $value
     * @return string|null
     */
    public function getProfession(string $value): ?string;
}
