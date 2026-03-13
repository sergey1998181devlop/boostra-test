<?php

header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");
define('ROOT', dirname(__DIR__));

session_start();
chdir('..');

require 'api/Simpla.php';

$simpla = new Simpla();
//create table calls_blacklist
//(
//    id          int auto_increment,
//    user_id     int  not null,
//    created     date  not null,
//    days        int  not null,
//    unblock_day date not null,
//    constraint calls_blacklist_pk
//        primary key (id),
//    constraint calls_blacklist_s_users_null_fk
//        foreign key (user_id) references s_users (id)
//);
//alter table calls_blacklist
//    add deleted_at datetime null;

//add cron/delete_from_call_blacklist.php

class AddCallBlacklist extends Simpla
{
    const TOKEN = 'If0kkGeHe2EGzDFCx79DogWpqrFUUXfWF3T0Ki5czzOie4dY86hc0IXhVDyRe1cl';
    function __construct()
    {
        $this->run();
    }
    function run()
    {
        $apiData = file_get_contents("php://input");
        $apiData = json_decode($apiData, true);
        if (empty($apiData)) {
            $userIds = $this->request->post('users_ids');
            $days = $this->request->post('days');
            $manager = $this->request->post('manager');
            $block = $this->request->post('block');
        }
       else{
           $token = $apiData['token'];
           if ((!empty($token) && self::TOKEN!=$token)) {
               http_response_code(400);
               $this->response->json_output(['error' => 'Неправильный токен']);
               exit();
           }
           if (!empty($token)) {
               if (empty($apiData['uids'])) {
                   http_response_code(400);
                   $this->response->json_output(['error' => 'Неправильный uids']);
               }else{
                   $userIds = $apiData['uids'];
                   $days = $apiData['days'];
                   $manager = $apiData['manager'];
               }
           }
       }
        if ($block == '0') {
            $this->unblock_user($userIds);
            exit();
        }
        if (empty($userIds) || empty($days) || $days < 0) {
            http_response_code(400);
            $this->response->json_output(['error' => 'Неправильные параметры']);
            exit();
        }
        $values = [];
        $created = date('Y-m-d');
        $unblockDay = date('Y-m-d', strtotime("+$days days"));
        if (is_numeric($userIds)) {
            $userIds = [$userIds];
        }
        foreach ($userIds as $user) {
            if (!is_numeric($user)){
                $user = $this->users->get_users(['uid' => $user])[0]->id;
            }else{
                $userUid = $this->users->getUserUidById($user);
                $this->soap->sendAddUserToCallBlacklist1c($userUid);
            }
            $values[] = '(' . intval($user) . ', "' . $created . '", ' . intval($days) . ', "' . $unblockDay . '")';
            $this->addComment($user,$manager);
        }
        $values = implode(', ', $values);
        $this->addBlacklist($values);
        $this->response->json_output(['success' => 'Успешно']);
    }

    private function addBlacklist($values)
    {
        $sql = "INSERT INTO calls_blacklist (user_id, created, days, unblock_day) VALUES $values";
        $this->db->query($sql);
    }
    private function addComment($user,$manager)
    {
        $comment = array(
            'manager_id' => $manager,
            'user_id' => $user,
            'block' => 'block-call',
            'created' => date('Y-m-d H:i:s'),
        );

        $this->comments->add_comment($comment);
    }
    private function unblock_user($userId) {
        $this->markUserAsDeleted($userId);
        $userUid = $this->users->getUserUidById($userId);
        $this->soap->deleteUserFromCallBlacklist1c($userUid);
    }
    private function markUserAsDeleted(int $id): void
    {
        $data = ['deleted_at' => date('Y-m-d H:i:s')];
        $query = $this->db->placehold("
            UPDATE calls_blacklist SET ?% WHERE user_id = ? AND deleted_at is null
        ", (array)$data, $id);

        $this->db->query($query);
    }

}

new AddCallBlacklist();