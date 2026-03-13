<?php
define('PATH', __DIR__ . '/../files/');
define('CHUNK_SIZE', 100);

$dirs = implode(',', ['equifax', 'axilink']);

$files_list  = array_filter(glob(PATH . "{{$dirs}}/*", GLOB_BRACE), fn($item) => count(explode('.', array_reverse(explode('/', $item))[0])) == 1);
$file_chunks = array_chunk($files_list, CHUNK_SIZE);
foreach($file_chunks as $chunk) {
    foreach($chunk as $fnum => $fname) {
        $zip = new ZipArchive();
        $zip->open("$fname.zip", ZipArchive::CREATE);
        $zip->addFile($fname, array_reverse(explode('/', $fname))[0]);
        $zip->setCompressionIndex(0, ZipArchive::CM_LZMA, 9);
        $zip->close();
        unlink($fname);
        #echo "$fnum: $fname\n";
    }
    break;
}
