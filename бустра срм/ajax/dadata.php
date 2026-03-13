<?php

chdir('..');
require 'api/Simpla.php';

class DadataAjax extends Simpla
{
    public function run()
    {
    	$query = $this->request->get('query');

        $action = $this->request->get('action');
        switch ($action):
            
            case 'inn':
                return $this->dadata->get_party($query);
            break;
            
            case 'region':
                return $this->dadata->get_region($query);
            break;
            
            case 'city':
                $region = $this->request->get('region');
                return $this->dadata->get_city($region, $query);
            break;
            
            case 'street':
                $city = $this->request->get('city');
                return $this->dadata->get_street($city, $query);
            break;
            
            case 'house':
                $street = $this->request->get('street');
                return $this->dadata->get_house($street, $query);
            break;
            
            case 'address':
                $city = $this->request->get('city');
                return $this->dadata->get_address($city, $query);
            break;
            
            default:
                return json_encode(array());

        endswitch;
    }
}
$dadata = new DadataAjax();
$result = $dadata->run();

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");	

echo $result;

