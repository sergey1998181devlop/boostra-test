<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VerificatorCcView
 *
 * @author alexey
 */
require_once 'View.php';

class VerificatorCcView extends View {

    private $statuses = [
        1 => 'Новая',
        2 => 'Одобрена',
        3 => 'Отказ',
        4 => 'Отказался сам',
        5 => 'На исправлении',
        6 => 'Исправлена',
        7 => 'Ожидание',
        8 => 'Выдан',
        9 => 'Не получены',
        10 => 'Не заняты',
        11 => 'В работе',
        14 => 'Предварительно одобрена',
    ];
    private $styles = [
        1 => 'label-primary',
        2 => 'label-success',
        3 => 'label-danger',
        4 => 'label-primary',
        5 => 'label-inverse',
        6 => 'label-info',
        7 => 'label-warning',
        8 => 'label-success',
        9 => 'label-danger',
        10 => 'label-success',
        11 => 'label-primary',
        14 => 'label-success',
    ];

    public function fetch() {

        $orders = $this->VerificatorCc->getInfo();

        if (isset($_GET['sort'])) {
            $sort = $_GET['sort'];
        } else {
            $sort = 'date_asc';
        }

        if (isset($_GET['search']) AND isset($_GET['value'])) {
            $search[$_GET['search']] = $_GET['value'];
        } else {
            $search = FALSE;
        }

        ksort($this->statuses);
        $this->design->assign('search', $search);
        $this->design->assign('sort', $sort);
        $this->design->assign('statuses', $this->statuses);
        $this->design->assign('styles', $this->styles);

        $current_page = $this->request->get('page', 'integer');
        $current_page = max(1, $current_page);
        $count = $this->VerificatorCc->getCount();
        $pages_num = ceil($count / ListingPageCount);
        $this->design->assign('current_page_num', $current_page);
        $this->design->assign('total_pages_num', $pages_num);
        $this->design->assign('total_orders_count', $count);
        $this->design->assign('orders', $orders);
        return $this->design->fetch('verification_cc.tpl');
    }

}
