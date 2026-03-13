<?php

namespace App\Service;

use App\Repositories\CbRequestRepository;
use Exception;

class CbRequestService
{
    /** @var CbRequestRepository */
    private CbRequestRepository $repository;

    public function __construct(CbRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Создать или обновить запрос ЦБ из парсера.
     *
     * @param array $data
     * @return array ['id' => int, 'files_count' => int, 'updated' => bool]
     * @throws Exception
     */
    public function createOrUpdateFromParser(array $data): array
    {
        $this->validateRequired($data, ['organization_id', 'message_text']);

        $hasFilesPayload = array_key_exists('files', $data) && is_array($data['files']);
        $files = $hasFilesPayload ? $data['files'] : [];
        $fileLinks = !empty($files)
            ? json_encode(array_values($files), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : null;

        $externalId = null;
        if (array_key_exists('external_id', $data)) {
            $externalIdRaw = trim((string) $data['external_id']);
            if ($externalIdRaw !== '') {
                if (!$this->isValidUuid($externalIdRaw)) {
                    throw new Exception('Обязательное поле external_id должно быть UUID');
                }
                $externalId = strtolower($externalIdRaw);
            }
        }

        // Upsert по external_id
        if ($externalId !== null) {
            $existing = $this->repository->findByExternalId($externalId);

            if ($existing !== null) {
                return $this->updateExisting($existing, $data, $fileLinks, $files, $hasFilesPayload);
            }
        }

        return $this->createNew($data, $fileLinks, $files, $externalId, $hasFilesPayload);
    }

    /**
     * @param object $existing
     * @param array $data
     * @param string|null $fileLinks
     * @param array $files
     * @return array
     */
    private function updateExisting(object $existing, array $data, ?string $fileLinks, array $files, bool $hasFilesPayload): array
    {
        $updateData = [
            'message_text' => (string) $data['message_text'],
        ];

        if ($hasFilesPayload) {
            $updateData['file_links'] = $fileLinks;
        }

        if (!empty($data['request_number'])) {
            $updateData['request_number'] = (string) $data['request_number'];
        }
        if (!empty($data['received_at'])) {
            $updateData['received_at'] = (string) $data['received_at'];
        }

        $this->repository->update((int) $existing->id, $updateData);
        $this->repository->logHistory((int) $existing->id, null, 'field_update', 'Запрос обновлён парсером');

        return [
            'id' => (int) $existing->id,
            'files_count' => count($files),
            'updated' => true,
        ];
    }

    /**
     * @param array $data
     * @param string|null $fileLinks
     * @param array $files
     * @param int|null $externalId
     * @return array
     * @throws Exception
     */
    private function createNew(array $data, ?string $fileLinks, array $files, ?string $externalId, bool $hasFilesPayload): array
    {
        $insertData = [
            'organization_id' => (int) $data['organization_id'],
            'message_text' => (string) $data['message_text'],
        ];

        if ($hasFilesPayload) {
            $insertData['file_links'] = $fileLinks;
        }

        if ($externalId !== null) {
            $insertData['external_id'] = $externalId;
        }
        if (!empty($data['request_number'])) {
            $insertData['request_number'] = (string) $data['request_number'];
        }
        if (!empty($data['received_at'])) {
            $insertData['received_at'] = (string) $data['received_at'];
        }

        $id = $this->repository->create($insertData);

        if (!$id) {
            throw new Exception('Не удалось создать запрос');
        }

        $this->repository->logHistory($id, null, 'creation', 'Запрос создан');

        return [
            'id' => $id,
            'files_count' => count($files),
            'updated' => false,
        ];
    }

    /**
     * @param array $data
     * @param array $keys
     * @throws Exception
     */
    private function validateRequired(array $data, array $keys): void
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data) || (empty($data[$key]) && $data[$key] !== 0)) {
                throw new Exception("Обязательное поле: {$key}");
            }
        }
    }

    private function isValidUuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value
        );
    }
}
