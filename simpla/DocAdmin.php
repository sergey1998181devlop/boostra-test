<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require_once 'api/Simpla.php';

class DocAdmin extends Simpla
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
    	$manager = $this->managers->get_manager();
        
        if ($this->request->method('post'))
        {
            $doc = new StdClass();
            
            $doc->id = $this->request->post('id');
            $doc->name = $this->request->post('name');
            $doc->description = $this->request->post('description');
            $doc->visible = $this->request->post('visible', 'integer');
            $doc->in_info = $this->request->post('in_info', 'integer');
            $doc->in_register = $this->request->post('in_register', 'integer');
            
            if (empty($doc->name))
            {
                $this->design->assign('message_error', 'empty_name');
            }
            else
            {
                if (empty($doc->id))
                {
                    $doc->id = $this->docs->add_doc($doc);
                    $this->docs->update_doc($doc->id, array('position'=>$doc->id));
                    
                    $this->design->assign('message_success', 'added');
                }
                else
                {
                    $this->docs->update_doc($doc->id, $doc);
                    
                    $this->design->assign('message_success', 'updated');
                }
                
                if($this->request->post('delete_file'))
        			$this->docs->delete_file($doc->id);
                
        		// Загрузка изображения
        		$file = $this->request->files('file');
                if(!empty($file['name'])) 
                {
        			if (empty($file['error']))
                    {
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        $fname = strtolower(pathinfo($file['name'], PATHINFO_FILENAME));
                        if (in_array($ext, $this->allowed_extensions))
                        {
                            $this->docs->delete_file($doc->id);
                            
                            $i = 0;
                            do {
                                $doc->filename = $this->translit($this->truncate($doc->name)).(empty($i)?'':'_'.$i).'.'.$ext;
                                $i++;
                            } while (file_exists($this->config->root_dir.$this->config->docs_files_dir.$doc->filename));
                            
                            if (move_uploaded_file($file['tmp_name'], $this->config->root_dir.$this->config->docs_files_dir.$doc->filename))
                            {
                                $this->docs->update_doc($doc->id, array('filename'=>$doc->filename));
                                $doc = $this->docs->get_doc($doc->id);
                            }
                            else
                            {
                                $this->design->assign('message_error', 'save_error');
                            }
                        }
                        else
                        {
                            $this->design->assign('message_error', 'extension_error');
                        }
                        
                    }
                    else
                    {
                        $this->design->assign('message_error', 'upload_error');
                    }
        		}
                else
                {
                    $doc = $this->docs->get_doc($doc->id);
                }
            }
            
            

        }
        else
        {
            $doc_id = $this->request->get('id', 'integer');
            $doc = $this->docs->get_doc($doc_id);
        }
        
        $this->design->assign('doc', $doc);
        
        return $this->design->fetch('doc.tpl');
    }
    
	private function translit($text)
	{
		$ru = explode('-', "А-а-Б-б-В-в-Ґ-ґ-Г-г-Д-д-Е-е-Ё-ё-Є-є-Ж-ж-З-з-И-и-І-і-Ї-ї-Й-й-К-к-Л-л-М-м-Н-н-О-о-П-п-Р-р-С-с-Т-т-У-у-Ф-ф-Х-х-Ц-ц-Ч-ч-Ш-ш-Щ-щ-Ъ-ъ-Ы-ы-Ь-ь-Э-э-Ю-ю-Я-я"); 
		$en = explode('-', "A-a-B-b-V-v-G-g-G-g-D-d-E-e-E-e-E-e-ZH-zh-Z-z-I-i-I-i-I-i-J-j-K-k-L-l-M-m-N-n-O-o-P-p-R-r-S-s-T-t-U-u-F-f-H-h-TS-ts-CH-ch-SH-sh-SCH-sch---Y-y---E-e-YU-yu-YA-ya");

	 	$res = str_replace($ru, $en, $text);
		$res = preg_replace("/[\s]+/ui", '-', $res);
		$res = preg_replace('/[^\p{L}\p{Nd}\d-]/ui', '', $res);
	 	$res = strtolower($res);
	    return $res;  
	}
	
    function truncate($text, $chars = 200) {
        if (mb_strlen($text, 'utf8') <= $chars) {
            return $text;
        }
        $text = $text." ";
        $text = mb_substr($text,0,$chars, 'utf8');
        $text = mb_substr($text,0,mb_strrpos($text,' ', 0, 'utf8'), 'utf8');
        
        return $text;
    }
}