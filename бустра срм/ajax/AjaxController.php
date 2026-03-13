<?php

use boostra\services\Core;

session_start();

require_once './../lib/autoloader.php';

abstract class AjaxController{
    
    protected $action;
    protected $error = '';
    protected $response = [];
    protected $message;
    protected $data = [];
    
    /** File validation */
    protected $upload_file_path;
    protected $tmp_file_name;
    protected $allowed_extensions = [
        'png',
        'jpg',
        'jpeg',
        'jp2',
    ];
    protected $max_file_size = 5242880;
    protected $new_filename;
    
    /**
     * @var \boostra\domains\User
     */
    protected $user;
    
    /**
     * @var \boostra\domains\Manager
     */
    protected $manager;
    
    
    /**
     * Returns an array of rule set separated by action-name similar to this:
     *      [
     *          'action_name' => [
     *               'param_name'     => 'string',
     *               'param_name2'  => 'integer',
     *               'service' => [ 'multipolis', 'credit_doctor', 'tv_medical' ],
     *          ],
     *      ]
     *
     * Allowed filter values:
     *      'bool'
     *      'int'
     *      'float'
     *      'email'  - checks if the param is valid e-mail
     *      'domain' - checks if the param is valid domain name
     *      'ip'     - checks if the param is valid IP-address (v4|v6)
     *      'mac'    - checks if the param is valid MAC-address
     *      'regexp' - checks if the param is valid regexp
     *      'url'    - URL
     *      'date'   - string with date, check if the date is valid by using strtotime()
     *      '@regular_expression@' - regular expression with "@" delimiter.
     *           Param will be checked through this regexp
     *      ['some','thing',123]   - strict comparison with array elements
     *           via in_array( $array_rule, $input_param, true )
     *      function( $param ){}: bool - filter could be a callable.
     *           In this case it will receive param to check and should return bool
     *           both closure or function could be passed
     *
     * @return array
     */
    abstract public function actions(): array;
    
    public function __construct()
    {
        /** Getting user */
        ! session_id() && @session_start();
        
        if( isset( $_SESSION['user_id'] ) ){
            $user = Core::instance()->users->get_user( (int)$_SESSION['user_id'] );
            if( $user && (int) $user->blocked === 0 ){
                $this->user = $user;
            }
        }elseif( isset( $_SESSION['passport_user'] ) ){
            $this->user = $_SESSION['passport_user'];
        }
        
        /** Get manager  */
        if( isset( $_SESSION['manager_id'] ) ){
            if( $_SESSION['manager_ip'] == $_SERVER['REMOTE_ADDR'] ){
                if( $manager = Core::instance()->managers->get_manager( intval( $_SESSION['manager_id'] ) ) ){
                    $manager->permissions = Core::instance()->managers->get_permissions( $manager->role );
                    $this->manager        = $manager;
                
                    Core::instance()->managers->update_manager(
                        $manager->id,
                        [ 'last_ip' => $_SERVER['REMOTE_ADDR'], 'last_visit' => date( 'Y-m-d H:i:s' ) ]
                    );
                }else{
                    $_SESSION['manager_id'] = null;
                    $_SESSION['manager_ip'] = null;
                    setcookie( 'ah', null, time() - 1, '/', 'boostra.ru' );
                    setcookie( 'mid', null, time() - 1, '/', 'boostra.ru' );
                    header( 'Location:/' );
                    exit;
                }
            }else{
                $_SESSION['manager_id'] = null;
                $_SESSION['manager_ip'] = null;
                setcookie( 'ah', null, time() - 1, '/', 'boostra.ru' );
                setcookie( 'mid', null, time() - 1, '/', 'boostra.ru' );
                header( 'Location:/' );
                exit;
            }
        }elseif( isset( $_COOKIE['ah'], $_COOKIE['mid'] ) ){
            $manager = Core::instance()->managers->get_manager( (int)$_COOKIE['mid'] );
        
            if( $manager && $_COOKIE['ah'] == md5( sha1( $_SERVER['REMOTE_ADDR'] . $manager->id ) . $manager->salt ) ){
                $manager->permissions = Core::instance()->managers->get_permissions( $manager->role );
                $this->manager = $manager;
                $_SESSION['manager_id'] = $manager->id;
                $_SESSION['manager_ip'] = $_SERVER['REMOTE_ADDR'];
            }else{
                setcookie( 'ah', null, time() - 1, '/', 'boostra.ru' );
                setcookie( 'mid', null, time() - 1, '/', 'boostra.ru' );
            }
        }
    
        ini_set( 'upload_max_filesize', $this->max_file_size );
        ini_set( 'post_max_size', $this->max_file_size + 4096 );
        $this->upload_file_path = Core::instance()->config->root_dir . Core::instance()->config->original_images_dir;
        
        ( $this->validate() && $this->castInputParameters() )
            || $this->outputError();
        
        /** Additional initializing */
        try{
            method_exists( $this, 'init' )
                && $this->init();
        }catch( Exception $e ){
            $this->error = $e->getMessage();
            $this->outputError();
        }
        
        $this->run();
    }
    
    /**
     * Validate input from POST|GET using the ruleset returned by $this->actions()
     
     * @return bool
     */
    private function validate(): bool
    {
        /** Validate action name */
        $this->action = $this->getParam( 'action' );
        $action_data = $this->actions()[ $this->action ] ?? null;
        
        if( ! $action_data ){
            $this->error = "Действие не поддерживается: '$this->action'";
            
            return false;
        }
        
        foreach( $action_data as $param => $rule ){
            
            /** Validate file */
            if( $rule === 'file' && ! $this->validateFile( $param ) ){
                return false;
            }
            
            $this->data[ $param ] = $this->getParam( $param );
            
            /** Validate strict value */
            if( is_array( $rule ) && ! in_array( $this->data[ $param ], $rule, true ) ){
                $this->error = "$param is not valid";
                return false;
            }
            
            /** Validate types 'bool', 'int', 'float', 'email', 'domain', 'ip', 'mac', 'regexp', 'url' */
            if( in_array( $rule, [ 'bool', 'int', 'float', 'email', 'domain', 'ip', 'mac', 'regexp', 'url' ], true ) &&
                filter_var( $this->data[ $param ], constant( 'FILTER_VALIDATE_' . strtoupper( $rule ) ) ) === false
            ){
                $this->error = "$param is not valid $rule";
                return false;
            }
            
            /** Validate date type */
            if( $rule === 'date' && ! strtotime( $param ) ){
                $this->error = "$param is not valid $rule";
                return false;
            }
            
            /**  Regexp with "@" delimiter: @example@ */
            if( is_string( $rule ) && strpos( $rule, '@') === 0 && ! preg_match( $rule, $this->data[ $param ] ) ){
                $this->error = "$param is not valid regexp";
                return false;
            }
            
            /** Special validation via callback */
            if( is_string( $rule ) && is_callable( static::class . "::$rule" ) && ! $rule( $this->data[ $param ] ) ){
                $this->error = "$param is not valid for callback validation";
                return false;
            }
        }
        
        return true;
    }
    
    protected function validateFile( $filename_field ): bool
    {
        // Проверяем наличие файлов
        $file = $this->getParam( $filename_field );
        if( ! $file ){
            $this->error = 'Нет файлов для загрузки';
            
            return false;
        }
        
        // Проверяем расширение файла
        $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
        if( ! in_array( $ext, $this->allowed_extensions ) ){
            $this->error = "Неверное расширение файла '$ext'. Допустимые расширения: " . implode( ', ', $this->allowed_extensions);
            
            return false;
        }
        
        // Проверяем размер файла
        if( $this->max_file_size < $file['size'] ){
            $this->error = 'Превышен размер файла в ' . round($this->max_file_size/1024/1024, 2);
            
            return false;
        }
        
        do{
            $new_filename = md5( microtime() . mt_rand() ) . '.' . $ext;
        }while( Core::instance()->users->check_filename( $new_filename ) );
        
        // Проверяем сохраняем в папку загрузки
        $file_uploaded = move_uploaded_file($file['tmp_name'], $this->upload_file_path . $new_filename);
        if( ! $file_uploaded ){
            $this->error = 'Ошибка загрузки файла';
            
            return false;
        }
        
        $this->tmp_file_name = $this->upload_file_path . $new_filename;
        $this->new_filename = $new_filename;
        
        return true;
    }
    
    /**
     * Cast input parameters to their expected type
     *  Please, note that the validation with this type already passed at this moment
     *
     * @return bool
     */
    private function castInputParameters(): bool
    {
        $action_data = $this->actions()[ $this->action ] ?? null;
        
        foreach( $action_data as $param => $rule ){
            
            // Cast to BOOL
            if( $rule === 'bool' ){
                $this->data[ $param ] = (bool) $this->data[ $param ];
                
            // Cast to INT
            }elseif( $rule === 'integer' ){
                $this->data[ $param ] = (int) $this->data[ $param ];
                
            // Cast to FLOAT
            }elseif( $rule === 'float' ){
                $this->data[ $param ] = (float) $this->data[ $param ];
                
            // Cast to STRING
            }else{
                $this->data[ $param ] = (string) $this->data[ $param ];
            }
        }
        
        return true;
    }
    
    /**
     * Safely (with handling exceptions) runs target method from the action param
     *
     * @return void
     */
    private function run(): void
    {
        /** Get action method name */
        $action_name = $this->convertToCamelCase( 'action' . $this->action, true );
        
        /** Check if the method exists */
        if( ! method_exists( $this, $action_name ) ){
            $this->error = "Действие '$action_name' не поддерживается";
            $this->outputError();
        }
        
        /** Run action */
        try{
            $this->response = $this->$action_name();
        }catch( Exception $e ){
            $this->error = $e->getMessage();
            $this->outputError();
        }
        
        /** Render and output the response */
        $this->outputResponse( $this->response );
    }
    
    /**
     * Get a param from request POST or GET or FILE
     *
     * @param $name
     *
     * @return bool|int|mixed|string|null
     */
    private function getParam( $name )
    {
        return Core::instance()->request->post( $name )
            ?? Core::instance()->request->get( $name )
               ?? $this->getFile( $name )
                  ?? null;
    }
    
    private function getFile( $name )
    {
        return $_FILES[ $name ] ?? null;
    }

    
    /**
     * Converts such_string_snake_string to the string in camelCase
     *
     * @param $string
     * @param $capitalizeFirstCharacter
     *
     * @return array|string|string[]
     */
    private function convertToCamelCase( $string, $capitalizeFirstCharacter = false )
    {
        $str = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $string ) ) );
        
        if( ! $capitalizeFirstCharacter ){
            $str[ 0 ] = strtolower( $str[ 0 ] );
        }
        
        return $str;
    }
    
    /**
     * Set response headers depending on request type
     *
     * @return void
     */
    private function setResponseHeaders(): void
    {
        header( "Content-type: application/json; charset=UTF-8" );
        header( "Cache-Control: must-revalidate" );
        header( "Pragma: no-cache" );
        header( "Expires: -1" );
    }
    
    /**
     * Renders an error response
     *
     */
    protected function outputError(): void
    {
        $this->outputResponse( [], $this->error, false );
    }
    
    /**
     * Renders a response
     *
     * @param        $result
     * @param string $message
     * @param bool   $status
     */
    protected function outputResponse( $result, string $message = '', bool $status = true ): void
    {
        session_write_close();
        
        $this->setResponseHeaders();
        
        Core::instance()->request->json_output([
            'status'  => $status,
            'message' => $message === '' ? $this->message : $message,
            'result'  => empty( $result ) ? new \stdClass : $result,
        ]);
    }
}