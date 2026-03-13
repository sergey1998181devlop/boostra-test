<?php

require_once 'Simpla.php';

class VoxUsers extends Simpla
{
    public function upsert(array $user): void
    {
        $voxUserId = isset($user['id']) ? (int)$user['id'] : 0;
        $fullName = $user['full_name'] ?? null;
        $email = $user['email'] ?? null;

        if ($voxUserId <= 0) {
            return;
        }

        $existing = $this->getByVoxUserId($voxUserId);

        if ($existing) {
            $this->db->query(
                "UPDATE __vox_users SET full_name = ?, email = ? WHERE vox_user_id = ?",
                $fullName,
                $email,
                $voxUserId
            );
        } else {
            $this->db->query(
                "INSERT INTO __vox_users (vox_user_id, email, full_name) VALUES (?, ?, ?)",
                $voxUserId,
                $email,
                $fullName
            );
        }
    }

    public function getByVoxUserId(int $voxUserId): ?object
    {
        $this->db->query("SELECT * FROM __vox_users WHERE vox_user_id = ? LIMIT 1", $voxUserId);
        $result = $this->db->result();
        return $result ?: null;
    }

    public function getAll(): array
    {
        $this->db->query("
            SELECT u.*, d.name AS department_name
            FROM __vox_users u
            LEFT JOIN __vox_user_departments d ON d.id = u.department_id
            ORDER BY u.full_name
        ");
        return $this->db->results();
    }

    public function getFiltered(string $search = null, int $page = 1, int $limit = 20, ?int $departmentId = null): array
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT u.*, d.name AS department_name
                FROM __vox_users u
                LEFT JOIN __vox_user_departments d ON d.id = u.department_id
                WHERE 1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND u.full_name LIKE ?";
            $params[] = "%{$search}%";
        }

        if ($departmentId !== null) {
            $sql .= " AND u.department_id = ?";
            $params[] = $departmentId;
        }

        $sql .= " ORDER BY u.full_name LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $this->db->query($sql, ...$params);
        return $this->db->results();
    }

    public function countFiltered(string $search = null, ?int $departmentId = null): int
    {
        $sql = "SELECT COUNT(*) as cnt FROM __vox_users WHERE 1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND full_name LIKE ?";
            $params[] = "%{$search}%";
        }

        if ($departmentId !== null) {
            $sql .= " AND department_id = ?";
            $params[] = $departmentId;
        }

        $this->db->query($sql, ...$params);
        $result = $this->db->result('cnt');
        return (int)$result;
    }

    public function setDepartment(int $voxUserId, ?int $departmentId): void
    {
        $this->db->query(
            "UPDATE __vox_users SET department_id = ? WHERE vox_user_id = ?",
            $departmentId,
            $voxUserId
        );
    }

    public function setEnabledForCallAnalysis(int $voxUserId, bool $enabled): void
    {
        $this->db->query(
            "UPDATE __vox_users SET is_call_analysis = ? WHERE vox_user_id = ?",
            $enabled ? 1 : 0,
            $voxUserId
        );
    }
}
