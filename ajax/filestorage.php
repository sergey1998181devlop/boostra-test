<?php

use api\services\FileStorageService;

error_reporting(-1);
ini_set('display_errors', 'On');

header('Access-Control-Allow-Origin:*');

chdir('..');

require_once 'api/Simpla.php';

class FilestorageAjax extends Simpla
{
    private $response = array(
        'files' => array(),
    );
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();

        $this->output();
    }

    private function run()
    {
        if ($user_id = $this->request->get('user_id', 'integer')) {
            if ($files = $this->users->get_files(compact('user_id'))) {
                if ($this->validateFiles($files)) {
                    foreach ($files as $file) {
                        $file_local_path = $this->filestorage->getFilePath($file->name);
                        $file_exists = file_exists($file_local_path);

                        if (!$file_exists) {
                            if (!$this->downLoadImage($file)) {
                                continue;
                            }
                        }

                        $file->exists = file_exists($file_local_path);
                        $file->url = $this->filestorage->getFileUrl($file->name);
                        $this->response['files'][] = $file;
                    }
                } else {
                    $this->getPhotoFrom1C($user_id);
                }
            }
        } else {
            $this->response['error'] = 'EMPTY_USER_ID';
        }
    }

    /**
     * Проверяем на существования файлов без данных старого хранилища и нового
     * @param array $files
     * @return bool
     */
    private function validateFiles(array $files): bool
    {
        $badFiles = array_filter($files, function ($file) {
           return empty($file->s3_name);
        });

        return empty($badFiles);
    }

    /**
     * Старая логика для загрузки фото
     * @param int $user_id
     * @return void
     */
    private function getPhotoFrom1C(int $user_id)
    {
        $user_uid = $this->users->get_user_uid($user_id);
        if (!empty($user_uid->uid)) {
            $uid_images = $this->soap->get_uid_images($user_uid->uid);
            $this->response['uid_images'] = [];
            if (!empty($uid_images)) {
                foreach ($uid_images as $uid_image) {
                    $uid_image->storage_uid = str_replace('"', '', $uid_image->UID);
                    $uid_image->s3_name = $uid_image->S3Name;
                    if (!$this->downLoadImage($uid_image)) {
                        continue;
                    }
                    $this->response['uid_images'][] = $uid_image;
                }
            }
        } else {
            $this->response['success'] = 'UID NOT FOUND';
        }

        $this->check_files($user_id);
    }

    /**
     * @param $file_image
     * @return bool
     */
    private function downLoadImage($file_image): bool
    {
        try {
            if ($file_image->s3_name) {
                $file_local_path = $this->filestorage->getFilePath(basename($file_image->name ?: $file_image->s3_name));

                $fileStorage = new FileStorageService(
                    $this->config->s3['endpoint'],
                    $this->config->s3['region'],
                    $this->config->s3['key'],
                    $this->config->s3['secret'],
                    $this->config->s3['Bucket']
                );

                $s3_file = $fileStorage->getFile($file_image->s3_name);
                file_put_contents($file_local_path, $s3_file->getContents());
            } else {
                $this->filestorage->load_file($file_image->storage_uid, $file_image->s3_name);
            }
            return true;
        }  catch(Exception $e) {
            return false;
        }
    }

    private function check_files($user_id)
    {
        if ($files = $this->users->get_files(['user_id' => $user_id]))
        {
            foreach ($files as $file)
            {
                $file->exists = file_exists($this->filestorage->getFilePath($file->name));
                $file->url = $this->filestorage->getFileUrl($file->name);
                $this->response['files'][] = $file;
            }
        }
    }

    private function output()
    {
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");		
    
        echo json_encode($this->response);
    }
    
}
new FilestorageAjax();