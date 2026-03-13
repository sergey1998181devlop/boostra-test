<?php
require_once 'Simpla.php';

/**
 * Class RosstatRegion
 * Таблица: s_rosstat_regions (id, region)
 *
 * Функционал:
 *  - CRUD по регионам
 *  - Резолв кода региона из произвольной строки
 *  - Резолв кода региона напрямую из orders (object|id)
 */
class RosstatRegion extends Simpla
{
    // -----------------------------
    // Базовые выборки/CRUD
    // -----------------------------

    /**
     * Получить все регионы
     * @return array<int, object>
     */
    public function getAll(): array
    {
        $q = $this->db->placehold("SELECT id, region FROM __rosstat_regions");
        $this->db->query($q);
        return (array) $this->db->results();
    }

    /**
     * Найти регион по ID
     * @param int $id
     * @return object|null
     */
    public function getById(int $id): ?object
    {
        $q = $this->db->placehold("SELECT id, region FROM __rosstat_regions WHERE id = ?", $id);
        $this->db->query($q);
        return $this->db->result() ?: null;
    }

    /**
     * Найти регион по точному названию (без нормализации)
     * @param string $regionName
     * @return object|null
     */
    public function getByName(string $regionName): ?object
    {
        $q = $this->db->placehold(
            "SELECT id, region FROM __rosstat_regions WHERE region = ?",
            $regionName
        );
        $this->db->query($q);
        return $this->db->result() ?: null;
    }

    /**
     * Добавить регион
     * @param array $row [ 'region' => '...' ]
     * @return int новый id
     */
    public function add(array $row): int
    {
        $q = $this->db->placehold("INSERT INTO __rosstat_regions SET ?%", (array)$row);
        $this->db->query($q);
        return (int) $this->db->insert_id();
    }

    /**
     * Обновить регион
     * @param int   $id
     * @param array $data
     * @return bool|resource|int
     */
    public function update(int $id, array $data)
    {
        $q = $this->db->placehold("UPDATE __rosstat_regions SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($q);
    }

    /**
     * Удалить регион
     * @param int $id
     * @return bool|resource|int
     */
    public function delete(int $id)
    {
        $q = $this->db->placehold("DELETE FROM __rosstat_regions WHERE id = ?", $id);
        return $this->db->query($q);
    }

    // -----------------------------
    // Резолв кода региона
    // -----------------------------

    /**
     * Определяет код региона из входной строки.
     * Возвращает id региона или пустую строку, если не найден.
     *
     * @param string|null $input
     * @return string
     */
    public function resolveCodeFromString(?string $input): string
    {
        if (!$input || !is_string($input)) {
            return '';
        }

        // Нормализуем строку
        $cleaned = $this->cleanString($input);
        if ($cleaned === '') {
            return '';
        }

        // Подготовка строки для поиска (дефисы → пробелы, схлопывание пробелов, обрамление)
        $haystack = ' ' . trim(preg_replace('/[\s\-]+/u', ' ', str_replace('-', ' ', $cleaned))) . ' ';

        // Поиск региона по совпадению LIKE
        $q = $this->db->placehold("
        SELECT id
        FROM __rosstat_regions
        WHERE ? LIKE CONCAT('% ', REPLACE(LOWER(REPLACE(region,'ё','е')),'-',' '), ' %')
        ORDER BY CHAR_LENGTH(region) DESC
        LIMIT 1
    ", $haystack);

        $this->db->query($q);
        $row = $this->db->result();

        return $row ? $row->id : '';
    }

    // -----------------------------
    // Вспомогательные методы
    // -----------------------------

    /**
     * Очистка строки: нижний регистр, ё->е, оставить только кириллицу/пробелы/дефисы
     * @param string $input
     * @return string
     */
    private function cleanString(string $input): string
    {
        $input = mb_strtolower($input, 'UTF-8');
        $input = str_replace('ё', 'е', $input);
        $input = preg_replace('/[^а-я\s\-]/u', ' ', $input);
        return trim((string) $input);
    }
}
