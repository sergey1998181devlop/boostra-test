<?php

require_once 'View.php';

class UploadView extends View
{
    use \api\traits\JWTAuthTrait;

    private $max_file_size = 5242880;

    public function fetch()
    {
        $this->jwtAuthValidate();

        $this->show_unaccepted_agreement_modal();

        /**
         * Нажатие по кнопке "Отправить файлы на проверку" в ЛК - Мои файлы
         */
        if ($this->request->post('confirm'))
        {
            /*$returned = $this->notify->soap_send_files($this->user->id);
            if ($returned->return == 'OK')
            {*/
            $files = $this->users->get_files(
                [
                    'user_id' => $this->user->id,
                    'status' => 0
                ]
            );
            foreach ($files as $file) {
                $this->users->update_file($file->id, array('status' => 1));

                // удаляем оригинальные файлы, оставляем только ресайзы
//                    if (file_exists($this->config->root_dir.$this->config->original_images_dir.$file->name))
//                        unlink($this->config->root_dir.$this->config->original_images_dir.$file->name);

            }
            $this->users->update_user($this->user->id, array('file_uploaded' => 1));

            // меняем статус заявки на 6 исправлена
            $last_order = (array)$this->orders->get_last_order($this->user->id);

            if ($last_order['status'] == $this->orders::ORDER_STATUS_CRM_CORRECTION) {
                $this->orders->update_order($last_order['id'], ['status' => $this->orders::ORDER_STATUS_CRM_CORRECTED]);
            }

            header('Location: ' . $this->config->root_url . '/user/upload');
            /*}
            else
            {
                $this->design->assign('error', 'error_upload');
            }*/

            
        }
        
         /** ? Этот блок не выполняется ? 
        elseif ($this->request->method('post'))
        {
            $old_files = $this->users->get_files(array('user_id'=>$this->user->id));
  		    $user_files = (array)$this->request->post('user_files');
            foreach ($old_files as $old_file)
            {
                if (!in_array($old_file->id, $user_files))
                    $this->users->delete_file($old_file->id);
            }
            
            if($face1 = $this->request->files('face1'))
  		    {
                if (empty($face1['error']))
                {
                    if ($this->max_file_size > $face1['size'])
                    {
                        $ext = pathinfo($face1['name'], PATHINFO_EXTENSION);
                        do {
          		            $face1_filename = md5(microtime().rand()).'.'.$ext;
          		        } while (file_exists($this->config->root_dir.'/'.$this->config->users_files_dir.$face1_filename));
                        
                        move_uploaded_file($face1['tmp_name'], $this->config->root_dir.'/'.$this->config->users_files_dir.$face1_filename);
                        
                        $this->users->add_file(array(
                            'user_id' => $this->user->id,
                            'name' => $face1_filename,
                            'type' => 'face1',
                            'status' => 0
                        ));
                    }
                    else
                    {
                        $this->design->assign('error_face1', 'max_file_size');
                    }
                }
                elseif (!empty($face1['name']))
                {
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('face1', $face1);echo '</pre><hr />';
                    $this->design->assign('error_face1', 'error_upload');
                }

			}

            if($face2 = $this->request->files('face2'))
  		    {
                if (empty($face2['error']))
                {
                    if ($this->max_file_size > $face2['size'])
                    {
                        $ext = pathinfo($face2['name'], PATHINFO_EXTENSION);
                        do {
          		            $face2_filename = md5(microtime().rand()).'.'.$ext;
          		        } while (file_exists($this->config->root_dir.'/'.$this->config->users_files_dir.$face2_filename));
                        
                        move_uploaded_file($face2['tmp_name'], $this->config->root_dir.'/'.$this->config->users_files_dir.$face2_filename);
                        
                        $this->users->add_file(array(
                            'user_id' => $this->user->id,
                            'name' => $face2_filename,
                            'type' => 'face2',
                            'status' => 0
                        ));
                    }
                    else
                    {
                        $this->design->assign('error_face2', 'max_file_size');
                    }
                }
                elseif (!empty($face2['name']))
                {
                    $this->design->assign('error_face2', 'error_upload');
                }

			}
            
            if($passports = $this->request->files('passport'))
  		    {
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($passports);echo '</pre><hr />';
                foreach ($passports['name'] as $kk => $pfile_name)
                {
                    if (empty($passports['error'][$kk]))
                    {
                        if ($this->max_file_size > $passports['size'][$kk])
                        {
                            $ext = pathinfo($passports['name'][$kk], PATHINFO_EXTENSION);
                            do {
              		            $passport_filename = md5(microtime().rand()).'.'.$ext;
              		        } while (file_exists($this->config->root_dir.'/'.$this->config->users_files_dir.$passport_filename));
    
                            move_uploaded_file($passports['tmp_name'][$kk], $this->config->root_dir.'/'.$this->config->users_files_dir.$passport_filename);
                            
                            $this->users->add_file(array(
                                'user_id' => $this->user->id,
                                'name' => $passport_filename,
                                'type' => 'passport',
                                'status' => 0
                            ));
                        }
                        else
                        {
                            $this->design->assign('error_passport', 'max_file_size');
                        }
                    }
                    elseif (!empty($passports['name'][$kk]))
                    {
                        $this->design->assign('error_passport', 'error_upload');
                    }
                }
			}

        }
        ? Этот блок не выполняется ? **/
        
        $user_files = $this->users->get_files(array('user_id'=>$this->user->id));

        $has_rejected_photo = false;

        $have_new_file = 0;
        $face1_file = null;
        $face2_file = null;
        foreach ($user_files as $kk => $ufile)
        {
            if ((int)$ufile->status === $this->users::PHOTO_STATUS_REJECT) {
                $has_rejected_photo = true;
            }

            if ($ufile->type == 'face1')
            {
                $face1_file = $ufile;
                unset($user_files[$kk]);
            }
            elseif ($ufile->type == 'face2')
            {
                $face2_file = $ufile;
                unset($user_files[$kk]);
            }
            elseif ($ufile->type == 'selfi')
            {
                $selfi_file = $ufile;
                unset($user_files[$kk]);
            }
            elseif ($ufile->type == 'passport1')
            {
                $passport1_file = $ufile;
                unset($user_files[$kk]);
            }
            elseif ($ufile->type == 'passport2')
            {
                $passport2_file = $ufile;
                unset($user_files[$kk]);
            }                
            elseif ($ufile->type == 'passport3')
            {
                $passport3_file = $ufile;
                unset($user_files[$kk]);
            }                
            elseif ($ufile->type == 'passport4')
            {
                $passport4_file = $ufile;
                unset($user_files[$kk]);
            }                
            
            if (empty($ufile->status))
                $have_new_file = 1;
        }

        $this->design->assign('has_rejected_photo', $has_rejected_photo);
        $this->design->assign('have_new_file', $have_new_file);
        $this->design->assign('face1_file', $face1_file);
        $this->design->assign('face2_file', $face2_file);
        $this->design->assign('selfi_file', $selfi_file);
        $this->design->assign('passport1_file', $passport1_file);
        $this->design->assign('passport2_file', $passport2_file);
        $this->design->assign('passport3_file', $passport3_file);
        $this->design->assign('passport4_file', $passport4_file);
        $this->design->assign('passport_files', $user_files);
        
        
    	$this->design->assign('max_file_size', $this->max_file_size);

        $last_order = (array)$this->orders->get_last_order($this->user->id);
        $this->design->assign('last_order', $last_order);

        return $this->design->fetch('upload.tpl');
    }
    
}

