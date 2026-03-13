<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VerificatorCc
 *
 * @author alexey
 */
require_once 'Simpla.php';

define('ListingPageCount', 20);

class VerificatorCc extends Simpla {

    private $sqlString = "SELECT * FROM __users, __orders WHERE ";
    private $whereSql = "__orders.utm_medium LIKE '%x%' AND __users.id = __orders.user_id ";

    public function __construct() {
        $this->filters();
        $this->sort();
    }

    /**
     * Получаем информацию о клиентах и заявках
     */
    public function getInfo() {
        $page = 0;
        if (isset($_GET['page'])) {
            $page = ListingPageCount * $_GET['page'];
            if ($page === (int) $this->getCount()) {
                $page = ListingPageCount * (int) $_GET['page'] - 1;
            }
            if ($page < 0) {
                $page = 0;
            }
        }

        $query = $this->db->placehold(
                $this->sqlString . "
            LIMIT " . $page . ', ' . ListingPageCount
        );
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получаем количество элементов в запросе
     */
    public function getCount() {
        $query = $this->db->placehold($this->sqlString);
        $result = $this->db->query($query);
        return $result->num_rows;
    }

    /**
     * Задаем параметры сортировки
     */
    private function sort() {
        $sortString = $this->sqlString;
        if (isset($_GET['sort'])) {
            $sort = $_GET['sort'];
            $sortString .= ' ORDER BY ';
            if ($sort == 'order_id_asc') {
                $sortString .= ' __orders.id ASC';
            } elseif ($sort == 'order_id_desc') {
                $sortString .= ' __orders.id DESC';
            } elseif ($sort == 'date_asc') {
                $sortString .= ' __orders.date ASC';
            } elseif ($sort == 'date_desc') {
                $sortString .= ' __orders.date DESC';
            } elseif ($sort == 'amount_asc') {
                $sortString .= ' __orders.amount ASC';
            } elseif ($sort == 'amount_desc') {
                $sortString .= ' __orders.amount DESC';
            } elseif ($sort == 'period_asc') {
                $sortString .= ' __orders.period ASC';
            } elseif ($sort == 'period_desc') {
                $sortString .= ' __orders.period DESC';
            } elseif ($sort == 'fio_asc') {
                $sortString .= ' __users.lastname ASC, __users.firstname ASC, __users.patronymic ASC';
            } elseif ($sort == 'fio_desc') {
                $sortString .= ' __users.lastname DESC, __users.firstname DESC, __users.patronymic DESC';
            } elseif ($sort == 'birth_asc') {
                $sortString .= ' __users.birth ASC';
            } elseif ($sort == 'birth_desc') {
                $sortString .= ' __users.birth DESC';
            } elseif ($sort == 'phone_asc') {
                $sortString .= ' __users.phone_mobile ASC';
            } elseif ($sort == 'phone_desc') {
                $sortString .= ' __users.phone_mobile DESC';
            } elseif ($sort == 'region_asc') {
                $sortString .= ' __users.Regregion ASC';
            } elseif ($sort == 'region_desc') {
                $sortString .= ' __users.Regregion DESC';
            } elseif ($sort == 'status_asc') {
                $sortString .= ' __orders.status ASC';
            } elseif ($sort == 'status_desc') {
                $sortString .= ' __orders.status DESC';
            } elseif ($sort == 'manager_asc') {
                $sortString .= ' __orders.manager_id ASC';
            } elseif ($sort == 'manager_desc') {
                $sortString .= ' __orders.manager_id DESC';
            } elseif ($sort == 'utm_asc') {
                $sortString .= ' __orders.utm_source ASC';
            } elseif ($sort == 'utm_desc') {
                $sortString .= ' __orders.utm_source DESC';
            }else{
                $sortString .= ' __orders.date ASC';
            }
        } else {
            $sortString .= " ORDER BY __orders.date ASC ";
        }
        $this->sqlString = $sortString;
    }

    /**
     * Задаем фильтры
     */
    private function filters() {
        $filtersString = $this->sqlString . $this->whereSql;
        if (isset($_GET['search']) AND isset($_GET['value'])) {
            $search = $_GET['search'];
            $value = $_GET['value'];
            if ($search AND $value) {
                if ($search == 'order_id') {
                    $filtersString .= " AND __orders.id = '$value' ";
                } elseif ($search == 'date') {
                    $filtersString .= " AND __orders.date = '$value' ";
                } elseif ($search == 'amount') {
                    $filtersString .= " AND __orders.amount = '$value' ";
                } elseif ($search == 'period') {
                    $filtersString .= " AND __orders.period = '$value' ";
                } elseif ($search == 'fio') {
                    $fio = explode(' ', $value);
                    if (isset($fio[0])) {
                        if ($fio[0]) {
                            $filtersString .= " AND __users.lastname LIKE '%" . $fio[0] . "%' ";
                            $filtersString .= " OR " . $this->whereSql . " AND __users.firstname LIKE '%" . $fio[0] . "%' ";
                            $filtersString .= " OR " . $this->whereSql . " AND __users.patronymic LIKE '%" . $fio[0] . "%' ";
                        }
                    }
                    if (isset($fio[1])) {
                        if ($fio[1]) {
                            $filtersString .= " AND __users.lastname LIKE '%" . $fio[1] . "%' ";
                            $filtersString .= " OR " . $this->whereSql . " AND __users.firstname LIKE '%" . $fio[1] . "%' ";
                            $filtersString .= " OR " . $this->whereSql . " AND __users.patronymic LIKE '%" . $fio[1] . "%' ";
                        }
                    }
                    if (isset($fio[1])) {
                        if ($fio[1]) {
                            $filtersString .= " AND __users.lastname LIKE '%" . $fio[2] . "%' ";
                            $filtersString .= " OR " . $this->whereSql . " AND __users.firstname LIKE '%" . $fio[2] . "%' ";
                            $filtersString .= " OR " . $this->whereSql . " AND __users.patronymic LIKE '%" . $fio[2] . "%' ";
                        }
                    }
                } elseif ($search == 'birth') {
                    $filtersString .= " AND __users.birth = '$value' ";
                } elseif ($search == 'phone') {
                    $filtersString .= " AND __users.phone_mobile LIKE '%$value%' ";
                } elseif ($search == 'region') {
                    $filtersString .= " AND __users.Regregion LIKE '%$value%' ";
                } elseif ($search == 'status') {
                    $filtersString .= " AND __orders.status = '$value' ";
                } elseif ($search == 'utm') {
                    $filtersString .= " AND __orders.utm_source LIKE '%$value%' ";
                } elseif ($search == 'manager_id') {
                    $filtersString .= " AND __orders.manager_id = '$value' ";
                }else{
                    $filtersString .= '';
                }
            }
        }
        $this->sqlString = $filtersString;
    }

}
