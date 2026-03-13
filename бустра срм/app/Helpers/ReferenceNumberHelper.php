<?php

if (!function_exists('formatReferenceNumber')) {
    function formatReferenceNumber(string $number): string
    {
        // Универсальная транслитерация кириллицы в латиницу с учетом исключений
        $number = transliterateCyrillicToLatin($number);

        // Нормализация пробелов и замена на дефисы
        $number = str_replace(' ', '-', trim(preg_replace('!\s+!', ' ', $number)));

        // Удаление лишних дефисов (оставляем только один)
        if (substr_count($number, '-') > 1) {
            $pos = strrpos($number, '-');
            if ($pos !== false) {
                $number = substr_replace($number, '', $pos, strlen('-'));
            }
        }

        return $number;
    }
}

if (!function_exists('transliterateCyrillicToLatin')) {
    function transliterateCyrillicToLatin(string $text): string
    {
        // Список исключений - буквенные префиксы, которые НЕ нужно транслитерировать
        $letterExceptions = [
            'Б', // Старые номера договоров с буквой Б
        ];

        // Извлекаем буквенную часть из начала номера
        if (preg_match('/^([А-Яа-яA-Za-z]+)/', $text, $matches)) {
            $letterPart = $matches[1];

            // Проверяем, является ли буквенная часть исключением
            if (in_array($letterPart, $letterExceptions)) {
                // Если буквенная часть - исключение, возвращаем номер без изменений
                return $text;
            }
        }

        // Карта транслитерации кириллицы в латиницу
        $transliterationMap = [
            // Заглавные буквы
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
            'Е' => 'E', 'Ё' => 'E', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I',
            'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
            'У' => 'U', 'Ф' => 'F', 'Х' => 'KH', 'Ц' => 'TS', 'Ч' => 'CH',
            'Ш' => 'SH', 'Щ' => 'SCH', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '',
            'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA',

            // Строчные буквы
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        ];

        // Применяем полную транслитерацию
        return strtr($text, $transliterationMap);
    }
}
