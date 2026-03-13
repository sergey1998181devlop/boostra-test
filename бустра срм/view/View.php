<?PHP

/**
 * Simpla CMS
 *
 * @copyright    2011 Denis Pikusov
 * @link        http://simp.la
 * @author        Denis Pikusov
 *
 * Базовый класс для всех View
 *
 */

require_once('api/Simpla.php');

class View extends Simpla
{
    /* Смысл класса в доступности следующих переменных в любом View */
    public $manager;

    /* Класс View похож на синглтон, храним статически его инстанс */
    private static $view_instance;

    public function __construct()
    {
        parent::__construct();

        // Если инстанс класса уже существует - просто используем уже существующие переменные
        if (self::$view_instance) {
            $this->manager = &self::$view_instance->manager;
        } else {
            // Сохраняем свой инстанс в статической переменной,
            // чтобы в следующий раз использовать его
            self::$view_instance = $this;

            if ($this->is_developer) {
//    $_SESSION['manager_id'] = 39;
            }
            // Пользователь, если залогинен
            if (isset($_SESSION['manager_id'])) {
                if ($_SESSION['manager_ip'] == $_SERVER['REMOTE_ADDR']) {
                    if ($manager = $this->managers->get_manager(intval($_SESSION['manager_id']))) {
                        $manager->permissions = $this->managers->get_permissions($manager->role);
                        $this->manager = $manager;

                        $this->managers->update_manager($manager->id, array('last_ip' => $_SERVER['REMOTE_ADDR'], 'last_visit' => date('Y-m-d H:i:s')));
                        $this->managers->update((int)$manager->id);
                    } else {
                        $_SESSION['manager_id'] = null;
                        $_SESSION['manager_ip'] = null;
                        setcookie('ah', null, time() - 1, '/', 'boostra.ru');
                        setcookie('mid', null, time() - 1, '/', 'boostra.ru');
                        header('Location:/');
                        exit;
                    }
                } else {
                    $_SESSION['manager_id'] = null;
                    $_SESSION['manager_ip'] = null;
                    setcookie('ah', null, time() - 1, '/', 'boostra.ru');
                    setcookie('mid', null, time() - 1, '/', 'boostra.ru');
                    header('Location:/');
                    exit;

                }
            } elseif (isset($_COOKIE['ah'], $_COOKIE['mid'])) {
                $manager = $this->managers->get_manager((int)$_COOKIE['mid']);

                if ($manager && $_COOKIE['ah'] == md5(sha1($_SERVER['REMOTE_ADDR'] . $manager->id) . $manager->salt)) {
                    $manager->permissions = $this->managers->get_permissions($manager->role);
                    $this->manager = $manager;
                    $_SESSION['manager_id'] = $manager->id;
                    $_SESSION['manager_ip'] = $_SERVER['REMOTE_ADDR'];
                } else {
                    setcookie('ah', null, time() - 1, '/', 'boostra.ru');
                    setcookie('mid', null, time() - 1, '/', 'boostra.ru');
                }
            }
            
            // Передаем в дизайн то, что может понадобиться в нем
            $this->design->assign('is_developer', $this->is_developer);
            $this->design->assign('manager', $this->manager);
            $this->design->assign('config', $this->config);
            $this->design->assign('settings', $this->settings);
        }
    }

    /**
     *
     * Отображение
     *
     */
    function fetch()
    {
        return false;
    }

    protected function json_output($data)
    {
        header("Content-type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }
}
