<?php

namespace App\Modules\AdditionalServiceRecovery\Infrastructure\Adapter;

use Soap1c;

/**
 * Адаптер для legacy Soap1c
 */
class Soap1cAdapter
{
    /** @var Soap1c */
    private Soap1c $soap1c;

    /**
     * @param Soap1c $soap1c
     */
    public function __construct(Soap1c $soap1c)
    {
        $this->soap1c = $soap1c;
    }

    /**
     * Получает график платежей для займа-инстолмента
     *
     * @param string $loanType
     * @param string $contractNumber
     * @return void
     */
    public function getSchedulePayments(string $loanType, string $contractNumber): array
    {
        $schedulePayments = [];

        if ($loanType == 'IL') {
            $result = $this->soap1c->get_schedule_payments($contractNumber);

            // Проверяем, что результат не является объектом ошибки и не пустой
            if ($result instanceof \SoapFault || $result instanceof \Exception || empty($result)) {
                return [];
            }

            $schedulePayments = (array)end($result);

            if (!empty($schedulePayments['Платежи']) && is_array($schedulePayments['Платежи'])) {
                $schedulePayments['Платежи'] = array_map(static function ($var) {
                    return (array)$var;
                }, $schedulePayments['Платежи']);
            } else {
                $schedulePayments['Платежи'] = [];
            }
        }

        return $schedulePayments;
    }

    /**
     * Получает детальную информацию по займу-инстолменту
     *
     * @param string $contractNumber
     * @return array
     */
    public function getIlDetails(string $contractNumber): array
    {
        $result = $this->soap1c->get_il_details($contractNumber);

        // Проверяем, что результат не является объектом ошибки и не пустой
        if ($result instanceof \SoapFault || $result instanceof \Exception || empty($result)) {
            return [];
        }

        return is_array($result) ? $result : (array)$result;
    }
}