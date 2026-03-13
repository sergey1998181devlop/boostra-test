<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

final class PersonProfileDto implements BaseDtoInterface
{
    public ?string $secretCodeWord;
    public ?string $middleName;
    public ?string $lastName;
    public ?string $birthDate;
    public ?string $birthPlace;
    public ?int $genderCode; // 1 – Мужчина 2 - Женщина
    public ?int $educationCode; // 1 – высшее 2 – два и более высших 3 – начальное, не полное среднее 5 – среднее или специальное
    public ?int $maritalCode; // 1 - Женат/Замужем 2 - Холост/не замужем 3 - Разведен/разведена 4 - Вдова/вдовец 5 - Гражданский брак

    public ?int $numberChild; // Количество детей
    public ?string $snils;


    public function __construct(
        ?string $secretCodeWord,
        ?string $middleName,
        ?string $lastName,
        ?string $birthDate,
        ?string $birthPlace,
        ?int $genderCode,
        ?int $educationCode,
        ?int $maritalCode,
        ?int $numberChild,
        ?string $snils
    ) {
        $this->secretCodeWord = $secretCodeWord;
        $this->middleName = $middleName;
        $this->lastName = $lastName;
        $this->birthDate = $birthDate;
        $this->birthPlace = $birthPlace;
        $this->genderCode = $genderCode;
        $this->educationCode = $educationCode;
        $this->maritalCode = $maritalCode;
        $this->numberChild = $numberChild;
        $this->snils = $snils;
    }

    public function isNull(): bool
    {
        return null === $this->secretCodeWord &&
               null === $this->middleName &&
               null === $this->lastName &&
               null === $this->birthDate &&
               null === $this->birthPlace &&
               null === $this->genderCode &&
               null === $this->educationCode &&
               null === $this->maritalCode &&
               null === $this->numberChild &&
               null === $this->snils;
    }
}