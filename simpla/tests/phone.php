<?PHP
$phone = '+7 (937) 204-69-07';
print_r($phone);
print_r('<br/><br/>');
$replace = array('+','(',')',' ','-');
$phone = str_replace($replace,'',$phone);
print_r($phone);
print_r('<br/><br/>');