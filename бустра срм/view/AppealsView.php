<?php

require_once('View.php');

class AppealsView extends View {

    function fetch() {

        if ($this->manager->id == 167)
            return $this->design->fetch('403.tpl');

        $id = $this->request->get('id', 'integer');

        $tags = $this->tickets->getTicketTags();
        $this->design->assign('tags', $tags);

        $manager = $this->managers->get_manager($_SESSION['manager_id']);
        $this->design->assign('curentManager', $manager);
        $this->design->assign('manager', $this->manager);

        $subjects = $this->tickets->get_subjects();
        $this->design->assign('subjects', $subjects);

        $this->design->assign('taskStatuses', $this->tasks->taskStatuses);
        $this->design->assign('actionsByTaskType', $this->tasks->actionsByTaskType);
        $this->design->assign('executorRole', $this->tasks->executorRole);
        $this->design->assign('taskNames', $this->tasks->taskNames);

        if ($id) {
            $appeal = $this->appeals->getApeal($id);
            $userInfo = $this->users->get_user($this->users->get_phone_user($appeal->Phone));
            $this->design->assign('appeal', $appeal);
            $this->design->assign('userInfo', $userInfo);
            return $this->design->fetch('appeal.tpl');
        } else {
            $appeals = $this->appeals->getApeals();
            $this->design->assign('appeals', $appeals);
            return $this->design->fetch('appeals.tpl');
        }
    }

}
