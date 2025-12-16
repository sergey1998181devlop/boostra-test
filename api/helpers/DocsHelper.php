<?php

namespace api\helpers;

class DocsHelper
{
    /**
     * Фильтрует массив, исключая элементы по регулярному выражению
     * @param $data - массив для фильтрации
     * @param string $field - поле объекта/массива для проверки (по умолчанию 'number')
     * @param bool $isAssoc - если true, фильтрует по ключам массива, а не по значениям
     * @param string $pattern - регулярное выражение (по умолчанию '/^[БВB]/ui' для удаления документов МКК Бустра)
     * @return array - отфильтрованный массив
     */
    public static function filterByPattern($data, bool $isAssoc = false, string $field = 'number', string $pattern = '/^[БВB]/ui'): array
    {
        if (is_string($data)) {
            $data = json_decode($data);

            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                return [];
            }
        }

        // Приводим данные к массиву объектов
        if ($data instanceof \stdClass) {
            $data = [$data]; // Одиночный объект -> массив с одним элементом
        } elseif (!is_array($data)) {
            return []; // Не массив и не объект -> отсеиваем
        }

        if ($isAssoc) {
            foreach ($data as $key => $value) {
                if (preg_match($pattern, $key)) {
                    unset($data[$key]);
                }
            }
            return $data;
        }

        return array_values(array_filter(
            $data,
            function ($item) use ($field, $pattern) {
                if (is_object($item)) {
                    return !preg_match($pattern, $item->$field ?? '');
                } elseif (is_array($item)) {
                    return !preg_match($pattern, $item[$field] ?? '');
                }
                return !preg_match($pattern, (string)$item);
            }
        ));
    }
}