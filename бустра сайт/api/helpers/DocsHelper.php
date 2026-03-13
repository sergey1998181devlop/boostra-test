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

    /**
     * Добавить дату и порядковые номера продаж к документам указанных типов
     * Делает из названия "Полис лицензионный Вита-мед" --> "Полис лицензионный Вита-мед_05032026.3"
     *
     * @param array $documents Массив документов
     * @param array $targetTypes Массив типов документов для обработки
     * @param int $timeThreshold Максимальный интервал между документами одной продажи в секундах
     * @return array
     */
    public static function addSaleStamp(array $documents, array $targetTypes, int $timeThreshold = 10): array
    {
        if (empty($documents) || empty($targetTypes)) {
            return $documents;
        }

        // Сортируем по времени создания
        usort($documents, function($a, $b) {
            return strtotime($a->created) - strtotime($b->created);
        });

        $saleCounter = 0; // Счетчик продаж
        $currentSale = []; // Текущая собираемая продажа (документы одного типа допа)
        $processedDocs = [];
        $lastTime = null;

        foreach ($documents as $doc) {
            $isTargetType = in_array($doc->type, $targetTypes);
            $currentTime = strtotime($doc->created);

            // Если документ не целевого типа
            if (!$isTargetType) {
                // Завершаем текущую продажу, если есть
                if (!empty($currentSale)) {
                    $saleCounter++;
                    $saleDate = date('dmY', strtotime($currentSale[0]->created));

                    foreach ($currentSale as $saleDoc) {
                        $saleDoc->name = $saleDoc->name . '_' . $saleDate . '.' . $saleCounter;
                        $processedDocs[] = $saleDoc;
                    }
                    $currentSale = [];
                    $lastTime = null;
                }

                $processedDocs[] = $doc;
                continue;
            }

            // Для целевых документов
            // Новая продажа начинается если:
            // 1. Нет текущей продажи (первый документ)
            // 2. Прошло больше timeThreshold секунд с последнего документа
            if (empty($currentSale) || ($lastTime !== null && ($currentTime - $lastTime) > $timeThreshold)) {
                // Завершаем предыдущую продажу
                if (!empty($currentSale)) {
                    $saleCounter++;
                    $saleDate = date('dmY', strtotime($currentSale[0]->created));

                    foreach ($currentSale as $saleDoc) {
                        $saleDoc->name = $saleDoc->name . '_' . $saleDate . '.' . $saleCounter;
                        $processedDocs[] = $saleDoc;
                    }
                }

                // Начинаем новую продажу
                $currentSale = [$doc];
            } else {
                // Добавляем в текущую продажу
                $currentSale[] = $doc;
            }

            $lastTime = $currentTime;
        }

        // Завершаем последнюю продажу
        if (!empty($currentSale)) {
            $saleCounter++;
            $saleDate = date('dmY', strtotime($currentSale[0]->created));

            foreach ($currentSale as $saleDoc) {
                $saleDoc->name = $saleDoc->name . '_' . $saleDate . '.' . $saleCounter;
                $processedDocs[] = $saleDoc;
            }
        }

        // Восстанавливаем порядок
        usort($processedDocs, function($a, $b) {
            return strtotime($a->created) - strtotime($b->created);
        });

        return $processedDocs;
    }
}