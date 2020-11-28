<?php

namespace MarketBoard;

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
}
