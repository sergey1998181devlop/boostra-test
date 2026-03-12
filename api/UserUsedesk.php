<?php


use api\services\UsedeskService;

require_once 'Simpla.php';

class UserUsedesk extends Simpla
{
    public function getUsedeskUserId(object $user)
    {
        $this->db->query("
            SELECT 
                usedesk_id
            FROM s_user_usedesk
            WHERE user_id = ?
            ",
            $user->id
        );
        $usedeskId = $this->db->result('usedesk_id');

        if (empty($usedeskId)) {
            return $this->createUserUsedesk($user)->usedesk_id ?? false;
        }

        return $usedeskId;
    }

    public function createUserUsedesk(object $user)
    {
        $note = "Дата рождения: " . $user->birth . "\n";
        $note .= "Договор: " . $this->users->getUserActiveZaimNumber($user->id) . "\n";
        $profileUrl = trim($this->config->back_url, '/') . "/client/" . $user->id . "\n";

        $apiToken = $this->config->USEDESK['TICKET_SECRET_KEY'];

        $usedeskService = new UsedeskService();

        try {
            $usedeskResponse = $usedeskService->createClient(
                $apiToken,
                $user->firstname . ' ' . $user->lastname,
                $user->email,
                $user->phone_mobile,
                $note,
                $profileUrl
            );
        } catch (Exception $e) {
            error_log('Usedesk create client error: ' . $e->getMessage());
            return false;
        }

        if (isset($usedeskResponse['client_id'])) {
            $this->db->query(
                "INSERT INTO s_user_usedesk (user_id, usedesk_id) VALUES (?, ?)",
                $user->id, $usedeskResponse['client_id']
            );

            $this->db->query("SELECT * FROM s_user_usedesk WHERE user_id = ?", $user->id);

            return $this->db->result();
        }

        return false;
    }
}