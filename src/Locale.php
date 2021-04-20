<?php


namespace MarketBoard;

class Locale
{
    private $code;
    private $language;
    private $url;

    public function __construct($code, $language, $url)
    {
        $this->code = $code;
        $this->language = $language;
        $this->url = $url;
    }

    public function toArray()
    {
        return ['code' => $this->code, 'language' => $this->language, 'url' => $this->url];
    }
}
