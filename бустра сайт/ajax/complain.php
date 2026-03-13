<?php
error_reporting(-1);
ini_set('display_errors', 'On');

session_start();
chdir('..');
require_once( 'vendor/autoload.php');
require_once 'api/Simpla.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Class ComplainAjax
 * Обработка формы с жалобой
 */
class ComplainAjax extends Simpla
{
    /**
     * Макс размер 1 файла 5 мб
     */
    public const FILE_MAX_SIZE = 15 * 1024 * 1024;
    public const RECIPIENT_EMAIL = 'info@boostra.ru';
    public const ACCESS_EXTENSIONS = [
      'doc', 'docx', 'pdf', 'txt', 'jpeg', 'jpg', 'png', 'zip'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    public function run()
    {
        $response = [];
        $data = [];

        $data['user_name'] = $this->request->post('user_name');
        $data['user_email'] = $this->request->post('user_email');
        $data['user_contract'] = $this->request->post('user_contract');
        $data['user_message'] = $this->request->post('user_message');
        $data['user_files'] = $this->request->files('user_files');

        $errors = $this->validate($data);

        if (!empty($errors)) {
            $response['errors'] = $errors;
        } else {
            $insert_data = $data;
            $insert_data['user_id'] = $_SESSION['user_id'] ?? null;
            unset($insert_data['user_files']);

            if($complain_id = $this->addComplain($insert_data)) {
                if (!empty($data['user_files'])) {
                    $data['files'] = $this->saveFiles((int)$complain_id, $data['user_files']);
                    $update_data = [
                        'files' => serialize($data['files'] )
                    ];
                    $this->updateComplain((int)$complain_id, $update_data);
                }

                $response['success'] = $this->sendMail($data);
                $response['message'] = $this->design->fetch('complain/complain_success.tpl');
            }
        }

        $this->request->json_output($response);
    }

    /**
     * Валидация формы
     * @param $data
     * @return array
     */
    private function validate($data): array
    {
        $errors = [];

        if (empty($data['user_name'])) {
            $errors['user_name'] = 'Заполните поле Имя';
        }

        if (empty($data['user_email'])) {
            $errors['user_email'] = 'Заполните поле Email';
        }

        if (empty($data['user_message'])) {
            $errors['user_message'] = 'Заполните поле Сообщение';
        }

        if (!filter_var($data['user_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['user_email'] = 'Поле Email заполнено неверно';
        }

        if (!empty($data['user_files'])) {
            $find_max_size = array_filter($data['user_files']['size'], function ($size){
                return $size > self::FILE_MAX_SIZE;
            });

            $find_extension = array_filter($data['user_files']['tmp_name'], function ($file_name){
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                return in_array($ext, self::ACCESS_EXTENSIONS);
            });

            if (!empty($find_max_size) || !empty($find_extension)) {
                $errors['user_files'] = 'Один из файлов имеет ограничение';
            }
        }

        return $errors;
    }

    /**
     * Добавляет запись в БД
     * @param $data
     * @return mixed
     */
    private function addComplain($data)
    {
        $query = $this->db->placehold("INSERT INTO s_complain_form SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновляет запись в БД
     * @param int $id
     * @param array $data
     * @return mixed
     */
    private function updateComplain(int $id, array $data)
    {
        $query = $this->db->placehold("UPDATE s_complain_form SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * Сохраняет файлы на сервер
     * @param int $id
     * @param array $files
     * @return array
     */
    private function saveFiles(int $id, array $files): array
    {
        $dir_path = $this->config->root_dir . 'files/uploads/complain/' . $id;

        if (!file_exists($dir_path)) {
            mkdir($dir_path, 0777, true);
        }

        $files_list = [];
        $total = count($files['name']);

        for ($i = 0; $i < $total; $i++) {
            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            $original_name =  pathinfo($files['name'][$i], PATHINFO_BASENAME);
            $new_filename = md5(microtime().rand()).'.'.$ext;
            if (move_uploaded_file($files['tmp_name'][$i], $dir_path . '/' .$new_filename))
            {
                $files_list[] = [
                    'original_name' => $original_name,
                    'file_name' => $new_filename,
                    'full_path' => $dir_path . '/' .$new_filename,
                ];
            }
        }

        return $files_list;
    }

    /**
     * Отправка писем
     * @param array $user_data
     * @return bool
     * @throws Exception
     */
    private function sendMail(array $user_data)
    {
        $this->design->assign('user_data', $user_data);
        $body_text = $this->design->fetch('complain/complain_mail.tpl');

        $email = new PHPMailer();
        $email->SetFrom('no-reply@boostra.ru', 'Boostra Mail Agent');
        $email->Subject   = 'Форма обратной связи (Жалобы)';
        $email->isHTML(true);
        $email->Body      = $body_text;
        $email->AddAddress(self::RECIPIENT_EMAIL);
        $email->AddCC($user_data['user_email'], $user_data['user_name']);
        $email->CharSet = 'UTF-8';

        if (!empty($user_data['files'])) {
            foreach ($user_data['files'] as $file) {
                $email->AddAttachment($file['full_path'] , $file['original_name']);
            }
        }

        return $email->Send();
    }
}

(new ComplainAjax())->run();
