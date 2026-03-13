<?php

require_once 'View.php';

class VoxOperatorsSettingsView extends View
{
    private const DEFAULT_LIMIT = 20;

    public function __construct()
    {
        parent::__construct();

        $action = $this->request->get('action');

        if ($this->request->method('post')) {
            $this->handlePost();
        }

        if ($action && method_exists($this, $action)) {
            $this->{$action}();
        }
    }

    public function fetch(): string
    {
        $search = $this->request->get('search');
        $page = (int)($this->request->get('page') ?: 1);
        $limit = self::DEFAULT_LIMIT;

        $users = $this->voxUsers->getFiltered($search, $page, $limit);
        $total = $this->voxUsers->countFiltered($search);
        $totalPages = (int)ceil($total / $limit);

        $departments = $this->voxUserDepartments->getAll();

        $this->design->assign('meta_title', 'Настройки операторов Vox');
        $this->design->assign('users', $users);
        $this->design->assign('departments', $departments);
        $this->design->assign('search', $search);
        $this->design->assign('page', $page);
        $this->design->assign('total', $total);
        $this->design->assign('total_pages', $totalPages);
        $this->design->assign('limit', $limit);

        return $this->design->fetch('vox_operators_settings.tpl');
    }

    private function handlePost(): void
    {
        $action = $this->request->post('action');

        switch ($action) {
            case 'save_settings':
                $this->saveSettings();
                break;
            case 'add_department':
                $this->addDepartment();
                break;
            case 'update_department':
                $this->updateDepartment();
                break;
            case 'delete_department':
                $this->deleteDepartment();
                break;
            case 'set_user_department':
                $this->setUserDepartment();
                break;
        }
    }

    private function saveSettings(): void
    {
        $enabledIds = $this->request->post('enabled_users');
        $enabledIds = is_array($enabledIds) ? array_map('intval', $enabledIds) : [];

        $users = $this->voxUsers->getAll();
        foreach ($users as $user) {
            $isEnabled = in_array((int)$user->vox_user_id, $enabledIds, true);
            $this->voxUsers->setEnabledForCallAnalysis((int)$user->vox_user_id, $isEnabled);
        }

        $this->design->assign('save_success', true);
    }

    private function toggle(): void
    {
        $voxUserId = (int)$this->request->get('vox_user_id');
        $enabled = (bool)$this->request->get('enabled');

        if (!empty($voxUserId)) {
            $this->voxUsers->setEnabledForCallAnalysis($voxUserId, $enabled);
        }

        $this->json_output(['status' => 'success']);
    }

    private function addDepartment(): void
    {
        $name = trim($this->request->post('name'));

        if (empty($name)) {
            $this->json_output(['error' => 'Укажите название подразделения']);
            return;
        }

        $id = $this->voxUserDepartments->add($name);
        $this->json_output(['status' => 'success', 'id' => $id, 'name' => $name]);
    }

    private function updateDepartment(): void
    {
        $id = (int)$this->request->post('id');
        $name = trim($this->request->post('name'));

        if (empty($name)) {
            $this->json_output(['error' => 'Укажите название подразделения']);
            return;
        }

        $this->voxUserDepartments->update($id, $name);
        $this->json_output(['status' => 'success', 'id' => $id, 'name' => $name]);
    }

    private function deleteDepartment(): void
    {
        $id = (int)$this->request->post('id');

        $this->voxUserDepartments->delete($id);
        $this->json_output(['status' => 'success']);
    }

    private function setUserDepartment(): void
    {
        $voxUserId = (int)$this->request->post('vox_user_id');
        $departmentId = $this->request->post('department_id');
        $departmentId = ($departmentId !== '' && $departmentId !== null) ? (int)$departmentId : null;

        if (!empty($voxUserId)) {
            $this->voxUsers->setDepartment($voxUserId, $departmentId);
        }

        $this->json_output(['status' => 'success']);
    }
}
