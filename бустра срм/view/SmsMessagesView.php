<?php

require_once 'View.php';

use App\Core\Application\Application;
use App\Models\SmsMessages;
use App\Repositories\SmsMessagesRepository;

class SmsMessagesView extends View
{
    private SmsMessagesRepository $smsMessagesRepository;

    public function __construct()
    {
        parent::__construct();
        $app = Application::getInstance();
        $this->smsMessagesRepository = $app->make(SmsMessagesRepository::class);
    }

    public function fetch()
    {
        $currentPageNum = $this->request->get('page', 'integer');
        $currentPageNum = max(1, $currentPageNum);
        $itemsPerPage = 20;

        $totalItems = $this->smsMessagesRepository->count();

        $totalPagesNum = ceil($totalItems / $itemsPerPage);
        $totalPagesNum = max(1, $totalPagesNum);
        $currentPageNum = min($currentPageNum, $totalPagesNum);

        $items = $this->smsMessagesRepository->get($currentPageNum, $itemsPerPage);

        $this->design->assign_array([
            'items' => $items,
            'current_page_num' => $currentPageNum,
            'total_pages_num' => $totalPagesNum,
        ]);

        return $this->design->fetch('sms_logs.tpl');
    }
}