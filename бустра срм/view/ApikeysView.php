<?php

class ApikeysView extends View
{
    const SITE_SPECIFIC_KEYS = [
        'dadata',
        'smsc',
        'mango',
        //'scorista', 'scorista2', - Настраивается на общей вкладке, там уже сделано разделение
        'isphere',
        'star_oracle', 'vitamed', 'multipolis',
        'smtp'
    ];

    public function fetch()
    {
        if (!in_array('settings', $this->manager->permissions))
        	return $this->design->fetch('403.tpl');

        $site_id = $this->request->get('site_id', 'string') ?: null;
        $this->settings->setSiteId($site_id, false);

        if ($this->request->method('post')) {
            $new_apikeys = $this->request->post('apikeys');

            $this->settings->apikeys = $new_apikeys;
        }

        /**
         * Загружаем $apikeys из настроек даже если только что сохранили их,
         * т.к. в настройках реализована критичная для проекта логика их "сортировки".
         * Если нужно - смотри метод Settings::processSettingsAfterSetUp
         */
        $apikeys = $this->settings->apikeys;

        if ($site_id !== null) {
            $apikeys = $apikeys[$site_id] ?? [];
        }

        // .tpl отображает ключи только если они существуют в массиве,
        // поэтому инициализируем пустые ключи для нужных апиключей
        $this->initSiteSpecificKeys($site_id, $apikeys);

        $this->design->assign_array([
            'all_sites' => $this->sites->getActiveSites(),
            'site_id' => $site_id,
            'apikeys' => $apikeys
        ]);
        
        return $this->design->fetch('apikeys.tpl');
    }

    /**
     * Инициализация ключей API, чтобы их отображало на нужных вкладках даже если они пустые
     *
     * @param string|null $site_id id сайта
     * @param array $apikeys Изменяемый массив ключей
     * @return void
     */
    private function initSiteSpecificKeys($site_id, array &$apikeys): void
    {
        if ($site_id === null) {
            return;
        }

        foreach (self::SITE_SPECIFIC_KEYS as $key) {
            $apikeys[$key] = $apikeys[$key] ?? true;
        }
    }
}