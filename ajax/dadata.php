<?php

ini_set('display_errors', 'On');
error_reporting(-1);

chdir('..');
require 'api/Simpla.php';

class Dadata extends Simpla
{

    private $token = "09b179798425c1b70d2c99c7e966815cc3af27f3";


    public function __construct()
    {
        parent::__construct();

        $this->token = $this->settings->apikeys['dadata']['api_key'];
    }


    public function suggest($type, $fields)
    {
        $result = false;
        if ($ch = curl_init("https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/$type")) {
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Token ' . $this->token
            ));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//             curl_setopt($ch, CURLOPT_SSL, 1);
            // json_encode
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
//             $result = json_decode($result, true);
            curl_close($ch);
        }

        return $result;
    }


    private function get_party()
    {
        $query = $this->request->get('query');

        return $this->suggest("party", array("query" => $query, "count" => 50));
    }


    private function get_region()
    {
        $query = $this->request->get('query');
        $request = array("query" => $query, "count" => 50);

        $request['from_bound'] = array('value' => 'region');
        $request['to_bound'] = array('value' => 'region');

        return $this->suggest("address", $request);
    }


    private function get_city($region_kladr_id = '')
    {
        $query = $this->request->get('query');
        $request = array("query" => $query, "count" => 50);

        $request['from_bound'] = array('value' => 'city');
        $request['to_bound'] = array('value' => 'settlement');
        if (!empty($region_kladr_id)) {
            $r = new StdClass();
            $r->kladr_id = $region_kladr_id;
            $request['locations'] = array($r);
            $request['restrict_value'] = true;
        }

//        echo '<hr />'.__FILE__.':'.__LINE__.'<pre>'; var_dump($request); echo '</pre>';
        return $this->suggest("address", $request);
    }


    /**
     * Только город или населенный пункт
     * @return bool|string
     */
    private function get_only_city()
    {
        $query = $this->request->get('query');
        $fias_id = $this->request->get('fias_id');
        $region = $this->request->get('region') ?? null;

        if ($region) {
            $query = $region . ', ' . $query;
        }

        $request = [
            "query" => $query,
            "count" => 20,
            'from_bound' => [
                'value' => 'city',
            ],
            'to_bound' => [
                'value' => 'settlement',
            ],
        ];

        if (!empty($fias_id)) {
            $request['locations'] = compact('fias_id');
        }

        return $this->suggest("address", $request);
    }


    /**
     * Улица
     * @return bool|string
     */
    private function get_street()
    {
        $query = $this->request->safe_get('query');
        $fias_id = $this->request->safe_get('fias_id');

        $region = $this->request->safe_get('region');
        $city = $this->request->safe_get('city');

        $query = ($region ? $region . ', ' : '') . ($city ? $city . ', ' : '') . $query;


        $request_only = [
            'value' => 'street'
        ];

        $request = [
            "query" => $query,
            "count" => 20,
            'from_bound' => $request_only,
            'to_bound' => $request_only,
        ];

        if (!empty($fias_id)) {
            $request['locations'] = compact('fias_id');
        }

        return $this->suggest("address", $request);
    }


    /**
     * Получить дом
     * @return bool|string
     */
    private function get_house()
    {
        $query = $this->request->get('query');
        $fias_id = $this->request->get('fias_id');

        $request = [
            "query" => $query,
            "count" => 20,
            'from_bound' => [
                'value' => 'house',
            ],
        ];

        if (!empty($fias_id)) {
            $request['locations'] = compact('fias_id');
        }

        return $this->suggest("address", $request);
    }


    /**
     * Квартира
     * @return bool|string
     */
    private function get_flat()
    {
        $query = $this->request->get('query');
        $fias_id = $this->request->get('fias_id');

        $request = [
            "query" => $query,
            "count" => 20,
            'from_bound' => [
                'value' => 'house',
            ],
            'to_bound' => [
                'value' => 'flat',
            ],
        ];

        if (!empty($fias_id)) {
            $request['locations'] = compact('fias_id');
        }

        return $this->suggest("address", $request);
    }


    /**
     * Полный адрес
     * @return bool|string
     */
    private function get_full_address()
    {
        $query = $this->request->get('query');

        $request = [
            "query" => $query,
            "count" => 20,
        ];

        return $this->suggest("address", $request);
    }


    private function get_address($city_kladr_id)
    {
        $query = $this->request->get('query');
        $request = array("query" => $query, "count" => 50);

        if (!empty($city_kladr_id)) {
            $r = new StdClass();
            $r->kladr_id = $city_kladr_id;
            $request['locations'] = array($r);
            $request['restrict_value'] = true;

            return $this->suggest("address", $request);
        }
//        echo '<hr />'.__FILE__.':'.__LINE__.'<pre>'; var_dump($request); echo '</pre>';
    }


    /**
     * Имя
     * @return bool|string
     */
    private function get_firstname()
    {
        $query = $this->request->get('query');

        return $this->suggest("fio", ["query" => $query, "count" => 10, 'parts' => ['NAME']]);
    }


    /**
     * Фамилия
     * @return bool|string
     */
    private function get_lastname()
    {
        $query = $this->request->get('query');

        return $this->suggest("fio", ["query" => $query, "count" => 10, 'parts' => ['SURNAME']]);
    }


    /**
     * Отчество
     * @return bool|string
     */
    private function get_patronymic()
    {
        $query = $this->request->get('query');

        return $this->suggest("fio", ["query" => $query, "count" => 10, 'parts' => ['PATRONYMIC']]);
    }


    private function fms_unit()
    {
        $query = $this->request->get('query');

        return $this->suggest("fms_unit", array("query" => $query, "count" => 5));
    }


    public function run()
    {
        $action = $this->request->get('action');
        switch ($action):

            case 'inn':
                return $this->get_party();
                break;

            case 'region':
                $region_kladr_id = $this->request->get('region');

                return $this->get_region();
                break;

            case 'city':
                return $this->get_city($this->request->get('region'));
                break;

            case 'only_city':
                return $this->get_only_city();
                break;

            case 'city_with_region':
                return $this->get_only_city();
                break;

            case 'street':
                return $this->get_street();
                break;

            case 'house':
                return $this->get_house();
                break;

            case 'flat':
                return $this->get_flat();
                break;

            case 'full_address':
                return $this->get_full_address();
                break;

            case 'address':
                return $this->get_address($this->request->get('city'));
                break;

            case 'fms_unit':
                return $this->fms_unit();

            case 'firstname':
                return $this->get_firstname();

            case 'lastname':
                return $this->get_lastname();

            case 'patronymic':
                return $this->get_patronymic();

            default:
                return json_encode(array());

        endswitch;
    }

}

$dadata = new Dadata();
$result = $dadata->run();

/**
 * function join(arr ) {
 * var separator = arguments.length > 1 ? arguments[1] : ", ";
 * return arr.filter(function(n){return n}).join(separator);
 * }
 *
 * function formatCity(suggestion) {
 * var address = suggestion.data;
 * if (address.city_with_type === address.region_with_type) {
 * return address.settlement_with_type || "";
 * } else {
 * return join([
 * address.city_with_type,
 * address.settlement_with_type]);
 * }
 * }
 *
 * var
 * token = "5ef98f5781a106962077fb18109095f9f11ebac1 ",
 * type  = "ADDRESS",
 * $region = $("#region"),
 * $city   = $("#city"),
 * $street = $("#street"),
 * $house  = $("#house");
 *
 * // регион и район
 * $region.suggestions({
 * token: token,
 * type: type,
 * hint: false,
 * bounds: "region-area"
 * });
 *
 * // город и населенный пункт
 * $city.suggestions({
 * token: token,
 * type: type,
 * hint: false,
 * bounds: "city-settlement",
 * constraints: $region,
 * formatSelected: formatCity
 * });
 *
 * // улица
 * $street.suggestions({
 * token: token,
 * type: type,
 * hint: false,
 * bounds: "street",
 * constraints: $city,
 * count: 15
 * });
 *
 * // дом
 * $house.suggestions({
 * token: token,
 * type: type,
 * hint: false,
 * noSuggestionsHint: false,
 * bounds: "house",
 * constraints: $street
 * });
 *
 *
 * console.log($house.suggestions())
 */

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");
//echo '<hr />'.__FILE__.':'.__LINE__.'<pre>'; var_dump($result); echo '</pre>';
echo($result);
