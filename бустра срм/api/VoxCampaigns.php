<?php

require_once 'Simpla.php';

class VoxCampaigns extends Simpla
{
    public function upsert(array $campaign): void
    {
        $voxCampaignId = isset($campaign['id']) ? (int)$campaign['id'] : 0;
        $title = $campaign['title'] ?? null;

        if ($voxCampaignId <= 0) {
            return;
        }

        $existing = $this->getByVoxCampaignId($voxCampaignId);

        if ($existing) {
            $this->db->query(
                "UPDATE __vox_campaigns SET title = ? WHERE vox_campaign_id = ?",
                $title,
                $voxCampaignId
            );
        } else {
            $this->db->query(
                "INSERT INTO __vox_campaigns (vox_campaign_id, title) VALUES (?, ?)",
                $voxCampaignId,
                $title
            );
        }
    }

    public function getByVoxCampaignId(int $voxCampaignId): ?object
    {
        $this->db->query("SELECT * FROM __vox_campaigns WHERE vox_campaign_id = ? LIMIT 1", $voxCampaignId);
        $result = $this->db->result();
        return $result ?: null;
    }

    public function getTitleByVoxCampaignId(int $voxCampaignId): ?string
    {
        $this->db->query("SELECT title FROM __vox_campaigns WHERE vox_campaign_id = ? LIMIT 1", $voxCampaignId);
        $result = $this->db->result('title');
        return $result ?: null;
    }

    public function getAll(): array
    {
        $this->db->query("SELECT * FROM __vox_campaigns ORDER BY title");
        return $this->db->results();
    }
}
