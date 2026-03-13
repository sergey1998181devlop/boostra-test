<?php

require_once 'Simpla.php';

class VoxQueues extends Simpla
{
    public function upsert(array $queue): void
    {
        $voxQueueId = isset($queue['id']) ? (int)$queue['id'] : 0;
        $title = $queue['acd_queue_title'] ?? null;

        if ($voxQueueId <= 0) {
            return;
        }

        $existing = $this->getByVoxQueueId($voxQueueId);

        if ($existing) {
            $this->db->query(
                "UPDATE __vox_queues SET title = ? WHERE vox_queue_id = ?",
                $title,
                $voxQueueId
            );
        } else {
            $this->db->query(
                "INSERT INTO __vox_queues (vox_queue_id, title) VALUES (?, ?)",
                $voxQueueId,
                $title
            );
        }
    }

    public function getByVoxQueueId(int $voxQueueId): ?object
    {
        $this->db->query("SELECT * FROM __vox_queues WHERE vox_queue_id = ? LIMIT 1", $voxQueueId);
        $result = $this->db->result();
        return $result ?: null;
    }

    public function getTitleByVoxQueueId(int $voxQueueId): ?string
    {
        $this->db->query("SELECT title FROM __vox_queues WHERE vox_queue_id = ? LIMIT 1", $voxQueueId);
        $result = $this->db->result('title');
        return $result ?: null;
    }

    public function getAll(): array
    {
        $this->db->query("SELECT * FROM __vox_queues ORDER BY title");
        return $this->db->results();
    }

    public function getEnabledQueueIds(): array
    {
        $this->db->query("SELECT vox_queue_id FROM __vox_queues WHERE enabled_for_report = 1");
        $ids = [];
        foreach ($this->db->results() as $row) {
            $ids[] = (int)$row->vox_queue_id;
        }
        return $ids;
    }

    public function getEnabledOptions(): array
    {
        $this->db->query("SELECT vox_queue_id, title FROM __vox_queues WHERE enabled_for_report = 1 ORDER BY title");
        $options = ['' => 'Все'];
        foreach ($this->db->results() as $row) {
            $label = !empty($row->title) ? $row->title : 'Очередь #' . $row->vox_queue_id;
            $options[$row->vox_queue_id] = $label;
        }
        return $options;
    }

    public function setEnabledForReport(int $voxQueueId, bool $enabled): void
    {
        $this->db->query(
            "UPDATE __vox_queues SET enabled_for_report = ? WHERE vox_queue_id = ?",
            $enabled ? 1 : 0,
            $voxQueueId
        );
    }

    public function enableQueues(array $voxQueueIds): void
    {
        if (empty($voxQueueIds)) {
            return;
        }

        $ids = array_map('intval', $voxQueueIds);
        $idsString = implode(',', $ids);

        $this->db->query("UPDATE __vox_queues SET enabled_for_report = 1 WHERE vox_queue_id IN ($idsString)");
        $this->db->query("UPDATE __vox_queues SET enabled_for_report = 0 WHERE vox_queue_id NOT IN ($idsString)");
    }

    public function disableAllQueues(): void
    {
        $this->db->query("UPDATE __vox_queues SET enabled_for_report = 0");
    }
}
