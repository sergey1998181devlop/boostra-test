<?php

namespace Api\VerifyConnection;

require_once(dirname(__DIR__) . '/VerifyConnection/VerifyBaseData.php');
require_once(dirname(__DIR__) . '/VerifyConnection/VerifyModelInterface.php');

class VerifyConnectionAdapter
{
    protected $model = null;

    public function __construct(VerifyModelInterface $model)
    {
        $this->model = $model;
    }

    public function create(VerifyBaseData $info)
    {
        return $this->model->create($info);
    }

    public function set(VerifyBaseData $info): bool
    {
        return $this->model->set($info);
    }

    public function get(string $hash)
    {
        return $this->model->get($hash);
    }
}