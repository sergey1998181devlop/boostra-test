<?PHP

/**
 * Simpla CMS
 *
 * @copyright 	2011 Denis Pikusov
 * @link 		http://simp.la
 * @author 		Denis Pikusov
 *
 * Этот класс использует шаблон index.tpl,
 * который содержит всю страницу кроме центрального блока
 * По get-параметру module мы определяем что сожержится в центральном блоке
 *
 */

require_once('View.php');

class IndexView extends View
{
    public $modules_dir = 'view/';

    public function __construct()
    {
        parent::__construct();
    }


    /**
     *
     * Отображение
     *
     */
    function fetch()
    {
        $managers = array();
        foreach ($this->managers->get_managers() ?: [] as $m)
            $managers[$m->id] = $m;
        $this->design->assign('managers', $managers);
        
        // Текущий модуль (для отображения центрального блока)
        $module = $this->request->get('module', 'string');
        $module = preg_replace("/[^A-Za-z0-9]+/", "", $module);

        if (!in_array($module, ['LoginView', 'ClientChecker']) && !$this->manager)
        {
            header('Location: '.$this->config->root_url.'/login?back='.$this->request->url());
            exit;
        }


        // Если не задан - берем из настроек
        if(empty($module))
        {
            if ($this->manager->role == 'contact_center')
                $module = 'ClientsView';
            else
                $module = 'OrdersView';
        }

        // Создаем соответствующий класс
        if (is_file($this->modules_dir."$module.php"))
        {
            include_once($this->modules_dir."$module.php");
            if (class_exists($module))
            {
                $this->main = new $module($this);
            } else return false;
        } else return false;

        // Создаем основной блок страницы
        if (!$content = $this->main->fetch())
        {
            return false;
        }

        // Передаем основной блок в шаблон
        $this->design->assign('content', $content);

        // Передаем название модуля в шаблон, это может пригодиться
        $this->design->assign('module', $module);

        // Создаем текущую обертку сайта (обычно index.tpl)
        $wrapper = $this->design->get_var('wrapper');
        if(is_null($wrapper))
            $wrapper = 'index.tpl';

        if(!empty($wrapper))
            return $this->body = $this->design->fetch($wrapper);
        else
            return $this->body = $content;

    }
}
