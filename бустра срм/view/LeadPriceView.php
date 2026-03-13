<?PHP

require_once('TableView.php');

/**
 * Таблица с настройкой цен по вебам
 *
 * https://tracker.yandex.ru/BOOSTRARU-2532
 */
class LeadPriceView extends TableView
{
    /**
     * @var string Заголовок таблицы
     */
    public const PAGE_TITLE = 'Цены по вебам';

    /**
     * @var string Название класса для работы с таблицей бд в Simpla
     */
    protected const TABLE_CLASS = 'leadPrice'; // api/LeadPrice.php

    /**
     * @var array Колонки в таблице
     */
    public const COLUMNS = [
        'id' => [
            'name' => 'Id',
            'type' => 'integer',
            'required' => true,
            'editable' => false,
        ],
        'utm_source' => [
            'name' => 'Лидген',
            'type' => 'string',
            'required' => true,
        ],
        'webmaster_id' => [
            'name' => 'Вебмастер',
            'type' => 'string',
            'required' => false,
        ],
        'price' => [
            'name' => 'Цена',
            'type' => 'float',
            'required' => true,
        ],
    ];

    /**
     * @var string Колонка, по которой выполняются update и delete
     */
    public const ID_COLUMN = 'id';
}