<?php

/**
 * Simpla CMS
 *
 * @copyright	2011 Denis Pikusov
 * @link		http://simplacms.ru
 * @author		Denis Pikusov
 *
 */
require_once('Simpla.php');

class Managers extends Simpla {

    /**
     * System менеджер
     */
    public const MANAGER_SYSTEM_ID = 50;

    public $permissions_list = array('products', 'categories', 'brands', 'features', 'orders', 'labels',
        'users', 'groups', 'coupons', 'pages', 'blog', 'comments', 'feedbacks', 'import', 'export', 'docs',
        'backup', 'stats', 'design', 'settings', 'currency', 'delivery', 'payment', 'managers', 'license');
    public $passwd_file = "simpla/.passwd";

    public function __construct() {
        // Для совсестимости с режимом CGI
        if (isset($_SERVER['REDIRECT_REMOTE_USER']) && empty($_SERVER['PHP_AUTH_USER'])) {
            $_SERVER['PHP_AUTH_USER'] = $_SERVER['REDIRECT_REMOTE_USER'];
        } elseif (empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER["REMOTE_USER"])) {
            $_SERVER['PHP_AUTH_USER'] = $_SERVER["REMOTE_USER"];
        }
    }

    public function getManagerIdForTaskByRole($role) {
        $query = $this->db->placehold("
            SELECT
                *
            FROM
                __managers
            WHERE
                role = '" . $role . "'
        ");
        $this->db->query($query);
        $results = $this->db->results();
        if ($results) {
            return $this->executorSearch($results);
        }
        return false;
    }

    /**
     * Поиск исполнителя по наименьшему количеству задач
     */
    public function executorSearch($executors) {
        foreach ($executors as $executor) {
            $managers[] = $executor->id;
        }
        $executorsTasks = $this->getTasksManagersByIds($managers);
        if ($executorsTasks) {
            // подсчет задач у каждого менеджера
            $dataCount = false;
            foreach ($executorsTasks as $executorTask) {
                foreach ($managers as $manager) {
                    if ($executorTask->manager_id === $manager) {
                        if (!isset($dataCount[$manager])) {
                            $dataCount[$manager] = 1;
                        } else {
                            $dataCount[$manager]++;
                        }
                    } else {
                        if (!isset($dataCount[$manager])) {
                            $dataCount[$manager] = 0;
                        }
                    }
                }
            }
            $min = min($dataCount);
            foreach ($dataCount as $key => $count) {
                if ($count === $min) {
                    return $key;
                }
            }
        }
        return $managers[0];
    }

    public function getTasksManagersByIds($array) {
        $query = $this->db->placehold("
            SELECT
                *
            FROM
                __pr_tasks
            WHERE
                manager_id IN (?@)
        ", array_map('intval', (array) $array));
        $this->db->query($query);
        return $this->db->results();
    }

    public function getManagerIdForTask($them) {
        $role = $this->getManagerRoleByThem($them);
        return $this->getManagerIdForTaskByRole($role);
    }

    public function getManagerRoleByThem($them) {
        $query = $this->db->placehold("
            SELECT
                Role
            FROM
                __topicsOfAppeals
            WHERE
                nameThem = ?
        ", (string) $them);
        $this->db->query($query);
        return $this->db->result('Role');
    }

    public function get_managers() {
        $lines = explode("\n", @file_get_contents(dirname(dirname(__FILE__)) . '/' . $this->passwd_file));
        $managers = array();
        foreach ($lines as $line) {
            if (!empty($line)) {
                $manager = null;
                $fields = explode(":", $line);
                $manager = new stdClass();
                $manager->login = trim($fields[0]);
                $manager->permissions = array();
                if (isset($fields[2])) {
                    $manager->permissions = explode(",", $fields[2]);
                    foreach ($manager->permissions as &$permission)
                        $permission = trim($permission);
                } else
                    $manager->permissions = $this->permissions_list;

                $managers[] = $manager;
            }
        }
        return $managers;
    }

    public function get_crm_managers($filter = array())
	{
		$id_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __managers
            WHERE 1
                $id_filter
 	           $keyword_filter
            ORDER BY name ASC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}

    public function count_managers($filter = array()) {
        return count($this->get_managers());
    }

    public function get_crm_manager($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __managers
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }

    public function get_manager($id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __managers
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        return $this->db->result();
    }

    public function add_manager($manager) {
        $manager = (object) $manager;
        if (!empty($manager->login))
            $m[0] = $manager->login;
        if (!empty($manager->password)) {
            // захешировать пароль
            $m[1] = $this->crypt_apr1_md5($manager->password);
        } else {
            $m[1] = "";
        }
        if (is_array($manager->permissions)) {
            if (count(array_diff($this->permissions_list, $manager->permissions)) > 0) {
                $m[2] = implode(",", $manager->permissions);
            } else {
                unset($m[2]);
            }
        }
        $line = implode(":", $m);
        file_put_contents($this->passwd_file, @file_get_contents($this->passwd_file) . "\n" . $line);
        if ($m = $this->get_manager($manager->login))
            return $m->login;
        else
            return false;
    }

    public function update_manager($login, $manager) {
        $manager = (object) $manager;
        // Не допускаем двоеточия в логине
        if (!empty($manager->login))
            $manager->login = str_replace(":", "", $manager->login);

        $lines = explode("\n", @file_get_contents($this->passwd_file));
        $updated_flag = false;
        foreach ($lines as &$line) {
            $m = explode(":", $line);
            if ($m[0] == $login) {
                if (!empty($manager->login))
                    $m[0] = $manager->login;
                if (!empty($manager->password)) {
                    // захешировать пароль
                    $m[1] = $this->crypt_apr1_md5($manager->password);
                }
                if (isset($manager->permissions) && is_array($manager->permissions)) {
                    if (count(array_diff($this->permissions_list, $manager->permissions)) > 0) {
                        $m[2] = implode(",", array_intersect($this->permissions_list, $manager->permissions));
                    } else {
                        unset($m[2]);
                    }
                }
                $line = implode(":", $m);
                $updated_flag = true;
            }
        }
        if ($updated_flag) {
            file_put_contents($this->passwd_file, implode("\n", $lines));
            if ($m = $this->get_manager($manager->login))
                return $m->login;
        }
        return false;
    }

    public function delete_manager($login) {
        $lines = explode("\n", @file_get_contents($this->passwd_file));
        foreach ($lines as $i => $line) {
            $m = explode(":", $line);
            if ($m[0] == $login)
                unset($lines[$i]);
        }
        file_put_contents($this->passwd_file, implode("\n", $lines));
        return true;
    }

    private function crypt_apr1_md5($plainpasswd) {
        $salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
        $len = strlen($plainpasswd);
        $text = $plainpasswd . '$apr1$' . $salt;
        $bin = pack("H32", md5($plainpasswd . $salt . $plainpasswd));
        for ($i = $len; $i > 0; $i -= 16) {
            $text .= substr($bin, 0, min(16, $i));
        }
        for ($i = $len; $i > 0; $i >>= 1) {
            $text .= ($i & 1) ? chr(0) : $plainpasswd[0];
        }
        $bin = pack("H32", md5($text));
        for ($i = 0; $i < 1000; $i++) {
            $new = ($i & 1) ? $plainpasswd : $bin;
            if ($i % 3)
                $new .= $salt;
            if ($i % 7)
                $new .= $plainpasswd;
            $new .= ($i & 1) ? $bin : $plainpasswd;
            $bin = pack("H32", md5($new));
        }
        $tmp = '';
        for ($i = 0; $i < 5; $i++) {
            $k = $i + 6;
            $j = $i + 12;
            if ($j == 16)
                $j = 5;
            $tmp = $bin[$i] . $bin[$k] . $bin[$j] . $tmp;
        }
        $tmp = chr(0) . chr(0) . $bin[11] . $tmp;
        $tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
                "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
                "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
        return "$" . "apr1" . "$" . $salt . "$" . $tmp;
    }

    public function access($module) {
        $manager = $this->get_manager();
        if (is_array($manager->permissions))
            return in_array($module, $manager->permissions);
        else
            return false;
    }

}
