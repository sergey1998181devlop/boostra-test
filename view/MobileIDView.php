<?php

declare(strict_types=1);

require_once('View.php');

class MobileIDView extends \View
{

    public function fetch(): void
    {
        $this->request->json_output([
            'keys' => [
                [
                    'alg' => 'RS256',
                    'e' => 'AQAB',
                    'kid' => '7c6c0f98-8131-4ffc-b473-8191d583f9a2',
                    'kty' => 'RSA',
                    "n" => "9BSSyCERxRogeLXvLVHfjHkU-ixn2MIQnCw_1ea08qXV99uQFCEmSY6Tfwo-H85MzeMyaMPxvsAr04IKzENmyi8H7-EYx-y8pEyW4xBQVql56eiu-Gw6DH_MiyTN697OMWJWR_rfOKq80q_PALrTikbP_XZ8Lw0LyOtNH8BOYD77l7qHug9iIyFEob5JQH6rtWktroBU39Nf6c8Eu1rN4DWEF6inNim1ZgAs5qMNK7TkmAaTuzdAFor_o3_ALuhHVpfHfOLZTRQro-nO0UzM_tHNOvnBPZ4uHheMsLSvQpyypgaA-L930JCCyXFF5_fE3EfOCL_8TD6pug1JQ5wdOG81Zjb3gUeKdxR_K95nVAIgB_8su3op3Q2q0BL9OAdss3jierqa43c_fmxEYCKozGFCPCuDSE6Np1yi0ToBVYZHwtK9KkEMI-3URC6T1GBNb-p5pQNGF_OTM5I6Wsn0tNZDDKEVK9UlsOsF2O7niFsXTWRA3elJ78yuQER04YSkdwsR5S6T5NlJ5Rxwo0ckOc5AeTD7yrEb-iEA6WcLljEmYlaT6rQoWJ6Ii9Xny2piNZXC4qmtwLCaiVo0W6dFW9ShqU2d02ZoW9N__c_aHoB5HGRogT48XqadBaXGW0pKh6AYidp5gIZwmknXf_ZLLruCgZFYPdM5EshqyIlhtec",
                    'use' => 'sig',
                ]
            ],
        ]);
    }

}