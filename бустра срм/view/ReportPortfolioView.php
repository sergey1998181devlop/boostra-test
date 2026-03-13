<?php
/*
CREATE TABLE `s_local_storage` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`type` VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'Группа файлов' COLLATE 'utf8mb4_unicode_ci',
	`name` VARCHAR(250) NOT NULL DEFAULT '' COMMENT 'Описание файла' COLLATE 'utf8mb4_unicode_ci',
	`path` VARCHAR(250) NOT NULL DEFAULT '' COMMENT 'Путь к файлу относительно коневой папки сайта' COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `type` (`type`) USING BTREE
)
COMMENT='Описания файлов на сервере'
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB;
*/

require_once 'View.php';

class ReportPortfolioView extends View
{
    public function __construct()
    {
        parent::__construct();
        $action = $this->request->get('action');

        if (method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    public function fetch()
    {
        $query = $this->db->placehold('SELECT
                                            ls.name,
                                            GROUP_CONCAT(CONCAT(ls.type, "\t", ls.path, "\t", ls.id)) reports
                                        FROM s_local_storage ls
                                        WHERE ls.type IN (?@)
                                        GROUP BY ls.name', ['pdn_remains', 'pdn_quarterly']);
        $this->db->query($query);
        $results = $this->db->results();

        $reports = [];
        foreach($results as $row) {
            $report = [];
            foreach(explode(',', $row->reports) as $packed) {
                $info = explode("\t", $packed);
                $report[$info[0]] = [
                    'id' => $info[2],
                    'path' => $info[1],
                ];
            }
            $report['date'] = $row->name;
            $reports[] = $report;
        }
        $this->design->assign('reports', $reports);

        return $this->design->fetch('pdn_reports_list.tpl');
    }

    private function download()
    {
        $file_id = $this->request->get('file_id');

        $query = $this->db->placehold('SELECT *
                                            FROM s_local_storage ls
                                            WHERE ls.id = ?', $file_id);
        $this->db->query($query);
        $metadata = $this->db->result();
        $ext = array_reverse(explode('.', $metadata->path))[0];

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename("{$metadata->type}_{$metadata->name}.$ext").'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize(__DIR__ . '/../' . $metadata->path));
        readfile(__DIR__ . '/../' . $metadata->path);
        exit;
    }
}