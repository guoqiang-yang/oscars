<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/12
 * Time: 下午6:23
 */
class Conf_Activity_Flash_Sale {
    public static $PRICE = array(0,1,2,3,4,5,6,7,8,9);
    const PALTFORM_WECHAT = 1;
    const PALTFORM_APP = 2;
    const PALTFORM_BOTH = 3;
    public static $PALTFORM = array(
        self::PALTFORM_WECHAT => '微信商城',
        self::PALTFORM_APP => 'APP',
        self::PALTFORM_BOTH => '微信商城和APP',
    );
    const TYPE_TIME = 1;
    public static $TYPE = array(
        self::TYPE_TIME => '限时抢购',
    );
}
