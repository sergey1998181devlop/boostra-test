<?php

require_once 'View.php';

class PostBackSettingsView extends View
{

    public function fetch()
    {
        if (!in_array('marketing_analyst_junior', $this->manager->permissions)) {
            return $this->design->fetch('403.tpl');
        }

        if ($this->request->method('post'))
        {
            $this->settings->postback = $this->request->post('postback');
        }

        return $this->design->fetch('post_back_settings.tpl');
    }
}