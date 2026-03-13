<?php

require_once('../api/Simpla.php');

class Fedresurs extends Simpla
{
    public function run_scoring($scoring_id)
    {
        if ($scoring = $this->scorings->get_scoring($scoring_id)) {
            if ($order = $this->orders->get_order((int)$scoring->order_id)) {
                if (
                    empty($order->lastname)
                    || empty($order->firstname)
                    || empty($order->patronymic)
                    || empty($order->passport_serial)
                    || empty($order->passport_date)
                    || empty($order->birth)
                ) {
                    $update = array(
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'в заявке не достаточно данных для проведения скоринга'
                    );
                } else {
                    $birthday = date('d.m.Y', strtotime($order->birth/*'24.01.1989'*/));
                    $passportdate = date('d.m.Y', strtotime($order->passport_date/*'19.06.2017'*/));

                    $surname = $order->lastname;//'Цупко';
                    $name = $order->firstname;//'Евгений';
                    $patronymic = $order->patronymic;//'Евгеньевич';
                    $docnumber = $order->passport_serial;//'45 18 119508';
                    $birthdate = $birthday;//$birthday;
                    $docdat = $passportdate;//$passportdate;
                    $doctype = 21;

                    $fns = (new Fns())
                        ->get_inn(
                            $surname,
                            $name,
                            $patronymic,
                            $birthdate,
                            $doctype,
                            $docnumber,
                            $docdat
                        );

                    if (empty($fns->inn)) {
                        $update = array(
                            'status' => $this->scorings::STATUS_ERROR,
                            'string_result' => 'в заявке не указаны inn'
                        );
                    } else {
                        $response = $this->getting_html(
                            $fns->inn
                        );

                        if ($response) {
                            $search = 'По заданным критериям не найдено ни одной записи. Уточните критерии поиска';

                            if (preg_match("/{$search}/i", $response)) {
                                $update = array(
                                    'status' => $this->scorings::STATUS_COMPLETED,
                                    'body' => $search,
                                    'success' => 1,
                                    'string_result' => 'банкротства не найдены'
                                );
                            } elseif (preg_match("/{$fns->inn}/i", $response)) {
                                $update = array(
                                    'status' => $this->scorings::STATUS_COMPLETED,
                                    'body' => '',
                                    'success' => 0,
                                    'string_result' => 'банкротства найдены'
                                );
                            } else {
                                $update = array(
                                    'status' => $this->scorings::STATUS_ERROR,
                                    'body' => '',
                                    'string_result' => 'неудачный парсинг'
                                );
                            }
                        } else {
                            $update = array(
                                'status' => $this->scorings::STATUS_ERROR,
                                'string_result' => 'При запросе произошла ошибка'
                            );
                        }
                    }
                }
            } else {
                $update = array(
                    'status' => $this->scorings::STATUS_ERROR,
                    'string_result' => 'не найдена заявка'
                );
            }

            if (!empty($update)) {
                $this->scorings->update_scoring($scoring_id, $update);
            }

            return $update;
        }
    }

    public function getting_html($inn)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://bankrot.fedresurs.ru/DebtorsSearch.aspx',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'authority: bankrot.fedresurs.ru',
                'cache-control: max-age=0',
                'upgrade-insecure-requests: 1',
                'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.72 Safari/537.36',
                'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: navigate',
                'sec-fetch-user: ?1',
                'sec-fetch-dest: document',
                'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="90", "Google Chrome";v="90"',
                'sec-ch-ua-mobile: ?0',
                'referer: https://bankrot.fedresurs.ru/DebtorsSearch.aspx?attempt=1',
                'accept-language: en-US,en;q=0.9,ru;q=0.8',
                'cookie: '
                . 'ASP.NET_SessionId=ovjrlpn4e4irc0xytjjwg4lk;'
                . ' _ym_uid=1619609781790218504; _ym_d=1619609781; _ym_visorc=w; _ym_isad=2;'
                . ' debtorsearch=typeofsearch=Persons&prslastname=&prsfirstname=&prsmiddlename=&prsaddress=&prsregionid=&prsinn='
                . $inn .
                '&prsogrn=&prssnils=&PrsCategory=&pagenumber=0;'
                . ' fedresurscookie=57f3d38e850f08e45d572bcc22816d1c;'
                . ' bankrotcookie=d2a8944443062328dee1ee09643e1513;'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}