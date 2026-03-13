<?php

require '../api/Simpla.php';

class MissingsActions extends Simpla {
    private $simpla;
    private $action;
    private $method;

    const ACTION_STAGES = 'missing_stages';
    const ACTION_LAST_CALLS = 'last_calls';

    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';

    public function __construct()
    {
        $this->simpla = new Simpla();
        $this->action = $this->simpla->request->post('action', 'string');
        $this->method = $this->request->method();

        parent::__construct();
    }

    public function run()
    {
        $result = '';

        if ($this->method === self::METHOD_POST) {
            switch ($this->action) {
                case self::ACTION_STAGES:
                    $result = $this->getStageData();
                    break;
                case self::ACTION_LAST_CALLS:
                    $result = $this->getLastCalls();
                    break;
            }
        }

        $this->simpla->response->json_output($result);
    }

    public function getClients(array $user_ids): array
    {
        $clients = $this->users->get_users(['id' => $user_ids]);

        return $clients ?: [];
    }

    /**
     * @return bool|int|mixed|string|null
     */
    public function getUserIds()
    {
        return $this->simpla->request->post('userIds');
    }

    public function getCalls(array $user_ids)
    {
        return $this->mango->get_calls(array('user_id' => $user_ids));
    }

    public function getStageData(): array
    {
        $user_ids = $this->getUserIds();
        if (empty($user_ids)) {
            return [];
        }

        $result = $this->getClients($user_ids);

        return $result;
    }

    private function getLastCalls()
    {
        $user_ids = $this->getUserIds();
        if (empty($user_ids)) {
            return [];
        }

        $calls = $this->getCalls($user_ids);

        $last_calls = [];
        $last_calls = $this->filterCalls($calls, $last_calls);

        $voxCalls = $this->voxCalls->get_calls(['user_id' => $user_ids]);
        $last_calls = $this->filterCalls($voxCalls, $last_calls);

        return $last_calls;
    }

    public function filterCalls($calls, array $last_calls): array
    {
        foreach ($calls as $call) {
            if (!array_key_exists($call->user_id, $last_calls)) {
                $last_calls[] = $call;
                continue;
            }

            if (new DateTime($last_calls[$call->user_id]->created) < new DateTime($call->created)) {
                $last_calls[$call->user_id] = $call;
            }
        }

        return $last_calls;
    }
}
