<?php

namespace Messengers;

use RuntimeException;

class Main
{
    public static function getProvider(?string $messenger = null): ?ProviderInterface
    {
        $provider = null;
        if ($messenger) {
            $messengerName = mb_strtolower($messenger);
            $providers = array_merge(
                ['messengers' => Init::class],
                Init::PROVIDERS
            );
            if (isset($providers[$messengerName])) {
                $provider = new $providers[$messengerName]();
            }
        }
        return $provider;
    }

    /**
     * Создать директорию для хранения файлов
     * @param string $dir
     * @return void
     */
    public static function createFilesDir(string $dir): bool
    {
        if (!is_dir($dir) && !mkdir($concurrentDirectory = $dir, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        return true;
    }

    public static function preparePhone(string $phone): string
    {
        return preg_replace('~(\D)~u', '', $phone);
    }

    public static function sendSms(string $text, string $phone): void
    {
        sendSms($phone, $text);
    }

}