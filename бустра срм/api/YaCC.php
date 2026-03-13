<?php

namespace api;

use Simpla;

/**
 * Сокращает ссылки с помощью https://clck.ru/
 */
class YaCC
{
    /**
     * Адрес отправки запроса
     */
    public const URL = 'https://clck.ru/--';

    private Simpla $simpla;

    public function __construct()
    {
        $this->simpla = new Simpla();
    }

    /**
     * @param string $link
     * @return string
     * @throws Exception
     */
    public function getCCLink(string $link): string
    {
        return $this->sendRequest($link);
    }

    /**
     * @param string $link
     * @return string
     */
    private function sendRequest(string $link): string
    {
        return file_get_contents(self::URL . '?url=' .  urlencode($link));
    }

    /**
     * Поиск ссылок в строке
     * @param $url
     * @return array
     */
    public static function findUrls($url): array
    {
        $pattern = '#(www\.|https?://)?[a-z0-9]+\.[a-z0-9]{2,4}\S*#i';
        preg_match_all($pattern, $url, $matches);
        return $matches;
    }

    /**
     * Заменяет все ссылки в строке на сокращенные
     * @param string $text
     * @return array|string|string[]
     * @throws Exception
     */
    public function replaceAllUrls(string $text)
    {
        $result_text = $text;
        $links = self::findUrls($text);

        if (!empty($links[0])) {
            foreach ($links[0] as $link) {
                $result_text = str_replace($link, $this->getCCLink($link), $result_text);
            }
        }

        return $result_text;
    }
}
