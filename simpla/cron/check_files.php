<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require_once(dirname(__DIR__, 2) . '/api/Simpla.php');

class CheckFilesCron extends Simpla
{
    private $statuses = array(
        0, // не проверен
        1, // нет уида клиента
        2, // в хранилище не найден
        3, // в хранилище найден не корректный файл
        
    );
    
    private $dir = '/home/boostra/boostra/files/users/';
    
    public function __construct()
    {
    	parent::__construct();
        
        
        for ($i = 0; $i < 4; $i++)
            $this->run();
    }
    
    
    
    public function run()
    {
    	if ($users = $this->get_users())
        {
            foreach ($users as $user)
            {
                $this->import_user($user);
                
            }
        }
    }
    
    
    private function import_user($user)
    {
        $user_files = $this->users->get_files(array('user_id' => $user->id));
$this->users->update_user($user->id, array('files_checked' => 1));
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($user, $user_files);echo '</pre><hr />';
        
        if (!empty($user_files))
        {
            for ($i = 0, $cnt = count($user_files); $i < $cnt; $i++)
            {
                $file = $user_files[$i];
                if (!file_exists($this->dir.$file->name))
                {
                    if (empty($user->uid) || $user->uid == 'Error')
                    {
                        $this->users->update_file($file->id, array('not_found' => 1));                        
                        unset($user_files[$i]);
        $this->users->update_user($user->id, array('files_checked' => 1));
                    }
                    else
                    {
                        if (!empty($file->storage_uid))
                        {
                            unset($user_files[$i]);
                        }
                    }
                }
            }

            if (empty($user_files))
            {
                $this->users->update_user($user->id, array('files_checked' => 1));
            }
            else
            {
                $res = json_decode(file_get_contents('https://boostra.ru/ajax/filestorage.php?user_id='.$user->id));
                if (count($res->uid_images) > 20)
                {
                    $this->users->update_user($user->id, array('files_checked' => 9));
                }
                else
                {
                    $user_files = array_values($user_files);
                    for ($i = 0, $cnt = count($user_files); $i < $cnt; $i++)
                    {
                        $file = $user_files[$i];
                        foreach ($res->files as $item)
                        {
                            if ($item->id == $file->id)
                            {
                                if (empty($item->exists))
                                {
                                    $this->users->update_file($file->id, array('not_found' => 1));
                                }
                                else
                                {
                                    unlink($this->dir.$file->name);
                                }
                            }
                        }
                        
                    }
                    $this->users->update_user($user->id, array('files_checked' => 8));
                }
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($res);echo '</pre><hr />';
            }
            
        }
        else
        {
        $this->users->update_user($user->id, array('files_checked' => 1));
        }
        
    }
    
    
    private function get_users()
    {
        $this->db->query("
            SELECT 
                id, 
                UID as uid
            FROM s_users
            WHERE 
            files_checked = 0
            ORDER BY id ASC
            LIMIT 100
        ");
        $users = $this->db->results();
        
        return $users;
    }
    
    
    
    private function run__()
    {
        $this->db->query("
            SELECT 
                id, 
                UID as uid
            FROM s_users
            WHERE 
            files_checked = 0
            ORDER BY id ASC
            LIMIT 1
        ");
        $users = $this->db->results();
        
        if (!empty($users))
        {
            foreach ($users as $user)
            {
                $uid_images = NULL;
                
                $user_files = $this->users->get_files(array('user_id' => $user->id));
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($user, $user_files);echo '</pre><hr />';        
                
                if (empty($user_files))
                {
                    $this->users->update_user($user->id, array('files_checked' => 1));
                }
                else
                {
                    if (empty($user->uid) || $user->uid == 'Error')
                    {
                        foreach ($user_files as $file)
                        {
                            if (!file_exists('/home/p/pravza/simpla/public_html/files/users/'.$file->name))
                            {
                                $this->users->update_file($file->id, array('not_found' => 1));
                            }
                        }
                        
                        $this->users->update_user($user->id, array('files_checked' => 1));
                    }
                    else
                    {
                        foreach ($user_files as $file)
                        {
                            $filename = '/home/p/pravza/simpla/public_html/files/users/'.$file->name;
                            
                            if (!file_exists($filename))
                            {
                                if (!empty($file->storage_uid))
                                {
                                    
                                }
                                else
                                {
                                    if (empty($uid_images))
                                        $uid_images = $this->soap->get_uid_images($user_uid->uid);
                                    
                                    
                                }
                            }
                        }
                        
                        
                        
                        $res = file_get_contents('https://boostra.ru/ajax/filestorage.php?user_id='.$user->id);
                        $res = json_decode($res);
                        if (is_object($res))
                        {
                            
                            if (count($res->uid_images) > 20)
                            {
                                $this->users->update_user($user->id, array('files_checked' => 9));
                            }
                            else
                            {
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($res);echo '</pre><hr />';        
                        
                                foreach ($user_files as $file)
                                {
                                    $filename = '/home/p/pravza/simpla/public_html/files/users/'.$file->name;
                                    
                                    if (!file_exists($filename))
                                    {
                                        $this->users->update_file($file->id, array('not_found' => 2));
                                    }
                                    else
                                    {
                                        $content = file_get_contents($filename);
                                    
                                        if (stripos('<html>', $content) !== false)
                                        {
                                            $this->users->update_file($file->id, array('not_found' => 3));
                                        }
                                        else
                                        {
                                            $this->users->update_file($file->id, array('not_found' => 0));
                                        }
                                        
                                        unlink($filename);
                                    }
                                }
                                $this->users->update_user($user->id, array('files_checked' => 1));
                                
                                usleep(100000);
                            }
                        }
                        else
                        {
                            $this->users->update_user($user->id, array('files_checked' => 3));
                        }
                    }
                }
usleep(100000);
            }
        }
        
        
    }
    
    
}
new CheckFilesCron();


class CheckFilesCronPREV extends Simpla
{
    private $statuses = array(
        0, // не проверен
        1, // нет уида клиента
        2, // в хранилище не найден
        3, // в хранилище найден не корректный файл
    );
    
    
    public function __construct()
    {
    	parent::__construct();
    
        $i = 1;
        while ($i > 0)
        {
            $this->run();
            $i--;
            
        }
    }
    
    private function run()
    {
        $this->db->query("
            SELECT 
                id, 
                UID as uid
            FROM s_users
            WHERE 
            files_checked = 0
            ORDER BY id ASC
            LIMIT 100
        ");
        $users = $this->db->results();
        
        if (!empty($users))
        {
            foreach ($users as $user)
            {
                $user_files = $this->users->get_files(array('user_id' => $user->id));
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($user, $user_files);echo '</pre><hr />';        
                
                if (empty($user_files))
                {
                    $this->users->update_user($user->id, array('files_checked' => 1));
                }
                else
                {
                    if (empty($user->uid) || $user->uid == 'Error')
                    {
                        foreach ($user_files as $file)
                        {
                            if (!file_exists('/home/p/pravza/simpla/public_html/files/users/'.$file->name))
                            {
                                $this->users->update_file($file->id, array('not_found' => 1));
                            }
                        }
                        
                        $this->users->update_user($user->id, array('files_checked' => 1));
                    }
                    else
                    {
                        $res = file_get_contents('https://boostra.ru/ajax/filestorage.php?user_id='.$user->id);
                        $res = json_decode($res);
                        if (is_object($res))
                        {
                            
                            if (count($res->uid_images) > 20)
                            {
                                $this->users->update_user($user->id, array('files_checked' => 9));
                            }
                            else
                            {
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($res);echo '</pre><hr />';        
                        
                                foreach ($user_files as $file)
                                {
                                    $filename = '/home/p/pravza/simpla/public_html/files/users/'.$file->name;
                                    
                                    if (!file_exists($filename))
                                    {
                                        $this->users->update_file($file->id, array('not_found' => 2));
                                    }
                                    else
                                    {
                                        $content = file_get_contents($filename);
                                    
                                        if (stripos('<html>', $content) !== false)
                                        {
                                            $this->users->update_file($file->id, array('not_found' => 3));
                                        }
                                        else
                                        {
                                            $this->users->update_file($file->id, array('not_found' => 0));
                                        }
                                        
                                        unlink($filename);
                                    }
                                }
                                $this->users->update_user($user->id, array('files_checked' => 1));
                                
                                usleep(100000);
                            }
                        }
                        else
                        {
                            $this->users->update_user($user->id, array('files_checked' => 3));
                        }
                    }
                }
usleep(100000);
            }
        }
        
        
    }
}
