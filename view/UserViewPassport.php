<?PHP

require_once('View.php');

/**
 * Class UserViewPassport
 * Класс для отображения задолжностей при входе по паспорту РФ
 */
class UserViewPassport extends View
{
    /**
     * @return false|string|void
     */
    function fetch()
    {
        if (empty($this->user)) {
            header('Location: ' . $this->config->root_url . '/user/passport');
            exit();
        }

        $this->show_unaccepted_agreement_modal();

        $array_fio = explode(" ", $this->user['user_name']);

        if (count($array_fio) === 3) {
            $fio = $array_fio[1] . " " . $array_fio[2] . " " . (mb_substr($array_fio[0], 0, 1)) . ".";
        } else {
            $fio = $this->user['user_name'];
        }

        $this->design->assign('user',  $this->user);
        $this->design->assign('fio',  $fio);
        $this->design->assign('url_get_payment_link', '/ajax/login_passport.php?action=get_payment_link');

        return $this->design->fetch('account_passport.tpl');
    }
}

