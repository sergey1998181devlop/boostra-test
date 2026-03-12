<?php

namespace boostra\helpers;

class InputValidator{
    
    private $filter_var_supported_types = [ 'bool', 'int', 'float', 'email', 'domain', 'ip', 'mac', 'regexp', 'url' ];
    
    private array $items;
    private array $rules = [
        'items' => 'array',
        'rules' => 'array',
    ];
    
    public function __construct( $items = [], $rules = [] )
    {
        $this->items = $items;
        $this->rules = $rules;
    }
    
    /**
     * @param array $rules
     *
     * @return InputValidator
     */
    public function setRules( array $rules ): self
    {
        $this->rules = $rules;
        
        return $this;
    }
    
    protected function getParam( $name )
    {
        return $this->items[ $name ] ?? null;
    }
    
    /**
     * Валидация формы
     
     * @return bool
     */
    private function validate(): bool
    {
        foreach( $this->rules as $param => $rule ){
            
            $value = $this->getParam( $param );
            
            // Validate strict value
            if( is_array( $rule ) && ! in_array( $value, $rule, true ) ){
                $this->error = "$param is not valid";
                return false;
            }
            
            // Validate types 'bool', 'int', 'float', 'email', 'domain', 'ip', 'mac', 'regexp', 'url'
            if( in_array( $rule, $this->filter_var_supported_types, true ) &&
                filter_var( $value, constant( 'FILTER_VALIDATE_' . strtoupper( $rule ) ) ) === false
            ){
                $this->error = "$param is not valid";
                return false;
            }
            
            if( $rule === 'date' && ! strtotime( $value ) ){
                $this->error = "$param is not valid";
                return false;
            }
            
            if( $rule === 'array' && ! is_array( $value ) ){
                $this->error = "$param is not valid";
                return false;
            }
            
            // Special function validation
            if( is_callable( $rule ) ){
                return $rule( $value );
            }
        }
        
        return true;
    }
}