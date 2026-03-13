<?php

require_once 'View.php';

class VoxQueuesSettingsView extends View
{
    public function __construct()
    {
        parent::__construct();

        $action = $this->request->get('action');

        if ($this->request->method('post')) {
            $this->saveSettings();
        }

        if ($action && method_exists($this, $action)) {
            $this->{$action}();
        }
    }

    public function fetch(): string
    {
        $queues = $this->voxQueues->getAll();

        $this->design->assign('meta_title', 'Настройки очередей Vox');
        $this->design->assign('queues', $queues);

        return $this->design->fetch('vox_queues_settings.tpl');
    }

    private function saveSettings(): void
    {
        $enabledIds = $this->request->post('enabled_queues');
        $enabledIds = is_array($enabledIds) ? array_map('intval', $enabledIds) : [];

        $queues = $this->voxQueues->getAll();
        foreach ($queues as $queue) {
            $isEnabled = in_array((int)$queue->vox_queue_id, $enabledIds, true);
            $this->voxQueues->setEnabledForReport((int)$queue->vox_queue_id, $isEnabled);
        }

        $this->design->assign('save_success', true);
    }

    private function toggle(): void
    {
        $voxQueueId = (int)$this->request->get('vox_queue_id');
        $enabled = (bool)$this->request->get('enabled');

        if ($voxQueueId > 0) {
            $this->voxQueues->setEnabledForReport($voxQueueId, $enabled);
        }

        $this->json_output(['status' => 'success']);
    }
}
