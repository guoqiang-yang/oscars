<?php

class Tool_Mobile
{
    /**
     * @param $mobile
     *
     * @return string
     *
     * 获取手机号所属省份
     */
    public static function getProvince($mobile)
    {
        $url = 'https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=' . $mobile;
        $res = file_get_contents($url);
        preg_match_all("/(\w+):'([^']+)/", $res, $m);
        $arr = array_combine($m[1], $m[2]);

        return iconv('gbk', 'utf8', $arr['province']);
    }

    public static function hideMobile($mobile)
    {
        $len = strlen($mobile);
        for ($i = 0; $i < 4 && $i < $len; $i++)
        {
            $mobile[$len - $i - 1] = '*';
        }

        return $mobile;
    }
}
