<?php
define('PATH_SOURCE', __DIR__ . '/../files/equifax/');
define('PATH_TARGET', __DIR__ . '/../files/equifax_zipped/');
define('CHUNK_SIZE', 5);

$files_list  = glob(PATH_SOURCE . "*.zip");
$file_chunks = array_chunk($files_list, CHUNK_SIZE);
foreach($file_chunks as $indx => $chunk) {
    echo str_pad('Processing ' . ($indx + 1) . ' of ' . count($file_chunks), 50, ' ');
    foreach($chunk as $fnum => $path) {
        $zip_source = new ZipArchive();
        $zip_target = new ZipArchive();
        $fname = explode('.', array_reverse(explode('/', $path))[0])[0];

        $zip_source->open($path, ZipArchive::RDONLY);
        $zip_target->open(PATH_TARGET . $fname . '.zip', ZipArchive::CREATE);

        $zip_target->addFromString($fname . '.xml', base64_decode($zip_source->getFromIndex(0), true));
        $zip_target->setCompressionIndex(0, ZipArchive::CM_LZMA, 9);

        $zip_source->close();
        $zip_target->close();

        unlink($path);
    }
    echo str_pad('', 50, chr(8));
    echo str_pad('', 50, ' ');
    echo str_pad('', 50, chr(8));
    break;
}
