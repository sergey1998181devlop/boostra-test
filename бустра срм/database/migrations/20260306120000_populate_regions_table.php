<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PopulateRegionsTable extends AbstractMigration
{
    /**
     * Данные регионов: [код, название, часовой пояс]
     * Коды — по списку МВД (автомобильные номера)
     */
    private function getRegions(): array
    {
        return [
            ['77', 'г. Москва', 'Europe/Moscow'],
            ['50', 'Московская область', 'Europe/Moscow'],
            ['78', 'г. Санкт-Петербург', 'Europe/Moscow'],
            ['47', 'Ленинградская область', 'Europe/Moscow'],
            ['01', 'Республика Адыгея', 'Europe/Moscow'],
            ['04', 'Республика Алтай', 'Asia/Krasnoyarsk'],
            ['22', 'Алтайский край', 'Asia/Krasnoyarsk'],
            ['28', 'Амурская область', 'Asia/Yakutsk'],
            ['29', 'Архангельская область', 'Europe/Moscow'],
            ['30', 'Астраханская область', 'Europe/Samara'],
            ['02', 'Республика Башкортостан', 'Asia/Yekaterinburg'],
            ['31', 'Белгородская область', 'Europe/Moscow'],
            ['32', 'Брянская область', 'Europe/Moscow'],
            ['03', 'Республика Бурятия', 'Asia/Irkutsk'],
            ['33', 'Владимирская область', 'Europe/Moscow'],
            ['34', 'Волгоградская область', 'Europe/Moscow'],
            ['35', 'Вологодская область', 'Europe/Moscow'],
            ['36', 'Воронежская область', 'Europe/Moscow'],
            ['05', 'Республика Дагестан', 'Europe/Moscow'],
            ['80', 'Донецкая народная республика', 'Europe/Moscow'],
            ['79', 'Еврейская автономная область', 'Asia/Vladivostok'],
            ['75', 'Забайкальский край', 'Asia/Yakutsk'],
            ['85', 'Запорожская область', 'Europe/Moscow'],
            ['37', 'Ивановская область', 'Europe/Moscow'],
            ['06', 'Республика Ингушетия', 'Europe/Moscow'],
            ['38', 'Иркутская область', 'Asia/Irkutsk'],
            ['07', 'Кабардино-Балкарская Республика', 'Europe/Moscow'],
            ['39', 'Калининградская область', 'Europe/Kaliningrad'],
            ['08', 'Республика Калмыкия', 'Europe/Moscow'],
            ['40', 'Калужская область', 'Europe/Moscow'],
            ['41', 'Камчатский край', 'Asia/Kamchatka'],
            ['10', 'Республика Карелия', 'Europe/Moscow'],
            ['09', 'Карачаево-Черкесская Республика', 'Europe/Moscow'],
            ['42', 'Кемеровская область — Кузбасс', 'Asia/Krasnoyarsk'],
            ['43', 'Кировская область', 'Europe/Moscow'],
            ['11', 'Республика Коми', 'Europe/Moscow'],
            ['44', 'Костромская область', 'Europe/Moscow'],
            ['23', 'Краснодарский край', 'Europe/Moscow'],
            ['24', 'Красноярский край', 'Asia/Krasnoyarsk'],
            ['82', 'Республика Крым', 'Europe/Moscow'],
            ['92', 'г. Севастополь', 'Europe/Moscow'],
            ['45', 'Курганская область', 'Asia/Yekaterinburg'],
            ['46', 'Курская область', 'Europe/Moscow'],
            ['48', 'Липецкая область', 'Europe/Moscow'],
            ['81', 'Луганская народная республика', 'Europe/Moscow'],
            ['49', 'Магаданская область', 'Asia/Magadan'],
            ['51', 'Мурманская область', 'Europe/Moscow'],
            ['83', 'Ненецкий автономный округ', 'Europe/Moscow'],
            ['52', 'Нижегородская область', 'Europe/Moscow'],
            ['53', 'Новгородская область', 'Europe/Moscow'],
            ['54', 'Новосибирская область', 'Asia/Krasnoyarsk'],
            ['12', 'Республика Марий Эл', 'Europe/Moscow'],
            ['13', 'Республика Мордовия', 'Europe/Moscow'],
            ['55', 'Омская область', 'Asia/Omsk'],
            ['56', 'Оренбургская область', 'Asia/Yekaterinburg'],
            ['57', 'Орловская область', 'Europe/Moscow'],
            ['58', 'Пензенская область', 'Europe/Moscow'],
            ['59', 'Пермский край', 'Asia/Yekaterinburg'],
            ['25', 'Приморский край', 'Asia/Vladivostok'],
            ['60', 'Псковская область', 'Europe/Moscow'],
            ['61', 'Ростовская область', 'Europe/Moscow'],
            ['62', 'Рязанская область', 'Europe/Moscow'],
            ['63', 'Самарская область', 'Europe/Samara'],
            ['64', 'Саратовская область', 'Europe/Samara'],
            ['14', 'Республика Саха (Якутия)', 'Asia/Yakutsk'],
            ['65', 'Сахалинская область', 'Asia/Magadan'],
            ['66', 'Свердловская область', 'Asia/Yekaterinburg'],
            ['15', 'Республика Северная Осетия — Алания', 'Europe/Moscow'],
            ['67', 'Смоленская область', 'Europe/Moscow'],
            ['26', 'Ставропольский край', 'Europe/Moscow'],
            ['68', 'Тамбовская область', 'Europe/Moscow'],
            ['16', 'Республика Татарстан', 'Europe/Moscow'],
            ['69', 'Тверская область', 'Europe/Moscow'],
            ['70', 'Томская область', 'Asia/Krasnoyarsk'],
            ['71', 'Тульская область', 'Europe/Moscow'],
            ['17', 'Республика Тыва', 'Asia/Krasnoyarsk'],
            ['72', 'Тюменская область', 'Asia/Yekaterinburg'],
            ['18', 'Удмуртская Республика', 'Europe/Samara'],
            ['73', 'Ульяновская область', 'Europe/Samara'],
            ['27', 'Хабаровский край', 'Asia/Vladivostok'],
            ['19', 'Республика Хакасия', 'Asia/Krasnoyarsk'],
            ['86', 'Ханты-Мансийский автономный округ — Югра', 'Asia/Yekaterinburg'],
            ['84', 'Херсонская область', 'Europe/Moscow'],
            ['74', 'Челябинская область', 'Asia/Yekaterinburg'],
            ['95', 'Чеченская Республика', 'Europe/Moscow'],
            ['21', 'Чувашская Республика — Чувашия', 'Europe/Moscow'],
            ['87', 'Чукотский автономный округ', 'Asia/Kamchatka'],
            ['89', 'Ямало-Ненецкий автономный округ', 'Asia/Yekaterinburg'],
            ['76', 'Ярославская область', 'Europe/Moscow'],
        ];
    }

    public function up(): void
    {
        if (!$this->hasTable('regions')) {
            return;
        }

        $connection = $this->getAdapter()->getConnection();
        $rows = $this->getRegions();

        foreach ($rows as [$code, $name, $timezone]) {
            $nameQuoted = $connection->quote($name);
            $timezoneQuoted = $connection->quote($timezone);
            $codeQuoted = $connection->quote($code);

            $this->execute(
                "INSERT INTO regions (code, name, timezone, district) VALUES ($codeQuoted, $nameQuoted, $timezoneQuoted, NULL)
                 ON DUPLICATE KEY UPDATE name = VALUES(name), timezone = VALUES(timezone)"
            );
        }
    }

    public function down(): void
    {
        if (!$this->hasTable('regions')) {
            return;
        }

        $connection = $this->getAdapter()->getConnection();
        $codes = array_column($this->getRegions(), 0);
        $codesQuoted = array_map(fn(string $code) => $connection->quote($code), $codes);
        $this->execute('DELETE FROM regions WHERE code IN (' . implode(',', $codesQuoted) . ')');
    }
}
