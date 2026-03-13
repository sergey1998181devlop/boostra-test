<?php

namespace Messengers;

use PDO;

class Config
{
    /**
     * DSN строка соединения с базой данных
     */
    public const DB_DSN = 'mysql:host=rc1c-t7m21ff6nc878a8f.mdb.yandexcloud.net;dbname=pravza_simpla;charset=utf8mb4';

    /**
     * Пользовать базы данных
     */
    public const DB_USER = 'mv_pravza_simpla';

    /**
     * Пароль пользователя базы данных
     */
    public const DB_PASSWORD = 'J!0(!*MnqhYreReEr_8*';

    /**
     * Опции соединения с базой данных
     */
    public const DB_OPTIONS = [
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::MYSQL_ATTR_SSL_CA => ROOT_DIR . '/config/root.crt',
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    /**
     * Наименование таблицы с информацией о пользователях в базе
     */
    public const DB_USERS_TABLE_NAME = 's_users';

    /**
     * Наименование таблицы с кодами авторизации
     */
    public const DB_AUT_CODES_TABLE_NAME = 's_authcodes';

    /**
     * Наименование колонки с номером мобильного телефона клиента
     */
    public const DB_USER_PHONE_FIELD_NAME = 'phone_mobile';

    /**
     * Наименование таблицы с сообщениями в базе
     */
    public const DB_MESSAGES_TABLE_NAME = 'messenger_chats';

    /**
     * Наименование таблицы для привязки и верификации мессенджера пользователя
     */
    public const DB_VERIFY_USERS_TABLE_NAME = 'verify_user_in_messengers';

    /**
     * Таблица с информацией о менеджерах
     */
    public const DB_MANGERS_TABLE = 's_managers';

    /**
     * Время в секундах для отслеживания менеджеров онлайн
     */
    public const MANAGER_LAST_TIME = 20;

    /**
     * Главный URL
     */
    public const MAIN_URL = 'https://www.boostra.ru';

    /**
     * Базовый URL для мессенджеров
     */
    public const BASE_URL = self::MAIN_URL . '/message';

    /**
     * Корневая директория для хранения файлов и медиа данных
     */
    public const FILES_DIR = ROOT_DIR . '/files/messengers';

    /**
     * Класс обертки сообщений
     */
    public const MESSAGE_WRAPPER_CLASS = 'message';

    /**
     * Класс ссылок в сообщениях
     */
    public const MESSAGE_URL_CLASS = 'message_link';

    /**
     * Класс текстового блока в сообщения
     */
    public const MESSAGE_TEXT_BLOCK_CLASS = 'message_text';

    /**
     * Класс для изображений в сообщениях
     */
    public const MESSAGE_IMAGE_CLASS = 'message_img';

    /**
     * Класс для видео в сообщениях
     */
    public const MESSAGE_VIDEO_CLASS = 'message_video';

    /**
     * Класс для аудио в сообщениях
     */
    public const MESSAGE_AUDIO_CLASS = 'audio_message';

    /**
     * Имя отправителя по умолчанию
     */
    public const DEFAULT_MANAGER_NAME = 'Бот МКО ООО "Бустра"';

    /**
     * Аватар по умолчанию
     */
    public const DEFAULT_MESSENGER_AVATAR = ROOT_DIR . '/design/boostra/img/favicon.ico';

    /**
     * Время для повторной верификации мессенджера в часах
     */
    public const VERIFY_TIME = 1;

}