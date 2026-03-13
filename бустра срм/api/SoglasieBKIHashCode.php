<?php

require_once(__DIR__ . '/Simpla.php');

class SoglasieBKIHashCode extends Simpla
{
    public string $table = 'soglasie_bki_hash_code';

    public function create($userId, $hash, $patch)
    {
        $query = $this->db->placehold("INSERT INTO {$this->table} SET ?%", [
            'user_id' => $userId,
            'hash_code' => $hash,
            'patch' => $patch,
        ]);

        $this->db->query($query);
    }

    public function getByUserId($userId)
    {
        $query = $this->db->placehold("SELECT hash_code FROM {$this->table} WHERE user_id = ? LIMIT 1", $userId);
        $this->db->query($query);

        return $this->db->result('hash_code');
    }

    public function generate($content)
    {
        return hash('stribog256', $content);
    }
}