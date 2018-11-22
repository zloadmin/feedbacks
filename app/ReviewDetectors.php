<?php


namespace App;


class ReviewDetectors
{
    private $text;
    private $ner;

    public function __construct($text, $ner)
    {
        $this->text = $text;
        $this->ner = $ner;

    }

    public function isAble()
    {
        return $this->isNotEnglish() === false && $this->isPersonal() === false;
    }
    public function isNotEnglish()
    {
        return strlen($this->text) != mb_strlen($this->text, 'utf-8');
    }
    public function isPersonal()
    {
        return strpos((string) $this->ner->tag($this->text), "/PERSON") !== false;
    }

}