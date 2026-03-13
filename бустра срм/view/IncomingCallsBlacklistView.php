<?php

require_once 'View.php';

use App\Core\Application\Application;
use App\Repositories\IncomingCallBlacklistRepository;

class IncomingCallsBlacklistView extends View
{
    /** @var IncomingCallBlacklistRepository */
    private $blacklistRepository;

    public function __construct()
    {
        parent::__construct();

        $app = Application::getInstance();
        $this->blacklistRepository = $app->make(IncomingCallBlacklistRepository::class);
    }

    public function fetch()
    {

        $currentPageNum = $this->request->get('page', 'integer');
        $currentPageNum = max(1, $currentPageNum);
        
        $itemsPerPage = 20;
        
        $search = $this->request->get('search');

        $totalItems = $this->blacklistRepository->count($search);
        
        $totalPagesNum = ceil($totalItems / $itemsPerPage);
        $totalPagesNum = max(1, $totalPagesNum);
        $currentPageNum = min($currentPageNum, $totalPagesNum);
        
        $blacklist = $this->blacklistRepository->getBlacklist($search, $currentPageNum, $itemsPerPage);
        
        $managerId = $this->managers->get_manager(intval($_SESSION['manager_id']))->id;
        $managers = array();
        foreach ($this->managers->get_managers() as $m) {
            $managers[$m->id] = $m;
        }
        
        $this->design->assign_array([
            'blacklist' => array_map(fn($item) => (object)$item, $blacklist),
            'manager_id' => $managerId,
            'managers' => $managers,
            'search' => $search,
            'current_page_num' => $currentPageNum,
            'total_pages_num' => $totalPagesNum,
            'items' => $blacklist
        ]);
        
        return $this->design->fetch('incoming_calls_blacklist.tpl');
    }
}