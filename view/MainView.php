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


class MainView extends View
{
    use \api\traits\AuthButtonsTrait;

	function fetch()
	{
		if (!empty($this->user)) {
            header('Location: /user');
            exit;
		}

        $this->initAfterScoringUrl();
        $this->users->clearSessionDataFlowAfterPersonalData();

        $percent = isset($_COOKIE['utm_source']) ? '0' : $this->orders::BASE_PERCENTS;
        $utm_source = $this->request->get('utm_source') ?? $_COOKIE['utm_source'] ?? '&';

        $this->initAuthAllButtons();

		if($this->page)
		{
			$this->design->assign('main_page', true);
			$this->design->assign('meta_title', $this->page->meta_title);
			$this->design->assign('meta_keywords', $this->page->meta_keywords);
			$this->design->assign('meta_description', $this->page->meta_description);
            $this->design->assign('max_period', $this->orders::MAX_PERIOD);
		}

        $comeback = $this->partner_href->getActualItem(0, 'bonon-comeback');
        $complaint = $this->partner_href->getActualItem(0, 'bonon-background-complaint');
        $background = $this->partner_href->getActualItem(0, 'bonon-background');
        $this->design->assign('comeback_url', $comeback->href);
        $this->design->assign('background_url', $background->href);
        $this->design->assign('complaint_background_url', $complaint->href);
        $this->design->assign('about_as_pages', $this->getAboutAsPages());
        $this->design->assign('base_percents', $percent);
        $this->design->assign('same_page', in_array($utm_source, explode(',', $this->settings->bonon_excluded_utms)));
		return $this->design->fetch('main.tpl');
	}

    private function getAboutAsPages()
    {
        $pages = $this->pages->getAboutAsPages();
        $nowDay = date('d'); // Текущая дата и время
        foreach ($pages as $page) {
            if ($nowDay != date('d', strtotime($page->updated_at))) {
                $this->pages->updateAboutAsPage($page->id, ['updated_at' => date('Y-m-d H:i:s'), 'total_like' => $page->total_like + rand(9, 32)]);
            }
        }

        return $pages;
    }

    private function initAfterScoringUrl()
    {
        if ($this->request->get('webmaster_id') == '0117' && $this->request->get('utm_source') == 'sms' && $this->request->get('short') == 1) {
            $this->design->assign('is_after_scorista_sms' , 1);
        }
    }
}
