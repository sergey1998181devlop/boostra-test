<?php

namespace api\helpers;

class BrowserDataParser
{
    public static function getIpAddress(): ?string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    public static function getOperatingSystem(): ?string
    {
        $osArray = [
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.1/i' => 'Windows XP',
            '/macintosh|mac os x/i' => 'Mac OS',
            '/linux/i' => 'Linux',
            '/iphone/i' => 'iPhone',
            '/android/i' => 'Android',
        ];

        foreach ($osArray as $regex => $os) {
            if (preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
                return $os;
            }
        }

        return null;
    }

    public static function getOperatingSystemVersion(): ?string
    {
        if (preg_match('/windows nt ([0-9.]+)/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
            return 'Windows ' . $matches[1];
        }
        if (preg_match('/android ([0-9.]+)/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
            return 'Android ' . $matches[1];
        }
        if (preg_match('/os ([0-9_]+)/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
            return str_replace('_', '.', $matches[1]);
        }

        return null;
    }

    public static function getBrowser(): ?string
    {
        $browserArray = [
            '/chrome/i' => 'Chrome',
            '/firefox/i' => 'Firefox',
            '/safari/i' => 'Safari',
            '/msie|trident/i' => 'Internet Explorer',
            '/edge/i' => 'Edge',
            '/opera|opr/i' => 'Opera',
        ];

        foreach ($browserArray as $regex => $browser) {
            if (preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
                return $browser;
            }
        }

        return null;
    }

    public static function getBrowserVersion(): ?string
    {
        $versionRegexArray = [
            'Chrome' => '/chrome\/([0-9.]+)/i',
            'Firefox' => '/firefox\/([0-9.]+)/i',
            'Safari' => '/version\/([0-9.]+)/i',
            'Edge' => '/edge\/([0-9.]+)/i',
            'Opera' => '/opr\/([0-9.]+)/i',
            'Internet Explorer' => '/(msie|rv:)([0-9.]+)/i',
        ];

        $browser = self::getBrowser();

        if (isset($versionRegexArray[$browser]) && preg_match($versionRegexArray[$browser], $_SERVER['HTTP_USER_AGENT'], $matches)) {
            return $matches[1] ?? $matches[2];
        }

        return null;
    }

    public static function getRefererUrl(): ?string
    {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }

    public static function getDeviceType(): string
    {
        if (preg_match('/mobile/i', $_SERVER['HTTP_USER_AGENT'])) {
            return 'Mobile';
        }
        if (preg_match('/tablet/i', $_SERVER['HTTP_USER_AGENT'])) {
            return 'Tablet';
        }
        return 'Desktop';
    }
}