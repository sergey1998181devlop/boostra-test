<?php

use Ekapusta\OAuth2Esia\Provider\EsiaProvider;
use Ekapusta\OAuth2Esia\Security\JWTSigner\OpenSslCliJwtSigner;
use Ekapusta\OAuth2Esia\Security\Signer\OpensslCli;


/**
 * Получить массив настроек провайдера ЕСИА
 * @return array
 */
function esiaGetConfig(): array
{
    $config = [
        // Мнемоника информационной системы
        'clientId' => 'BOOSTRA22',
        // Адрес перенаправления после успешной авторизации
        'redirectUri' => 'https://www.boostra.ru/esia/login',
        // Запрашиваемые поля (scope)
        'defaultScopes' => [
            'id_doc',
            'openid' // В таком же порядке, как и в документе
        ],
        // Режим тестирования true - включен, false - выключен
        'test' => false,
        // Абсолютный путь к исполняемому файлу openssl в системе
        'toolPath' => '/usr/bin/openssl',
    ];
    if (!isset($config['test']) || $config['test'] === false) {
        $config['remoteUrl'] = 'esia.gosuslugi.ru';
        // Пути к файлу ключа, сертификата и пароль ключа для боевого режима
        $config['certificatePath'] = ROOT_DIR . '/rest_api/certs/esia/boostra.prod.cer';
        $config['privateKeyPath'] = ROOT_DIR .  '/rest_api/certs/esia/boostra.prod.key';
        $config['keyPassword'] = 'test';
    } else {
        $config['remoteUrl'] = 'esia-portal1.test.gosuslugi.ru';
        // Пути к файлу ключа, сертификата и пароль ключа для тестового режима
        $config['certificatePath'] = ROOT_DIR . '/rest_api/certs/esia/boostra.prod.cer';
        $config['privateKeyPath'] = ROOT_DIR .  '/rest_api/certs/esia/boostra.prod.key';
        $config['keyPassword'] = null;
    }
    return $config;
}

/**
 * Получить объект провайдера ЕСИА
 * @return EsiaProvider
 */
function esiaGetProvider(): EsiaProvider
{
    $config = esiaGetConfig();
    return new EsiaProvider(
        [
            'clientId' => $config['clientId'],
            'redirectUri' => $config['redirectUri'],
            'defaultScopes' => $config['defaultScopes'],
        ],
        [
            'signer' => new OpensslCli(
                $config['certificatePath'],
                $config['privateKeyPath'],
                $config['keyPassword'],
                $config['toolPath']
            ),
            'remoteSigner' => new OpenSslCliJwtSigner($config['toolPath'])
        ]
    );
}

/**
 * @return void
 */
function esiaAuth()
{

}