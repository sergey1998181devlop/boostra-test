<?php

class BlackListView extends View
{
    /**
     * Limit for records
     * @var const int LIMIT
     */
    protected const LIMIT = 20;

    public function fetch()
    {
        if (!in_array('settings', $this->manager->permissions)) {
            return $this->design->fetch('403.tpl');
        }
        $currentPage = $this->request->get('page', 'integer') ?: 1;
        $search = $this->request->get('search') ?: '';

        $filters = [
            'offset' => ($currentPage - 1) * self::LIMIT,
            'limit' => self::LIMIT
        ];

        foreach ($search as $filterMethod => $searchText) {
            $searchText = trim($searchText);
            $filterMethod = 'getFilter' . ucfirst($filterMethod);
            if ($searchText && method_exists($this->blacklist, $filterMethod)) {
                $filters = array_merge($filters, $this->blacklist->$filterMethod($searchText));
            }
        }

        $this->design->assign('blacklist', $this->blacklist->getAll($filters));
        $this->design->assign('showCount', $this->blacklist->count);
        $this->design->assign('totalPage',  floor($this->blacklist->count($filters) / self::LIMIT));
        $this->design->assign('currentPage', $currentPage);
        $this->design->assign('search', $search);

        return $this->design->fetch('blacklist.tpl');
    }
}