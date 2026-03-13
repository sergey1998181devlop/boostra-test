<?php

use App\Core\Application\Response\Response;
use App\Enums\CommentBlocks;
use App\Http\Controllers\ClientController;
use App\Models\Comment;
use App\Models\Manager;
use App\Models\User;
use App\Service\CommentService;
use App\Service\VoximplantService;

error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');
require_once 'api/Simpla.php';

class MangoCallback extends Simpla
{
    public $mango;

    private $managerId = 50;

    private $callsFolder = 'files/calls/';

    private $logFile = 'mango.txt';

    public function __construct()
    {
    	parent::__construct();

        $this->mango = new Mango();

        if ($this->request->method('post')) {
            $this->run();
        }
        else {
            exit('ERROR METHOD');
        }
    }

    private function run()
    {
        $vpbx_api_key = $this->request->post('vpbx_api_key') ?? '';
        $sign         = $this->request->post('sign') ?? '';
        $json_raw     = $this->request->post('json') ?? '';

        $json_decoded = urldecode($json_raw);
        $data = json_decode($json_decoded, true);

        $post = [
            'vpbx_api_key' => $vpbx_api_key,
            'sign'         => $sign,
            'json'         => $data,
        ];

        if (isset($post['json']))
        {
            $data = (object)$post['json'];

            $request_method = trim(trim($_GET['m']), '/');

            $this->logging(__METHOD__, 'init: '.$request_method, $data, '', $this->logFile);

            switch ($request_method):

                case 'events/call':
                    if($data->call_state == 'OnHold'){
                        file_get_contents($this->config->back_url . '/chats.php?chat=mango&method=onHoldCall&json='. $json);
                    }elseif($data->call_state == 'Connected'){
                        file_get_contents($this->config->back_url . '/chats.php?chat=mango&method=connectedCall&json='. $json);
                    }elseif($data->call_state == 'Disconnected'){
                        file_get_contents($this->config->back_url . '/chats.php?chat=mango&method=disconnectedCall&json='. $json);
                    }elseif($data->call_state == 'Appeared'){
                        file_get_contents($this->config->back_url . '/chats.php?chat=mango&method=incomingCall&json='. $json);
                    }
                    $this->call_action($data);
                break;

                case 'events/summary':
                    $this->summary_action($data);
                break;

                case 'events/record/added':
                    $this->record_action($data);
                break;

                case 'events/recording':
                    if (isset($data->recording_state) && $data->recording_state === 'Completed') {
                        $this->record_action($data);
                    }
                break;

                case 'result/callback':
                    $this->result_action($data);
                break;


            endswitch;
        }
    }

    private function command2call($command_id)
    {
        if (strpos($command_id, 'ID_') !== false)
        {
            return str_replace('ID_', '', $command_id);
        }
        return NULL;
    }

    private function result_action($data)
    {
        if (!empty($data->command_id))
        {
            if ($mangocall_id = $this->command2call($data->command_id))
                $this->mango->update_call($mangocall_id, array('result_code' => $data->result));
        }
    }

    private function call_action($data)
    {
        if ($data->call_state == 'Appeared')
        {
            if (empty($data->command_id) || !($mangocall_id = $this->command2call($data->command_id)))
                $mangocall_id = $this->mango->get_call_id($data->entry_id);

            $update = array(
                'entry_id' => $data->entry_id,
                'call_id' => $data->call_id,
                'from_extension' => $data->from['extension'] ?? '',
                'from_number' => $this->phoneFormat($data->from['number'] ?? ''),
                'to_extension' => $data->to['extension'] ?? '',
                'to_number' => $this->phoneFormat($data->to['number'] ?? ''),
            );

            if (!empty($mangocall_id))
            {
                $this->mango->update_call($mangocall_id, $update);
            }
            else
            {
                $this->mango->add_call($update);
            }
        }elseif ($data->call_state == 'OnHold'){
            file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'hold.log', json_encode($data));
        }
    }

    private function summary_action($data)
    {
        if ($mangocall_id = $this->mango->get_call_id($data->entry_id))
        {
            $mangocall = $this->mango->get_call($mangocall_id);

            $duration = $data->talk_time > 0 ? $data->end_time - $data->talk_time : 0;

            $this->mango->update_call($mangocall_id, array(
                'call_direction' => isset($data->call_direction) ? $data->call_direction : '',
                'create_time' => $data->create_time,
                'forward_time' => $data->forward_time,
                'talk_time' => $data->talk_time,
                'end_time' => $data->end_time,
                'entry_result' => $data->entry_result,
                'disconnect_reason' => $data->disconnect_reason,
                'duration' => $duration,
            ));

            if (!empty($mangocall->order_id))
            {
                if (!empty($duration))
                {
                    $order = $this->orders->get_order((int)$mangocall->order_id);
                    if (empty($order->call_date))
                    {
                        $this->orders->update_order($mangocall->order_id, array(
                            'call_date' => $mangocall->created
                        ));
                    }
                }
            }
        }

    }

    private function record_action($data)
    {
        if ($mangocall_id = $this->mango->get_call_id($data->entry_id))
        {
            if ($record_link = $this->mango->get_record_link($data->recording_id))
            {
                $this->logging(__METHOD__, 'recording_link_from_mango','$record_link', ['$record_link' => $record_link], $this->logFile);
                $file = file_get_contents($record_link);
                do {
                    $fileName = md5(rand().microtime()).'.mp3';
                } while (file_exists($this->config->root_dir.$this->callsFolder.$fileName));

                file_put_contents($this->config->root_dir.$this->callsFolder.$fileName, $file);

                try {
                    $mangoCall = $this->mango->get_call($mangocall_id);
                    $s3Url = $this->addRecordToS3($fileName, $mangoCall);

                    $this->logging(__METHOD__, 'start_upload_file_to_s3','$s3Url', ['$s3Url' => $s3Url], $this->logFile);
                    logger('s3')->info('Начало загрузки записи звонка в S3', ['s3Url' => $s3Url]);

                    $this->sendCallToComment($s3Url, $mangoCall);

                } catch (Throwable $e) {
                    $error = [
                        'Ошибка: ' . $e->getMessage(),
                        'Файл: ' . $e->getFile(),
                        'Строка: ' . $e->getLine(),
                        'Подробности: ' . $e->getTraceAsString()
                    ];
                    $this->logging(__METHOD__, 'error_upload_file_to_3cx', $this->config->root_dir.$this->callsFolder.$fileName, $error, $this->logFile);
                    logger('s3')->error('Ошибка при загрузке записи звонка в S3', $error);
                }
            }
            $this->mango->update_call($mangocall_id, array(
                'recording_id' => $data->recording_id,
                'record_file' => $fileName
            ));
        }
    }

    private function addRecordToS3($fileName, $mangoCall){
        $directory = $this->config->root_dir.$this->callsFolder;
        $file_local_path = $directory . $fileName;
        $s3_name = date('Y/m/d', $mangoCall->create_time) . '/' . $fileName;

        $this->s3_api_client->setBucket('call-storage');

        $this->s3_api_client->putFileContent($file_local_path, $s3_name);

        $publicUrl = $this->s3_api_client->getPublicUrl($s3_name, '+7 days');

        return $publicUrl;
    }

    private function sendCallToComment($s3Url, $mangoCall){
        $callExist = $this->comments->get_mango_comment($mangoCall->call_id);

        if ($callExist || $mangoCall->duration < 10 || empty($s3Url)/* || empty($mangoCall->user_id)*/) {
            $this->logging(__METHOD__, 'sendCallToComment','sendCallToComment', ['message' => 'Call already exists or duration is less than 10 seconds or record URL is empty'], $this->logFile);
            return response()->json(['message' => 'Call already exists or duration is less than 10 seconds or record URL is empty'], Response::HTTP_OK);
        }

        if ($mangoCall->call_direction != 2) {
            $this->logging(__METHOD__, 'sendCallToComment','sendCallToComment', ['message' => 'Not an outgoing call or operator group mismatch'], $this->logFile);
            return response()->json(['message' => 'Not an outgoing call or operator group mismatch'], Response::HTTP_OK);
        }

        $query = $this->db->placehold("
            SELECT id, uid, firstname, lastname, patronymic, phone_mobile FROM __users
            WHERE
                phone_mobile = '".$mangoCall->to_number."'
        ");
        $this->db->query($query);
        $user = $this->db->result();

        if (empty($user)) {
            $this->logging(__METHOD__, 'sendCallToComment','sendCallToComment', ['message' => 'User not found'], $this->logFile);
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $query = $this->db->placehold("SELECT * FROM __managers WHERE id = ".$this->managerId);
        $this->db->query($query);
        $manager = $this->db->result();

        $operator = $this->mango->getOperatorByExtension($mangoCall->from_extension);

        $data = [
            'call_id' => $mangoCall->call_id,
            'operator_id' => $operator['id'],
            'operator_name' => $operator['name'],
            'record_url' => $s3Url,
            'is_sent_analysis' => false,
            'provider' => 'mango',
        ];

        $sqlData = [
            'manager_id' => $manager->id,
            'user_id' => $user->id,
            'block' => CommentBlocks::OUTGOING_CALL,
            'created' => date('Y-m-d H:i:s'),
            'text' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ];

        $query = $this->db->placehold("INSERT INTO __comments SET ?% ", $sqlData);
        $this->db->query($query);
        $id = $this->db->insert_id();

        $this->logging(__METHOD__, 'add_comments_table',$sqlData, '', $this->logFile);


        (new CommentService())->sendCommentTo1C([
            'manager' => $manager->name_1c,
            'data' => $data,
            'number' => '',
            'user_uid' => $user->uid
        ]);

        $this->logging(__METHOD__, 'sendCallToComment','исходящий звонок успешно сохранено', '', $this->logFile);
    }

    public static function phoneFormat($phone): string
    {
        $phone = trim($phone);

        $res = preg_replace(
            array(
                '/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{3})[-|\s]?\)[-|\s]?(\d{3})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?(\d{3})[-|\s]?(\d{3})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{4})[-|\s]?\)[-|\s]?(\d{2})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?(\d{4})[-|\s]?(\d{2})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{4})[-|\s]?\)[-|\s]?(\d{3})[-|\s]?(\d{3})/',
                '/[\+]?([7|8])[-|\s]?(\d{4})[-|\s]?(\d{3})[-|\s]?(\d{3})/',
            ),
            array(
                '7$2$3$4$5',
                '7$2$3$4$5',
                '7$2$3$4$5',
                '7$2$3$4$5',
                '7$2$3$4',
                '7$2$3$4',
            ),
            $phone
        );

        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) === 10 && $digits[0] !== '7') {
            $res = '7' . $digits;
        }

        return $res;
    }
}

new MangoCallback();
