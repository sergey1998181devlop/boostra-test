<?php

//require_once( __DIR__ . '/../vendor/autoload.php');
//require_once( __DIR__ . '/../api/Simpla.php');

/**
 * Class Telegram
 * Класс для работы с API TG
 */
final class TelegramApi
{
    private $token = '';
    private $chat_id = '';
    private $message_thread_id = '';

    /**
     * Данные для информирования мультиполиса
     */
    public const API_KEYS_MULTIPOLIS = [
        'token' => '6089775131:AAFggoJFOYc_E4MRwsOsK_f_IYfuNICcYc0', // BoostraMultipolisBot
        'chat_id' => '-1001862130506',
    ];

    public function __construct($init_data)
    {
        $this->token = $init_data['token'];
        $this->chat_id = $init_data['chat_id'];
        $this->message_thread_id = $init_data['message_thread_id'];
    }

    private function callApi($method, $params)
    {
        $url = sprintf(
            "https://api.telegram.org/bot%s/%s",
            $this->token,
            $method
        );
        $headers = ['Accept-Language: ru,ru-RU'];
        if ($method == 'sendDocument' || $method == 'sendMediaGroup') {
            $headers[] = 'Content-Type:multipart/form-data';
        }
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => FALSE,
            CURLOPT_HEADER => FALSE,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $params,
        ));
        $response = curl_exec($ch);
        return json_decode($response);
    }

    /**
     * Отправляет сообщение
     * @param $message
     * @param string $pars_mode
     * @return mixed
     */
    public function sendMessage($message, string $pars_mode = 'html')
    {
        return $this->callApi('sendMessage', [
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode' => $pars_mode,
            'message_thread_id' => $this->message_thread_id ?? null,
            // 'reply_to_message_id'   => $data->message->message_id,
        ]);
    }

    public function sendDocument($docPath, $docMimeType, $docName)
    {
        $this->callApi('sendDocument', [
            'chat_id' => $this->chat_id,
            'document' => curl_file_create($docPath, $docMimeType, $docName),
            'caption' => $docName,
            'message_thread_id' => $this->message_thread_id ?? null,
        ]);
    }

    public function sendMediaGroup(array $media, array $media_files)
    {
        $files = [];
        foreach ($media_files as $file_name => $media_file) {
            $files[$file_name] = $media_file;
        }

        $this->callApi(
            'sendMediaGroup',
            array_merge(
                [
                    'chat_id' => $this->chat_id,
                    'media' => json_encode($media),
                    'message_thread_id' => $this->message_thread_id ?? null,
                ],
                $files
            )
        );
    }
}
