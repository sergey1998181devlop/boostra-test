<?php

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

require_once( __DIR__ . '/../api/Simpla.php' );
require_once __DIR__ . '/../lib/autoloader.php';

// Inifite execution
ini_set( 'max_execution_time', 60 * 60 * 24 * 2 ); // 2 дня

class ScoringEfrsb extends Simpla{
    
    /**
     * Delay for new page in seconds
     */
    private const DELAY_FOR_A_NEW_PAGE = 4;
    
    private $driver;
    private $bankruptcy_date;
    private $error;
    private $response;
    private $content;
    
    /**
     * Starts a scoring
     *
     * @param \boostra\domains\ScoringEFRSB $scoring
     *
     * @return \boostra\domains\ScoringEFRSB
     */
    public function run_scoring( \boostra\domains\ScoringEFRSB $scoring ): \boostra\domains\ScoringEFRSB
    {
        // Fallout if scoring already been checked or error or something else happened
        if( $scoring->status != $this->scorings::STATUS_NEW ){
            return $scoring;
        }
        
        // Выставляем статус "в работе" и сохраняем
        $scoring->start_date = date( 'Y-m-d H:i:s' );
        $scoring->status     = $this->scorings::STATUS_PROCESS;
        $scoring->save();
        
        // Нет ИНН
        if( empty( $scoring->inn ) ){
            $scoring->status        = $this->scorings::STATUS_ERROR;
            $scoring->string_result = 'ИНН клиента отсутствует в базе';
            $scoring->end_date      = date( 'Y-m-d H:i:s' );
            $scoring->success       = 0;
            $scoring->save();
            
            return $scoring;
        }
        
        $this->parse( 'https://bankrot.fedresurs.ru/bankrupts?searchString=' . $scoring->inn );
        
        // Ошибка парсинга
        if( isset( $this->error ) ){
            $scoring->status        = $this->scorings::STATUS_ERROR;
            $scoring->string_result = "Ошибка парсинга";
            $scoring->body          = serialize( $this->response );
        
        // Найдены банкротства
        }elseif( $this->bankruptcy_date ){
            $scoring->status = $this->scorings::STATUS_COMPLETED;
            $scoring->body = serialize( $this->response );
            $scoring->success = 0;
            $scoring->string_result = 'Банкротства найдены';
            $scoring->bankruptcy_date = ( new DateTime( $this->bankruptcy_date ) )->format( 'Y-m-d H:i:s' );
            
        // Банкротств нет
        }else{
            $scoring->success       = 1;
            $scoring->string_result = 'Банкротства не найдены';
            $scoring->status        = $this->scorings::STATUS_COMPLETED;
            $scoring->body          = serialize( $this->response );
        }
        
        $this->resetState();
        
        $scoring->end_date = date( 'Y-m-d H:i:s' );
        $scoring->save();
        
        return $scoring;
    }
    
    /**
     * Parses the page
     * Specific logic for each task
     *
     * @param $parse_url
     *
     * @return array
     */
    private function parse( $parse_url )
    {
        try{
            
            // Инициализация селеноида
            $this->driver = RemoteWebDriver::create(
                "http://{$this->settings->selenoid}:4444/wd/hub",
                DesiredCapabilities::chrome()
            );
            $this->driver->get( $parse_url );
            sleep( 2 );
            
            // Попытка найти и перейти по ссылке информации о банкротстве
            if( ! $this->searchAndClick( "//div[contains(text(),'Вся информация')]" ) ){
                $this->content = $this->driver
                    ->findElement( WebDriverBy::xpath( "/html/body/app-root/div[1]/app-bankrupt/div/div[2]/div" ) )
                    ->getText();
                
                return $this->compileResponse();
            }
            
            // Попытка найти и перейти по ссылке информации о судебном заседании
            // Используем именно ссылку потому что судя по всему есть защита от клика
            $this->OpenLinkInNewWindow( "//a[contains(text(),'Сообщение о судебном акте')]" );
            
            // Получение даты судебного решения
            $this->bankruptcy_date = $this->driver
                ->findElement( WebDriverBy::xpath( "//div[contains(text(),'Дата решения:')]/following-sibling::div" ) )
                ->getText();
            
        }catch( Exception $e ){
            $this->error = substr( $e->getMessage(), 0, 1024 );
        }
        
        return $this->compileResponse();
    }
    
    /**
     * Searches the element, click on it
     * Sleeps for 4 seconds (maybe it's a JS-link)
     *
     * @param $xpath_selector
     *
     * @return bool
     */
    private function searchAndClick( $xpath_selector ): bool
    {
        $handle_count = count( $this->driver->getWindowHandles() );
        $elements     = $this->driver->findElements( WebDriverBy::xpath( $xpath_selector ) );
        $element      = end( $elements );
        
        if( $element ){
            
            $element->click();
            
            sleep( self::DELAY_FOR_A_NEW_PAGE );
            
            // Переключаемся на новое окно если оно есть
            if( $handle_count < count( $this->driver->getWindowHandles() ) ){
                $windows_handles = $this->driver->getWindowHandles();
                $this->driver
                    ->switchTo()
                    ->window( end($windows_handles ) );
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Searches <A> tag, gets its href and follow by this link in the current window (driver)
     * Sleeps for 4 seconds
     *
     * @param $xpath_selector
     *
     * @return void
     */
    private function OpenLinkInNewWindow( $xpath_selector ): void
    {
        $elems = $this->driver
            ->findElements( WebDriverBy::xpath( $xpath_selector ) );
        $elem = end( $elems );
        $href = $elem->getAttribute( 'href' );
        $this->driver->get( $href );
        
        sleep( self::DELAY_FOR_A_NEW_PAGE );
    }
    
    /**
     * Complies a response to put it into a "body" field
     *
     * @param $url
     *
     * @return array
     */
    private function compileResponse( $url = null )
    {
        $this->response = [
            'url'     => $url           ?? $this->driver->getCurrentURL(),
            'content' => $this->content ?? '',
        ];
        
        if( ! empty( $this->bankruptcy_date ) ){
            $this->response['bankruptcy_date'] = $this->bankruptcy_date;
        }
        
        if( ! empty( $this->error ) ){
            $this->response['error'] = $this->error;
        }
        
        return $this->response;
    }
    
    /**
     * Drop the data for a new run
     *
     * @return void
     */
    private function resetState(): void
    {
        $this->driver->quit();
        
        $this->driver          = null;
        $this->bankruptcy_date = null;
        $this->error           = null;
        $this->response        = null;
        $this->content         = null;
    }
}