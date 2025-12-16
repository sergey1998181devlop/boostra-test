<?php

namespace Messengers;

use Messengers\Config as MainConf;

final class MessageHtmlBody
{

    public static function picture(string $file, string $text = null): string
    {
        $str = '<div class="' . MainConf::MESSAGE_WRAPPER_CLASS . '">';
        $str .= '<img class="' . MainConf::MESSAGE_IMAGE_CLASS . '" src="' . $file . '"/>';
        if ($text) {
            $str .= '<p class="' . MainConf::MESSAGE_TEXT_BLOCK_CLASS . '">' .
                $text . '</p>';
        }
        $str .= '</div>';
        return $str;
    }

    public static function file(string $file, string $fileName, string $text = null): string
    {
        $str = '<div class="' . MainConf::MESSAGE_WRAPPER_CLASS . '">';
        $str .= '<a class="' . MainConf::MESSAGE_URL_CLASS . '" href="' . $file . '" download target="_blank">' . $fileName . '</a>';
        if ($text) {
            $str .= '<p class="' . MainConf::MESSAGE_TEXT_BLOCK_CLASS . '">' .
                $text . '</p>';
        }
        $str .= '</div>';
        return $str;
    }

    public static function url(string $url): string
    {
        $str = '<div class="' . MainConf::MESSAGE_WRAPPER_CLASS . '">';
        $str .= '<a class="' . MainConf::MESSAGE_URL_CLASS . '" href="' .
            $url . '" target="_blank">' . $url . '</a>';
        $str .= '</div>';
        return $str;
    }

    /**
     * @param string $text
     * @return string
     */
    public static function text(string $text): string
    {
        $str = '<div class="' . MainConf::MESSAGE_WRAPPER_CLASS . '">';
        if ($text) {
            $str .= '<p class="' . MainConf::MESSAGE_TEXT_BLOCK_CLASS . '">' . $text . '</p>';
        }
        $str .= '</div>';
        return $str;
    }

    /**
     * @param string $file
     * @param string|null $thumbnail
     * @param string|null $text
     * @return string
     */
    public static function video(string $file, ?string $thumbnail = null, string $text = null): string
    {
        $str = '<div class="' . MainConf::MESSAGE_WRAPPER_CLASS . '">';
        $str .= '<video class="' . MainConf::MESSAGE_VIDEO_CLASS . '" controls';
        if ($thumbnail) {
            $str .= ' poster="' . $thumbnail . '" >';
        }
        $str .= '<source src="' . $file . '" type="' . mime_content_type($file) . '">';
        $str .= "'Your browser doesn't support HTML5 video tag.'";
        $str .= '</video>';
        if ($text) {
            $str .= '<p class="' . MainConf::MESSAGE_TEXT_BLOCK_CLASS . '">' . $text . '</p>';
        }
        $str .= '</div>';
        return $str;
    }

    public static function audio(string $file, ?string $fileName = null, string $text = null): string
    {
        $str = '<div class="' . MainConf::MESSAGE_WRAPPER_CLASS . '">';
        $str .= '<figure>';
        if ($fileName) {
            $str .= '<figcaption>' . $fileName . '</figcaption>';
        }
        $str .= '<audio class="' . MainConf::MESSAGE_AUDIO_CLASS . '" controls src="' . $file . '">';
        $str .= '<a href="' . $file . '">Download audio</a>';
        $str .='</audio>';
        $str.='</figure>';
        if ($text) {
            $str .= '<p class="' . MainConf::MESSAGE_TEXT_BLOCK_CLASS . '">' . $text . '</p>';
        }
        $str .= '</div>';
        return $str;
    }

}