<?php

require_once 'View.php';

/**
 * Class SettingsSmsCollection
 */
class SettingsSmsCollection extends View
{
    public function fetch()
    {
        if ($this->request->method('post')) {
            $this->handlePostRequest();
        }

        $templates = $this->getSmsTemplates();
        $formattedTemplates = $this->formatTemplates($templates);

        $this->assignTemplates($formattedTemplates);

        return $this->design->fetch('sms_collection_list.tpl');
    }

    private function handlePostRequest()
    {
        $postKey = $this->getPostKey();
        $templateValue = $_POST[$postKey];

        $existingTemplate = $this->sms->get_templates(['type' => $postKey]);

        if (empty($existingTemplate)) {
            $this->sms->add_template([
                'template' => $templateValue,
                'type' => $postKey,
                'name' => 'Просрочка'
            ]);
        } else {
            $this->sms->update_template($existingTemplate[0]->id, [
                'template' => $templateValue,
            ]);
        }
    }

    private function getPostKey()
    {
        return array_keys($_POST)[0];
    }

    private function getSmsTemplates()
    {
        return $this->sms->get_templates([
            'types' => [
                'sms-lk',
                'sms-prolongation',
                'sms-payment'
            ]
        ]);
    }

    private function formatTemplates($templates)
    {
        $formattedTemplates = [];

        foreach ($templates as $template) {
            $formattedTemplates[$template->type] = $template;
        }

        return $formattedTemplates;
    }

    private function assignTemplates($templates)
    {
        $this->design->assign('templates', $templates);
    }
}
