<?php

use api\services\FileStorageService;

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', -1);
ini_set('memory_limit', '2G');

chdir(dirname(__FILE__).'/../');
require_once 'api/Simpla.php';

class MoveFileToS3 extends Simpla
{
    private $offset = 0;

    public function run()
    {
        do {
            $images = $this->getImages($this->getLastImageId());

            foreach ($images as $image) {
                $image_name = $image->name;
                $file_local_path = $this->filestorage->getFilePath($image_name);

                if (file_exists($file_local_path)) {
                    $fileStorage = new FileStorageService(
                        $this->config->s3['endpoint'],
                        $this->config->s3['region'],
                        $this->config->s3['key'],
                        $this->config->s3['secret'],
                        $this->config->s3['Bucket']
                    );

                    $file_local_path = $this->filestorage->getFilePath($image_name);

                    $s3_name = 'S3/' . date('Ymd') . '/' . $image_name;
                    $fileStorage->putFile($file_local_path, $s3_name);
                    $this->users->update_file($image->id, ['s3_name' => $s3_name]);
                }
            }

            $last_image = end($images);
            $this->setLastImageId($last_image->id);

            $this->offset+= 1000;

            $images = $this->getImages($last_image->id);
        } while (!empty($images));
    }

    public function setLastImageId(int $id)
    {
        $this->settings->last_image_id = $id;
    }

    public function getLastImageId()
    {
       return $this->settings->last_image_id ?: 1;
    }

    public function getImages(int $id)
    {
        $query = $this->db->placehold("SELECT * FROM s_files WHERE id  > ? AND (s3_name IS NULL OR s3_name = '') ORDER BY id ASC LIMIT 1000 OFFSET ?", $id, $this->offset);
        $this->db->query($query);
        return $this->db->results();
    }
}

(new MoveFileToS3())->run();

