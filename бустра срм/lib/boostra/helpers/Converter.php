<?php

namespace boostra\helpers;

class Converter{
    
    private $data;
    
    /**
     * @var string json|xml|serialized|object|array
     */
    private $input_formats;
    
    /**
     * @var string json|xml|serialized|object|array
     */
    private $output_format = 'object';
    
    /**
     * @var
     */
    private $supported_formats = [
        'json',
        'xml',
        'serialized',
        'object',
        'array',
    ];
    
    /**
     * @var array
     */
    private $parsed;
    
    public function __construct( $data )
    {
        $this->data = $data;
    }
    
    /**
     * @param $input_formats
     *
     * @return Converter
     * @throws \Exception
     */
    public function from( $input_formats ): self
    {
        if( is_string( $input_formats ) && ! in_array( $input_formats, $this->supported_formats ) ){
            throw new \Exception('Input format is not supported');
        }
        
        if( is_array( $input_formats ) && array_diff( $input_formats, $this->supported_formats ) ){
            throw new \Exception('Input format is not supported');
        }
        
        $this->input_formats = $input_formats;
        
        return $this;
    }
    
    /**
     * @param string $output_format
     * @param bool   $convert
     *
     * @return null
     * @throws \Exception
     */
    public function to( string $output_format, bool $convert = true )
    {
        if( ! in_array( $output_format, $this->supported_formats ) ){
            throw new \Exception('Input format is not supported');
        }

        $this->output_format = $output_format;
        
        return $convert
            ? $this->convert()
            : $this;
    }
    
    /**
     * @return void
     * @throws \Exception
     */
    public function convert()
    {
        $format = $this->detectFormat( $this->data, $this->input_formats );
        
        if( ! in_array( $format, $this->supported_formats ) ){
            throw new \Exception('Input data is not corresponding input format');
        }
        
        $this->parsed = $this->parseFrom( $this->data,   $format );
        $converted    = $this->convertTo( $this->parsed, $this->output_format );
        
        return $converted;
    }
    
    private function is( $data, $format )
    {
        switch( $format ){
            case 'json':
                return $this->isJSON( $data );
            case 'xml':
                return $this->isXML( $data );
            case 'serialized':
                return $this->isSerialized( $data );
            case 'object':
                return is_object( $data );
            case 'array':
                return is_array( $data );
            default:
                return false;
        }
    }
    
    private function isJSON( $string )
    {
        return is_scalar( $string ) &&
               ( $string[0] === '[' || $string[0] === '{' ) &&
               ( @json_decode( $string ) || json_last_error() === JSON_ERROR_NONE );
    }
    
    private function isSerialized( $string, $strict = true ): bool
    {
        // If it isn't a string, it isn't serialized.
        if ( ! is_string( $string ) ) {
            return false;
        }
        $string = trim( $string );
        if ( 'N;' === $string ) {
            return true;
        }
        if ( strlen( $string ) < 4 ) {
            return false;
        }
        if ( ':' !== $string[1] ) {
            return false;
        }
        if ( $strict ) {
            $lastc = substr( $string, -1 );
            if ( ';' !== $lastc && '}' !== $lastc ) {
                return false;
            }
        } else {
            $semicolon = strpos( $string, ';' );
            $brace     = strpos( $string, '}' );
            // Either ; or } must exist.
            if ( false === $semicolon && false === $brace ) {
                return false;
            }
            // But neither must be in the first X characters.
            if ( false !== $semicolon && $semicolon < 3 ) {
                return false;
            }
            if ( false !== $brace && $brace < 4 ) {
                return false;
            }
        }
        $token = $string[0];
        switch ( $token ) {
            case 's':
                if ( $strict ) {
                    if ( '"' !== substr( $string, -2, 1 ) ) {
                        return false;
                    }
                } elseif ( false === strpos( $string, '"' ) ) {
                    return false;
                }
                // Or else fall through.
            case 'a':
            case 'O':
                return (bool) preg_match( "/^{$token}:\d+:/s", $string );
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match( "/^{$token}:[\d.E+-]+;$end/", $string );
        }
        
        return false;
    }
    
    private function isXML( $data )
    {
        return (bool)@simplexml_load_string( $data );
    }
    
    /**
     * @param $data
     * @param $input_formats
     *
     * @return string
     */
    public function detectFormat( $data = null, $input_formats = null ): string
    {
        $data          = $data ?? $this->data;
        $input_formats = $input_formats ?: $this->supported_formats;
        
        switch( true ){
            case in_array( 'object', $input_formats, true ) && is_object( $data ):
                $detected_format = 'object';
                break;
            case in_array( 'array', $input_formats, true ) && is_array( $data ):
                $detected_format = 'array';
                break;
            case in_array( 'json', $input_formats, true ) && $this->isJSON( $data ):
                $detected_format = 'json';
                break;
            case in_array( 'serialized', $input_formats, true ) && $this->isSerialized( $data ):
                $detected_format = 'serialized';
                break;
            case in_array( 'xml', $input_formats, true ) && $this->isXML( $data ):
                $detected_format = 'xml';
                break;
            default:
                $detected_format = 'unknown';
        }
        
        $this->input_formats = [ $detected_format ];
        
        return $detected_format;
    }
    
    
    /**
     * @param        $data
     * @param string $format
     *
     * @return array
     * @throws \JsonException
     * @throws \Exception
     */
    private function parseFrom( $data, string $format ): array
    {
        switch( $format ){
            case 'json':
                return json_decode( $data, true, 512, JSON_THROW_ON_ERROR );
            case 'xml':
                return (array) simplexml_load_string( $data );
            case 'serialized':
                return unserialize( $data );
            case 'object':
                return (array) $data;
            case 'array':
                return $data;
            default:
                throw new \Exception('Unknown format');
        }
    }
    
    /**
     * @param array  $parsed
     * @param string $output_format
     *
     * @return array|false|object|string
     * @throws \JsonException
     */
    private function convertTo( array $parsed, string $output_format )
    {
        switch( $output_format ){
            case 'json':
                return json_encode( $parsed, JSON_THROW_ON_ERROR );
            case 'xml':
                return $this
                    ->toXml( $parsed )
                    ->asXML();
            case 'serialized':
                return serialize( $parsed );
            case 'object':
                return (object) $parsed;
            case 'array':
                return $parsed;
            default:
                throw new \Exception('Unknown format');
        }
    }
    
    /**
     * @param array                  $data
     * @param string                 $root
     * @param \SimpleXMLElement|null $xml
     *
     * @return \SimpleXMLElement
     */
    private function toXml( array $data, string $root = 'root', \SimpleXMLElement $xml = null ): \SimpleXMLElement
    {
        $xml  = $xml ?? new \SimpleXMLElement( "<$root/>" );
        
        foreach( $data as $key => &$value ){
            is_array( $value )
                ? $this->toXml( $value, $root, $xml->addChild( $key ) )
                : $xml->addChild( $key, $value );
        }
        
        return $xml;
    }
}