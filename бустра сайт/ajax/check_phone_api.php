<?php

declare(strict_types=1);

require_once(dirname(__DIR__) . '/api/Simpla.php');

/**
 * @api
 */
class PhoneChecker extends Simpla
{
    private const PHONE_CHECKER_TOKEN = 'ZlRn21yzgzkxcfCJ6WIE8nF1R-uzySMA0F7djlT3k50NHUjxlF9GTHHE-cpCvD';

    /**
     * @var string
     */
    private $token;

    /**
     * @var int
     */
    private $phone;

    public function __construct()
    {
        $this->token = $this->request->get('token', 'string');
        $this->phone = $this->request->get('phone', 'integer');

        parent::__construct();
    }

    public function run(): void
    {
        $validationResult = $this->validateParams();

        // Задерживаем работу скрипта на 1 секунду, дабы не перегружать БД. Т.е. это некое подобие лимита при запросах
        // из одного источника
        usleep(400000);

        if (!empty($validationResult['error'])) {
            $this->request->json_output($validationResult['error']);
        } else {
            $this->request->json_output($this->checkPhone());
        }
    }

    /**
     * Validate received params
     *
     * @return array
     */
    private function validateParams(): array
    {
        $result = [];
        if (!$this->token) {
            $result['error'][] = 'No token';
        }

        if (!$this->phone) {
            $result['error'][] = 'No phone';
        }

        if ($this->token && $this->token !== self::PHONE_CHECKER_TOKEN) {
            $result['error'][] = 'Wrong token';
        }

        return $result;
    }

    /**
     * Check if a user with this phone exists in the database
     *
     * @return int
     */
    private function checkPhone(): int
    {
        return $this->users->get_phone_user($this->phone) ? 1 : 0;
    }
}

(new PhoneChecker())->run();
