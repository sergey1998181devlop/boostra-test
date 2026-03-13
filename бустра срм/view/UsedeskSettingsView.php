<?php

require_once('View.php');

/**
 * Страница настроек виджета Usedesk
 */
class UsedeskSettingsView extends View
{
    private const DEFAULT_AVATAR = 'https://secure.usedesk.ru/images/icons/chat-svg/operator.svg';

    private const DEFAULT_SETTINGS = [
        'operator_avatar' => '',
        'custom_icon_enabled' => true,
    ];

    private function getUsedeskSettings(): array
    {
        $raw = (string)($this->settings->usedesk_settings ?? '');

        if (empty($raw)) {
            return self::DEFAULT_SETTINGS;
        }

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            return self::DEFAULT_SETTINGS;
        }

        $result = array_merge(self::DEFAULT_SETTINGS, $decoded);
        $result['custom_icon_enabled'] = (bool)$result['custom_icon_enabled'];
        $result['operator_avatar'] = (string)($result['operator_avatar'] ?? '');

        return $result;
    }

    private function saveUsedeskSettings(array $settings): void
    {
        $this->settings->usedesk_settings = json_encode($settings, JSON_UNESCAPED_UNICODE);
    }

    public function fetch()
    {
        if (!in_array('settings', $this->manager->permissions)) {
            return $this->design->fetch('403.tpl');
        }

        $usedesk = $this->getUsedeskSettings();

        if ($this->request->method('post') && $this->request->post('action') === 'reset') {
            $usedesk['operator_avatar'] = '';
            $this->saveUsedeskSettings($usedesk);
            header('Location: ' . $this->request->url(['reset' => 'success']));
            exit;
        }

        if ($this->request->get('reset') === 'success') {
            $this->design->assign('message', 'Аватар сброшен к значению по умолчанию');
        }

        $currentAvatar = $usedesk['operator_avatar'];
        $this->design->assign_array([
            'current_avatar' => !empty($currentAvatar) ? $currentAvatar : self::DEFAULT_AVATAR,
            'has_custom_avatar' => !empty($currentAvatar),
            'custom_icon_enabled' => $usedesk['custom_icon_enabled'],
        ]);

        return $this->design->fetch('usedesk_settings.tpl');
    }
}
