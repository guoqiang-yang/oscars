<?php
/**
 * 活动图片配置
 */
class Conf_Picture
{
    const PICTURE_PLATFORM_WECHAT = 1,
          PICTURE_PLATFORM_APP = 2,
          PICTURE_PLATFORM_TOC_WEB = 3,
          PICTURE_PLATFORM_TOC_H5 = 4,
          PICTURE_PLATFORM_TOC_H5_CASE = 5,
          PICTURE_PLATFORM_TOC_H5_WIKI = 6;
    //活动图片的展示平台
	public static $PLATFORM = array(
        self::PICTURE_PLATFORM_WECHAT => '微信商城',
        self::PICTURE_PLATFORM_APP => 'APP',
        self::PICTURE_PLATFORM_TOC_WEB => '好材工长Web',
        self::PICTURE_PLATFORM_TOC_H5 => '好材工长Wap',
        self::PICTURE_PLATFORM_TOC_H5_CASE => '好材工长Wap案例列表',
        self::PICTURE_PLATFORM_TOC_H5_WIKI => '好材工长Wap百科列表',
	);

    const PICTURE_TYPE_BANNER = 1;
    const PICTURE_TYPE_AD = 2;
    //活动图片的类型
    public static $TYPE = array(
        self::PICTURE_TYPE_BANNER => 'banner',
        self::PICTURE_TYPE_AD => 'app广告',
    );
}