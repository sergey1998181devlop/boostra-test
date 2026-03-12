<?php

/**
 * Simpla CMS
 *
 * @copyright	2011 Denis Pikusov
 * @link		http://simplacms.ru
 * @author		Denis Pikusov
 *
 */
 
require_once(dirname(__FILE__).'/'.'Simpla.php');
require_once(dirname(dirname(__FILE__)).'/Smarty/libs/Smarty.class.php');

class Design extends Simpla
{
	public $smarty;

	public function __construct()
	{
		parent::__construct();

		// Создаем и настраиваем Смарти
		$this->smarty = new Smarty();
		$this->smarty->compile_check = $this->config->smarty_compile_check;
		$this->smarty->caching = $this->config->smarty_caching;
		$this->smarty->cache_lifetime = $this->config->smarty_cache_lifetime;
		$this->smarty->debugging = $this->config->smarty_debugging;
		$this->smarty->error_reporting = E_ALL & ~E_NOTICE;

		// Берем тему из настроек
		$theme = $this->settings->theme;
		

		$this->smarty->compile_dir = $this->config->root_dir.'/compiled/'.$theme;
		$this->smarty->template_dir = $this->config->root_dir.'/design/'.$theme.'/html';		

		// Создаем папку для скомпилированных шаблонов текущей темы
		if(!is_dir($this->smarty->compile_dir))
			mkdir($this->smarty->compile_dir, 0777);
						
		$this->smarty->cache_dir = 'cache';
				
		$this->smarty->registerPlugin('modifier', 'resize',		array($this, 'resize_modifier'));		
		$this->smarty->registerPlugin('modifier', 'token',		array($this, 'token_modifier'));
		$this->smarty->registerPlugin('modifier', 'plural',		array($this, 'plural_modifier'));		
		$this->smarty->registerPlugin('function', 'url', 		array($this, 'url_modifier'));		
		$this->smarty->registerPlugin('modifier', 'first',		array($this, 'first_modifier'));		
		$this->smarty->registerPlugin('modifier', 'cut',		array($this, 'cut_modifier'));		
		$this->smarty->registerPlugin('modifier', 'date',		array($this, 'date_modifier'));		
		$this->smarty->registerPlugin('modifier', 'time',		array($this, 'time_modifier'));		
		$this->smarty->registerPlugin('function', 'api',		array($this, 'api_plugin'));

		$this->smarty->registerPlugin('modifier', 'price_string',array($this, 'price_string_modifier'));		
		$this->smarty->registerPlugin('modifier', 'price_string_short',array($this, 'price_string_short_modifier'));		
		$this->smarty->registerPlugin('modifier', 'percent_string',array($this, 'percent_string_modifier'));
        $this->smarty->registerPlugin('modifier', 'fio',          array($this, 'fio_modifier'));

		if($this->config->smarty_html_minify)
			$this->smarty->loadFilter('output', 'trimwhitespace');
	}
	
	public function assign($var, $value)
	{
		return $this->smarty->assign($var, $value);
	}

    public function assignBulk( $params )
    {
        foreach( (array) $params as $item => $value ){
            $this->assign( $item, $value );
        }
    }
    
	public function fetch($template)
	{
		// Передаем в дизайн то, что может понадобиться в нем
		$this->assign('config',		$this->config);
		$this->assign('settings',	$this->settings);
		return $this->smarty->fetch($template);
	}
	
	public function set_templates_dir($dir)
	{
		$this->smarty->template_dir = $dir;			
	}

	public function set_compiled_dir($dir)
	{
		$this->smarty->compile_dir = $dir;
	}
	
	public function get_var($name)
	{
		return $this->smarty->getTemplateVars($name);
	}
	
	public function clear_cache()
	{
		$this->smarty->clearAllCache();	
	}

	private function is_mobile_browser()
	{
		$user_agent = $_SERVER['HTTP_USER_AGENT']; 
		$http_accept = isset($_SERVER['HTTP_ACCEPT'])?$_SERVER['HTTP_ACCEPT']:'';

		if(eregi('iPad', $user_agent))
			return false;
		
		if(stristr($user_agent, 'windows') && !stristr($user_agent, 'windows ce'))
			return false;
		
		if(eregi('windows ce|iemobile|mobile|symbian|mini|wap|pda|psp|up.browser|up.link|mmp|midp|phone|pocket', $user_agent))
			return true;
	
		if(stristr($http_accept, 'text/vnd.wap.wml') || stristr($http_accept, 'application/vnd.wap.xhtml+xml'))
			return true;
			
		if(!empty($_SERVER['HTTP_X_WAP_PROFILE']) || !empty($_SERVER['HTTP_PROFILE']) || !empty($_SERVER['X-OperaMini-Features']) || !empty($_SERVER['UA-pixels']))
			return true;
	
		$agents = array(
		'acs-'=>'acs-',
		'alav'=>'alav',
		'alca'=>'alca',
		'amoi'=>'amoi',
		'audi'=>'audi',
		'aste'=>'aste',
		'avan'=>'avan',
		'benq'=>'benq',
		'bird'=>'bird',
		'blac'=>'blac',
		'blaz'=>'blaz',
		'brew'=>'brew',
		'cell'=>'cell',
		'cldc'=>'cldc',
		'cmd-'=>'cmd-',
		'dang'=>'dang',
		'doco'=>'doco',
		'eric'=>'eric',
		'hipt'=>'hipt',
		'inno'=>'inno',
		'ipaq'=>'ipaq',
		'java'=>'java',
		'jigs'=>'jigs',
		'kddi'=>'kddi',
		'keji'=>'keji',
		'leno'=>'leno',
		'lg-c'=>'lg-c',
		'lg-d'=>'lg-d',
		'lg-g'=>'lg-g',
		'lge-'=>'lge-',
		'maui'=>'maui',
		'maxo'=>'maxo',
		'midp'=>'midp',
		'mits'=>'mits',
		'mmef'=>'mmef',
		'mobi'=>'mobi',
		'mot-'=>'mot-',
		'moto'=>'moto',
		'mwbp'=>'mwbp',
		'nec-'=>'nec-',
		'newt'=>'newt',
		'noki'=>'noki',
		'opwv'=>'opwv',
		'palm'=>'palm',
		'pana'=>'pana',
		'pant'=>'pant',
		'pdxg'=>'pdxg',
		'phil'=>'phil',
		'play'=>'play',
		'pluc'=>'pluc',
		'port'=>'port',
		'prox'=>'prox',
		'qtek'=>'qtek',
		'qwap'=>'qwap',
		'sage'=>'sage',
		'sams'=>'sams',
		'sany'=>'sany',
		'sch-'=>'sch-',
		'sec-'=>'sec-',
		'send'=>'send',
		'seri'=>'seri',
		'sgh-'=>'sgh-',
		'shar'=>'shar',
		'sie-'=>'sie-',
		'siem'=>'siem',
		'smal'=>'smal',
		'smar'=>'smar',
		'sony'=>'sony',
		'sph-'=>'sph-',
		'symb'=>'symb',
		't-mo'=>'t-mo',
		'teli'=>'teli',
		'tim-'=>'tim-',
		'tosh'=>'tosh',
		'treo'=>'treo',
		'tsm-'=>'tsm-',
		'upg1'=>'upg1',
		'upsi'=>'upsi',
		'vk-v'=>'vk-v',
		'voda'=>'voda',
		'wap-'=>'wap-',
		'wapa'=>'wapa',
		'wapi'=>'wapi',
		'wapp'=>'wapp',
		'wapr'=>'wapr',
		'webc'=>'webc',
		'winw'=>'winw',
		'winw'=>'winw',
		'xda-'=>'xda-'
		);
		
		if(!empty($agents[substr($_SERVER['HTTP_USER_AGENT'], 0, 4)]))
	    	return true;
	}	


    function price_string_modifier($value)
    {
    	$value = explode('.', number_format($value, 2, '.', ''));
     
    	$f = new NumberFormatter('ru', NumberFormatter::SPELLOUT);
    	$str = $f->format($value[0]);
     
    	// Первую букву в верхний регистр.
    	$str = mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1, mb_strlen($str));
     
    	// Склонение слова "рубль".
    	$num = $value[0] % 100;
    	if ($num > 19) { 
    		$num = $num % 10; 
    	}	
    	switch ($num) {
    		case 1: $rub = 'рубль'; break;
    		case 2: 
    		case 3: 
    		case 4: $rub = 'рубля'; break;
    		default: $rub = 'рублей';
    	}	
    	
    	return $str . ' ' . $rub . ' ' . $value[1] . ' копеек.';
    }    
    
    function price_string_short_modifier($value)
    {
    	$value = explode('.', number_format($value, 2, '.', ''));
     
    	$f = new NumberFormatter('ru', NumberFormatter::SPELLOUT);
    	$str = $f->format($value[0]);
     
    	// Первую букву в верхний регистр.
    	$str = mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1, mb_strlen($str));
     
    	return $str;
    }    
    
    function percent_string_modifier($value)
    {
    	$value = explode('.', number_format($value, 2, '.', ''));
     
    	$f = new NumberFormatter('ru', NumberFormatter::SPELLOUT);
    	$str = $f->format($value[0]);
     
    	// Первую букву в верхний регистр.
    	$str = mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1, mb_strlen($str));
     
    	// Склонение слова "рубль".
    	$num = $value[0] % 100;
    	if ($num > 19) { 
    		$num = $num % 10; 
    	}	
    	switch ($num) {
    		case 1: $rub = 'целая'; break;
    		case 2: 
    		case 3: 
    		case 4: $rub = 'целых'; break;
    		default: $rub = 'целых';
    	}	
    	
    	return $str . ' ' . $rub . ' ' . $value[1]*1 . ' тысячных';
    }    
    
	public function resize_modifier($filename, $width=0, $height=0, $set_watermark=false)
	{
		$resized_filename = $this->image->add_resize_params($filename, $width, $height, $set_watermark);
		$resized_filename_encoded = $resized_filename;
		
		if(substr($resized_filename_encoded, 0, 7) == 'http://')
			$resized_filename_encoded = rawurlencode($resized_filename_encoded);

		$resized_filename_encoded = rawurlencode($resized_filename_encoded);

		return $this->config->root_url.'/'.$this->config->resized_images_dir.$resized_filename_encoded.'?'.$this->config->token($resized_filename);
	}

	public function token_modifier($text)
	{
		return $this->config->token($text);
	}

	public function url_modifier($params)
	{
		if(is_array(reset($params)))
			return $this->request->url(reset($params));
		else
			return $this->request->url($params);
	}

	public function plural_modifier($number, $singular, $plural1, $plural2=null)
	{
		$number = abs($number); 
		if(!empty($plural2))
		{
		$p1 = $number%10;
		$p2 = $number%100;
		if($number == 0)
			return $plural1;
		if($p1==1 && !($p2>=11 && $p2<=19))
			return $singular;
		elseif($p1>=2 && $p1<=4 && !($p2>=11 && $p2<=19))
			return $plural2;
		else
			return $plural1;
		}else
		{
			if($number == 1)
				return $singular;
			else
				return $plural1;
		}
	
	}

	public function first_modifier($params = array())
	{
		if(!is_array($params))
			return false;
		return reset($params);
	}

	public function cut_modifier($array, $num=1)
	{
		if($num>=0)
	    	return array_slice($array, $num, count($array)-$num, true);
	    else
	    	return array_slice($array, 0, count($array)+$num, true);
	}
	
	public function date_modifier($date, $format = null)
	{
		if(empty($date))
			$date = date("Y-m-d");
	    return date(empty($format)?$this->settings->date_format:$format, strtotime($date));
	}
	
	public function time_modifier($date, $format = null)
	{
	    return date(empty($format)?'H:i':$format, strtotime($date));
	}
	
	public function api_plugin($params, &$smarty)
    {
        if (!isset($params['module']))
            return false;
        if (!isset($params['method']))
            return false;

        $module = $params['module'];
        $method = $params['method'];
        $var = $params['var'];
        unset($params['module']);
        unset($params['method']);
        unset($params['var']);
        $res = $this->$module->$method($params);
        $smarty->assign($var, $res);
    }


    /**
     * Преобразует полное ФИО в склонённую форму с инициалами
     *
     * @param string $fullName Полное ФИО в формате "Фамилия Имя Отчество"
     * @param string $case Род падежа ('genitive' - родительный, 'dative' - дательный)
     * @return string ФИО в формате "Фамилии И.О." (по умолчанию родительный падеж)
     *
     * Примеры:
     * fio_modifier("Иванов Пётр Сергеевич") → "Иванова П.С."
     * fio_modifier("Смирнова Анна Владимировна", 'dative') → "Смирновой А.В."
     */
    public function fio_modifier(?string $fullName, string $case = 'genitive'): string
    {
        if (empty($fullName)) {
            return '';
        }
        // Нормализация входящих данных
        $parts = preg_split('/\s+/', trim($fullName));
        if (count($parts) < 3) {
            return $fullName; // Возвращаем как есть, если не хватает частей
        }

        [$lastname, $firstname, $middlename] = $parts;

        // Определение пола по отчеству
        $gender = preg_match('/(?:вич|вна|тич|тна|чич|чна)$/ui', $middlename)
            ? (preg_match('/вна$|тна$|чна$/ui', $middlename) ? 'female' : 'male')
            : (preg_match('/[ая]$/ui', $firstname) ? 'female' : 'male');

        // Склонение фамилии
        $lastname = $this->decline_last_name($lastname, $gender, $case);

        // Формирование инициалов
        $initials = mb_substr($firstname, 0, 1) . '.' . mb_substr($middlename, 0, 1) . '.';

        return $lastname . ' ' . $initials;
    }

    /**
     * Склоняет фамилию в зависимости от пола и падежа
     * @param string $lastname
     * @param string $gender
     * @param string $case
     * @return string
     */
    public function decline_last_name(string $lastname, string $gender, string $case): string
    {
        // Общие правила склонения
        $replacements = [
            'male' => [
                'genitive' => [
                    '/ов$|ев$/' => 'ова',
                    '/ин$|ын$/' => 'ина',
                    '/ий$/' => 'ого',
                    '/ой$/' => 'ого',
                    '/ец$/' => 'ца'
                ],
                'dative' => [
                    '/ов$|ев$/' => 'ову',
                    '/ин$|ын$/' => 'ину',
                    '/ий$/' => 'ому',
                    '/ой$/' => 'ому'
                ]
            ],
            'female' => [
                'genitive' => [
                    '/ова$|ева$/' => 'овой',
                    '/ина$/' => 'иной',
                    '/ая$/' => 'ой',
                    '/яя$/' => 'ей',
                    '/а$/' => 'ой',
                    '/ь$/' => 'и'
                ],
                'dative' => [
                    '/ова$|ева$/' => 'овой',
                    '/ина$/' => 'иной',
                    '/ая$/' => 'ой',
                    '/яя$/' => 'ей',
                    '/а$/' => 'е',
                    '/ь$/' => 'и'
                ]
            ]
        ];

        // Применение правил
        foreach ($replacements[$gender][$case] ?? [] as $pattern => $replacement) {
            if (preg_match($pattern, $lastname)) {
                return preg_replace($pattern, $replacement, $lastname);
            }
        }

        return $lastname;
    }
}