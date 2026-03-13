<?php

/**
 * Класс-обертка для конфигурационного файла с настройками магазина
 * В отличие от класса Settings, Config оперирует низкоуровневыми настройками, например найстройками базы данных.
 *
 *
 * @copyright 	2017 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 */
 
require_once('Simpla.php');

class Config
{
	public $version = '2.3.8';
	
	// Файл для хранения настроек
	public $config_file = 'config/config.php';

	private $vars = array();
	
	// В конструкторе записываем настройки файла в переменные этого класса
	// для удобного доступа к ним. Например: $simpla->config->db_user
	public function __construct()
	{		
		// Читаем настройки из дефолтного файла
		$ini = $this->parse_ini_file_multi(dirname(dirname(__FILE__)).'/'.$this->config_file);
		// Записываем настройку как переменную класса
		foreach($ini as $var=>$value)
			$this->vars[$var] = $value;
		
		// Вычисляем DOCUMENT_ROOT вручную, так как иногда в нем находится что-то левое
		$localpath=getenv("SCRIPT_NAME");
		$absolutepath=getenv("SCRIPT_FILENAME");
		$_SERVER['DOCUMENT_ROOT']=substr($absolutepath,0,strpos($absolutepath,$localpath));

		// Адрес сайта - тоже одна из настроек, но вычисляем его автоматически, а не берем из файла
		$script_dir1 = realpath(dirname(dirname(__FILE__)));
		$script_dir2 = realpath($_SERVER['DOCUMENT_ROOT']);
		$subdir = trim(substr($script_dir1, strlen($script_dir2)), "/\\");

		// Протокол
		$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'? 'https' : 'http';
		if($_SERVER["SERVER_PORT"] == 443)
			$protocol = 'https';
		elseif (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1')))
			$protocol = 'https';
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
			$protocol = 'https';

		$this->vars['protocol'] = $protocol;		
		$this->vars['root_url'] = $protocol.'://'.rtrim($_SERVER['HTTP_HOST']);
		if(!empty($subdir))
			$this->vars['root_url'] .= '/'.$subdir;

		// Подпапка в которую установлена симпла относительно корня веб-сервера
		$this->vars['subfolder'] = $subdir.'/';

		// Определяем корневую директорию сайта
		$this->vars['root_dir'] =  dirname(dirname(__FILE__)).'/';

		// Максимальный размер загружаемых файлов
		$max_upload = (int)(ini_get('upload_max_filesize'));
		$max_post = (int)(ini_get('post_max_size'));
		$memory_limit = (int)(ini_get('memory_limit'));
		$this->vars['max_upload_filesize'] = min($max_upload, $max_post, $memory_limit)*1024*1024;
		
		// Соль (разная для каждой копии сайта, изменяющаяся при изменении config-файла)
		$s = stat(dirname(dirname(__FILE__)).'/'.$this->config_file);
		$this->vars['salt'] = md5(md5_file(dirname(dirname(__FILE__)).'/'.$this->config_file).$s['dev'].$s['ino'].$s['uid'].$s['mtime']);
		
		// Часовой пояс
		if(!empty($this->vars['php_timezone']))
			date_default_timezone_set($this->vars['php_timezone']);
		elseif (!ini_get('date.timezone'))
			date_default_timezone_set('UTC');
	}

	// Магическим методов возвращаем нужную переменную
	public function __get($name)
	{
		if(isset($this->vars[$name]))
			return $this->vars[$name];
		else
			return null;
	}
	
	// Магическим методов задаём нужную переменную
	public function __set($name, $value)
	{
		# Запишем конфиги
		if(isset($this->vars[$name]))
		{
			$conf = file_get_contents(dirname(dirname(__FILE__)).'/'.$this->config_file);
			$conf = preg_replace("/".$name."\s*=.*\n/i", $name.' = '.$value."\r\n", $conf);
			$cf = fopen(dirname(dirname(__FILE__)).'/'.$this->config_file, 'w');
			fwrite($cf, $conf);
			fclose($cf);
			$this->vars[$name] = $value;
		}
	}

	public function token($text)
	{
		return md5($text.$this->salt);
	}	
	
	public function check_token($text, $token)
	{
		if(!empty($token) && $token === $this->token($text))
			return true;
		return false;
	}

    private function parse_ini_file_multi($file, $process_sections = false, $scanner_mode = INI_SCANNER_NORMAL) {
        $explode_str = '.';
        $escape_char = "'";
        // load ini file the normal way
        $data = parse_ini_file($file, $process_sections, $scanner_mode);
        if (!$process_sections) {
            $data = array($data);
        }
        foreach ($data as $section_key => $section) {
            // loop inside the section
            foreach ($section as $key => $value) {
                if (strpos($key, $explode_str)) {
                    if (substr($key, 0, 1) !== $escape_char) {
                        // key has a dot. Explode on it, then parse each subkeys
                        // and set value at the right place thanks to references
                        $sub_keys = explode($explode_str, $key);
                        $subs =& $data[$section_key];
                        foreach ($sub_keys as $sub_key) {
                            if (!isset($subs[$sub_key])) {
                                $subs[$sub_key] = [];
                            }
                            $subs =& $subs[$sub_key];
                        }
                        // set the value at the right place
                        $subs = $value;
                        // unset the dotted key, we don't need it anymore
                        unset($data[$section_key][$key]);
                    }
                    // we have escaped the key, so we keep dots as they are
                    else {
                        $new_key = trim($key, $escape_char);
                        $data[$section_key][$new_key] = $value;
                        unset($data[$section_key][$key]);
                    }
                }
            }
        }
        if (!$process_sections) {
            $data = $data[0];
        }
        return $data;
    }
}
