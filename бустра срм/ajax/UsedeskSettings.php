<?php

use boostra\services\Core;

require_once './AjaxController.php';

class UsedeskSettings extends AjaxController
{
    protected $allowed_extensions = ['png', 'jpg', 'jpeg'];
    protected $max_file_size = 2097152; // 2MB

    public function actions(): array
    {
        return [
            'upload' => [
                'action' => 'upload',
            ],
            'save_settings' => [
                'custom_icon_enabled' => ['0', '1'],
            ],
        ];
    }

    /**
     * Переопределяем валидацию файла - не сохраняем на диск
     */
    protected function validateFile($filename_field): bool
    {
        return true;
    }

    /**
     * Загружает аватар оператора Usedesk в виде base64
     *
     * @return array
     * @throws Exception
     */
    public function actionUpload(): array
    {
        if (!in_array('settings', $this->manager->permissions)) {
            throw new Exception('Недостаточно прав для выполнения этой операции');
        }

        $file = Core::instance()->request->files('avatar');
        if (!$file || empty($file['tmp_name'])) {
            throw new Exception('Файл не был загружен');
        }

        // Проверяем размер файла
        if ($file['size'] > $this->max_file_size) {
            throw new Exception('Превышен максимальный размер файла: ' . round($this->max_file_size / 1024 / 1024, 2) . ' MB');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowed_extensions)) {
            throw new Exception('Неверное расширение файла. Допустимые: ' . implode(', ', $this->allowed_extensions));
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $fileContent = file_get_contents($file['tmp_name']);
        $base64 = base64_encode($fileContent);
        $dataUri = "data:$mimeType;base64,$base64";

        $settings = Core::instance()->settings;
        $usedesk = $this->getUsedeskSettingsArray($settings);
        $usedesk['operator_avatar'] = $dataUri;
        $usedesk['custom_icon_enabled'] = !isset($usedesk['custom_icon_enabled']) || $usedesk['custom_icon_enabled'];
        $settings->usedesk_settings = json_encode($usedesk, JSON_UNESCAPED_UNICODE);

        return [
            'success' => true,
            'url' => $dataUri,
            'path' => 'base64 (' . round(strlen($base64) / 1024, 2) . ' KB)'
        ];
    }

    /**
     * Сохраняет переключатель «Кастомная иконка» (вкл/выкл)
     *
     * @return array
     * @throws Exception
     */
    public function actionSaveSettings(): array
    {
        if (!in_array('settings', $this->manager->permissions)) {
            throw new Exception('Недостаточно прав для выполнения этой операции');
        }

        $enabled = isset($this->data['custom_icon_enabled']) && $this->data['custom_icon_enabled'] === '1';

        $settings = Core::instance()->settings;
        $usedesk = $this->getUsedeskSettingsArray($settings);
        $usedesk['custom_icon_enabled'] = $enabled;
        $settings->usedesk_settings = json_encode($usedesk, JSON_UNESCAPED_UNICODE);

        return ['success' => true];
    }


    /**
     * @param $settings
     * @return array
     */
    private function getUsedeskSettingsArray($settings): array
    {
        $raw = $settings->usedesk_settings ?? '';
        $usedesk = is_string($raw) ? (json_decode($raw, true) ?: []) : [];
        return is_array($usedesk) ? $usedesk : [];
    }
}

new UsedeskSettings;
