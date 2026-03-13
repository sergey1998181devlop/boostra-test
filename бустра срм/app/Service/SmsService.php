<?php

namespace App\Service;

use App\Core\Application\Response\Response;
use App\Dto\SendSmsDto;
use App\Repositories\SmsTemplateRepository;
use App\Repositories\SmsMessagesRepository;
use App\Repositories\UserDataRepository;
use App\Repositories\UserRepository;
use App\Repositories\ChangelogRepository;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Сервис для отправки SMS через SMSC.ru
 */
class SmsService
{
    /** @var string */
    private $apiUrl;

    /** @var string */
    private $login;

    /** @var string */
    private $password;

    /** @var Client */
    private $client;

    /** @var string */
    private $sender;

    /** @var SmsTemplateRepository */
    private $smsTemplateRepo;

    /** @var SmsMessagesRepository */
    private $smsMessagesRepo;

    /** @var UserDataRepository */
    private $userDataRepo;

    /** @var UserRepository */
    private $userRepository;

    /** @var ChangelogRepository */
    private $changelogRepo;

    public function __construct(
        SmsTemplateRepository $smsTemplateRepo = null,
        SmsMessagesRepository $smsMessagesRepo = null,
        UserDataRepository    $userDataRepo = null,
        UserRepository        $userRepository = null,
        ChangelogRepository   $changelogRepo = null
    )
    {
        $this->apiUrl = config('services.smsc.api_url');
        $this->login = config('services.smsc.login');
        $this->password = config('services.smsc.password');
        $this->sender = config('services.smsc.sender', 'Boostra.ru');

        $this->client = new Client([
            'timeout' => 10,
            'verify' => false
        ]);

        $this->smsTemplateRepo = $smsTemplateRepo;
        $this->smsMessagesRepo = $smsMessagesRepo;
        $this->userDataRepo = $userDataRepo;
        $this->userRepository = $userRepository;
        $this->changelogRepo = $changelogRepo;
    }

    /**
     * Отправка SMS сообщения
     *
     * @param SendSmsDto $dto
     * @return array
     * @throws GuzzleException
     */
    public function send(SendSmsDto $dto): array
    {
        try {
            $params = [
                'login' => $this->login,
                'psw' => $this->password,
                'phones' => $dto->phone,
                'mes' => $dto->message,
                'sender' => $this->sender,
                'fmt' => 3  // JSON format
            ];

            $response = $this->client->post($this->apiUrl, [
                'form_params' => $params
            ]);

            $result = json_decode($response->getBody()->getContents(), true) ?: [];

            if (isset($result['error'])) {
                return [
                    'success' => false,
                    'message' => $result['error'],
                    'id' => $result['id'] ?? null,
                    'error_code' => $result['error_code'] ?? null,
                ];
            }

            return [
                'success' => true,
                'message' => 'SMS успешно отправлено',
                'id' => $result['id'] ?? null,
            ];

        } catch (Exception $e) {
            logger('api')->error(__METHOD__ . PHP_EOL
                . 'Ошибка отправки SMS через SMSC' . PHP_EOL
                . 'URL: ' . $this->apiUrl . PHP_EOL
                . 'Params: ' . json_encode($params) . PHP_EOL
                . 'File: ' . $e->getFile() . PHP_EOL
                . 'Line: ' . $e->getLine() . PHP_EOL
                . 'Message: ' . $e->getMessage()
            );

            return [
                'success' => false,
                'message' => 'Ошибка при отправке SMS. Попробуйте позже.'
            ];
        }
    }

    /**
     * @param string $template
     * @param array $params
     * @return string
     */
    public function replacePlaceholders(string $template, array $params): string
    {
        return preg_replace_callback('/\{([^}]+)\}/', function ($matches) use ($params) {
            $key = $matches[1];
            return $params[$key] ?? $matches[0];
        }, $template);
    }

    /**
     * @param SendSmsDto $dto
     * @param int|null $managerId
     * @return array
     * @throws GuzzleException
     */
    public function sendSms(SendSmsDto $dto, ?int $managerId = null): array
    {
        try {
            $errors = $dto->validate();
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => implode(', ', $errors),
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                ];
            }

            $user = $this->userRepository->getByPhone($dto->phone);
            $userId = $user && $user->id ? (int)$user->id : 0;

            if (!$user) {
                logger('sms')->warning('User not found by phone', [
                    'phone' => $dto->phone,
                    'template_id' => $dto->template_id
                ]);
            }

            if ($dto->template_id) {
                $template = $this->smsTemplateRepo->findByIdAndType($dto->template_id);

                if (!$template) {
                    return [
                        'success' => false,
                        'message' => 'Шаблон не найден или недоступен',
                        'status' => Response::HTTP_NOT_FOUND
                    ];
                }

                $dto->message = $this->replacePlaceholders($template->template, $dto->params);
            } else {
                return [
                    'success' => false,
                    'message' => 'Отправка SMS без использования шаблона запрещена',
                    'status' => Response::HTTP_BAD_REQUEST
                ];
            }

            $result = $this->send($dto);

            $this->smsMessagesRepo->log([
                'phone' => $dto->phone,
                'message' => $dto->message,
                'send_status' => $result['success'] ? 'success' : 'error',
                'send_id' => $result['id'] ?? null,
                'user_id' => $userId,
            ]);

            if ($userId > 0 && $managerId !== null && $this->changelogRepo !== null) {
                $this->changelogRepo->addLog(
                    $managerId,
                    'send_sms',
                    '',
                    mb_substr($dto->message, 0, 255),
                    null,
                    $userId
                );
            }

            if ($result['success'] && $dto->template_id == 81) {
                if ($userId > 0) {
                    try {
                        $this->userDataRepo->enableShowExtraDocs($userId);

                        logger('api')->info(__METHOD__ . PHP_EOL
                            . 'Включен показ доп. документов для user_id: ' . $userId . PHP_EOL
                            . 'phone: ' . $dto->phone . PHP_EOL
                            . 'template_id: ' . $dto->template_id
                        );
                    } catch (Exception $e) {
                        logger('api')->error(__METHOD__ . PHP_EOL
                            . 'Ошибка при включении показа доп. документов' . PHP_EOL
                            . 'user_id: ' . $userId . PHP_EOL
                            . 'phone: ' . $dto->phone . PHP_EOL
                            . 'File: ' . $e->getFile() . PHP_EOL
                            . 'Line: ' . $e->getLine() . PHP_EOL
                            . 'Message: ' . $e->getMessage()
                        );
                    }
                } else {
                    logger('api')->warning(__METHOD__ . PHP_EOL
                        . 'Пользователь не найден по телефону: ' . $dto->phone . PHP_EOL
                        . 'template_id: ' . $dto->template_id
                    );
                }
            }

            $result['status'] = $result['success']
                ? Response::HTTP_OK
                : Response::HTTP_BAD_REQUEST;

            return $result;

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
    }
}