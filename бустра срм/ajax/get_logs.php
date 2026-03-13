<?php
/*
 *  Функция получения логов из папки logs
 *
 * Параметры запроса
 * token = md5 (TOKEN_SALT . date(d.m.Y))
 * file = название файла без txt
 * dir = папка (пока только одна archive)
 * */

const TOKEN_SALT = "boostra2024";
const AVAILABLE_DIRS = array('', 'archive');
const LOGS_DIR = "logs/";

session_start();

chdir('..');

require_once 'api/Simpla.php';
$simpla = new Simpla();

$token = $simpla->request->get('token') ?? '';
$file = str_replace(array("/","\\",".."),"", $simpla->request->get('file') ?? '');
$dir = $simpla->request->get('dir') ?? '';


if (
    ($token != md5(TOKEN_SALT . date('d.m.Y'))) OR
    !(in_array($dir,AVAILABLE_DIRS))
    )
    {
        echo "Ошибка";
        exit;
    }

if ($file) {
    $folder = ($dir) ? $dir . "/": "";
    $filepath = LOGS_DIR. $folder . $file;
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($filepath));
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit;
}

echo "<h1>Доступ к логам - </h1>";
foreach (AVAILABLE_DIRS as $available_dir) {
    $folder = ($available_dir) ? $available_dir . "/" : $available_dir;
    echo "<h2>" . LOGS_DIR . $folder . "</h2>" . PHP_EOL;

    foreach (glob(LOGS_DIR . $folder . "*.*") as $filename) {
        echo "<p><a href='" . $_SERVER['PHP_SELF'] . "?token={$token}&dir={$available_dir}&file=" . basename($filename) . "' target='_blank'>" .basename($filename) . "</a></p>";
    }
}


