<?php

namespace api\traits;

trait setPages {

    /**
     * количество строк выборки (это же количество будет на странице)
     */
    public static $limit = 20;

    /**
     * Дополняет sql запрос условием выборки 
     */
    public function getLimit($query) {
        $getPage = $this->request->get('page');
        if (!$getPage) {
            $getPage = 1;
        }
        $page = self::$limit * $getPage;
        if ($page === $this->getCount($query)) {
            $page = (self::$limit * $getPage) - 1;
        }
        if ($getPage == 1) {
            return ' LIMIT ' . self::$limit;
        } else {
            $page = self::$limit * ($getPage - 1);
            return ' LIMIT ' . $page . ', ' . self::$limit;
        }
    }

    /**
     * Возвращает максимальное количество элементов запроса
     */
    private function getCount($sql) {
        $match = false;
        $strArray = explode("\n", $sql);
        $string = '';
        foreach ($strArray as $str) {
            $string .= trim($str) . ' ';
        }
        preg_match('/SELECT (?<filds>.+) FROM (?<table>\w+)/ui', $string, $match);
        if (isset($match['filds'])) {
            $newSql = str_replace($match['filds'], 'COUNT(*)', $string);
        }
        if ($newSql) {
            $this->db->query($newSql);
            $count = $this->db->result('COUNT(*)');
            $this->setPages($count);
            $this->setSort();
            return (int) $count;
        }
        return 0;
    }

    /**
     * Передача сортировки в шаблон
     */
    private function setSort() {
        if (!($sort = $this->request->get('sort', 'string'))) {
            $sort = 'id_desc';
        }
        $this->design->assign('sort', $sort);
    }

    /**
     * Передача переменных пагинации в шаблон
     */
    private function setPages($count = 0) {
        $items_per_page = self::$limit;
        $pages_num = ceil($count / $items_per_page);
        $current_page = max(1, $this->request->get('page', 'integer'));
        $this->design->assign('total_pages_num', $pages_num);
        $this->design->assign('total_orders_count', $count);
        $this->design->assign('current_page_num', $current_page);
    }

}
