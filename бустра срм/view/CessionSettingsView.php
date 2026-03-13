<?php

require_once 'View.php';

class CessionSettingsView extends View
{
    public function fetch()
    {
        $this->design->assign('meta_title', 'Настройки Цессии');

        // Какие "секции" читаем из s_settings.
        $enumFields = ['execution_status', 'importance', 'contract_form', 'cedent', 'counterparty'];

        // Базовые значения (нельзя удалять из UI).
        $baseValues = [
            'execution_status' => ['Новый', 'Отозвано', 'Не отозвано'],
            'importance' => ['СРОЧНО', 'Не срочно'],
            'contract_form' => ['Займ', 'Доп.услуга'],
            'cedent' => ['Алфавит', 'Аквариус', 'Бустра', 'Акадо', 'ФР', 'Дивэлопмэнт'],
            'counterparty' => ['Санколлет', 'Сириус', 'BPA', 'Экспресс Коллекшн', 'Эвентус', 'Грифон', 'Легал Коллекшн', 'Апгрейд-Финанс', 'Уна Лекс', 'Арка', 'Сегмент', 'ЦПВ'],
        ];

        $enumValues = [];
        $deletableFlags = [];

        // Теперь все значения хранятся в одной строке, ключ: cession_settings
        $this->db->query("SELECT `value` FROM s_settings WHERE `name` = ? LIMIT 1", 'cession_settings');
        $row = $this->db->result();
        $settings = [];
        if ($row && isset($row->value)) {
            $decoded = json_decode((string)$row->value, true);
            if (is_array($decoded)) {
                $settings = $decoded;
            }
        }

        foreach ($enumFields as $field) {
            $list = [];
            if (isset($settings[$field]) && is_array($settings[$field])) {
                foreach ($settings[$field] as $v) {
                    if (is_scalar($v)) {
                        $v = trim((string)$v);
                        if ($v !== '') {
                            $list[] = $v;
                        }
                    }
                }
            }
            $enumValues[$field] = $list;
            // Флаги удаляемости: базовые — false (нельзя удалить), остальные — true.
            foreach ($list as $value) {
                $deletableFlags[$field][$value] = !in_array($value, $baseValues[$field] ?? [], true);
            }
        }

        $counterparties = $this->getCounterpartiesWithEmails();

        $this->design->assign('enumValues', $enumValues);
        $this->design->assign('baseValues', $baseValues);
        $this->design->assign('deletableFlags', $deletableFlags);
        $this->design->assign('counterparties', $counterparties);

        return $this->design->fetch('cession_settings.tpl');
    }

    /**
     * Возвращает массив контрагентов, каждый с массивом emails.
     */
    private function getCounterpartiesWithEmails(): array
    {
        $sql = "
        SELECT c.*, e.email
        FROM counterparties c
        LEFT JOIN counterparty_emails e ON e.counterparty_id = c.id
        ORDER BY c.id ASC, e.id ASC
    ";
        $this->db->query($sql);
        $rows = $this->db->results();

        $out = [];
        foreach ($rows as $r) {
            $id = (int)$r->id;

            if (!isset($out[$id])) {
                $cp = clone $r;
                unset($cp->_e_email);
                $cp->emails = [];
                $out[$id] = $cp;
            }

            if (!empty($r->email)) {
                $out[$id]->emails[] = $r->email;
            }
        }

        return array_values($out);
    }
}