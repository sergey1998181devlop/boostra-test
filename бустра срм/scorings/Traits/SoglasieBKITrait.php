<?php

namespace Traits;

use Exception;
use SoglasieBKIService;

require_once(__DIR__ . '/../Services/SoglasieBKIService.php');

trait SoglasieBKITrait
{
    /**
     * @throws Exception
     */
    public function getHashCodeSoglasieBKI($userId)
    {
        $hashCode = $this->soglasie_bki_hash_code->getByUserId($userId);

        if (empty($hashCode)) {
            $service = new SoglasieBKIService();
            $content = $service->getContentForUser($userId);

            if (empty($content)) {
                throw new Exception('Не удалось получить соглашение BKI из внешнего сервиса');
            }

            $patch = $service->saveInS3($this->generateNameForFile($userId), $content);
            $hashCode = $this->soglasie_bki_hash_code->generate($content);

            $this->soglasie_bki_hash_code->create($userId, $hashCode, $patch);
        }

        return $hashCode;
    }

    private function generateNameForFile($userId)
    {
        return sprintf('%s_%s.pdf', $userId, date('Ymd'));
    }
}