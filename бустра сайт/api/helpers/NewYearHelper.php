<?php

namespace api\helpers;

use Simpla;

require_once('api/Simpla.php');

class NewYearHelper extends Simpla
{
    private $hasOpenContracts;
    private $hasOpenContractsOutsidePromoPeriod;
    private $hasClosedContracts;
    private $hasClosedContractsOutsidePromoPeriod;
    private $hasOpenedContractsInPromoPeriod;
    private $isFirstContract;
    private $isFirstContractForUser;
    private $onlyOneClosedContract;
    private $loanUsedForDays;
    private $closedLoanUsedForDays;
    private $promoCodeGenerated;
    private $canGenerateCode;
    private $returnedOnTime;

    /**
     * Отображение информации о пользователе и акции
     *
     * @param int $userId
     * @return void
     */
    public function displayUserInfoAndPromo(int $userId)
    {
        $promoBanners = $this->getAllPromoBannersForUser($userId);

        $this->design->assign('promo_banners', $promoBanners);
    }

    /**
     * Получить все баннеры для пользователя в зависимости от условий
     *
     * @param int $userId
     * @return array|null
     */
    public function getAllPromoBannersForUser(int $userId): ?array
    {
        $level = $this->newYearPromo->getParticipantLevel($userId);
        $this->initializeUserState($userId);

        switch ($level) {
            case 0:
                return $this->getLevelZeroBanners();
            case 1:
                return $this->getLevelOneBanners();
            case 2:
                return $this->getLevelTwoBanners();
        }

        return [];
    }

    /**
     * Инициализирует состояние пользователя
     *
     * @param int $userId
     */
    private function initializeUserState(int $userId): void
    {
        $this->hasOpenContracts = $this->newYearPromo->hasOpenContractsInPromoPeriod($userId);
        $this->hasOpenContractsOutsidePromoPeriod = $this->newYearPromo->hasOpenContractsOutsidePromoPeriod($userId);
        $this->hasClosedContracts = $this->newYearPromo->hasClosedContractsInPromoPeriod($userId);
        $this->hasClosedContractsOutsidePromoPeriod = $this->newYearPromo->hasClosedContractsOutsidePromoPeriod($userId);
        $this->hasOpenedContractsInPromoPeriod = $this->newYearPromo->hasOpenedContractsInPromoPeriod($userId);
        $this->isFirstContract = $this->newYearPromo->isFirstContractInPromoPeriod($userId);
        $this->isFirstContractForUser = $this->newYearPromo->isFirstContractForUser($userId);
        $this->onlyOneClosedContract = $this->newYearPromo->hasOnlyOneClosedContract($userId);
        $this->loanUsedForDays = $this->newYearPromo->isLoanUsedForDays($userId, 16);
        $this->closedLoanUsedForDays = $this->newYearPromo->isClosedLoanUsedInPromotion($userId);
        $this->returnedOnTime = $this->newYearPromo->hasReturnedMoneyOnTime($userId);
        $this->promoCodeGenerated = $this->newYearPromo->hasParticipantCode($userId) > 0;
        $this->canGenerateCode = $this->newYearPromo->canGenerateCode($userId);
    }

    /**
     * Инициализирует массив баннеров
     *
     * @return array
     */
    private function initializeBannersData(): array
    {
        return [];
    }

    /**
     * Получить баннеры для пользователя с уровнем 0
     *
     * @return array
     */
    private function getLevelZeroBanners(): array
    {
        $bannersData = $this->initializeBannersData();

        if (!$this->hasOpenContracts && !$this->hasClosedContracts) {
            $bannersData[] = $this->newYearPromo->getBannerData(1);
        } elseif ($this->hasOpenContracts && $this->isFirstContractForUser && !$this->loanUsedForDays && !$this->hasClosedContracts || $this->hasOpenContractsOutsidePromoPeriod && !$this->hasClosedContracts) {
            $bannersData[] = $this->newYearPromo->getBannerData(2);
        } elseif (!$this->hasOpenContracts && $this->hasClosedContracts && $this->onlyOneClosedContract || !$this->hasOpenedContractsInPromoPeriod) {
            $bannersData[] = $this->newYearPromo->getBannerData(3);
        } elseif ($this->hasOpenContracts && !$this->onlyOneClosedContract && !$this->loanUsedForDays) {
            $bannersData[] = $this->newYearPromo->getBannerData(4);
        } elseif ($this->closedLoanUsedForDays && $this->hasOpenContracts && !$this->isFirstContract && $this->loanUsedForDays && $this->hasClosedContractsOutsidePromoPeriod || $this->hasOpenContracts && $this->isFirstContract && $this->loanUsedForDays) {
            $bannersData[] = $this->newYearPromo->getBannerData(5);
        }

        return $bannersData;
    }

    /**
     * Получить баннеры для пользователя с уровнем 1
     *
     * @return array
     */
    private function getLevelOneBanners(): array
    {
        $bannersData = $this->initializeBannersData();

        if (!$this->promoCodeGenerated && !$this->hasOpenContracts || !$this->promoCodeGenerated && !$this->hasOpenContracts && $this->closedLoanUsedForDays) {
            $bannersData[] = $this->newYearPromo->getBannerData(6);
        } elseif ($this->promoCodeGenerated && !$this->hasOpenContracts) {
            $bannersData[] = $this->newYearPromo->getBannerData(7);
        } elseif ($this->hasOpenContracts && !$this->loanUsedForDays && $this->promoCodeGenerated) {
            $bannersData[] = $this->newYearPromo->getBannerData(8);
        } elseif ($this->hasOpenContracts && $this->loanUsedForDays && $this->promoCodeGenerated) {
            $bannersData[] = $this->newYearPromo->getBannerData(9);
        }

        return $bannersData;
    }

    /**
     * Получить баннеры для пользователя с уровнем 2
     *
     * @return array
     */
    private function getLevelTwoBanners(): array
    {
        $bannersData = $this->initializeBannersData();

        if (!$this->hasOpenContracts && $this->canGenerateCode ||!$this->hasOpenContracts && $this->canGenerateCode && $this->closedLoanUsedForDays) {
            $bannersData[] = $this->newYearPromo->getBannerData(10);
        } else {
            $bannersData[] = $this->newYearPromo->getBannerData(11);
        }

        return $bannersData;
    }

    /**
     * Генерация и отображение кода участника акции
     *
     * @param int $userId
     * @return string
     */
    public function generateAndDisplayPromoCode(int $userId): string
    {
        if ($this->newYearPromo->canGenerateCode($userId)) {
            $promoCode = $this->generatePromoCode($userId);

            if (!empty($promoCode)) {
                $this->newYearPromo->saveParticipantCode($userId, $promoCode);
                $this->newYearPromo->incrementGeneratedCodesCount($userId);

                return $promoCode;
            }
        }

        return 'Невозможно сгенерировать код.';
    }

    /**
     * Генерация кода участника акции
     *
     * @param int $userId
     * @return string
     */
    public function generatePromoCode(int $userId): string
    {
        $level = $this->newYearPromo->getParticipantLevel($userId);

        do {
            $code = $level . $this->generateRandomString(5);
        } while ($this->newYearPromo->isCodeExists($code));

        return $code;
    }

    /**
     * Генерация случайной строки
     *
     * @param int $length
     * @return string
     */
    protected function generateRandomString(int $length): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($characters), 0, $length);
    }

}