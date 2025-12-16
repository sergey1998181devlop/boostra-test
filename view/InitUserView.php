<?PHP

require_once('View.php');

class InitUserView extends View
{
    use \api\traits\AuthButtonsTrait;

	public function fetch()
	{
        if (!empty($this->user->id)) {
            header('Location: ' . $this->config->root_url . '/user');
            exit;
        }

        $_SESSION['sms_count'] = 0;

        if (empty($_SESSION['flow_after_personal_data_step_register'])) {
            $this->users->clearSessionDataFlowAfterPersonalData();
        }

        $user_phone = $_SESSION['user_info']['phone_mobile'] ?? '';
        $calc_amount = $this->request->get('amount', 'integer') ?: $this->orders::MAX_AMOUNT_FIRST_LOAN;
        $calc_period = $this->request->get('period', 'integer') ?: $this->orders::MAX_PERIOD_FIRST_LOAN;

        setcookie('calc_amount', $calc_amount, time() + 3600, '/', $this->config->main_domain);
        setcookie('calc_period', $calc_period, time() + 3600, '/', $this->config->main_domain);

        if (!empty($_SESSION['flow_after_personal_data_step_register']) && $user_phone) {
            $this->design->assign('format_phone', \api\helpers\UserHelper::formatPhoneToMAsk($user_phone));
        }

        $this->design->assign('flow_after_personal_data_register', !empty($_SESSION['flow_after_personal_data_step_register']));
        $this->design->assign('body_class', 'bg-white max-h');
        $this->design->assign('calc_amount', $calc_amount);
        $this->design->assign('calc_period', $calc_period);
        $this->design->assign('user_phone', $user_phone);
        $this->design->assign('is_virtual_card_checkbox', isset($_COOKIE['utm_campaign']) && $_COOKIE['utm_campaign'] === 'vctest');

        $this->initAuthAllButtons();

        unset($_SESSION['esia_id_error']);
        unset($_SESSION['t_id_error']);

        return $this->design->fetch('init_user.tpl');
	}
}
