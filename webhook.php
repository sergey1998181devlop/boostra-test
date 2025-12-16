<?php
// Веб-хуки приходят в json, конвертируем его
$hook = json_decode(file_get_contents('php://input'));

// Состояния каналов
if (isset($hook->channels)) {
    $log = date('Y-m-d H:i:s') . ' Состояние каналов - ' . print_r($hook->channels, true);
    file_put_contents(__DIR__ . '/log.txt', $log . PHP_EOL, FILE_APPEND);
 foreach ($hook->channels as $channel) {
   // Перебор полей объекта. Тут с данными можно что-то сделать - например, положить в БД
   foreach ($channel as $key => $value) {
     error_log("$key : $value");
   }
 }
}

// Сообщения
if (isset($hook->messages)) {
 foreach ($hook->messages as $message) {
   // Перебор полей объекта. Тут с данными можно что-то сделать - например, положить в БД
    $log = date('Y-m-d H:i:s') . ' Сообщения - ' . print_r($message, true);
    file_put_contents(__DIR__ . '/log.txt', $log . PHP_EOL, FILE_APPEND);
   foreach ($message as $key => $value) {
          
     if($key == 'status' && $value == 99) {
      $log = date('Y-m-d H:i:s') . ' => ' . $message->chatId . ' - ' . $message->authorName . ' - ' . $message->text;
      file_put_contents(__DIR__ . '/logs/replay_chat.txt', $log . PHP_EOL, FILE_APPEND);
     }
   }
 }
}

// Статусы сообщений
if (isset($hook->statuses)) {
    $log = date('Y-m-d H:i:s') . ' Статусы сообщений - ' . print_r($hook->statuses, true);
    file_put_contents(__DIR__ . '/log.txt', $log . PHP_EOL, FILE_APPEND);
 foreach ($hook->statuses as $status) {
   // Перебор полей объекта. Тут с данными можно что-то сделать - например, положить в БД
   foreach ($status as $key => $value) {
     error_log("$key : $value");
   }
 }
}

?>