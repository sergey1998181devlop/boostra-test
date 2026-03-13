<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/RevocationAgreementGenerator.php';

class RevocationMailer
{
    private PHPMailer $mail;

    public function __construct(array $smtpSettings)
    {
        $this->mail = new PHPMailer(true);

        $this->mail->isSMTP();
        $this->mail->Host = $smtpSettings['host'] ?? '';
        $this->mail->Username = $smtpSettings['mail'] ?? '';
        $this->mail->Password = $smtpSettings['password'] ?? '';
        $this->mail->SMTPAuth = true;
        $this->mail->SMTPSecure = 'TLS';
        $this->mail->Port = 587;
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Encoding = 'base64';
        $this->mail->isHTML(true);
        $this->mail->setFrom($smtpSettings['mail'] ?? '', 'Цессия Boostra');
    }

    public function send(array $rows): array
    {
        $errors = [];

        $docxRows = array_map(function ($row) {
            return is_array($row) ? $row : (array)$row;
        }, $rows);

        $generator = new RevocationAgreementGenerator();
        $docxPath = $generator->generate($docxRows);

        foreach ($rows as $row) {
            if (empty($row->email)) {
                $errors[] = "ID {$row->id}: Email не найден";
                continue;
            }

            try {
                $this->mail->clearAddresses();
                $this->mail->clearAttachments();
                $this->mail->addAddress($row->email);
                $this->mail->Subject = "Уведомление об отзыве заявки";

                $this->mail->Body = "<b>Уважаемый партнер,</b><br><br>
                    Запрос по <b>{$row->full_name_with_birth}</b> номер договора займа <b>{$row->contract_number}</b>номер договора ШКД <b>{$row->shkd_number}</b> получен, обработан.<br><br>
                    Компанией принято решение об <b>отзыве</b> данного договора.<br>
                    Дополнительное соглашение во вложении.<br><br>
                    С уважением,<br>
                    Команда boostra.ru";

                $this->mail->addAttachment($docxPath, 'Дополнительное_соглашение.docx');

                $this->mail->send();
            } catch (Exception $e) {
                $errors[] = "ID {$row->id}: Ошибка отправки: " . $this->mail->ErrorInfo;
            }
        }

        if (file_exists($docxPath)) {
            @unlink($docxPath);
        }

        return [
            'success' => empty($errors),
            'error' => $errors ? implode('; ', $errors) : null
        ];
    }
}
