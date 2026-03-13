<?php

use boostra\services\DadataService;

require_once 'Simpla.php';
require_once __DIR__ . '/../lib/autoloader.php';

class Dadata extends Simpla
{
    private DadataService $dadataService;

    public function __construct()
    {
        parent::__construct();

        $this->dadataService = new DadataService();
    }

    public function get_party($query, $count = 50)
    {
        return $this->dadataService->suggest("party", array("query" => $query, "count" => $count));
    }

    public function get_region($query, $count = 50)
    {
        $request = new StdClass();
        $request->query = $query;
        $request->count = $count;

        $request->from_bound = new StdClass();
        $request->from_bound->value = 'region';
        $request->to_bound = new StdClass();
        $request->to_bound->value = 'region';

        return $this->dadataService->suggest("address", $request);
    }

    public function get_city($region_kladr_id, $query, $count = 50)
    {
        $request = new StdClass();
        $request->query = $query;
        $request->count = $count;

        $request->from_bound = new StdClass();
        $request->from_bound->value = 'city';
        $request->to_bound = new StdClass();
        $request->to_bound->value = 'settlement';

        if (!empty($region_kladr_id)) {
            $r = new StdClass();
            $r->kladr_id = $region_kladr_id;
            $request->locations = array($r);
            $request->restrict_value = true;
        }
        return $this->dadataService->suggest("address", $request);
    }

    public function get_street($city_kladr_id, $query, $count = 50)
    {
        $request = new StdClass();
        $request->query = $query;
        $request->count = $count;

        $request->from_bound = new StdClass();
        $request->from_bound->value = 'street';
        $request->to_bound = new StdClass();
        $request->to_bound->value = 'street';

        if (!empty($city_kladr_id)) {
            $r = new StdClass();
            $r->kladr_id = $city_kladr_id;
            $request->locations = array($r);
            $request->restrict_value = true;
        }
        return $this->dadataService->suggest("address", $request);
    }

    public function get_house($street_kladr_id, $query, $count = 50)
    {
        $request = new StdClass();
        $request->query = $query;
        $request->count = $count;

        $request->from_bound = new StdClass();
        $request->from_bound->value = 'house';
        $request->to_bound = new StdClass();
        $request->to_bound->value = 'house';

        if (!empty($street_kladr_id)) {
            $r = new StdClass();
            $r->kladr_id = $street_kladr_id;
            $request->locations = array($r);
            $request->restrict_value = true;
        }
        return $this->dadataService->suggest("address", $request);
    }

    public function get_address($city_kladr_id, $query, $count = 50)
    {
        $request = array("query" => $query, "count" => $count);

        if (!empty($city_kladr_id)) {
            $r = new StdClass();
            $r->kladr_id = $city_kladr_id;
            $request['locations'] = array($r);
            $request['restrict_value'] = true;

            return $this->dadataService->suggest("address", $request);
        }
    }
}