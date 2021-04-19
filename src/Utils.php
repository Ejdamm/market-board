<?php

namespace MarketBoard;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

class Utils
{
    public static function generateRemovalCode()
    {
        return strtoupper(substr(sha1(mt_rand()), 17, 6));
    }

    public static function dump($array)
    {
        echo "<pre>" . print_r($array, true) . "</pre>";
    }

    public static function createCaptcha($session)
    {
        $phraseBuilder = new PhraseBuilder(5, '23456789ABCDEFGHJKLMNPQRSTUVWXYZ');
        $captcha = new CaptchaBuilder(null, $phraseBuilder);
        $captcha->build();
        $session->set('captcha', $captcha->getPhrase());
        return $captcha->inline();
    }

    public static function localizeUrl($url, $newLocale, $currentLocale)
    {
        $explodedUrl = explode("/", $url);
        if (isset($explodedUrl[1]) && ($explodedUrl[1] == $currentLocale || $explodedUrl[1] == $newLocale)) {
            $explodedUrl[1] = $newLocale;
        } else {
            array_splice( $explodedUrl, 1, 0, $newLocale);
        }
        return implode("/", $explodedUrl);
    }
}
