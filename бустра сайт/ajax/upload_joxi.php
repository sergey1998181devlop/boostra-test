<?php

use api\traits\FileUploadConversionTrait;

error_reporting(0);
session_start();
require_once('../api/Simpla.php');
$simpla = new Simpla();


class UploadApp extends Simpla
{
    use FileUploadConversionTrait;
    private $response;
    private $user;

    private $max_file_size = 15 * 1024 * 1024;
    private $pdf_max_pages = 2;
    private $pdf_dpi = 300;
    private $jpeg_quality = 85;
    private $pdftoppm_timeout_seconds = 20;
    private $last_conversion_error = '';
    private $allowed_mime_types = [
        'png'  => ['image/png'],
        'jpg'  => ['image/jpeg', 'image/pjpeg'],
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'jp2'  => ['image/jp2', 'image/jpeg2000', 'image/jpx', 'image/jpm', 'application/octet-stream'],
        'pdf'  => ['application/pdf'],
        'heic' => ['image/heic', 'image/heif', 'application/octet-stream'],
        'heif' => ['image/heif', 'image/heic', 'application/octet-stream'],
    ];

    private $allowed_extensions = [
        'png',
        'jpg',
        'jpeg',
        'jp2',
        'pdf',
        'heic',
        'heif',
    ];
    private $convert_to_jpeg_extensions = [
        'pdf',
        'heic',
        'heif',
    ];

    public function __construct()
    {
        $this->response = new StdClass();

        $this->run();

        $this->output();
    }

    public function run()
    {
        //var_dump($this->request->post('token'));
        //var_dump($this->request->files('file'));
        if (!empty($this->request->post('user_id')))
            $this->user = $this->users->get_user((int)$this->request->post('user_id'));

        if ('123ighdfgys_dfgd_1' !== $this->request->post('token', 'string'))
        {
            $this->response->error = 'unknown_token';
        }
        elseif (empty($this->user))
        {
            $this->response->error = 'unknown_user';
        }
        else
        {

            switch ($this->request->post('action', 'string')) :

                case 'add':
                    $this->add();
                    break;

                case 'remove':
                    $this->remove();
                    break;

                case 'update':
                    $this->update();
                    break;

                default:
                    $this->response->error = 'undefined action';

            endswitch;

        }
    }

    private function add()
    {
        if(isset($_FILES['file_upload'])
            && $_FILES['file_upload']['error'] != UPLOAD_ERR_NO_FILE
            && $type = $this->request->post('type', 'string')) {
            $file_name = $_FILES['file_upload']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if(in_array($file_ext, $this->allowed_extensions)) {
                $detected_mime = $this->detectMimeType((string)$_FILES['file_upload']['tmp_name']);
                if (!$this->isAllowedMimeType($file_ext, $detected_mime)) {
                    $this->response->error = "С вашим файлом что-то не так, попробуйте другой";
                    return;
                }
                $needs_conversion = in_array($file_ext, $this->convert_to_jpeg_extensions, true);
                $target_ext = $needs_conversion ? 'jpg' : $file_ext;
                do {
                    $new_filename = md5(microtime().rand()).'.'.$target_ext;
                } while ($this->users->check_filename($new_filename));
                $type = $type == 'выберите тип...' ? 'passport' : $type;
                $file_local_path = $this->config->root_dir . $this->config->original_images_dir . $new_filename;
                if ($needs_conversion) {
                    $this->last_conversion_error = '';
                    $file_uploaded = $this->convertToJpeg($_FILES['file_upload']['tmp_name'], $file_local_path, $file_ext);
                } else {
                    $file_uploaded = move_uploaded_file($_FILES['file_upload']['tmp_name'], $file_local_path);
                }
                if($file_uploaded) {
                    $this->response->filename = $this->design->resize_modifier($new_filename, 100, 100);
                    $this->response->id = $this->users->add_file([
                        'user_id' => $this->user->id,
                        'name' => $new_filename,
                        'type' => $type,
                        'status' => 0,
                        'visible' => 0
                    ]);

                    $returned = $this->notify->soap_send_files($this->user->id, false, false);
                    if ($returned->return == 'OK') {
                        $files = $this->users->get_files(['user_id' => $this->user->id, 'status' => 0], false);
                        foreach ($files as $file) {
                            $this->users->update_file($file->id, ['status' => 1]);

                            // удаляем оригинальные файлы, оставляем только ресайзы
                            if (file_exists($this->config->root_dir . $this->config->original_images_dir . $file->name))
                                unlink($this->config->root_dir . $this->config->original_images_dir . $file->name);
                        }
                    }

                    //теперь юиды не требуется получать из 1с
                    $uid_images = $this->users->get_files(['user_id' => $this->user->id]);
                    if (!empty($uid_images)) {
                        foreach ($uid_images as $uid_image) {
                            //теперь юиды не требуется получать из 1с
                            $this->filestorage->load_file($uid_image->storage_uid);
                        }
                    }
                } else {
                    $this->response->error = $needs_conversion ? ($this->last_conversion_error ?: 'Ошибка конвертации файла') : 'Ошибка загрузки файла';
                }
            }
        }
    }

    private function remove()
    {
        if ($id = $this->request->post('id', 'integer'))
        {
            $this->notify->soap_delete_file($id, 1);

            $this->users->delete_file($id);

            $this->response->success = 'removed';

        }
        else
        {
            $this->response->error = 'empty_file_id';
        }
    }

    private function update()
    {
        if ($id = $this->request->post('file_id', 'integer'))
        {
            $type = $this->request->post('type');

            $this->users->update_file($id, ['type' => $type]);

            //$this->users->delete_file($id);

            $this->response->success = 'updated';

        }
        else
        {
            $this->response->error = 'empty_file_id';
        }
    }

    private function output()
    {
        //header("Content-type: application/json; charset=UTF-8");
        //header("Cache-Control: must-revalidate");
        //header("Pragma: no-cache");
        //header("Expires: -1");

        if ($this->request->post('order_id')) {
            header('Location: http://manager.boostra.ru/order/' . $this->request->post('order_id'));
            //echo json_encode($this->response);
            exit();
        } else {
            header('Location: http://manager.boostra.ru/client/' . $this->request->post('user_id'));
            //echo json_encode($this->response);
            exit();
        }

    }

}


new UploadApp();

