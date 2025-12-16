<?php

/**
 * Работа с метками utm, хранящимися в базе данных
 *
 */

require_once('Simpla.php');

/**
 * @property int $id
 * @property string $utm_medium
 * @property string $utm_content
 * @property string $utm_campaign
 * @property string $utm_term
 * @property string $utm_source
 * @property int $user_id
 * @property string $create_date
 */
class UserUtm extends Simpla
{
    function __construct()
    {
        parent::__construct();
    }

    public function getAll()
    {
        $query = "
            SELECT *
            FROM __user_utm
            ORDER BY id DESC
        ";
        $this->db->query($query);

        return $this->db->result();
    }

    public function getByUserId(int $user_id)
    {
        $query = $this->db->placehold("
            SELECT *
            FROM __user_utm
            WHERE user_id = ?
        ", $user_id);
        $this->db->query($query);

        return $this->db->result();
    }

    public function create(int $user_id)
    {
        $query = $this->db->placehold("
            SELECT * FROM __user_utm
            WHERE 
                user_id = ? AND 
                utm_source = ?
        ",
            $user_id,
            $_COOKIE['utm_source']
        );

        $this->db->query($query);
        $result = $this->db->result();

        if (empty($result)) {
            $query = $this->db->placehold("
                INSERT INTO __user_utm SET 
                utm_source = ?, 
                utm_medium = ?, 
                utm_content = ?, 
                utm_campaign = ?, 
                utm_term = ?,
                user_id = ?",
                $_COOKIE['utm_source'] ?? '',
                $_COOKIE['utm_medium'] ?? '',
                $_COOKIE['utm_content'] ?? '',
                $_COOKIE['utm_campaign'] ?? '',
                $_COOKIE['utm_term'] ?? '',
                $user_id
            );
            $this->db->query($query);
        }

        return;
    }
}