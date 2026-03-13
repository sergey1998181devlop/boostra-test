<?php

namespace Traits;

use Exception;

trait BKIHelperTrait
{

    /**
     * @throws Exception
     */
    protected function preparePassportFullNumber(string $passportFullNumber): string
    {
        $preparedPassportFullNumber  = str_replace([' ', '-'], '', $passportFullNumber);

        if (preg_match('/^\d{10}$/', $preparedPassportFullNumber)) {
            return $preparedPassportFullNumber;
        }

        throw new Exception('Passport Serial is invalid - ' . $passportFullNumber);
    }

    /**
     * @throws Exception
     */
    protected function prepareSubdivisionCode(string $subdivisionCode): string
    {
        $strlen = strlen($subdivisionCode);

        // часто не указывают тире
        if ($strlen === 6) {
            $subdivisionCode = substr($subdivisionCode, 0, 3) . '-' . substr($subdivisionCode, 3);
        }

        if (preg_match('/^\d{3}-\d{3}$/', $subdivisionCode)) {
            return $subdivisionCode;
        }

        throw new Exception('Subdivision code is not valid - ' . $subdivisionCode);
    }

    protected function preparePatronymic($patronymic): string
    {
        return empty($patronymic) ? 'НЕТ' : $patronymic;
    }

    /**
     * @throws Exception
     */
    protected function getGenderCode(string $gender): string
    {
        $map = [
            'male' => '1',
            'female' => '2',
        ];

        if (!isset($map[$gender])) {
            throw new Exception("Unknown gender: {$gender}");
        }

        return $map[$gender];
    }

    protected function preparePassportIssued(string $passportIssued): string
    {
        return htmlspecialchars(str_replace('№', '', $passportIssued));
    }

    protected function getPassportNumberFromPassportFullNumber(string $passportFullNumber): string
    {
        return substr($passportFullNumber, 4);
    }

    protected function getPassportSerialFromPassportFullNumber(string $passportFullNumber): string
    {
        return substr($passportFullNumber, 0, 4);
    }
}
