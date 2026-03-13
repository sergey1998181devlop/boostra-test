<?php

if (!function_exists('formatPhoneNumber')) {
    /**
     * Форматирует номер телефона в формат 7XXXXXXXXXX
     * Обратная совместимость: возвращает false при невалидном номере
     *
     * @param string $phone
     * @return string|false
     */
    function formatPhoneNumber(string $phone)
    {
        // Оставляем только цифры (игнорируем пробелы, +, дефисы, скобки)
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if ($cleaned === '') {
            return false;
        }

        $len = strlen($cleaned);
        $first = $cleaned[0] ?? '';

        // 11 цифр: 7XXXXXXXXXX — оставить как есть
        if ($len === 11) {
            if ($first === '7') {
                return $cleaned;
            }
            // 11 цифр и начинается с 8 — заменить первую цифру на 7
            if ($first === '8') {
                return '7' . substr($cleaned, 1);
            }
            // Иные 11-значные варианты — невалидны
            return false;
        }

        // 10 цифр и начинается с 9 — добавить 7 в начало
        if ($len === 10 && $first === '9') {
            return '7' . $cleaned;
        }

        // Иные случаи — невалидны (вызывающая сторона должна запросить номер в формате 79XXXXXXXXX)
        return false;
    }
}

if (!function_exists('formatPhoneForDnc')) {
    /**
     * Форматирует номер телефона для использования в DNC (формат 7XXXXXXXXXX - полный номер)
     * Возвращает null для невалидных номеров
     *
     * @param string $phone
     * @return string|null
     */
    function formatPhoneForDnc(string $phone): ?string
    {
        $fullPhone = formatPhoneNumber($phone);

        if ($fullPhone === false) {
            return null;
        }

        // Возвращаем полный номер с префиксом 7
        return $fullPhone;
    }
}

if (!function_exists('formatPhonesForDnc')) {
    /**
     * Форматирует массив телефонов для DNC с удалением дубликатов и невалидных номеров
     *
     * @param array $phones
     * @return array
     */
    function formatPhonesForDnc(array $phones): array
    {
        $validPhones = [];

        foreach ($phones as $phone) {
            if (empty($phone)) {
                continue;
            }

            $formattedPhone = formatPhoneForDnc($phone);
            if ($formattedPhone !== null) {
                $validPhones[] = $formattedPhone;
            }
        }

        // Удаляем дубликаты и переиндексируем массив
        return array_values(array_unique($validPhones));
    }
}
