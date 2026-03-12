<?php

use api\services\FileStorageService;
use api\traits\FileUploadConversionTrait;

header('Access-Control-Allow-Origin: *');
//ini_set('display_errors', 'off');
session_start();
require_once('../api/Simpla.php');

ini_set( 'max_execution_time', 120);

class UploadApp extends Simpla
{
    use FileUploadConversionTrait;
    private $response;
    private $user;
    private $action;

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
        $this->action   = $this->request->post( 'action', 'string' );

        $this->run();
        $this->output();
    }

    public function run()
    {
        $this->user = ! empty( $_SESSION['user_id'] )
            ? $this->users->get_user( (int)$_SESSION['user_id'] )
            : $this->user;

        if( empty( $this->user ) ){
            $this->response->error = 'unknown_user';

            return;
        }

        $this->setLoggerState($this->action, $this->config->root_url . '/ajax/upload.php', [$_SESSION['user_id'], $this->request->post( 'type')], 'add_ajax_upload_files.txt');

        switch( $this->action ) :

            case 'add':
                $this->add();
                return;

            case 'remove':
                $this->remove();
                return;

            default:
                $this->response->action_after = $this->action;

        endswitch;
    }

    private function add()
    {
        // Проверяем наличие файлов
        $file = $this->request->files( 'file' );
        if( ! $file ){
            $this->response->error = 'Нет файлов для загрузки';

            return;
        }

        // Проверяем тип файла
        $type = $this->request->post( 'type', 'string' );
        if( ! $type ){
            $this->response->error = 'Не указан тип файла';

            return;
        }

        // Проверяем расширение файла
        $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
        if( ! in_array( $ext, $this->allowed_extensions ) ){
            $this->response->error              = 'Не верное расширение файла';
            $this->response->allowed_extensions = $this->allowed_extensions;

            return;
        }

        $detected_mime = $this->detectMimeType((string)$file['tmp_name']);
        if (!$this->isAllowedMimeType($ext, $detected_mime)) {
            $this->response->error = "С вашим файлом что-то не так, попробуйте другой";
            return;
        }

        // Проверяем размер файла
        if( $this->max_file_size < $file['size'] ){
            $this->response->error         = 'Превышен размер файла';
            $this->response->max_file_size = $this->max_file_size;

            return;
        }

        $needs_conversion = in_array( $ext, $this->convert_to_jpeg_extensions, true );
        $target_ext = $needs_conversion ? 'jpg' : $ext;

        do{
            $new_filename = md5( microtime() . rand() ) . '.' . $target_ext;
        }while( $this->users->check_filename( $new_filename ) );

        // Проверяем сохраняем в папку загрузки
        $file_local_path = $this->config->root_dir . $this->config->original_images_dir . $new_filename;
        if ( $needs_conversion ) {
            $this->last_conversion_error = '';
            $file_uploaded = $this->convertToJpeg( $file['tmp_name'], $file_local_path, $ext );
        } else {
            $file_uploaded = move_uploaded_file( $file['tmp_name'], $file_local_path );
        }
        if( ! $file_uploaded ){
            $this->response->error = $needs_conversion ? ($this->last_conversion_error ?: 'Ошибка конвертации файла') : 'Ошибка загрузки файла';

            return;
        }

        $this->response->filename = $this->design->resize_modifier( $new_filename, 100, 100 );
        $this->response->name = $new_filename;

        file_get_contents( $this->response->filename );

        $s3_name = 'S3/' . date('Ymd') . '/' . $new_filename;

        $fileStorage = new FileStorageService(
            $this->config->s3['endpoint'],
            $this->config->s3['region'],
            $this->config->s3['key'],
            $this->config->s3['secret'],
            $this->config->s3['Bucket']
        );
        $fileStorage->putFile($file_local_path, $s3_name);

        $this->response->id = $this->users->add_file( [
            'user_id' => $this->user->id,
            'name'    => $new_filename,
            'type'    => $type,
            'status'  => 0,
            's3_name' => $s3_name,
        ] );

        $returned = $this->soap->soap_send_files( $this->user->id );

        if( $returned->return == 'OK' ){
            $this->users->update_file( $this->response->id, [ 'status' => 1 ] );
        }

        $this->response->success = 'added';

        $files = $this->users->get_files( [ 'user_id' => $this->user->id ] );
        if( $files && count( $files ) > 1 ){
            $this->users->update_user( $this->user->id, [ 'file_uploaded' => 1 ] );
        }

        // Логируем добавление нового фото в лк
        if ($type === 'passport') {
            $this->changelogs->add_changelog(
                [
                    'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'new_photo',
                    'old_values' => '',
                    'new_values' => serialize($file),
                    'user_id' => $this->user->id,
                ]
            );
        }

        $last_order = $this->orders->get_last_order($this->user->id);
        if ($last_order->status == $this->orders::ORDER_STATUS_CRM_CORRECTION) {
            $this->orders->update_order($last_order->id, ['status' => $this->orders::ORDER_STATUS_CRM_CORRECTED]);
        }
    }

    private function remove()
    {
        $id = $this->request->post( 'id', 'integer' );

        if( ! $id ){
            $this->response->error = 'empty_file_id';
        }

        $file = $this->users->get_file($id);
        if ($file->s3_name) {
            $fileStorage = new FileStorageService(
                $this->config->s3['endpoint'],
                $this->config->s3['region'],
                $this->config->s3['key'],
                $this->config->s3['secret'],
                $this->config->s3['Bucket']
            );
            $fileStorage->deleteFile($file->s3_name);
        }

        $this->notify->soap_delete_file( $id, 1 );
        $this->users->delete_file( $id );

        $this->response->success = 'removed';
    }

    private function output()
    {
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");

        echo json_encode($this->response);
        exit();
    }
}

new UploadApp();