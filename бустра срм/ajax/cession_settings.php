<?php

session_start();
chdir('..');

require 'api/Simpla.php';

class CessionSettingsHandler extends Simpla
{
    public function run(): void
    {
        if (empty($_SESSION['manager_id'])) {
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $action = $this->request->get('action', 'string');

        switch ($action) {
            case 'delete_enum_value':
                $this->delete_enum_value();
                $this->redirect();
                break;

            case 'add_enum_value':
                $this->add_enum_value();
                $this->redirect();
                break;

            case 'add_counterparty':
                $this->add_counterparty();
                break;

            case 'delete_counterparty':
                $this->delete_counterparty();
                break;

            case 'update_counterparty_name':
                $this->update_counterparty_name();
                break;

            case 'add_counterparty_email':
                $this->add_counterparty_email();
                break;

            case 'delete_counterparty_email':
                $this->delete_counterparty_email();
                break;

            case 'update_counterparty_director_name':
                $this->update_counterparty_director_name();
                break;

            case 'update_counterparty_director_position':
                $this->update_counterparty_director_position();
                break;

            default:
                $this->request->json_output(false, 'Неизвестное действие');
        }
    }

    /**
     * Добавляет новое значение в JSON-массив в s_settings.
     */
    public function add_enum_value(): void
    {
        $allowedFields = ['execution_status', 'importance', 'contract_form', 'cedent', 'counterparty'];

        $field = $this->request->post('field');
        $newValue = trim($this->request->post('new_value'));

        if (!in_array($field, $allowedFields, true)) {
            $this->fail('Недопустимое поле');
        }
        if ($newValue === '') {
            $this->fail('Пустое значение');
        }
        if (mb_strlen($newValue) > 100) {
            $this->fail('Слишком длинное значение (>100)');
        }

        $settings = $this->getCessionSettings();
        if (!isset($settings[$field]) || !is_array($settings[$field])) {
            $settings[$field] = [];
        }

        if (!in_array($newValue, $settings[$field], true)) {
            $settings[$field][] = $newValue;
            $this->saveCessionSettings($settings);
        }
    }

    /**
     * Удаляет значение из JSON-массива (если оно не базовое).
     */
    public function delete_enum_value(): void
    {
        $allowedFields = ['execution_status', 'importance', 'contract_form', 'cedent', 'counterparty'];

        $baseValues = [
            'contract_form' => ['Займ', 'Доп.услуга'],
            'cedent' => ['Алфавит', 'Аквариус', 'Бустра', 'Акадо', 'ФР', 'Дивэлопмэнт'],
            'counterparty' => ['Санколлет', 'Сириус', 'BPA', 'Экспресс Коллекшн', 'Эвентус', 'Грифон', 'Легал Коллекшн', 'Апгрейд-Финанс', 'Уна Лекс', 'Арка', 'Сегмент', 'ЦПВ'],
            'importance' => ['СРОЧНО', 'Не срочно'],
            'execution_status' => ['Новый', 'Отозвано', 'Не отозвано']
        ];

        $field = $this->request->post('field');
        $valueToDelete = trim($this->request->post('value_to_delete'));

        if (!in_array($field, $allowedFields, true)) {
            $this->fail('Недопустимое поле');
        }
        if (in_array($valueToDelete, $baseValues[$field], true)) {
            $this->fail('Нельзя удалить базовое значение');
        }

        $settings = $this->getCessionSettings();
        if (!isset($settings[$field]) || !is_array($settings[$field])) {
            return;
        }

        $filtered = [];
        $deleted = false;
        foreach ($settings[$field] as $v) {
            if ($v === $valueToDelete && !$deleted) {
                $deleted = true;
                continue;
            }
            $filtered[] = $v;
        }

        if ($deleted) {
            $settings[$field] = $filtered;
            $this->saveCessionSettings($settings);
        }
    }

    /**
     * Добавляет контрагента и его почты.
     */
    public function add_counterparty(): void
    {
        $name = trim($this->request->post('name'));
        $emails = $this->request->post('emails');
        $director_name = trim($this->request->post('director_name'));
        $director_position = trim($this->request->post('director_position'));

        if ($name === '' || !is_array($emails) || empty($emails)) {
            $this->request->json_output(false, 'Неверные данные');
            return;
        }

        $this->db->query("SELECT id FROM counterparties WHERE name = ?", $name);
        $existing = $this->db->result();

        if ($existing) {
            $counterpartyId = (int)$existing->id;
        } else {
            $this->db->query(
                "INSERT INTO counterparties SET name = ?, director_name = ?, director_position = ?, created_at = NOW(), updated_at = NOW()",
                $name,
                $director_name,
                $director_position
            );
            $counterpartyId = (int)$this->db->insert_id();
        }

        foreach ($emails as $email) {
            $email = trim($email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $this->db->query(
                "SELECT id FROM counterparty_emails WHERE counterparty_id = ? AND email = ?",
                $counterpartyId,
                $email
            );
            if ($this->db->result()) {
                continue;
            }

            $this->db->query(
                "INSERT INTO counterparty_emails SET counterparty_id = ?, email = ?, created_at = NOW(), updated_at = NOW()",
                $counterpartyId,
                $email
            );
        }

        $this->redirect();
    }

    /**
     * Удаляет контрагента по id.
     */
    public function delete_counterparty(): void
    {
        $id = (int)$this->request->post('id');
        $this->db->query("DELETE FROM counterparties WHERE id = ?", $id);
        $this->request->json_output(true);
    }

    /**
     * Переименование контрагента с проверкой на дубликаты
     */
    public function update_counterparty_name(): void
    {
        $id    = (int)$this->request->post('id');
        $value = (string)$this->request->post('value');

        $value = trim(preg_replace('/\s+/u', ' ', $value));

        if ($value === '') {
            $this->request->json_output(false, 'Имя не может быть пустым');
            return;
        }

        $this->db->query(
            "SELECT id FROM counterparties WHERE name = ? AND id != ?",
            $value,
            $id
        );
        if ($this->db->result()) {
            $this->request->json_output(false, 'Контрагент с таким именем уже существует');
            return;
        }

        $this->db->query(
            "UPDATE counterparties SET name = ?, updated_at = NOW() WHERE id = ?",
            $value,
            $id
        );
        $this->request->json_output(true);
    }

    /**
     * Добавляет email к контрагенту.
     */
    public function add_counterparty_email(): void
    {
        $counterpartyId = (int)$this->request->post('counterparty_id');
        $email = trim($this->request->post('email'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->request->json_output(false, 'Некорректный email');
            return;
        }

        $this->db->query(
            "INSERT INTO counterparty_emails SET counterparty_id = ?, email = ?, created_at = NOW(), updated_at = NOW()",
            $counterpartyId,
            $email
        );
        $this->request->json_output(true);
    }

    /**
     * Удаляет email у контрагента.
     */
    public function delete_counterparty_email(): void
    {
        $counterpartyId = (int)$this->request->post('counterparty_id');
        $email = trim($this->request->post('email'));

        $this->db->query(
            "DELETE FROM counterparty_emails WHERE counterparty_id = ? AND email = ?",
            $counterpartyId,
            $email
        );
        $this->request->json_output(true);
    }

    public function update_counterparty_director_name(): void
    {
        $id = (int)$this->request->post('id');
        $director_name = trim($this->request->post('director_name'));

        $this->db->query(
            "UPDATE counterparties SET director_name = ?, updated_at = NOW() WHERE id = ?",
            $director_name,
            $id
        );
        $this->request->json_output(true);
    }

    public function update_counterparty_director_position(): void
    {
        $id = (int)$this->request->post('id');
        $director_position = trim($this->request->post('director_position'));

        $this->db->query(
            "UPDATE counterparties SET director_position = ?, updated_at = NOW() WHERE id = ?",
            $director_position,
            $id
        );
        $this->request->json_output(true);
    }

    /**
     * Читает cession_settings JSON-объект из s_settings.
     */
    private function getCessionSettings(): array
    {
        $raw = $this->settings->cession_settings ?? [];
        $data = is_array($raw) ? $raw : json_decode($raw, true);
        if (!is_array($data)) {
            return [];
        }

        foreach (['execution_status', 'importance', 'contract_form', 'cedent', 'counterparty'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $clean = [];
                foreach ($data[$field] as $v) {
                    if (is_scalar($v)) {
                        $trimmed = trim((string)$v);
                        if ($trimmed !== '') {
                            $clean[] = $trimmed;
                        }
                    }
                }
                $data[$field] = $clean;
            }
        }
        return $data;
    }

    /**
     * Сохраняет cession_settings JSON-объект в s_settings.
     */
    private function saveCessionSettings(array $settings): void
    {
        $this->settings->cession_settings = json_encode($settings, JSON_UNESCAPED_UNICODE);
    }

    private function fail(string $msg): void
    {
        $_SESSION['error'] = $msg;
        header('Location: /cession_settings');
        exit;
    }

    private function redirect(): void
    {
        header('Location: /cession_settings');
        exit;
    }
}

$handler = new CessionSettingsHandler();
$handler->run();