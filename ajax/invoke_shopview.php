<?php
header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");		
echo('{success: true}');
//fastcgi_finish_request();
set_time_limit(1);

require_once('../api/Simpla.php');
$simpla = new Simpla();

$source = $simpla->request->post('source');
$href = $simpla->request->post('href');

$simpla->db->query("SELECT link_type from s_partner_href WHERE link_type LIKE 'bonon-%' AND href > ''");
$accepted_sources = $simpla->db->results('link_type');
[$type, $suffix] = explode(':', $source);

if(in_array($type, $accepted_sources)) {
    $params = [];
    [$url, $query_params] = explode('?', $href);
    parse_str($query_params, $params);
    if(isset($params['id'])) {
        $simpla->db->query("INSERT INTO __partner_links_visits SET ?%", ['source' => $source, 'href' => "bonon-{$params['id']}", 'phone' => $params['p'] ?? null]);
    }
}