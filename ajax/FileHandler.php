<?php

use api\services\FileStorageService;
use boostra\services\extraServices;

require_once './ajaxController.php';

/**
 * Contains validation rules and handlers
 */
class FileHandler extends ajaxController{
    
    /**
     * @var extraServices
     */
    private $type;
    private $id;
    private $cardPan;

    /**
     * @var mixed|null
     */

    public function actions(): array
    {
        return [
            'add' => [
                'type' => [ 'passport', 'passport1', 'passport2', 'passport3', 'passport4', ],
                'file' => 'file',
                'cardPan' => 'string'
            ],
            'remove' => [
                'id'      => 'integer',
            ],
        ];
    }
    
    /**
     * Init properties depends on input data
     *
     * @return void
     * @throws Exception
     */
    protected function init(): void
    {
        $this->type = $this->data['type'] ?? null;
        $this->id   = $this->data['id']   ?? null;
        $this->cardPan   = $this->data['cardPan']   ?? null;
    }
    
    /**
     * Handler for action === 'prepare_docs'
     *
     * @return array
     * @throws Exception
     */
    public function actionAdd(): array
    {
        $s3_name = 'S3/' . date('Ymd') . '/' . $this->new_filename;
        $this->setLoggerState(
            __METHOD__,
            $this->config->root_url . '/ajax/FileHandler.php',
            [
                $this->upload_file_path . $this->new_filename,
                $s3_name,
            ],
            'add_ajax_user_files.txt'
        );

        $fileStorage = new FileStorageService(
            $this->config->s3['endpoint'],
            $this->config->s3['region'],
            $this->config->s3['key'],
            $this->config->s3['secret'],
            $this->config->s3['Bucket']
        );
        $fileStorage->putFile($this->upload_file_path . $this->new_filename, $s3_name);

        $file_thumbnail = $this->design->resize_modifier( $this->new_filename, 100, 100 );
        $file_id        = $this->users->add_file( [
            'user_id' => $this->user->id,
            'name'    => $this->new_filename,
            'type'    => $this->type,
            'status'  => 0,
            's3_name' => $s3_name,
        ]);
        $returned = $this->soap->soap_send_files( $this->user->id );
        if( $returned->return == 'OK' ){
            $this->users->update_file( $file_id, [ 'status' => 1 ] );
        }
        
        $files = $this->users->get_files( [ 'user_id' => $this->user->id ] );
        if( $files && count( $files ) > 1 ){
            $this->users->update_user( $this->user->id, [ 'file_uploaded' => 1 ] );
        }
        if (empty($cardPan) && $this->type=="passport4") {
            $this->best2pay->update_card_file($this->user->id,$this->cardPan,$file_id);
        }
        return [
            'id'       => $file_id,
            'filename' => $file_thumbnail,
            'success'  => 'added',
        ];
    }
    
    public function actionRemove(): array
    {
        $file = $this->users->get_file((int)$this->id);
        $this->notify->soap_delete_file( $this->id );
        $this->users->delete_file( $this->id );

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

        return [
            'id' => $this->id,
            'success' => 'removed'
        ];
    }
}

new FileHandler;