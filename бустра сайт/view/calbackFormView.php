<?php

require_once('View.php');

class calbackFormView extends View {

    function fetch() {
        $file = false;
        $telephone = $this->curl->preparePhone($this->request->post('telephone'));
        $from = $this->request->post('email');
        $them = $this->request->post('them');
        $to = 'info@boostra.ru';
        $text = $this->request->post('text');
        $them = $this->request->post('nameThem');

        $birthday = $this->request->post('birthday');
        $fio = $this->request->post('fio');

        if ($telephone) {
            $addString = '<div>Телефон отправителя : +' . $telephone . '</div>';
        }
        if ($from) {
            $addString .= '<div>Email отправителя : ' . $from . '</div>';
        }

        if (!empty($birthday)) {
            $addString .= '<div>Дата рождения отправителя: ' . $birthday . '</div>';
        }

        if (!empty($fio)) {
            $addString .= '<div>ФИО отправителя: ' . $fio . '</div>';
        }

        if ($this->request->files('file')) {
            $fileInfo = $this->request->files('file');
            $dir = $this->config->root_dir . $this->config->mail_temp_dir;
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            move_uploaded_file($fileInfo['tmp_name'], $dir . $fileInfo['name']);
            $file[] = $dir . $fileInfo['name'];
            $text .= '<div>' . $this->setFileType($this->config->mail_temp_dir, $fileInfo['name']) . '</div>';
        }
        /**
        if ($file) {
            $this->notify->email($to, $them, $text . $addString, $from, $from, $file);
            $this->notify->email('kargina@akticom.ru', $them, $text . $addString . " (Копия)", $from, $from, $file);
        } else {
            $this->notify->email($to, $them, $text . $addString, $from, $from);
            $this->notify->email('kargina@akticom.ru', $them, $text . $addString . " (Копия)", $from, $from);
        }
        */

        if ($file) {
            $this->notify->email('info@boostra.ru', $them, $text . $addString, $from, '', $file);
            $this->notify->email('kargina@akticom.ru', $them, $text . $addString . " (Копия)", $from, '', $file);
        } else {
            $this->notify->email('info@boostra.ru', $them, $text . $addString, $from, '');
            $this->notify->email('kargina@akticom.ru', $them, $text . $addString . " (Копия)", $from, '');
        }

        $data = [
            'AppealDate' => date("Y-m-d H:i:s"),
            'Text' => $text,
            'Them' => $them,
            'Phone' => $telephone,
            'Email' => $from,
            'ToEmail' => $to
        ];
        $appeals = $this->appeals->addAppeal($data);
        $userMessage = '<p></p><h1>Ваше обращение успешно отправлено.</h1> ';

        $managerId = $this->managers->getManagerIdForTask($them);
        if (!$managerId) {
            $managerId = $this->managers->getManagerIdForTaskByRole('yurist');
        }
        $userId = $this->users->get_phone_user($telephone);
        if (!$userId) {
            $userId = $userId = $this->users->getUserIdByEmail($from);
        }

        if ($them === 'insur@boostra.ru') {
            $taskType = 10;
        } elseif ($them === 'lk@boostra.ru') {
            $taskType = 9;
        } else {
            $taskType = 10;
        }

        $taskData = [
            'ticketId' => (int) $appeals,
            'managerId' => (int) $managerId,
            'userId' => (int) $userId,
            'taskType' => (int) $taskType,
            'taskStatus' => 0,
            'taskDate' => date("Y-m-d 00:00:00", time()),
            'dateCreate' => date("Y-m-d H:i:s"),
            'dateComplition' => date("Y-m-d H:i:s", time() + (60 * 60 * 24 * 14)),
            'dateEdit' => date("Y-m-d H:i:s"),
            'inputChanel' => 'Email',
        ];
        $this->tasks->addMyTask($taskData);
        $this->design->assign('message', strip_tags($userMessage));
        $this->design->assign('lastPage', strip_tags($_SERVER['HTTP_REFERER']));
        return $this->design->fetch('calbackPage.tpl');
    }

    private $imageArray = [
        'jpeg', 'jpg', 'png', 'gif', 'svg'
    ];
    private $videoArray = [
        'avi', 'mkv', 'mov', 'fvl', 'vob', '3gp', 'mp4',
    ];

    public function setFileType($patch, $fileName) {
        $ext = [false, false];
        $style = 'style="height: 120px; width: auto;"';
        $frontUrl = $this->config->front_url;
        if (is_file(ROOT . DIRECTORY_SEPARATOR . $patch . $fileName)) {
            preg_match('/\.(\w{3,5})$/ui', $fileName, $ext);
            if (in_array(strtolower($ext[1]), $this->imageArray)) {
                return '<img onclick="appeals.fullImg(this);" ' . $style . ' src="' . $frontUrl . $patch . $fileName . '"/>';
            } elseif (in_array(strtolower($ext[1]), $this->videoArray)) {
                return '<video ' . $style . ' src="' . $frontUrl . $patch . $fileName . '"/>';
            } else {
                return '<a target="_blank" href="' . $frontUrl . $patch . $fileName . '">Скачать файл</a>';
            }
        }
        return false;
    }

}
