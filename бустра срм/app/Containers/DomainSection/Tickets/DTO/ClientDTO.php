<?php

namespace App\Containers\DomainSection\Tickets\DTO;

use DateTime;

/**
 * Вспомогательный DTO для работы тикетов, не является и не должен являться основным DTO для сущности клиента
 */
class ClientDTO
{
    //region Table properties
    private int $id;
    private string $name;
    private string $firstName;
    private string $lastName;
    private string $fullName;
    private string $phone;
    private string $email;
    private DateTime $birthday;
    //endregion Table properties

    //region Getters and setters
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getBirthday(): DateTime
    {
        return $this->birthday;
    }

    public function setBirthday(DateTime $birthday): void
    {
        $this->birthday = $birthday;
    }
    //endregion Getters and setters

    /**
     * @param int $id
     * @param string $name
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param string $email
     * @param string $birthday
     * @param string $fullName
     * @throws \Exception
     */
    public function __construct(
        int $id = 0,
        string $name = '',
        string $firstName = '',
        string $lastName = '',
        string $phone = '',
        string $email = '',
        string $birthday = '',
        string $fullName = ''
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->email = $email;

        if (!empty($birthday)) {
            $this->birthday = new DateTime($birthday);
        }

        $this->fullName = $fullName ?:
            $firstName . ' ' . $name . ' ' . $lastName;
    }
}