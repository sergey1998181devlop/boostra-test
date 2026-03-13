<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPTrait.php to edit this template
 */

namespace api\traits;

/**
 *
 * @author alexey
 */
trait Sorts {

    /**
     * Задаем условия сортировки для тикетов контак центра
     */
    public function sortMyTycketsCC() {
        $string = '__tickets.id DESC';
        $getSort = $this->request->get('sort', 'string');
        if ($getSort) {
            $sortInfo = explode('_', $getSort);
            $sort = strtoupper($sortInfo[1]);
            # Задаем параметры сортировки eсли значение переменной с 2 и более нижними подчеркиваниями
            if ($getSort == 'id_asc') {
                $string = '__tickets.id ASC';
            } elseif ($getSort == 'id_desc') {
                $string = '__tickets.id DESC';
            } elseif ($getSort == 'status_asc') {
                $string = '__ticket_statuses.id ASC';
            } elseif ($getSort == 'status_desc') {
                $string = '__ticket_statuses.id DESC';
            } elseif ($getSort == 'date_asc') {
                $string = '__tickets.appeal_date ASC';
            } elseif ($getSort == 'date_desc') {
                $string = '__tickets.appeal_date DESC';
            } elseif ($getSort == 'phone_asc') {
                $string = '__users.phone_mobile ASC';
            } elseif ($getSort == 'phone_desc') {
                $string = '__users.phone_mobile DESC';
            } elseif ($getSort == 'subject_asc') {
                $string = '__ticket_subjects.id ASC';
            } elseif ($getSort == 'subject_desc') {
                $string = '__ticket_subjects.id DESC';
            }

            # Задаем параметры сортировки eсли значение переменной с 1 нижним подчеркиванием
            elseif ($sortInfo[0] == 'fio') {
                $string = '__users.lastname ' . $sort . ', __users.firstname ' . $sort . ', __users.patronymic ' . $sort;
            } elseif ($sortInfo[0] == 'phone') {
                $string = '__users.phone_mobile ' . $sort;
            } elseif ($sortInfo[0] == 'number') {
                $string = '__user_balance.zaim_number ' . $sort;
            } elseif ($sortInfo[0] == 'date') {
                $string = '__user_balance.zaim_date ' . $sort;
            } elseif ($sortInfo[0] == 'summ') {
                $string = '__user_balance.zaim_summ ' . $sort;
            } elseif ($sortInfo[0] == 'payment') {
                $string = '__user_balance.ostatok_od ' . $sort;
            } elseif ($sortInfo[0] == 'prolongation') {
                $string = '__user_balance.prolongation_count ' . $sort;
            } elseif ($sortInfo[0] == 'status') {
                $string = '__user_balance.cc_status ' . $sort;
            }
        }
        return ' ORDER BY ' . $string;
    }

    /**
     * Задаем условия сортировки
     * @return type
     */
    private function sortTasksOverdue() {
        $string = '__user_balance.payment_date ASC';
        $getSort = $this->request->get('sort', 'string');
        if ($getSort) {
            $sortInfo = explode('_', $getSort);
            $sort = strtoupper($sortInfo[1]);
            # Задаем параметры сортировки eсли значение переменной с 2 и более нижними подчеркиваниями
            if ($getSort == 'date_payment_asc') {
                $string = '__user_balance.payment_date ASC';
            } elseif ($getSort == 'date_payment_desc') {
                $string = '__user_balance.payment_date DESC';
            } elseif ($getSort == 'the_day_of_the_last_call_asc') {
                $string = '__lpt.updated_at ASC';
            } elseif ($getSort == 'the_day_of_the_last_call_desc') {
                $string = '__lpt.updated_at DESC';
            } elseif ($getSort == 'lpt_status_asc') {
                $string = '__lpt.status ASC';
            } elseif ($getSort == 'lpt_status_desc') {
                $string = '__lpt.status DESC';
            }

            # Задаем параметры сортировки eсли значение переменной с 1 нижним подчеркиванием
            elseif ($sortInfo[0] == 'fio') {
                $string = '__users.lastname ' . $sort . ', __users.firstname ' . $sort . ', __users.patronymic ' . $sort;
            } elseif ($sortInfo[0] == 'phone') {
                $string = '__users.phone_mobile ' . $sort;
            } elseif ($sortInfo[0] == 'number') {
                $string = '__user_balance.zaim_number ' . $sort;
            } elseif ($sortInfo[0] == 'date') {
                $string = '__user_balance.zaim_date ' . $sort;
            } elseif ($sortInfo[0] == 'summ') {
                $string = '__user_balance.zaim_summ ' . $sort;
            } elseif ($sortInfo[0] == 'payment') {
                $string = '__user_balance.ostatok_od ' . $sort;
            } elseif ($sortInfo[0] == 'prolongation') {
                $string = '__user_balance.prolongation_count ' . $sort;
            } elseif ($sortInfo[0] == 'status') {
                $string = '__user_balance.cc_status ' . $sort;
            } elseif ($sortInfo[0] == 'tag') {
                $string = '__lpt.tag ' . $sort;
            }
        }
        return 'ORDER BY ' . $string;
    }

    public function sortApeals() {
        $string = 'AppealDate DESC';
        $getSort = $this->request->get('sort', 'string');
        if ($getSort) {
            if ($getSort == 'id_asc') {
                $string = 'Id ASC';
            } elseif ($getSort == 'id_desc') {
                $string = 'Id DESC';
            } elseif ($getSort == 'date_asc') {
                $string = 'AppealDate ASC';
            } elseif ($getSort == 'date_desc') {
                $string = 'AppealDate DESC';
            } elseif ($getSort == 'them_asc') {
                $string = 'Them ASC';
            } elseif ($getSort == 'them_desc') {
                $string = 'Them DESC';
            } elseif ($getSort == 'text_asc') {
                $string = 'Text ASC';
            } elseif ($getSort == 'text_desc') {
                $string = 'Text DESC';
            } elseif ($getSort == 'telephone_asc') {
                $string = 'Phone ASC';
            } elseif ($getSort == 'telephone_desc') {
                $string = 'Phone DESC';
            } elseif ($getSort == 'email_from_asc') {
                $string = 'Email ASC';
            } elseif ($getSort == 'email_from_desc') {
                $string = 'Email DESC';
            } elseif ($getSort == 'email_to_asc') {
                $string = 'ToEmail ASC';
            } elseif ($getSort == 'email_to_desc') {
                $string = 'ToEmail DESC';
            }
        }
        return ' ORDER BY ' . $string;
    }

    public function sortMyTasks() {
        $string = ' taskStatus ASC, taskDate ASC';
        $getSort = $this->request->get('sort', 'string');
        if ($getSort) {
            if ($getSort == 'id_asc') {
                $string = 'id ASC';
            } elseif ($getSort == 'id_desc') {
                $string = 'id DESC';
            } elseif ($getSort == 'ticket_asc') {
                $string = 'ticketId ASC';
            } elseif ($getSort == 'ticket_desc') {
                $string = 'ticketId DESC';
            } elseif ($getSort == 'header_asc') {
                $string = 'taskType ASC';
            } elseif ($getSort == 'header_desc') {
                $string = 'taskType DESC';
            } elseif ($getSort == 'status_asc') {
                $string = 'taskStatus ASC';
            } elseif ($getSort == 'status_desc') {
                $string = 'taskStatus DESC';
            } elseif ($getSort == 'dateEnd_asc') {
                $string = 'dateCreate ASC';
            } elseif ($getSort == 'dateEnd_desc') {
                $string = 'dateCreate DESC';
            } elseif ($getSort == 'dateEdit_asc') {
                $string = 'dateEdit ASC';
            } elseif ($getSort == 'dateEdit_desc') {
                $string = 'dateEdit DESC';
            } elseif ($getSort == 'date_asc') {
                $string = 'dateCreate ASC';
            } elseif ($getSort == 'date_desc') {
                $string = 'dateCreate DESC';
            }
        }
        return ' ORDER BY ' . $string;
    }

}
