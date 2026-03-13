<?php

require_once 'View.php';

/**
 * Страница CRUD для Vox DNC по сайту (отключение звонков робота).
 * Шаблон и данные для селектов — здесь; список записей и операции — через API (JS).
 */
class VoxSiteDncView extends View
{
    public function fetch(): string
    {
        $sites = $this->sites->getActiveSites();
        $sites = is_array($sites) ? $sites : [];

        $organizations = $this->organizations->getList();

        $this->design->assign('meta_title', 'Vox DNC по сайтам');
        $this->design->assign('sites', $sites);
        $this->design->assign('organizations', $organizations);
        $this->design->assign('sites_json', json_encode($sites));
        $this->design->assign('organizations_json', json_encode($organizations));

        return $this->design->fetch('vox_site_dnc.tpl');
    }
}
