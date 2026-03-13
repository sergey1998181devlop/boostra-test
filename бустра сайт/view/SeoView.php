<?php
require_once('View.php');

class SeoView extends View
{
	function fetch()
	{
        $r = (string)$this->request->get('r');
        
        $ch = curl_init($this->config->seo_site.$r);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        
        echo $res;
        exit;
	}
}