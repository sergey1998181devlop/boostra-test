<?php

namespace App\Modules\Payment\Services;

use Simpla;

class AddCardService extends Simpla
{
    /**
     * Определение ID организации по сектору добавления карты
     *
     * @param string $sector Сектор транзакции
     * @param bool $throwException Выбрасывать исключение, если сектор не найден
     * @return int ID организации
     * @throws \InvalidArgumentException
     */
    public function getOrganizationIdBySector(string $sector, bool $throwException = false): ?int
    {
        $mapping = [
            'AKVARIUS_ADD_CARD' => $this->organizations::AKVARIUS_ID,
            'FINLAB_ADD_CARD' => $this->organizations::FINLAB_ID,
            'LORD_ADD_CARD' => $this->organizations::LORD_ID,
            'RZS_ADD_CARD' => $this->organizations::RZS_ID,
            'FRIDA_ADD_CARD' => $this->organizations::FRIDA_ID,
            'FASTFINANCE_ADD_CARD' => $this->organizations::FASTFINANCE_ID,
        ];

        foreach ($mapping as $sectorKey => $organizationId) {
            if ($this->best2pay->sectors[$sectorKey] === $sector) {
                return $organizationId;
            }
        }

        if ($throwException) {
            throw new \InvalidArgumentException("Unknown sector: {$sector}");
        }

        return null;
    }

    /**
     * Получение ссылки на добавление карты в зависимости от организации
     *
     * @param int $user_id ID пользователя
     * @param int $organization_id ID организации
     * @param string|null $card_id ID карты (для некоторых организаций)
     * @param string|null $recurring_consent Согласие на рекуррентные платежи
     * @param array|null $params
     * @return string
     */
    public function getAddCardLink(
        int $user_id,
        int $organization_id,
        ?string $card_id = null,
        ?string $recurring_consent = null,
        ?array $params = []
    ): string {
        $sector = null;
        $useCardId = null;

        if ($organization_id == $this->organizations::BOOSTRA_ID) {
            return $this->best2pay->get_link_add_card($user_id, null, null, $recurring_consent, $params ?? []);
        }

        if ($organization_id == $this->organizations::LORD_ID) {
            $sector = $this->best2pay->sectors['LORD_ADD_CARD'];
            $useCardId = $card_id;
        } elseif ($organization_id == $this->organizations::FRIDA_ID) {
            $sector = $this->best2pay->sectors['FRIDA_ADD_CARD'];
            $useCardId = $card_id;
        } elseif ($organization_id == $this->organizations::FASTFINANCE_ID) {
            $sector = $this->best2pay->sectors['FASTFINANCE_ADD_CARD'];
            $useCardId = $card_id;
        } else {
            $sector = $this->best2pay->sectors['RZS_ADD_CARD'];
            $useCardId = null;
        }

        return $this->best2pay->get_link_add_card($user_id, $sector, $useCardId, $recurring_consent, $params ?? []);
    }
}
