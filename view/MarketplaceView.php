<?PHP

require_once('View.php');

class MarketplaceView extends View
{
    function fetch()
    {
        if ($doc = $this->request->get('doc'))
        {
            $this->download_document($doc);
        }
        return $this->design->fetch('marketplace.tpl');
    }

    function download_document($name)
    {
        $url = 'http://boostra.ru/files/doc/' . $name;
        header("Content-disposition: attachment;filename=$name");
        readfile($url);
        exit();
    }
}
