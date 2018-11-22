<?php


namespace App;


class ReviewDetectors
{
    private static $ner;

    public function __construct($ner)
    {
        self::$ner = $ner;
    }

    static function isAble($text)
    {
        return self::isNotEnglish($text) === false && self::isPersonal($text) === false;
    }
    static function isNotEnglish($text)
    {
        return strlen($text) != mb_strlen($text, 'utf-8');
    }
    static function isPersonal($text)
    {
        return strpos((string) self::$ner->tag($text), "/PERSON") !== false;
    }

}