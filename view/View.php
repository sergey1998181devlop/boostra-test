<?PHP

/**
 * Simpla CMS
 *
 * @copyright 	2011 Denis Pikusov
 * @link 		http://simp.la
 * @author 		Denis Pikusov
 *
 * Базовый класс для всех View
 *
 */

use api\helpers\BalanceHelper;

use api\helpers\UserHelper;

require_once('api/Simpla.php');

class View extends Simpla
{
	/* Смысл класса в доступности следующих переменных в любом View */
	public $user;
	public $contract_user;
	public $page;

	/* Класс View похож на синглтон, храним статически его инстанс */
	private static $view_instance;

	public function __construct()
	{
		parent::__construct();
        unset($_SESSION['init_smart_captcha']);
//echo $_SERVER['REMOTE_ADDR'];94.154.39.111
		// Если инстанс класса уже существует - просто используем уже существующие переменные
		if(self::$view_instance)
		{
			$this->user         = &self::$view_instance->user;
			$this->contract_user= &self::$view_instance->contract_user;
			$this->page         = &self::$view_instance->page;
		}
		else
		{
			// Сохраняем свой инстанс в статической переменной,
			// чтобы в следующий раз использовать его
			self::$view_instance = $this;


			// Пользователь, если залогинен
			if(isset($_SESSION['user_id']))
			{
				$u = $this->users->get_user(intval($_SESSION['user_id']));
				if($u && empty($u->blocked))
				{
					$this->user = $u;

				}
			}

            // если пользователь залогинен по паспорту
            if (isset($_SESSION['passport_user'])) {
                $this->user = $_SESSION['passport_user'];
            }
            if (isset($_SESSION['contract_user_id'])) {
                $this->contract_user = $this->users->get_user(intval($_SESSION['contract_user_id']));
            }


			// Текущая страница с / в начале, но без ? в конце
			$page_link_clear = (explode('?', $_SERVER['REQUEST_URI']))[0];

			$show_get_a_loan = true;
			$hide_get_a_loan_routes = [
				'/',
				'/user/login',
				'/user/passport',
				'/user/register',
				'/user/logout',
				'/user/loanhistory',
				'/user/delete_account',
				'/user/upload',
				'/user/payment',
				'/user/docs',
				'/user/b2p_payment',
				'/user/credit_rating',
				'/neworder',
			];
			if (in_array($page_link_clear, $hide_get_a_loan_routes)) {
				$show_get_a_loan = false;
			}

			$this->design->assign('show_get_a_loan', $show_get_a_loan);


            $page_url = ltrim($page_link_clear,'/');
 			$this->page = $this->pages->get_page((string)$page_url);
			$this->design->assign('page', $this->page);

            // Передаем в дизайн то, что может понадобиться в нем
            $this->design->assign('user', $this->user);

            if (!empty($this->user))
            {
                if (isset($_SESSION['restricted_mode']) && $_SESSION['restricted_mode'] == 1){
                    $this->check_restricted_user_request();
                }
                $this->design->assign('user_approved', (int)$this->users->getUserApprove($this->user->id));
                $this->design->assign('user_return_credit_doctor', (int)($this->users->getUserReturnExtraService($this->user->id,'credit_doctor') > 3));
                //$this->design->assign('pdn', $this->users->getExcessedPdn($this->user->id));
                $clear_passport_serial = $this->users::splitPassportSerial($this->user->passport_serial);
                $this->design->assign('passport_number', $clear_passport_serial['number']);
                $this->design->assign('passport_serial', $clear_passport_serial['serial']);

                $debtInDays = BalanceHelper::getDebtInDays($this->users->get_user_balance($this->user->id));
                $this->design->assign('debtInDays', $debtInDays);
                $this->design->assign('notice_contact_me_available', $this->users->check_contact_me_notice_availability((int) $this->user->id));
            }

            $this->design->assign('vapidPublicKey', $this->config->vapid_public_key);
            $this->design->assign('juiceScoreToken',  $this->settings->apikeys['juicescore']['token'] ?? '');
			$this->design->assign('base_percents', $this->orders::BASE_PERCENTS);

            $this->design->assign('config',		$this->config);
			$this->design->assign('settings',	$this->settings);

            $this->design->assign('is_developer', $this->is_developer);
            $this->design->assign('is_admin', $this->is_admin);
            $this->design->assign('is_looker', $this->is_looker);

//			// Настраиваем плагины для смарти
//			$this->design->smarty->registerPlugin("function", "get_posts",					array($this, 'get_posts_plugin'));
//			$this->design->smarty->registerPlugin("function", "get_brands",					array($this, 'get_brands_plugin'));
//			$this->design->smarty->registerPlugin("function", "get_browsed_products",		array($this, 'get_browsed_products'));
//			$this->design->smarty->registerPlugin("function", "get_featured_products",		array($this, 'get_featured_products_plugin'));
//			$this->design->smarty->registerPlugin("function", "get_new_products",			array($this, 'get_new_products_plugin'));
//			$this->design->smarty->registerPlugin("function", "get_discounted_products",	array($this, 'get_discounted_products_plugin'));
		}
	}

	/**
	 *
	 * Отображение
	 *
	 */
	function fetch()
	{
		return false;
	}

	/**
	 *
	 * Плагины для смарти
	 *
	 */
	public function get_posts_plugin($params, &$smarty)
	{
		if(!isset($params['visible']))
			$params['visible'] = 1;
		if(!empty($params['var']))
			$smarty->assign($params['var'], $this->blog->get_posts($params));
	}

    public function check_restricted_user_request()
    {
        $required_route = (explode('?', $_SERVER['REQUEST_URI']))[0];

        $allowed_routes = [
            '/',
            '/user/login',
            '/user/logout',
            '/user/payment',
            '/user',
            '/info',
            '/info_partners',
            '/contacts',
            '/best2pay_callback/payment',
        ];

        if (!in_array($required_route, $allowed_routes)) {
            header('Location: ' . $this->config->root_url . '/user');
            exit;
        }
    }

	public function get_brands_plugin($params, &$smarty)
	{
		if(!empty($params['var']))
			$smarty->assign($params['var'], $this->brands->get_brands($params));
	}

	public function get_browsed_products($params, &$smarty)
	{
		if(!empty($_COOKIE['browsed_products']))
		{
			$browsed_products_ids = explode(',', $_COOKIE['browsed_products']);
			$browsed_products_ids = array_reverse($browsed_products_ids);
			if(isset($params['limit']))
				$browsed_products_ids = array_slice($browsed_products_ids, 0, $params['limit']);

			$products = array();
			foreach($this->products->get_products(array('id'=>$browsed_products_ids, 'visible'=>1)) as $p)
				$products[$p->id] = $p;

			$browsed_products_images = $this->products->get_images(array('product_id'=>$browsed_products_ids));
			foreach($browsed_products_images as $browsed_product_image)
				if(isset($products[$browsed_product_image->product_id]))
					$products[$browsed_product_image->product_id]->images[] = $browsed_product_image;

			foreach($browsed_products_ids as $id)
			{
				if(isset($products[$id]))
				{
					if(isset($products[$id]->images[0]))
						$products[$id]->image = $products[$id]->images[0];
					$result[] = $products[$id];
				}
			}
			$smarty->assign($params['var'], $result);
		}
	}


	public function get_featured_products_plugin($params, &$smarty)
	{
		if(!isset($params['visible']))
			$params['visible'] = 1;
		$params['featured'] = 1;
		if(!empty($params['var']))
		{
			foreach($this->products->get_products($params) as $p)
				$products[$p->id] = $p;

			if(!empty($products))
			{
				// id выбраных товаров
				$products_ids = array_keys($products);

				// Выбираем варианты товаров
				$variants = $this->variants->get_variants(array('product_id'=>$products_ids, 'in_stock'=>true));

				// Для каждого варианта
				foreach($variants as &$variant)
				{
					// добавляем вариант в соответствующий товар
					$products[$variant->product_id]->variants[] = $variant;
				}

				// Выбираем изображения товаров
				$images = $this->products->get_images(array('product_id'=>$products_ids));
				foreach($images as $image)
					$products[$image->product_id]->images[] = $image;

				foreach($products as &$product)
				{
					if(isset($product->variants[0]))
						$product->variant = $product->variants[0];
					if(isset($product->images[0]))
						$product->image = $product->images[0];
				}
			}

			$smarty->assign($params['var'], $products);

		}
	}


	public function get_new_products_plugin($params, &$smarty)
	{
		if(!isset($params['visible']))
			$params['visible'] = 1;
		if(!isset($params['sort']))
			$params['sort'] = 'created';
		if(!empty($params['var']))
		{
			foreach($this->products->get_products($params) as $p)
				$products[$p->id] = $p;

			if(!empty($products))
			{
				// id выбраных товаров
				$products_ids = array_keys($products);

				// Выбираем варианты товаров
				$variants = $this->variants->get_variants(array('product_id'=>$products_ids, 'in_stock'=>true));

				// Для каждого варианта
				foreach($variants as &$variant)
				{
					// добавляем вариант в соответствующий товар
					$products[$variant->product_id]->variants[] = $variant;
				}

				// Выбираем изображения товаров
				$images = $this->products->get_images(array('product_id'=>$products_ids));
				foreach($images as $image)
					$products[$image->product_id]->images[] = $image;

				foreach($products as &$product)
				{
					if(isset($product->variants[0]))
						$product->variant = $product->variants[0];
					if(isset($product->images[0]))
						$product->image = $product->images[0];
				}
			}

			$smarty->assign($params['var'], $products);

		}
	}


	public function get_discounted_products_plugin($params, &$smarty)
	{
		if(!isset($params['visible']))
			$params['visible'] = 1;
		$params['discounted'] = 1;
		if(!empty($params['var']))
		{
			foreach($this->products->get_products($params) as $p)
				$products[$p->id] = $p;

			if(!empty($products))
			{
				// id выбраных товаров
				$products_ids = array_keys($products);

				// Выбираем варианты товаров
				$variants = $this->variants->get_variants(array('product_id'=>$products_ids, 'in_stock'=>true));

				// Для каждого варианта
				foreach($variants as &$variant)
				{
					// добавляем вариант в соответствующий товар
					$products[$variant->product_id]->variants[] = $variant;
				}

				// Выбираем изображения товаров
				$images = $this->products->get_images(array('product_id'=>$products_ids));
				foreach($images as $image)
					$products[$image->product_id]->images[] = $image;

				foreach($products as &$product)
				{
					if(isset($product->variants[0]))
						$product->variant = $product->variants[0];
					if(isset($product->images[0]))
						$product->image = $product->images[0];
				}
			}

			$smarty->assign($params['var'], $products);

		}
	}

    /**
     * Проверяет наличие не подписанного доп.соглашения на изменение данных.
     * Если не подписано - возвращает true и выводит модалку
     * @return bool
     */
	public function show_unaccepted_agreement_modal(): bool
    {
        if (empty($_SESSION['user_id']) && empty($_SESSION['passport_user']))
            return false;

        //  Обработка необходимости подписания доп.соглашения (После изменения данных о пользователе в CRM)
        $hasUnacceptedAgreement = $this->users->hasUnacceptedAgreement($this->user->id);
        $this->design->assign('has_unaccepted_agreement', $hasUnacceptedAgreement);
        if ($hasUnacceptedAgreement)
        {
            //  Для корректного отображения неизменённых соглашением данных в модальном окне
            $unacceptedAgreement = $this->users->getUnacceptedAgreement($this->user->id);
            foreach ($unacceptedAgreement as $key => &$value) {
                if (empty($value))
                    $value = $this->user->$key;
            }
            $this->design->assign('unaccepted_agreement', $unacceptedAgreement);
            return true;
        }
        return false;
    }

    /** New methods */
    public function redirectIfNotLoggedIn()
    {
        if( empty( $this->user ) ){
            header( 'Location: ' . $this->config->root_url . '/user/login' );

            exit;
        }
    }

    /**
     * @return void
     */
    protected function initFlow()
    {
        if (!empty($this->settings->flow_after_personal_data['status']) && in_array($this->getUtmSource(), $this->settings->flow_after_personal_data['utm_sources'] ?? [])) {
            $_SESSION['user_flow'] = UserHelper::FLOW_AFTER_PERSONAL_DATA;
        } else {
            unset($_SESSION['user_flow']);
        }
    }

    /**
     * @return bool|int|mixed|string
     */
    protected function getUtmSource()
    {
        return $this->request->get('utm_source') ?: $_COOKIE['utm_source'] ?? '';
    }

    /**
     * @return void
     */
    protected function generateFlowVar()
    {
        switch(UserHelper::getFlow()) {
            case UserHelper::FLOW_AFTER_PERSONAL_DATA:
                $this->design->assign('flow_after_personal_data',  1);
                break;
            default:
                $this->design->assign('default_user_flow',  1);
                break;
        }
    }

    protected function checkEmailRedirect()
    {
        if ($this->getUtmSource() == "e-mail") {
            $this->user_data->set($this->user->id, 'user_returned_from_email', 1);
        }
    }
}
