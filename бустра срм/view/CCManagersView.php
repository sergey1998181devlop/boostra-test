<?php

require_once 'View.php';

class CCManagersView extends View
{
    public function fetch()
    {
        if (!in_array('boss_cc', $this->manager->permissions)) {
            return $this->design->fetch('403.tpl');
        }

        $filters = [
            'roles' => ['contact_center', 'contact_center_plus']
        ];
        $ccManagers = $this->managers->get_managers($filters);
        array_filter($ccManagers, function ($manager) {
            return empty($manager->blocked);
        });

        $this->design->assign('ccManagers', $ccManagers);

        return $this->design->fetch('cc_managers.tpl');
    }
}