<?php

ini_set('max_execution_time', '600');
use PHPMailer\PHPMailer\PHPMailer;

require_once dirname(__FILE__).'/../api/Simpla.php';


class VoxCallBackLog extends Simpla
{
    private const SMTP_HOST = 'smtp.yandex.ru';
    private const SMTP_MAIL = 'sv@boostra.ru';
    private const SMTP_PASSWORD = 'SVB163(hj9';
    private const RECIPIENTS_EMAILS = [
        'veyko@boostra.ru'
    ];
    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    private function run(): void
    {
        $lastDataTime = $this->getLastInsertedData();
        $currentTime = new DateTime();
        $lastInsertTime = new DateTime($lastDataTime);
        $diff = $currentTime->diff($lastInsertTime)->h + ($currentTime->diff($lastInsertTime)->days * 24);
        if ($diff >= 10) {
            $this->sendEmail();
        } else {
            echo "No email sent, less than 10 hours passed.";
        }
    }

    private function getLastInsertedData()
    {
        $query = $this->db->placehold("
            SELECT created_at FROM vox_call_result ORDER BY id DESC LIMIT 1
            ");
        $this->db->query($query);
        return $this->db->result('created_at');
    }

    private function sendEmail(): void
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = self::SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = self::SMTP_MAIL;
        $mail->Password = self::SMTP_PASSWORD;
        $mail->SMTPSecure = 'TLS';
        $mail->Port = 587;

        $mail->setFrom(self::SMTP_MAIL, 'Ежедневная отчётность');
        foreach (self::RECIPIENTS_EMAILS as $email) {
            $mail->addAddress($email);
        }

        $mail->isHTML(true);
        $mail->Subject = ("Logs of Voximplant");
        $mail->Body = "<b>Упали коллбеки от Vox</b>";
        $mail->send();
    }

}

new VoxCallBackLog();
