<?PHP

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

require_once('View.php');

/**
 * Class ProlongationSettingsView
 * /prolongation_settings
 */
class ProlongationSettingsView extends View
{
    function fetch()
    {
        if ($action = $this->request->post('action')) {
            $response = ['error' => 'Unknown action'];
            switch ($action) {
                case 'save_banner-visible':
                    $response = $this->saveBannerVisible();
                    break;

                case 'save_banner-text':
                    $response = $this->saveBannerText();
                    break;
            }

            if (!empty($response))
                $this->json_output($response);
        }

        return $this->design->fetch('prolongation_settings.tpl');
    }

    function saveBannerVisible()
    {
        $prolongation_visible = $this->request->post('settings')['prolongation_visible'];
        $this->settings->prolongation_visible = $prolongation_visible;

        // Стандартный рендер шаблона
        return null;
    }

    function saveBannerText()
    {
        $prolongation_text = $this->request->post('settings')['prolongation_text'];
        foreach ($prolongation_text as &$text) {
            $text = trim($text);
            if (empty($text))
                $text = '';
        }

        $this->settings->prolongation_text = $prolongation_text;

        // Стандартный рендер шаблона
        return null;
    }
}