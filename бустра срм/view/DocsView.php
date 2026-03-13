<?php

require_once 'View.php';

class DocsView extends View
{

    private $allowed_extensions = array(
        'zip',
        'rar',
        'txt',
        'csv',
        'pdf',
        'xls',
        'xlsx',
        'odt',
        'doc',
        'rtf',
        'docx',
        'png',
        'gif',
        'jpeg',
        'jpg',
    );

    public function fetch()
    {

        if (!$this->docs->can_view_docs($this->manager)) {
            header('Location:/');
            exit();
        }

        $method = $this->request->get('method', 'string');
        if (!empty($method) && method_exists($this, $method)) {
            return $this->$method();
        }

        if ($this->request->method('post')) {
            switch ($this->request->post('action', 'string')):

                case 'add':
                    return $this->addDoc();

                case 'delete':
                    return $this->deleteDoc();

                case 'update':
                    return $this->updateDoc();

                case 'update_positions':
                    return $this->updatePositions();

                case 'update_visibility':
                    return $this->updateVisibility();

            endswitch;
        }

        $docs = $this->docs->get_docs();
        $this->design->assign('docs', $docs);

        return $this->design->fetch('docs.tpl');
    }

    private function addDoc()
    {
        $name = trim($this->request->post('name'));
        $description = trim($this->request->post('description'));
        $file = $_FILES['file'];

        if (empty($name)) {
            $this->json_output(array('error' => 'Укажите название документа'));
        } elseif (empty($description)) {
            $this->json_output(array('error' => 'Укажите описание документа'));
        } elseif (empty($file)) {
            $this->json_output(array('error' => 'Выберите файл для загрузки'));
        } else {
            $filename = $this->processFile($file);

            $doc = array(
                'name' => $name,
                'description' => $description,
                'filename' => $filename,
                'created' => date('Y-m-d H:i:s')
            );

            $id = $this->docs->add_doc($doc);

            $this->json_output(array(
                'id' => $id,
                'name' => $name,
                'filename' => $filename,
                'description' => $description,
                'success' => 'Документ добавлен'
            ));
        }
    }

    private function deleteDoc()
    {
        $id = $this->request->post('id', 'integer');

        $this->docs->delete_doc($id);

        $this->json_output(array(
            'id' => $id,
            'success' => 'Документ удален'
        ));
    }

    private function updateDoc()
    {
        $id = $this->request->post('id', 'integer');
        $newName = trim($this->request->post('name'));
        $newDescription = trim($this->request->post('description'));
        $newCreated = $this->request->post('created');

        if (empty($newName)) {
            $this->json_output(array('error' => 'Укажите новое название документа'));
        } elseif (empty($newDescription)) {
            $this->json_output(array('error' => 'Укажите новое описание документа'));
        } elseif (empty($newCreated)) {
            $this->json_output(array('error' => 'Укажите новое время создания'));
        } else {
            $doc = array(
                'name' => $newName,
                'description' => $newDescription,
                'created' => $newCreated
            );

            $this->docs->update_doc($id, $doc);

            $this->json_output(array(
                'id' => $id,
                'name' => $newName,
                'description' => $newDescription,
                'created' => $newCreated,
                'success' => 'Документ обновлен'
            ));
        }
    }

    private function updatePositions()
    {
        $positions = $this->request->post('positions');
        if (is_array($positions)) {
            $this->docs->update_positions($positions);
            $this->json_output(array('success' => 'Позиции документов обновлены'));
        } else {
            $this->json_output(array('error' => 'Неверный формат позиций'));
        }
    }

    private function updateVisibility()
    {
        if ($this->request->post('id') === null || $this->request->post('newState') === null) {
            $this->json_output(array('success' => false, 'error' => 'Не переданы обязательные параметры.'));
        }

        $id = $this->request->post('id', 'integer');
        $newState = $this->request->post('newState', 'integer');

        // Обновите запись в базе данных
        $result = $this->docs->update_visibility($id, $newState);

        if (!$result) {
            $this->json_output(array('success' => false, 'error' => 'Ошибка при обновлении базы данных.'));
        }

        $this->json_output(array('success' => true));
    }

    private function processFile($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log('Ошибка при загрузке файла: ' . $file['error']);
            throw new Exception('Ошибка при загрузке файла: ' . $file['error']);
        }

        // Проверка расширения файла
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowed_extensions)) {
            error_log('Недопустимый тип файла.');
            throw new Exception('Недопустимый тип файла.');
        }

        // Транслитерация имени файла
        $fname = strtolower(pathinfo($file['name'], PATHINFO_FILENAME));
        $fname = $this->translit($this->truncate($fname));
        $filename = $fname . '.' . $ext;

        $uploadUrl = $this->config->front_url.'/ajax/upload_docs.php';
        $filePath = $file['tmp_name'];
        $post = array(
            'file' => new CURLFile($filePath, $ext, $filename),
            'token' => '3f0a7d75a8cb5d4f3c5a76a22d55a4608a0c77a6b6e8d2c4b6d7a8e5a3f0e1a3',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uploadUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log('Curl error: ' . curl_error($ch));
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);

        if (!$result) {
            throw new Exception('Ошибка при сохранении файла.');
        }

        // Обработка ответа от Boostra и получение URL загруженного файла
        $response = json_decode($result, true);
        if (isset($response['success']) && $response['success'] && isset($response['url'])) {
            return $response['url'];
        } else {
            error_log('Ошибка при загрузке файла на сервер Boostra: ' . $result);
            throw new Exception('Ошибка при загрузке файла на сервер Boostra.');
        }
    }



    private function translit($text)
    {
        $ru = explode('-', "А-а-Б-б-В-в-Ґ-ґ-Г-г-Д-д-Е-е-Ё-ё-Є-є-Ж-ж-З-з-И-и-І-і-Ї-ї-Й-й-К-к-Л-л-М-м-Н-н-О-о-П-п-Р-р-С-с-Т-т-У-у-Ф-ф-Х-х-Ц-ц-Ч-ч-Ш-ш-Щ-щ-Ъ-ъ-Ы-ы-Ь-ь-Э-э-Ю-ю-Я-я");
        $en = explode('-', "A-a-B-b-V-v-G-g-G-g-D-d-E-e-E-e-E-e-ZH-zh-Z-z-I-i-I-i-I-i-J-j-K-k-L-l-M-m-N-n-O-o-P-p-R-r-S-s-T-t-U-u-F-f-H-h-TS-ts-CH-ch-SH-sh-SHCH-shch-Y-y-Y-y-Y-y-E-e-YU-yu-YA-ya");
        $res = str_replace($ru, $en, $text);
        $res = preg_replace("/[\s]+/ui", '-', $res);
        $res = preg_replace("/[^a-z0-9\-]+/ui", '', $res);
        $res = strtolower($res);
        return $res;
    }

    private function truncate($text, $chars = 100)
    {
        if (strlen($text) > $chars) {
            $text = $text . " ";
            $text = substr($text, 0, $chars);
            $text = substr($text, 0, strrpos($text, ' '));
            $text = $text . "...";
        }
        return $text;
    }

}

