<?php

require_once 'View.php';

class RegistrationRegionView extends View
{

    public function fetch()
    {
        if (!in_array('chief_verificator', $this->manager->permissions))
            return $this->design->fetch('403.tpl');

        if ($this->request->method('post')) {
            $scoring_settings = $this->request->post('settings');

            foreach ($scoring_settings as $scoring_type) {
                $update_item = new StdClass();
                $update_item->active = isset($scoring_type['active']) ? (int)$scoring_type['active'] : 0;

                if (isset($scoring_type['params'])) {
                    $params = (array)$scoring_type['params'];
                    if (isset($params['regions']) && is_array($params['regions'])) {
                        $params['regions'] = implode(',', $params['regions']);
                    }
                    $update_item->params = $params;
                } else {
                    $update_item->params = array();
                }

                $this->scorings->update_type($scoring_type['id'], $update_item);
            }

            if ($positions = $this->request->post('position')) {
                $i = 1;
                foreach ($positions as $pos => $id)
                    $this->scorings->update_type($id, array('position' => $i++));
            }
        } else {
            $scoring_settings = $this->settings->scoring_settings;
        }

        if (!empty($scoring_settings))
            $this->design->assign('scoring_settings', $scoring_settings);

        $scoring_types = $this->scorings->get_types();
        $this->design->assign('scoring_types', $scoring_types);

        $captcha_status = $this->settings->captcha_status;
        $this->design->assign('captcha_status', $captcha_status);

        return $this->design->fetch('registration_region.tpl');
    }


}