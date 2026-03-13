<?php

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');
date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__) . '/../api/Simpla.php';

class DeleteFromCallBlacklist extends Simpla
{
    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    private function run(): void
    {
        $users = $this->getUsersToUnblock();
        foreach ($users as $user) {
            $this->processUserUnblocking($user);
        }
    }

    private function getUsersToUnblock(): array
    {
        $date = date('Y-m-d');
        $query = $this->db->placehold("
            SELECT * 
            FROM calls_blacklist
            WHERE unblock_day = ? and deleted_at is null
        ", $date);
        $this->db->query($query);
        return $this->db->results();
    }

    private function processUserUnblocking($user): void
    {
        $this->markUserAsDeleted($user->id);
        $userUid = $this->users->getUserUidById($user->user_id);
        $this->soap->deleteUserFromCallBlacklist1c($userUid);
    }

    private function markUserAsDeleted(int $id): void
    {
        $data = ['deleted_at' => date('Y-m-d H:i:s')];
        $query = $this->db->placehold("
            UPDATE calls_blacklist SET ?% WHERE user_id = ?
        ", (array)$data, $id);
        $this->db->query($query);
    }
}

new DeleteFromCallBlacklist();
