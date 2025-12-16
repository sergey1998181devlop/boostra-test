<?PHP

/**
 * Simpla CMS
 *
 * @copyright 	2011 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 * Этот класс использует шаблон page.tpl
 *
 */
require_once('View.php');

class PageView extends View
{
	function fetch()
	{
		$url = $this->request->get('page_url', 'string');

		$page = $this->pages->get_page($url);
		
		// Отображать скрытые страницы только админу
		if(empty($page) || (!$page->visible && empty($_SESSION['admin'])))
			return false;
		$thems = $this->pages->getThems();
		$this->design->assign('page', $page);
		$this->design->assign('meta_title', $page->meta_title);
		$this->design->assign('meta_keywords', $page->meta_keywords);
		$this->design->assign('meta_description', $page->meta_description);
		$this->design->assign('thems', $thems);
		// Тут добавим проверку на случай если другой шаблон выбран
		if(!empty($page->template))
		{
			// Тут хорошо бы добавить проверку на существование такого файла
			return $this->design->fetch($page->template);
		}
		else
			return $this->design->fetch('page.tpl');
	}
}