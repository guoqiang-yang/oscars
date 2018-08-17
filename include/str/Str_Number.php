<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/4/12
 * Time: 18:57
 */
class Str_Number
{
    public static function formatByWan($number)
    {
        $number = floor($number);
        $str = '';
        $wan = floor($number / 10000);
        if ($wan > 0)
        {
            $str = $wan . '万';
        }
        else
        {
            $str = $number;
        }

        return $str;
    }
    public static function hideMobile($mobile)
    {
        return preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $mobile);
    }
}