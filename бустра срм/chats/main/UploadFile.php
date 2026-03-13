<?php

namespace chats\main;

use Simpla;

class UploadFile {

    private static $uploadDir = uploadDir . 'sent' . DIRECTORY_SEPARATOR;

    public static function uploadDocument() {
        $uploadDir = self::$uploadDir . 'Documents' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir);
        }
        return self::upload('document', $uploadDir);
    }

    public static function uploadVideo() {
        $uploadDir = self::$uploadDir . 'Videos' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir);
        }
        return self::upload('video', $uploadDir);
    }

    public static function uploadImage() {
        $uploadDir = self::$uploadDir . 'Images' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir);
        }
        return self::upload('image', $uploadDir);
    }

    private static function upload(string $type, string $dir) {
        $error = self::setErrors($type);
        $match = false;
        if (isset($_FILES)) {
            if (!empty($_FILES[$type]['name']) AND!$_FILES[$type]['error']) {
                preg_match('/(?<mimeFile>\w{3,5})$/iu', $_FILES[$type]['name'], $match);
                if ($match['mimeFile']) {
                    $ext = $match['mimeFile'];
                    $fileName = md5($_FILES[$type]['name'] . $_FILES[$type]['type'] . $_FILES[$type]['size']) . '.' . $ext;
                    $file = $dir . $fileName;
                    $obj = new Simpla;
                    $data = (object) [
                                'type' => $type,
                                'ext' => $ext,
                                'name' => $fileName,
                                'putch' => $file,
                                'url' => str_replace(ROOT, $obj->config->back_url, $file),
                                'error' => false
                    ];
                    if (move_uploaded_file($_FILES[$type]['tmp_name'], $file)) {
                        $result = 'Файл успешно загружен';
                    } else {
                        $error = self::setErrors($type);
                    }
                } else {
                    $error = 'Не удалось определить MIME тип файла';
                    $data = (object) [
                                'error' => true
                    ];
                }
            } else {
                $result = "Файл не загружен";
                $data = (object) [
                            'error' => true
                ];
            }
        } else {
            $error = 'Не выбран файл для загрузки. ';
            $data = (object) [
                        'error' => true
            ];
        }
        if ($error) {
            return (object) ['Data' => $data, 'result' => $error];
        } else {
            return (object) ['Data' => $data, 'result' => $result];
        }
    }

    private static function setErrors($type) {
        $error = false;
        if ($_FILES[$type]['error'] == 1 OR $_FILES[$type]['error'] == 2) {
            $error = 'Размер файла превысил максимально разрешеный размер загрузок, установленные настройками сервера';
        } elseif ($_FILES[$type]['error'] == 3) {
            $error = 'При загрузке файла возникли ошибки. Повторите еще раз';
        } elseif ($_FILES[$type]['error'] == 4) {
            $error = 'Не выбран файл для загрузки. ';
        } elseif ($_FILES[$type]['error'] == 6) {
            $error = 'Отсутствует временная папки для загрузки на сервере. Обратитесь к администратору сервера.';
        } elseif ($_FILES[$type]['error'] == 7) {
            $error = 'При загрузке файла был получен ответ от сервера "Отказано в доступе". Обратитесь к администратору сервера.';
        } elseif ($_FILES[$type]['error'] == 8) {
            $error = 'При загрузке файла был получен ответ от сервера "Отказано в доступе". Обратитесь к администратору сервера.';
        }
        return $error;
    }

}
