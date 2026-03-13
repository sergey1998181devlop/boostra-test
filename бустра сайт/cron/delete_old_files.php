<?PHP

header("Content-Type: text/html; charset=utf-8");
header('Cache-Control: no-store, no-cache');
session_start();
require_once dirname(__DIR__) . '/api/Simpla.php';

$simpla = new Simpla();


$query = $simpla->db->placehold("
    SELECT *
    FROM __files
    WHERE NOW() > (created + INTERVAL 2 DAY)
    AND status = 0
    ORDER BY id DESC
");

$simpla->db->query($query);
$results = $simpla->db->results();

foreach ($results as $item)
{
    $original_filename = $simpla->config->root_dir.$simpla->config->original_images_dir.$item->name;
    if (file_exists($original_filename))
    {
        unlink($original_filename);
    }
    
    $ext = pathinfo($item->name, PATHINFO_EXTENSION);
    $basename = pathinfo($item->name, PATHINFO_FILENAME);
    $resized_filename = $simpla->config->root_dir.$simpla->config->resized_images_dir.$basename.'.100x100.'.$ext;
    if (file_exists($resized_filename))
    {
        unlink($resized_filename);
        
    }
    
    $simpla->db->query("DELETE FROM __files WHERE id = ?", $item->id);
}
/*
$query = $simpla->db->placehold("
    SELECT *
    FROM __files
    WHERE NOW() > (created + INTERVAL 4 DAY)
    ORDER BY id DESC
    LIMIT 10000
");

$simpla->db->query($query);
$results = $simpla->db->results();
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($results);echo '</pre><hr />';
foreach ($results as $item)
{
    $original_filename = $simpla->config->root_dir.$simpla->config->original_images_dir.$item->name;
    if (file_exists($original_filename))
    {
//        unlink($original_filename);
    }
        
}
*/
$i = 1;
$border_time = time() - (86400 * 2);
if ($handle = opendir($simpla->config->root_dir.$simpla->config->original_images_dir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $filemtime = filemtime($simpla->config->root_dir.$simpla->config->original_images_dir.$entry);
            if ($border_time > $filemtime)
            {
                unlink($simpla->config->root_dir.$simpla->config->original_images_dir.$entry);
                echo $i.date(' d-m-y ', $filemtime).$simpla->config->root_dir.$simpla->config->original_images_dir." $entry <br />";
                $i++;
            }
        }
    }
    closedir($handle);
}