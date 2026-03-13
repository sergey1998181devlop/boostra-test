<?php

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

require_once 'AService.php';

class BankrotFedresursService extends AService
{
    public function __construct()
    {
        parent::__construct();

//        $this->response['info'] = array(
//
//        );

        $this->run();
    }

    public function run_scoring($inn)
    {
        if ($inn) {
            if (empty($inn)) {
                $update = array(
                    'status' => $this->scorings::STATUS_ERROR,
                    'string_result' => 'Не найден ИНН'
                );
            } else {
                $response = $this->getting_html(
                    $inn
                );
//echo __LINE__.'<br />'.$response;
                if ($response) {
                    $search = 'По заданным критериям не найдено ни одной записи. Уточните критерии поиска';

                    if (preg_match("/{$search}/i", $response)) {
                        $update = array(
                            'status' => $this->scorings::STATUS_COMPLETED,
                            'body' => $search,
                            'success' => 1,
                            'string_result' => 'банкротства не найдены'
                        );
                    } elseif (preg_match("/{$inn}/i", $response)) {
                        preg_match_all('/PrivatePersonCard.aspx\?ID=(.*)" title/', $response, $output_array);

                        $output_result = array_map(function ($value) {
                            return 'https://bankrot.fedresurs.ru/PrivatePersonCard.aspx?ID=' . $value;
                        }, $output_array[1]);

                        $update = array(
                            'status' => $this->scorings::STATUS_COMPLETED,
                            'body' => serialize($output_result),
                            'success' => 0,
                            'string_result' => 'банкротства найдены'
                        );
                    } else {
                        $update = array(
                            'status' => $this->scorings::STATUS_ERROR,
                            'body' => $response,
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

        } else {
            $update = array(
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'не найдена заявка'
            );
        }

        return $update;
    }

    public function getting_html($inn)
    {
        try {
            $host = 'http://178.154.253.203:4444/wd/hub';

            $capabilities = DesiredCapabilities::chrome();

            $driver = RemoteWebDriver::create($host, $capabilities);

            $driver->get('https://bankrot.fedresurs.ru/DebtorsSearch.aspx');
            //$driver->takeScreenshot('iscr1.jpg');
            $driver->findElement(
                WebDriverBy::cssSelector('#ctl00_cphBody_rblDebtorType > tbody > tr > td:nth-child(2) > label')
            )->click();

            $driver->findElement(
                WebDriverBy::cssSelector('#ctl00_cphBody_PersonCode1_CodeTextBox')
            )
                ->sendKeys($inn)
                ->submit();

            $driver->findElement(WebDriverBy::cssSelector('#ctl00_cphBody_btnSearch'))->click();

            //$driver->wait(1);

            //$driver->takeScreenshot('iscr2.jpg');

            //$response = $driver->findElement(
            //    WebDriverBy::cssSelector('#ctl00_cphBody_upList')
            //)->getText();

            $response = $driver->getPageSource();

            $driver->quit();
        } catch (Exception $e) {
            $response = $e->getMessage();
        }

        return $response;
    }

    private function run()
    {
        if ($inn = $this->request->get('inn')) {
            $scoring = $this->run_scoring($inn);

            if (empty($scoring)) {
                $this->response['success'] = 0;
            } else {
                $this->response['success'] = 1;
                $this->response['date'] = date("Y-m-d H:i:s");

                if ($scoring['success'] === 1 || $scoring['success'] === NULL) {
                    $this->response['bankrupt'] = 0;
                } else {
                    $this->response['bankrupt'] = 1;
                    $this->response['links'] = unserialize($scoring['body']);
                }
            }
        } else {
            $this->response['error'] = 'EMPTY_INN';
        }

        $this->json_output();
    }
}

new BankrotFedresursService();