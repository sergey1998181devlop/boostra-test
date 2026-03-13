<?php

require_once('View.php');
require_once(dirname(__DIR__) . '/ajax/LoginClass.php');

/**
 * Class AccountPassportView
 * Класс осуществляющий вход в ЛК по паспорту РФ
 */
class AccountPassportView extends View
{
    /**
     * @return false|string|void
     */
    function fetch()
    {
        return false;
        
        if ($this->user) {
            header('Location: ' . $this->config->root_url . '/user_passport');
            exit();
        }

        $this->design->assign('url_login', '/ajax/login_passport.php?action=login');
        return $this->design->fetch('account_passport_login.tpl');
    }
}