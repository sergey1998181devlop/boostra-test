<?PHP
/**
 * Simpla CMS
 * Storefront class: Каталог товаров
 *
 * Этот класс использует шаблоны hits.tpl
 *
 * @copyright 	2010 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 *
 *
 */

require_once('View.php');


class LptView extends View
{

    function fetch()
    {
        $query = $this->db->placehold("
            SELECT *
            FROM __lpt
        ");

        $this->db->query($query);
        $lpt_collection = $this->db->results();

        $this->design->assign('lpt_collection', $lpt_collection);

        return $this->design->fetch('lpt.tpl');
    }
}