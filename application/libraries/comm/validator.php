<?php

class validator
{
    public function isNum($v)
    {
        $reg = '/\d+/';
        if (preg_match($reg, $v, $matches)) {
            return true;
        }

        return false;
    }

    public function length($v, $min, $max)
    {
        $len = sizeof($v);
        if ($len > $max || $len < $min) {
            return false;
        }
        return true;
    }

    public function range($v, $min, $max)
    {
        if ($v > $max || $v < $min) {
            return false;
        }
        return true;
    }

    public function isEmail($v)
    {
        $reg = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
        if (preg_match($reg, $v, $matches)) {
            return true;
        }

        return false;
    }

    public function isQQ($v)
    {
        $reg = '/[1-9]\d{4,11}/';
        if (preg_match($reg, $v, $matches)) {
            return true;
        }

        return false;
    }

    public function isDate($v){
       $reg = '/(\d{4}-\d{2}-\d{2}\d{2}:\d{2}:\d{2})/';
       if (preg_match($reg, $v, $matches)) {
           return true;
       }
       return false;

    }
    public function isMobile($v)
    {
        $reg = '/^1[0-9\s]+$/';
        if (preg_match($reg, $v, $matches)) {
            return true;
        }

        return false;
    }

    public function isPhone($v){
        $reg = '/^(0[0-9]{2,3}\-)?([2-9][0-9]{6,7})+(\-[0-9]{1,4})?$/';
        if (preg_match($reg, $v, $matches)) {
            return true;
        }

        return false;
    }


    public function isMk($v)
    {
        if ((is_string($v)) && (strlen($v) === 32)){
            return true;
        }

        return false;
    }

    public function isPhone_code($v){
        if ((is_string($v)) && (strlen($v) === 6)){
            return true;
        }
        return false;
    }

    public function isTruename($v){
        if ((is_string($v)) && (strlen($v) > 2)){
            return true;
        }
        return false;
    }

    public function isIdcard($v){
        if ((is_string($v)) && (strlen($v) == 18)){
            return true;
        }
        return false;
    }

    public function isBankcard($v){
        if ((is_string($v)) && (strlen($v) > 8) && (strlen($v) < 25)){
            return true;
        }
        return false;
    }

    public function isMid($v){
        if ((is_string($v)) && (strlen($v) == 9)){
            return true;
        }
        return false;
    }

    public function isPw($v){
        if (strlen($v) == 32){
            return true;
        }
        return false;
    }

    public function isImgCode($v)
    {
        if (strlen($v) === 4){
            return true;
        }
        return false;
    }

    public function isUrl($v)
    {
        if(!preg_match('/http:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',$v)){
            return false;
        }
        return true;
    }
}