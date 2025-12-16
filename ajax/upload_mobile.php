<?php

use api\services\FileStorageService;

session_start();
require_once('../api/Simpla.php');
$simpla = new Simpla();


class UploadApp extends Simpla
{
    private $response;
    private $user;
    
    private $max_file_size = 10485760;
    
    private $allowed_extensions = array(
        'png',
        'jpg',
        'jpeg',
        'jp2',
    );
    
    public function __construct()
    {
        $this->response = new StdClass();
        
        $this->run();
        
        $this->output();
    }
    
    public function run()
    {
        $user_id = $this->request->post('user_id', 'string');

        if (!empty($user_id))
            $this->user = $this->users->get_user((int)$user_id);
        

        if ('123ighdfgys_dfgd' != $this->request->post('token'))
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
                
                default:
                    $this->response->error = 'undefined action';
                
            endswitch;
            
        }
    }
    
    private function add()
    {
    	if ($file =$this->request->files('file'))
        {
            if ($type = $this->request->post('type', 'string'))
            {
                if ($this->max_file_size > $file['size'])
                {
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, $this->allowed_extensions))
                    {
                        do {
          		            $new_filename = md5(microtime().rand()).'.'.$ext;
          		        } while ($this->users->check_filename($new_filename));

                        $file_local_path = $this->config->root_dir . $this->config->original_images_dir . $new_filename;
                        $file_uploaded = move_uploaded_file($file['tmp_name'], $file_local_path);
                        if( ! $file_uploaded ){
                            $this->response->error = 'error_uploading';

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
                    else
                    {
                        $this->response->error = 'extension';
                        $this->response->allowed_extensions = $this->allowed_extensions;                        
                    }
                }
                else
                {
                    $this->response->error = 'max_file_size';
                    $this->response->max_file_size = $this->max_file_size;
                }
            }
            else
            {
                $this->response->error = 'empty_type';
            }

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($file);echo '</pre><hr />';            
        }
        else
        {
            $this->response->error = 'empty_file';
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

