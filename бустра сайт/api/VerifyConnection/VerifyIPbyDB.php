<?php

namespace Api\VerifyConnection;

require_once(dirname(__DIR__) . '/VerifyConnection/VerifyModelInterface.php');
require_once(dirname(__DIR__) . '/VerifyConnection/VerifyBaseData.php');

require_once(dirname(__DIR__) . '/SimplaPSR.php');
require_once(dirname(__DIR__) . '/Simpla.php');

use Api\SimplaPSR as Simpla;

class VerifyIPbyDB extends Simpla implements VerifyModelInterface
{
    const HASH_NAME = 'verify_ip';
    const TABLE_NAME = '__connection_by_ip';

    public function __construct()
    {
    }

    public function delete(string $hash): bool
    {
        $table = self::TABLE_NAME;
        $query = $this->db->placehold("DELETE
            FROM {$table} b
            WHERE b.hash='{$hash}'
            LIMIT 1"
        );

        return ($this->db->query($query));
    }

    public function create(VerifyBaseData $info)
    {
        $table = self::TABLE_NAME;
        $createdAt = empty($info->createdAt) ? date('Y-m-d H:i:s') : $info->createdAt;
        $updatedAt = empty($info->updatedAt) ? date('Y-m-d H:i:s') : $info->updatedAt;

        $query = $this->db->placehold("INSERT
            INTO {$table} (hash, ip, count, created_at, updated_at)
            VALUES ('{$info->hash}', '{$info->ip}', {$info->count}, '{$createdAt}', '{$updatedAt}')"
        );

        return $this->db->query($query);
    }

    public function set(VerifyBaseData $info): bool
    {
        $table = self::TABLE_NAME;
        $updatedAt = empty($info->updatedAt) ? date('Y-m-d H:i:s') : $info->updatedAt;

        $query = $this->db->placehold("UPDATE {$table} b
            SET b.count='{$info->count}', b.updated_at='{$updatedAt}'
            WHERE b.hash='{$info->hash}'
            LIMIT 1"
        );

        return ($this->db->query($query));
    }

    public function get(string $hash)
    {
        $table = self::TABLE_NAME;

        $query = $this->db->placehold("SELECT *
            FROM {$table} b
            WHERE b.hash='{$hash}'
            LIMIT 1"
        );
		$this->db->query($query);

        return $this->db->result();
    }
}