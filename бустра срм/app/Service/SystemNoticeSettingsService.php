<?php

namespace App\Service;

use Carbon\Carbon;

class SystemNoticeSettingsService
{
    private const STYLE_COLORS = [
        'info' => ['bg' => '#2196F3', 'text' => '#ffffff'],
        'warning' => ['bg' => '#FF9800', 'text' => '#ffffff'],
        'error' => ['bg' => '#F44336', 'text' => '#ffffff'],
        'success' => ['bg' => '#4CAF50', 'text' => '#ffffff']
    ];

    private const DEFAULTS = [
        'enabled' => false,
        'show_on_main_page' => false,
        'position' => 'top',
        'closeable' => false,
        'animation' => 'slide',
        'show_from' => null,
        'show_from_timestamp' => null,
        'timeout' => ['enabled' => false, 'minutes' => 1440],
        'desktop' => [
            'font_weight' => 'normal',
            'padding' => '12px 20px',
            'border_radius' => '4px'
        ],
        'mobile' => [
            'font_weight' => 'normal',
            'padding' => '10px 15px',
            'border_radius' => '4px'
        ]
    ];

    public function process(array $bannerConfig): ?string
    {
        if (empty($bannerConfig)) {
            return null;
        }

        if (empty($bannerConfig['message']) || trim($bannerConfig['message']) === '') {
            return null;
        }

        if (isset($bannerConfig['style']) && $bannerConfig['style'] !== 'custom') {
            $this->applyStyleColors($bannerConfig);
        }

        if (!empty($bannerConfig['show_from'])) {
            $dateTime = str_replace('T', ' ', $bannerConfig['show_from']);
            if (strlen($dateTime) === 16) {
                $dateTime .= ':00';
            }
            $dateTimeObj = Carbon::parse($dateTime, 'Europe/Moscow');
            $bannerConfig['show_from_timestamp'] = $dateTimeObj->getTimestamp() * 1000;
            $bannerConfig['show_from'] = $dateTime;
        } else {
            $bannerConfig['show_from'] = null;
            $bannerConfig['show_from_timestamp'] = null;
        }

        $result = self::DEFAULTS;
        
        if (isset($bannerConfig['desktop'])) {
            $result['desktop'] = array_merge($result['desktop'], $bannerConfig['desktop']);
            unset($bannerConfig['desktop']);
        }
        
        if (isset($bannerConfig['mobile'])) {
            $result['mobile'] = array_merge($result['mobile'], $bannerConfig['mobile']);
            unset($bannerConfig['mobile']);
        }
        
        if (isset($bannerConfig['timeout'])) {
            $result['timeout'] = array_merge($result['timeout'], $bannerConfig['timeout']);
            
            if (isset($result['timeout']['minutes'])) {
                $result['timeout']['minutes'] = (int)$result['timeout']['minutes'];
            } elseif (isset($result['timeout']['hours'])) {
                $result['timeout']['minutes'] = (int)((float)$result['timeout']['hours'] * 60);
                unset($result['timeout']['hours']);
            }
            
            unset($bannerConfig['timeout']);
        }
        
        $result = array_merge($result, $bannerConfig);
        
        $result['enabled'] = (bool)($result['enabled'] ?? false);
        $result['show_on_main_page'] = (bool)($result['show_on_main_page'] ?? false);
        $result['timeout']['enabled'] = (bool)($result['timeout']['enabled'] ?? false);
        $result['timeout']['minutes'] = (int)($result['timeout']['minutes'] ?? 1440);
        $result['closeable'] = (bool)($result['closeable'] ?? false);

        return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function applyStyleColors(array &$bannerConfig): void
    {
        if (!isset($bannerConfig['style']) || !isset(self::STYLE_COLORS[$bannerConfig['style']])) {
            return;
        }

        $colors = self::STYLE_COLORS[$bannerConfig['style']];
        $bannerConfig['desktop']['background_color'] = $colors['bg'];
        $bannerConfig['desktop']['text_color'] = $colors['text'];
        $bannerConfig['mobile']['background_color'] = $colors['bg'];
        $bannerConfig['mobile']['text_color'] = $colors['text'];
    }
}
