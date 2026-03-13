<?php

require_once 'Simpla.php';

class VoxScenarios extends Simpla
{
    public function upsert(array $scenario): void
    {
        $voxScenarioId = isset($scenario['id']) ? (int)$scenario['id'] : 0;
        $title = $scenario['title'] ?? null;

        if ($voxScenarioId <= 0) {
            return;
        }

        $existing = $this->getByVoxScenarioId($voxScenarioId);

        if ($existing) {
            $this->db->query(
                "UPDATE __vox_scenarios SET title = ? WHERE vox_scenario_id = ?",
                $title,
                $voxScenarioId
            );
        } else {
            $this->db->query(
                "INSERT INTO __vox_scenarios (vox_scenario_id, title) VALUES (?, ?)",
                $voxScenarioId,
                $title
            );
        }
    }

    public function getByVoxScenarioId(int $voxScenarioId): ?object
    {
        $this->db->query("SELECT * FROM __vox_scenarios WHERE vox_scenario_id = ? LIMIT 1", $voxScenarioId);
        $result = $this->db->result();
        return $result ?: null;
    }

    public function getTitleByVoxScenarioId(int $voxScenarioId): ?string
    {
        $this->db->query("SELECT title FROM __vox_scenarios WHERE vox_scenario_id = ? LIMIT 1", $voxScenarioId);
        $result = $this->db->result('title');
        return $result ?: null;
    }

    public function getAll(): array
    {
        $this->db->query("SELECT * FROM __vox_scenarios ORDER BY title");
        return $this->db->results();
    }

    public function getEnabledScenarioIds(): array
    {
        $this->db->query("SELECT vox_scenario_id FROM __vox_scenarios WHERE enabled_for_report = 1");
        $ids = [];
        foreach ($this->db->results() as $row) {
            $ids[] = (int)$row->vox_scenario_id;
        }
        return $ids;
    }

    public function getEnabledOptions(): array
    {
        $this->db->query("SELECT vox_scenario_id, title FROM __vox_scenarios WHERE enabled_for_report = 1 ORDER BY title");
        $options = ['' => 'Все'];
        foreach ($this->db->results() as $row) {
            $label = !empty($row->title) ? $row->title : 'Сценарий #' . $row->vox_scenario_id;
            $options[$row->vox_scenario_id] = $label;
        }
        return $options;
    }

    public function setEnabledForReport(int $voxScenarioId, bool $enabled): void
    {
        $this->db->query(
            "UPDATE __vox_scenarios SET enabled_for_report = ? WHERE vox_scenario_id = ?",
            $enabled ? 1 : 0,
            $voxScenarioId
        );
    }

    public function getOptions(): array
    {
        $this->db->query("SELECT vox_scenario_id, title FROM __vox_scenarios ORDER BY title");
        $results = [];
        foreach ($this->db->results() as $row) {
            $results[] = [
                'value' => (string)$row->vox_scenario_id,
                'label' => (string)($row->title ?? 'Сценарий #' . $row->vox_scenario_id),
            ];
        }
        return $results;
    }
}
