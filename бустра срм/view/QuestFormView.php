<?PHP

require_once('View.php');

/**
 * Class QuestFormView
 * Настройка анкеты регистрации
 */
class QuestFormView extends View
{
    function fetch()
    {
        if ($this->request->post()) {
            $this->settings->quest_form_sources = trim($this->request->post('sources'));
            $this->settings->quest_form_enabled = $this->request->post('enabled');
        }
        $sources = $this->settings->quest_form_sources;
        $enabled = $this->settings->quest_form_enabled;
        $this->design->assign('sources', $sources);
        $this->design->assign('enabled', $enabled);

        return $this->design->fetch('quest_form_settings.tpl');
    }
}
