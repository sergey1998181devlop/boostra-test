<?php

require_once 'View.php';

class ExportPhotosView extends View
{
    private const ZIP_ACTION = 'exportPhotosToZip';
    private const EXPORT_SINGLE_PHOTO_ACTION = 'exportSinglePhoto';

    public function fetch()
    {
        if (!in_array('export_photos', $this->manager->permissions)) {
            return $this->design->fetch('403.tpl');
        }

        if ($this->request->method('POST') && $this->request->post('action', 'string') === self::ZIP_ACTION) {
            $this->sendUserFilesAsZip();
        }

        if ($this->request->method('GET') && $this->request->get('action', 'string') === self::EXPORT_SINGLE_PHOTO_ACTION) {
            $this->getSinglePhoto();
        }

        $contractNumber = $this->request->post('contractNumber', 'string')
            ? $this->request->post('contractNumber', 'string')
            : $this->request->get('contractNumber', 'string');

        $isRequestSent = false;
        if (!empty($contractNumber)) {
            $isRequestSent = true;
            $this->design->assign('contractNumber', $contractNumber);

            $contractQuery = $this->db->placehold("
                    SELECT user_id FROM `s_contracts` WHERE number = ? LIMIT 1",
                $contractNumber
            );
            $this->db->query($contractQuery);
            $contact = $this->db->result();
            if ($contact === null) {
                $isHasContract = false;
            } else {
                $isHasContract = true;
                $userId = $contact->user_id;
                $user = $this->users->get_user($userId);
                $files = $this->users->get_files(['user_id' => $userId]);
                $this->design->assign('user', $user);
                $this->design->assign('files', $files);
            }
        }

        $this->design->assign('isRequestSent', $isRequestSent);
        $this->design->assign('isHasContract', $isHasContract);

        return $this->design->fetch('export_photos.tpl');
    }

    private function sendUserFilesAsZip()
    {
        $userId = $this->request->post('userId', 'integer');
        $contractNumber = $this->request->post('contractNumber', 'string');
        $contractNumber = strtolower($contractNumber);
        $files = $this->users->get_files(['user_id' => $userId]);

        $filesToZip = [];
        $errors = [];
        if (!empty($files)) {
            foreach ($files as $file) {
                $fileUrl = $this->getFileUrl($file);
                $filePath = $this->saveFile($file);

                if ($filePath && (file_exists($filePath) && filesize($filePath) > 0)) {
                    $filesToZip[] = $filePath;
                } else {
                    $documentType = $this->mapDocumentType($file->type);
                    $errors[] = "Файл $documentType пустой или не был корректно сохранён: " . $fileUrl;
                }
            }

            $zip = new ZipArchive();
            $zipName = time() . "_contract_".$contractNumber."_user_files.zip";

            if ($zip->open($zipName, ZIPARCHIVE::CREATE) !== true) {
                throw new ErrorException('Sorry File is open...');
            }

            if (!empty($filesToZip)) {
                foreach ($filesToZip as $file) {
                    $filePaths = explode('/',$file);
                    $zip->addFile($file, end($filePaths));
                }

                $zip->close();

                if (file_exists($zipName)) {
                    $this->response->file_output($zipName, 'application/zip');
                }
            }
        } else {
            $errors[] = 'Нет доступных для загрузки файлов';
        }

        $this->design->assign('fileErrors', $errors);
    }

    private function getSinglePhoto(): void
    {
        $photoId = $this->request->get('photoId', 'integer');

        if (empty($photoId)) {
           $this->response->json_output(
               ['success' => false, 'message' => 'В запросе отсутствует идентификатор файла'],
               400
           );
        }

        $file = $this->users->get_file($photoId);

        if (empty($file)) {
            $this->response->json_output(['success' => false, 'message' => 'Файл не найден'], 404);
        }

        $filePath = $this->saveFile($file);

        if (!empty($filePath)) {
          $this->response->file_output(
              $filePath,
              $this->getMimeType($filePath),
              false
          );
        } else {
            $this->response->json_output(
                ['success' => false, 'message' => 'Не удалось сохранить файл'],
                400
            );
        }
    }

    private function saveFile($file): string
    {
        $fileUrl = $this->getFileUrl($file);
        $rootPath = $this->config->root_dir . 'files/contractsFiles/user_' . $file->user_id;

        if (!is_dir($rootPath)) {
            mkdir($rootPath, 0777, true);
        }

        $filePath = $rootPath . '/user_file_' . basename($fileUrl);


        $downloadResult = file_put_contents($filePath, fopen($fileUrl, 'r'));

        if ($downloadResult === false) {
            return false;
        }

        return $filePath;
    }

    private function getFileUrl($file): string
    {
        return $this->config->front_url . '/' . $this->config->users_files_dir . $file->name;
    }

    private function getMimeType(string $filePath): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        return $mime ?: 'application/octet-stream';
    }

    private function mapDocumentType(string $documentType): string
    {
        $map = [
            'face1'     => 'Профиль',
            'face2'     => 'Анфас',
            'passport'  => 'Документ',
            'passport1' => 'Паспорт',
            'passport2' => 'Прописка',
            'passport3' => 'Брак',
            'passport4' => 'Карта',
            'selfi'     => 'Селфи с паспортом',
        ];

        return $map[$documentType] ?? $documentType;
    }
}