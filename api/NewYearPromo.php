<?php

/**
 * Simpla CMS
 *
 * @copyright    2011 Denis Pikusov
 * @link        http://simplacms.ru
 * @author        Denis Pikusov
 *
 */
require_once('Simpla.php');

class NewYearPromo extends Simpla
{
    const START_DATE = '2023-12-01 00:00:00';
    const END_DATE = '2024-01-15';

    /**
     * Проверить, существует ли код участника у пользователя
     *
     * @param int $userId
     * @return int
     */
    public function hasParticipantCode(int $userId): int
    {
        $query = $this->db->placehold('SELECT COUNT(id) cnt FROM __participant_codes WHERE user_id = ? AND code IS NOT NULL', $userId);
        $this->db->query($query);
        return $this->db->result('cnt');
    }

    /**
     * Пересчитать количество кодов у пользователя
     *
     * @param int $userId
     * @return int
     */
    public function getGeneratedCodesCount(int $userId): int
    {
        $query = $this->db->placehold('SELECT generated_codes_count FROM __users WHERE id = ?', $userId);
        $this->db->query($query);
        return $this->db->result('generated_codes_count');
    }

    /**
     * Увеличить количество сгенерированных кодов у пользователя
     *
     * @param int $userId
     * @return void
     */
    public function incrementGeneratedCodesCount(int $userId)
    {
        $query = $this->db->placehold('UPDATE __users SET generated_codes_count = generated_codes_count + 1 WHERE id = ?', $userId);
        $this->db->query($query);
    }

    /**
     * Проверка кода на уникальность
     *
     * @param string $code
     * @return bool
     */
    public function isCodeExists(string $code): bool
    {
        $query = $this->db->placehold('SELECT COUNT(id) cnt FROM __participant_code WHERE code = ?', $code);
        $this->db->query($query);
        $count = $this->db->result('cnt');

        return $count > 0;
    }

    /**
     * Сохранить код участника для пользователя
     *
     * @param int $userId
     * @param string $participantCode
     * @return void
     */
    public function saveParticipantCode(int $userId, string $participantCode)
    {
        while ($this->isCodeExists($participantCode)) {
            $participantCode = $this->newYearHelper->generatePromoCode($userId);
        }

        $existingCode = $this->getParticipantCode($userId);

        if ($existingCode) {
            $query = $this->db->placehold('UPDATE __participant_codes SET code = ? WHERE user_id = ?', $participantCode, $userId);
        } else {
            $query = $this->db->placehold('INSERT INTO __participant_codes SET user_id = ?, code = ?', $userId, $participantCode);
        }

        $this->db->query($query);
    }

    /**
     * Получить код участника для пользователя
     *
     * @param int $userId
     * @return string|null
     */
    public function getParticipantCode(int $userId): ?string
    {
        $query = $this->db->placehold('SELECT code FROM __participant_codes WHERE user_id = ?', $userId);
        $this->db->query($query);
        return $this->db->result('code');
    }

    /**
     * Может ли пользователь сгенерировать код участника
     *
     * @param int $userId
     * @return bool
     */
    public function canGenerateCode(int $userId): bool
    {
        $currentLevel = $this->getParticipantLevel($userId);
        $generatedCodesCount = $this->getGeneratedCodesCount($userId);

        if ($currentLevel === 1) {
            return ($generatedCodesCount < 1);
        }

        if ($currentLevel === 2) {
            return ($generatedCodesCount < 2);
        }

        return false;
    }

    /**
     * Получить данные баннера по его идентификатору
     *
     * @param int $bannerId
     * @return array|null
     */
    public function getBannerData(int $bannerId): ?stdClass
    {
        $query = $this->db->placehold('SELECT * FROM __promo_banners WHERE id = ?', $bannerId);
        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Получить уровень участника акции
     *
     * @param int $userId
     * @return int
     */
    public function getParticipantLevel(int $userId): int
    {

        $takenAndClosedContractsInPromoPeriod = $this->getTakenAndClosedContractsCountInPromoPeriod($userId);
        $closedContractsOutsidePromoPeriod = $this->hasClosedContractsOutsidePromoPeriod($userId);
        $hasReturnedMoneyOnTime = $this->hasReturnedMoneyOnTime($userId);
        $isClosedLoanUsedInPromotion = $this->isClosedLoanUsedInPromotion($userId);
        $hasClosedContractUsedForDays = $this->hasClosedContractUsedForDays($userId, 16);

        if (!$hasReturnedMoneyOnTime) {
            $this->clearParticipantDataOnOverdue($userId);
            return 0;
        }

        if ($takenAndClosedContractsInPromoPeriod === 1) {
            if ($closedContractsOutsidePromoPeriod) {
                return 1;
            }
            return 0;
        }

        if ($takenAndClosedContractsInPromoPeriod >= 2) {
            if ($closedContractsOutsidePromoPeriod || $isClosedLoanUsedInPromotion || $hasClosedContractUsedForDays) {
                return 2;
            }
            return 1;
        }

        return 0;
    }

    /**
     * Проверка, вернул ли клиент деньги вовремя после закрытия договора с учетом просрочки
     *
     * @param int $userId
     * @return bool
     */
    public function hasReturnedMoneyOnTime(int $userId): bool
    {
        $currentDate = date('Y-m-d');

        $query = $this->db->placehold('
        SELECT COUNT(id) AS cnt 
        FROM __contracts 
        WHERE user_id = ? 
            AND issuance_date BETWEEN ? AND ?
            AND (
                (close_date IS NOT NULL AND close_date > return_date AND close_date BETWEEN ? AND ?) 
                OR 
                (close_date IS NULL AND return_date < ?)
            )',
            $userId, self::START_DATE, self::END_DATE, self::START_DATE, self::END_DATE, $currentDate);

        $this->db->query($query);
        $overdueCount = $this->db->result('cnt');

        return $overdueCount <= 0;
    }


    /**
     * Обнулить количество генераций кодов у пользователя
     *
     * @param int $userId
     * @return void
     */
    public function resetGeneratedCodesCount(int $userId): void
    {
        $query = $this->db->placehold('UPDATE __users SET generated_codes_count = 0 WHERE id = ?', $userId);
        $this->db->query($query);
    }

    /**
     * Удаление кода в связи с просрочкой
     * @param int $userId
     * @return void
     */
    public function deleteParticipantCodes(int $userId): void
    {
        $query = $this->db->placehold('DELETE FROM __participant_codes WHERE user_id = ?', $userId);
        $this->db->query($query);
    }

    /**
     * Обнулить клиента
     * @param int $userId
     * @return void
     */
    public function clearParticipantDataOnOverdue(int $userId): void
    {
        $this->resetGeneratedCodesCount($userId);
        $this->deleteParticipantCodes($userId);
    }

    /**
     * Получить количество взятых и закрытых договоров в период акции
     *
     * @param int $userId
     * @return int
     */
    public function getTakenAndClosedContractsCountInPromoPeriod(int $userId): int
    {
        $query = $this->db->placehold('SELECT COUNT(id) cnt 
                                   FROM __contracts 
                                   WHERE user_id = ? 
                                   AND issuance_date BETWEEN ? AND ? 
                                   AND close_date IS NOT NULL 
                                   AND close_date BETWEEN ? AND ?',
            $userId, self::START_DATE, self::END_DATE, self::START_DATE, self::END_DATE);

        $this->db->query($query);
        return $this->db->result('cnt');
    }

    /**
     * Проверка, использовался ли закрытый займ клиентом указанное количество дней и более
     *
     * @param int $userId
     * @param int $days
     * @return int
     */
    public function hasClosedContractUsedForDays(int $userId, int $days): int
    {
        $query = $this->db->placehold('SELECT COUNT(id) cnt 
                           FROM __contracts 
                           WHERE user_id = ? 
                           AND close_date IS NOT NULL
                           AND DATEDIFF(close_date, issuance_date) >= ?
                           AND close_date BETWEEN ? AND ?
                           AND issuance_date BETWEEN ? AND ?',
            $userId, $days, self::START_DATE, self::END_DATE, self::START_DATE, self::END_DATE);

        $this->db->query($query);
        return $this->db->result('cnt');
    }

    /**
     * Проверка, использовался ли займ клиентом указанное количество дней и более
     *
     * @param int $userId
     * @param int $days
     * @return bool
     */
    public function isLoanUsedForDays(int $userId, int $days): bool
    {
        $query = $this->db->placehold('SELECT COUNT(id) cnt 
                               FROM __contracts 
                               WHERE user_id = ? 
                               AND issuance_date BETWEEN ? AND ? 
                               AND DATEDIFF(NOW(), issuance_date) >= ?',
            $userId, self::START_DATE, self::END_DATE, $days);

        $this->db->query($query);
        $loanUsedCount = $this->db->result('cnt');

        return $loanUsedCount > 0;
    }

    /**
     * Проверка, использовался ли закрытый займ клиентом менее 16 дней в период акции
     *
     * @param int $userId
     * @return bool
     */
    public function isClosedLoanUsedInPromotion(int $userId): bool
    {
        $maxDays = 16;

        $query = $this->db->placehold('SELECT COUNT(id) cnt 
                               FROM __contracts 
                               WHERE user_id = ? 
                               AND close_date IS NOT NULL
                               AND DATEDIFF(close_date, issuance_date) < ?
                               AND close_date BETWEEN ? AND ?',
            $userId, $maxDays, self::START_DATE, self::END_DATE);

        $this->db->query($query);
        $closedLoanUsedCount = $this->db->result('cnt');

        return $closedLoanUsedCount > 0;
    }

    /**
     * Проверка, есть ли открытые договоры у клиента в текущем периоде акции
     *
     * @param int $userId
     * @return bool
     */
    public function hasOpenContractsInPromoPeriod(int $userId): bool
    {
        $query = $this->db->placehold('SELECT COUNT(id) cnt 
                                       FROM __contracts 
                                       WHERE user_id = ? 
                                       AND issuance_date BETWEEN ? AND ? 
                                       AND close_date IS NULL',
            $userId, self::START_DATE, self::END_DATE);

        $this->db->query($query);
        $openContractsCount = $this->db->result('cnt');

        return $openContractsCount > 0;
    }

    /**
     * Проверка, есть ли только один закрытый договор у клиента
     *
     * @param int $userId
     * @return bool
     */
    public function hasOnlyOneClosedContract(int $userId): bool
    {
        $query = $this->db->placehold('SELECT COUNT(id) as contracts_count 
                                   FROM __contracts 
                                   WHERE user_id = ? 
                                   AND close_date IS NOT NULL',
            $userId);

        $this->db->query($query);
        $contractsCount = $this->db->result('contracts_count');

        return $contractsCount === 1;
    }

    /**
     * Проверка, открывал ли клиент договоры в период акции
     *
     * @param int $userId
     * @return bool
     */
    public function hasOpenedContractsInPromoPeriod(int $userId): bool
    {
        $query = $this->db->placehold('SELECT COUNT(id) cnt 
                                   FROM __contracts 
                                   WHERE user_id = ? 
                                   AND issuance_date BETWEEN ? AND ? 
                                   AND close_date IS NULL',
            $userId, self::START_DATE, self::END_DATE);

        $this->db->query($query);
        $openContractsCount = $this->db->result('cnt');

        return $openContractsCount > 0;
    }

    /**
     * Проверка, есть ли закрытые договоры у клиента в текущем периоде акции
     *
     * @param int $userId
     * @return bool
     */
    public function hasClosedContractsInPromoPeriod(int $userId): bool
    {
        $query = $this->db->placehold('SELECT COUNT(id) cnt 
                                       FROM __contracts 
                                       WHERE user_id = ? 
                                       AND close_date BETWEEN ? AND ?',
            $userId, self::START_DATE, self::END_DATE);

        $this->db->query($query);
        $closedContractsCount = $this->db->result('cnt');

        return $closedContractsCount > 0;
    }

    /**
     * Проверка, есть ли открытые договоры у клиента вне текущего периода акции
     *
     * @param int $userId
     * @return bool
     */
    public function hasOpenContractsOutsidePromoPeriod(int $userId): bool
    {
        $query = $this->db->placehold('SELECT COUNT(id) cnt 
                               FROM __contracts 
                               WHERE user_id = ? 
                               AND close_date IS NULL 
                               AND issuance_date < ?',
            $userId, self::START_DATE);

        $this->db->query($query);
        $openContractsCount = $this->db->result('cnt');

        return $openContractsCount > 0;
    }

    /**
     * Проверка, есть ли закрытые договоры у клиента вне текущего периода акции
     *
     * @param int $userId
     * @return bool
     */
    public function hasClosedContractsOutsidePromoPeriod(int $userId): bool
    {
        $query = $this->db->placehold('SELECT COUNT(id) cnt 
                                   FROM __contracts 
                                   WHERE user_id = ? 
                                   AND close_date IS NOT NULL 
                                   AND (close_date < ? OR close_date > ?)',
            $userId, self::START_DATE, self::END_DATE);

        $this->db->query($query);
        $closedContractsCount = $this->db->result('cnt');

        return $closedContractsCount > 0;
    }

    /**
     * Проверка, является ли контракт первым для пользователя
     *
     * @param int $userId
     * @return bool
     */
    public function isFirstContractForUser(int $userId): bool
    {
        $query = $this->db->placehold('SELECT COUNT(id) as contracts_count
                           FROM __contracts 
                           WHERE user_id = ?',
            $userId);

        $this->db->query($query);
        $contractsCount = $this->db->result('contracts_count');

        return $contractsCount <= 1;
    }

    /**
     * Проверка, был ли открыт первый договор в текущем периоде акции
     *
     * @param int $userId
     * @return bool
     */
    public function isFirstContractInPromoPeriod(int $userId): bool
    {
        $query = $this->db->placehold('SELECT MIN(issuance_date) as first_contract_date 
                                   FROM __contracts 
                                   WHERE user_id = ? AND issuance_date >= ?',
            $userId, self::START_DATE);

        $this->db->query($query);
        $firstContractDate = $this->db->result('first_contract_date');

        return strtotime($firstContractDate) >= strtotime(self::START_DATE);
    }

}
