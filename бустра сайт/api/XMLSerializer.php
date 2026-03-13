<?php

class XMLSerializer
{
    // The root key, specified in the constructor
    private $root;
    private $tagOpen;

    // Some commonly used constants
    const SPACE = ' ';
    const SLASH = '/';
    const OPEN = '<';
    const CLOSE = '>';
    const INDENT = '    ';
    const NEWLINE = "\n";

    // This expects attributes to be stored in an array indicated by this key
    const ATTRIBUTES_KEY = '@attributes';

    // Set this to true to generate a log
    const DEBUG = false;

    public function __construct($tagOpen = 'Request', $root = 'Request')
    {
        $this->root = $root;
        $this->tagOpen = $tagOpen;
    }

    public function serialize($obj)
    {
        ob_start();
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        echo self::NEWLINE;

        $attributes = null;
        if (isset($obj[self::ATTRIBUTES_KEY])) {
            $attributes = $obj[self::ATTRIBUTES_KEY];
        }

        self::tag_open($this->tagOpen, 0, $attributes);
        echo self::NEWLINE;
        self::_to_xml($obj, 0);
        self::tag_close($this->root, 0);

        $xml = ob_get_clean();

        return $xml;
    }

    private static function debug($msg)
    {
        if (self::DEBUG) {
            error_log($msg);
        }
    }

    private static function _to_xml(array $array, $depth, $parent_key = null)
    {
        $depth++;

        // This is a special case where there is a single empty element in this string-keyed array
        // Generate a self-closing tag
        if (count($array) == 0) {
            self::debug("_to_xml found array with one empty field");
            $key_to_use = current(array_keys($array));
            self::tag_open($key_to_use, $depth, null);
            return;
        }

        foreach ($array as $key => $value) {
            if ($key === self::ATTRIBUTES_KEY) {
                continue;
            }

            if (is_int($key)) {
                self::_to_xml($value, 0);
            } else {
                $attributes = null;
                if (is_array($value) && isset($value[self::ATTRIBUTES_KEY])) {
                    $attributes = $value[self::ATTRIBUTES_KEY];
                }

                $key_to_use = ($parent_key ? $parent_key : $key);

                $surrounding_key = true;

                if ($surrounding_key) {
                    self::debug("Generating standard tag with key {$key_to_use}");
                    self::tag_open($key_to_use, $depth, $attributes);
                    self::tag_value($value, $depth);
                    self::tag_close($key_to_use, (self::is_scalar($value) ? 0 : $depth));
                    echo self::NEWLINE;
                } else {
                    if (empty($value)) {
                        self::debug("Generating self-closing tag for key {$key_to_use}");
                        self::tag_open($key, $depth, null, true);
                        echo self::NEWLINE;
                    } else {
                        self::debug("Generating numeric array item with key {$key_to_use}");
                        self::_to_xml($value, $depth - 1, $key);
                    }
                }
            }
        }
    }

    private static function is_scalar($value)
    {
        return is_string($value) || is_numeric($value);
    }

    public static function has_string_keys(array $array)
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    private static function tag_open($key, $depth = 0, $attributes = null, $self_closing = false)
    {
        self::indent($depth);
        echo self::OPEN;
        echo $key;
        if ($attributes) {
            foreach ($attributes as $attribute_key => $attribute_value) {
                echo " {$attribute_key}=\"";
                self::sanitized_scalar($attribute_value);
                echo "\"";
            }
        }
        if ($self_closing) {
            echo self::SPACE;
            echo self::SLASH;
        }
        echo self::CLOSE;
    }

    private static function tag_value($value, $depth)
    {
        if (self::is_scalar($value)) {
            self::sanitized_scalar($value);
        } else if (is_array($value)) {
            echo self::NEWLINE;
            self::_to_xml($value, $depth);
        }
    }

    private static function sanitized_scalar($value)
    {
        echo str_replace(array('&', "'", '"'), array('&amp;', '&apos;', '&quot;'), $value);
    }

    private static function tag_close($key, $depth)
    {
        if ($depth > 0) {
            self::indent($depth);
        }

        echo self::OPEN;
        echo '/';
        echo $key;
        echo self::CLOSE;
    }

    private static function indent($depth)
    {
        echo str_repeat(self::INDENT, $depth);
    }
}