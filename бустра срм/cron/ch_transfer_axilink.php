<?php

require_once __DIR__ . '/../api/Simpla.php';

define('PATH_SOURCE', __DIR__ . '/../files/axilink/');
define('PATH_TARGET', __DIR__ . '/../files/axilink_zipped/');
define('CHUNK_SIZE', 100);

class CreditHistoryTransferAxilink extends Simpla
{
    public function process()
    {
        $files_list  = glob(PATH_SOURCE . "*.zip");
        $file_chunks = array_chunk($files_list, CHUNK_SIZE);
        foreach($file_chunks as $indx => $chunk) {
            echo str_pad('Processing ' . ($indx + 1) . ' of ' . count($file_chunks), 50, ' ');
            foreach($chunk as $path) {
                $fname = explode('.', array_reverse(explode('/', $path))[0])[0];
                $query = $this->db->placehold("SELECT
                                                    IFNULL(sc.scorista_id, sc_old.scorista_id) agrid
                                                FROM __axilink axi
                                                LEFT JOIN __scorings sc
                                                    ON sc.order_id = axi.order_id
                                                    AND sc.type = ?
                                                    AND sc.scorista_id
                                                LEFT JOIN __scorings_old sc_old
                                                    ON sc_old.order_id = axi.order_id
                                                    AND sc_old.type = 'axilink'
                                                    AND sc_old.scorista_id
                                                WHERE
                                                    axi.app_id = ?
                                                LIMIT 1", $this->scorings::TYPE_AXILINK, $fname);
                $this->db->query($query);
                $agrid = $this->db->result('agrid');

                if($agrid) {
                    $zip_source = new ZipArchive();
                    $zip_target = new ZipArchive();

                    $zip_source->open($path, ZipArchive::RDONLY);
                    $zip_target->open(PATH_TARGET . $agrid . '.zip', ZipArchive::CREATE);
            
                    $zip_target->addFromString($agrid . '.xml', $zip_source->getFromIndex(0));
                    $zip_target->setCompressionIndex(0, ZipArchive::CM_LZMA, 9);
            
                    $zip_source->close();
                    $zip_target->close();
            
                    unlink($path);
                }
            }
            echo str_pad('', 50, chr(8));
            echo str_pad('', 50, ' ');
            echo str_pad('', 50, chr(8));
        }
    }
}

(new CreditHistoryTransferAxilink)->process();