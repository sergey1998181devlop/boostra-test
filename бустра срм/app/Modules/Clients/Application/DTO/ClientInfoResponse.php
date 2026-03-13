<?php

namespace App\Modules\Clients\Application\DTO;

/**
 * DTO для ответа API с информацией о клиенте.
 * 
 * Содержит полную информацию о клиенте, включая базовые данные пользователя,
 * информацию о заявке, договоре и балансе. Может возвращать только данные
 * пользователя если активных займов нет.
 * 
 * @package App\Modules\Clients\Application\DTO
 */
class ClientInfoResponse
{
    private string $id;
    private string $firstname;
    private string $lastname;
    private string $patronymic;
    private string $phone;
    private bool $blocked;
    private bool $autoInformerEnabled;
    private bool $recurrentsDisabled;
    private array $loans;
    private ?ContractDTO $contract;
    private ?BalanceDTO $balance;

    /**
     * @param string $id ID клиента
     * @param string $firstname Имя
     * @param string $lastname Фамилия
     * @param string $patronymic Отчество
     * @param string $phone Номер телефона
     * @param bool $blocked Статус блокировки
     * @param bool $autoInformerEnabled Признак включенного автоинформатора
     * @param bool $recurrentsDisabled Признак отключенных рекуррентных платежей
     * @param array<int, array{order: OrderDTO, contract: ContractDTO|null, balance: BalanceDTO|null}> $loans
     * @param ContractDTO|null $contract Верхнеуровневый контракт (для совместимости со сценарием Voximplant)
     * @param BalanceDTO|null $balance Верхнеуровневый баланс (для совместимости со сценарием Voximplant)
     */
    public function __construct(
        string $id,
        string $firstname,
        string $lastname,
        string $patronymic,
        string $phone,
        bool $blocked,
        bool $autoInformerEnabled,
        bool $recurrentsDisabled,
        array $loans = [],
        ?ContractDTO $contract = null,
        ?BalanceDTO $balance = null
    ) {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->patronymic = $patronymic;
        $this->phone = $phone;
        $this->blocked = $blocked;
        $this->autoInformerEnabled = $autoInformerEnabled;
        $this->recurrentsDisabled = $recurrentsDisabled;
        $this->loans = $loans;
        $this->contract = $contract;
        $this->balance = $balance;
    }

    /**
     * Преобразует DTO в массив для JSON ответа.
     * 
     * @return array Массив данных для сериализации в JSON
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'patronymic' => $this->patronymic,
            'phone' => $this->phone,
            'blocked' => $this->blocked,
            'auto_informer_enabled' => $this->autoInformerEnabled,
            'recurrents_disabled' => $this->recurrentsDisabled,
            'contract' => $this->contract ? $this->contract->toArray() : null,
            'balance' => $this->balance ? $this->balance->toArray() : null,
            'loans' => array_map(function ($loan) {
                return [
                    'order' => $loan['order']->toArray(),
                    'contract' => $loan['contract'] ? $loan['contract']->toArray() : null,
                    'balance' => $loan['balance'] ? $loan['balance']->toArray() : null,
                ];
            }, $this->loans),
        ];
    }
}