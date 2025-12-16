<?PHP

/**
 * Simpla CMS
 * Storefront class: Каталог товаров
 *
 * Этот класс использует шаблоны hits.tpl
 *
 * @copyright 	2010 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 *
 *
 */

require_once('View.php');


class CarDepositView extends View
{

	function fetch()
	{
		if($this->page)
		{
			$this->design->assign('main_page', true);
			$this->design->assign('meta_title', $this->page->meta_title);
			$this->design->assign('meta_keywords', $this->page->meta_keywords);
			$this->design->assign('meta_description', $this->page->meta_description);
            $this->design->assign('max_period', $this->orders::MAX_PERIOD);
		}

		if($this->request->method('post') && !$this->show_unaccepted_agreement_modal())
		{
			
			$name = $this->request->post('name');
			$phone = $this->request->post('phone');
			$email = $this->request->post('email');
			$car_number = $this->request->post('car_number');

			$aplication = array(
				'name' => $name,
				'phone' => $phone,
				'email' => $email,
				'car_number' => $car_number,
			);

			$application_id = $this->car_deposit_application->addApplication($aplication);

			if($application_id) {
				$this->design->assign('isSuccess', true);
			}
		}

		return $this->design->fetch('car_deposit/car_deposit.tpl');
	}
}
