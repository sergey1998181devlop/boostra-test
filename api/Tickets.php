<?php

require_once 'api/Simpla.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Tickets extends Simpla
{
    /**
     * @throws Exception
     */
    public function handleRequest($data): int
    {
        if ($this->isLimitExceeded($data->user_id)) {
            throw new Exception('Вы превысили лимит на количество обращений за сутки. Пожалуйста, попробуйте позже.');
        }

        if ($this->areRequiredFieldsEmpty($data->fio, $data->email, $data->phone, $data->topic, $data->text)) {
            throw new Exception('Не все обязательные поля заполнены');
        }

        $uploaded_files = $this->handleFileUploads($data->files);

        $ticketId = $this->createTicket($data->user_id, $data->topic, $data->phone, $data->fio, $data->email, $data->text, $uploaded_files);

        if (!$ticketId) {
            throw new Exception('Ошибка при создании обращения');
        }

        if (!$this->sendEmail($ticketId, $data->fio, $data->email, $data->phone, $data->text, $data->topic, $uploaded_files)) {
            throw new Exception('Ошибка при отправке email');
        }

        return $ticketId;
    }

    private function isLimitExceeded($user_id): bool
    {
        $query = $this->db->placehold("SELECT COUNT(*) as count FROM __mytickets WHERE client_id = ? AND created_at >= NOW() - INTERVAL 1 DAY", $user_id);
        $this->db->query($query);
        return $this->db->result()->count >= 2;
    }

    private function areRequiredFieldsEmpty($fio, $email, $phone, $topic, $text): bool
    {
        return empty($fio) || empty($email) || empty($phone) || !isset($topic) || empty($text);
    }

    private function handleFileUploads($files): array
    {
        $uploaded_files = [];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_file_size = 15 * 1024 * 1024; // 15 MB in bytes

        foreach ($files['tmp_name'] as $key => $tmp) {
            if ($files['error'][$key] !== UPLOAD_ERR_OK) {
                error_log("File upload error: " . $files['name'][$key]);
                continue;
            }

            $file_size = $files['size'][$key];
            $extension = strtolower(pathinfo($files['name'][$key], PATHINFO_EXTENSION));

            if (!in_array($extension, $allowed_extensions)) {
                continue;
            }

            if ($file_size >= $max_file_size) {
                continue;
            }

            $new_filename = md5(microtime() . rand()) . '.' . $extension;
            $dir_path = $this->config->root_dir . 'files/uploads/complain/';
            $new_filepath = $dir_path . $new_filename;

            if (!file_exists($dir_path)) {
                mkdir($dir_path, 0777, true);
            }

            if (move_uploaded_file($tmp, $new_filepath)) {
                $original_name = $files['name'][$key];

                $file_uid = $this->filestorage->upload_file($new_filepath);
                if ($file_uid) {
                    $file_url = $this->filestorage->getStorageUrl() . $file_uid;
                    $uploaded_files[] = [
                        'path' => $new_filepath,
                        'url' => $file_url,
                        'name' => $original_name
                    ];
                } else {
                    error_log("Failed to upload file to storage: $new_filename");
                }
            } else {
                error_log("Failed to move uploaded file: " . $files['name'][$key]);
            }
        }

        return $uploaded_files;
    }

    private function createTicket($user_id, $topic, $phone, $fio, $email, $description, $uploaded_files): int
    {
        $file_data = array_map(function($file) {
            return [
                'name' => $file['name'],
                'url' => $file['url']
            ];
        }, $uploaded_files);

        $data_post = [
            'subject_id' => $topic,
            'client_id' => $user_id,
            'status_id' => 1,
            'chanel_id' => 2,
            'company_id' => 1,
            'description' => $description,
            'is_repeat' => 0,
            'data' => json_encode([
                'phone' => $phone,
                'fio' => $fio,
                'email' => $email,
                'attached_files' => $file_data,
            ]),
            'created_at' => date('Y-m-d h:i:s'),
        ];

        $query = $this->db->placehold("INSERT INTO __mytickets SET ?%", $data_post);

        $this->db->query($query);
        return $this->db->insert_id();
    }

    private function sendEmail($ticketId, $fio, $email, $phone, $text, $subject, $uploaded_files): bool
    {
        try {
            $topic = $this->getTopicById($subject);

            $body = "<strong>Новое обращение #". $ticketId ." в категории " . $topic->name . "</strong><br>
                            <strong>Клиент:</strong> $fio<br>
                            <strong>Почта:</strong> $email<br>
                            <strong>Телефон:</strong> $phone<br>
                            <strong>Текст обращения:</strong> $text";
            
            $phpMailer = new PHPMailer();
            $phpMailer->SMTPDebug = 0;
            $phpMailer->isSMTP();
            $phpMailer->Host = 'smtp.mail.ru';
            $phpMailer->SMTPAuth = true;
            $phpMailer->Username = $this->config->mail_mp_user_name;
            $phpMailer->Password = $this->config->mail_mp_password;
            $phpMailer->SMTPSecure = 'ssl';
            $phpMailer->Port = 465;

            $phpMailer->addAddress('info@boostra.ru');
            $phpMailer->CharSet = 'UTF-8';

            $phpMailer->setFrom($this->config->mail_mp_user_name, 'Boostra Mail Agent');
            $phpMailer->Subject = 'Новое обращение #'. $ticketId .' в категории ' . $topic->name;
            $phpMailer->isHTML(true);
            $phpMailer->Body = $body;

            // Добавляем вложения к письму
            foreach ($uploaded_files as $file) {
                $phpMailer->addAttachment($file['path'], $file['name']);
            }

            return $phpMailer->send();
        } catch (Exception $e) {
            return false;
        }
    }

    public function getTopics()
    {
        $this->db->query('SELECT * FROM __mytickets_subjects');

        return $this->db->results();
    }
    
    public function getTopicById($id)
    {
        $this->db->query('SELECT * FROM __mytickets_subjects WHERE id = ?', $id);

        return $this->db->result();
    }

    /**
     * Получение тикетов клиента по ID клиента
     *
     * @param int $clientId ID клиента
     * @return array Массив тикетов клиента
     */
    public function getClientTickets(int $clientId): array
    {
        $query = $this->db->placehold("
            SELECT 
                tick.id,
                tick.created_at,
                tick.status_id,
                stat.name AS status_name,
                tick.subject_id,
                subj.name AS subject_name,
                tick.manager_id,
                man.name AS manager_name,
                tick.notify_user
            FROM __mytickets AS tick
            LEFT JOIN __mytickets_statuses AS stat ON stat.id = tick.status_id
            LEFT JOIN __mytickets_subjects AS subj ON subj.id = tick.subject_id
            LEFT JOIN __managers AS man ON man.id = tick.manager_id
            WHERE tick.client_id = ?
            ORDER BY tick.created_at DESC
        ", $clientId);

        $this->db->query($query);

        return $this->db->results() ?: [];
    }
}
