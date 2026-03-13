<?PHP

require_once dirname(__DIR__) . '/api/addons/sms_new.php';
require_once('View.php');

/**
 * Class SmsInfoView
 * Просмотр информации о балансе и т.д.
 */
class SmsInfoView extends View
{
	function fetch()
	{
        if ($this->manager->id == 167)
            return $this->design->fetch('403.tpl');

        $balance = get_balance($this,'boostra');
        $this->design->assign('balance', $balance);

		return $this->design->fetch('sms_info.tpl');
	}	
}
