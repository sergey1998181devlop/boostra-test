<?php

use boostra\services\UsersAddressService;

/**
 * Бизнес логика короткого флоу регистрации с использованием кабутека
 *
 * @see https://tracker.yandex.ru/BOOSTRARU-3554 BOOSTRARU-3554
 */
class ShortFlow extends Simpla
{
    public const USERDATA_SHORT_FLOW = 'is_short_flow';
    public const USERDATA_STAGE = 'short_flow_stage';
    public const USERDATA_DATA_CONFIRM =  'short_flow_data_confirm';

    /**
     * Список utm_source которые проходят короткое флоу.
     */
    public const ALLOWED_UTM_SOURCES = [
        //  TODO: Заменить на реальные источники, согласовать список с шефом
        'Test',
        'Boostra'
    ];

    /**
     * Проходит ли источник по короткому флоу.
     * @param $utm_source
     * @return bool
     */
    public function isShortFlowSource($utm_source)
    {
        return in_array($utm_source, self::ALLOWED_UTM_SOURCES);
    }

    /**
     * Признак прохождения (сейчас или в прошлом) клиента по короткому флоу регистрации
     * @param number|string $user_id
     * @return bool
     */
    public function isShortFlowUser($user_id)
    {
        $is_short_flow = $this->user_data->read($user_id, self::USERDATA_SHORT_FLOW);
        return !empty($is_short_flow);
    }

    /**
     * Подтвердил ли пользователь или верификатор корректность автоматически распознанных данных.
     * @param number|string $user_id
     * @return bool
     */
    public function isPersonalDataConfirm($user_id)
    {
        $is_confirmed = $this->user_data->read($user_id, self::USERDATA_DATA_CONFIRM);
        // Данные считаются подтвержденными если у клиента нет этой записи ИЛИ если она != 0
        return !isset($is_confirmed) || $is_confirmed != 0;
    }

    /**
     * Подтвердил ли пользователь или верификатор корректность автоматически распознанных данных.
     * @param number|string $user_id
     * @param bool $is_confirm true если пользователь подтвердил данные, false если нашёл ошибку
     * @return void
     */
    public function setPersonalDataConfirm($user_id, $is_confirm)
    {
        // При подтверждении данных из CRM в значение ставим 2 вместо 1 чтобы различать были ли данные подтверждены изначально
        $this->user_data->set($user_id, self::USERDATA_DATA_CONFIRM, $is_confirm ? 2 : 0);
    }

    /**
     * Включен ли короткий флоу (глобально)
     * @return bool
     */
    public function isShortFlowEnabled()
    {
        $is_enabled = $this->settings->short_flow_enabled;
        return !empty($is_enabled);
    }

    /**
     * Текущая стадия прохождения короткого флоу.
     * @param number|string $user_id
     * @return string|null
     */
    public function getRegisterStage($user_id)
    {
        return $this->user_data->read($user_id, self::USERDATA_STAGE);
    }
}