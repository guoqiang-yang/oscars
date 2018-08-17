<?php

/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/10/30
 * Time: 上午2:04
 */
class Conf_Channel
{
    //app相关配置

    /*const 	versionCode = 6;	//版本号
    const	versionName = '2.0.0';	//版本名
    const	url = 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/hc_app_2.1.0.apk';	//更新下载地址
    const	force = false;	//是否强制更新
    const	APPINTRO = "1.优化了界面和交互\n2.增加了运行安全性和稳定性\n";	//新版本介绍*/

    public static $CHANNEL = array(
        'guanwangand' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-guanwangand-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'erweima' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-erweima-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'yingyongbao' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-yingyongbao-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'baiduxi' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-baiduxi-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        '360zhushou' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-c360zhushou-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'sale1' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-sale1-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'sale2' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-sale2-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'sale3' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-sale3-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'sale4' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-sale4-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'sale5' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-sale5-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'sale6' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-sale6-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'sale7' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-sale7-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'sale8' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-sale8-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'sale9' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-sale9-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'leshangdian' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-leshangdian-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'jifeng' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-jifeng-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'wandoujia' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-wandoujia-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'nduo' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-nduo-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'mumayi' => array(
            'versionCode' => 7,
            'versionName' => '2.1.0',
            'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-mumayi-release-v2.1.0.apk',
            'force' => false,
            'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'jrtt' => array(
	        'versionCode' => 7,
	        'versionName' => '2.1.0',
	        'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-jrtt-release-v2.1.0.apk',
	        'force' => false,
	        'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'anzhi' => array(
	        'versionCode' => 7,
	        'versionName' => '2.1.0',
	        'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-anzhi-release-v2.1.0.apk',
	        'force' => false,
	        'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'yingyonghui' => array(
	        'versionCode' => 7,
	        'versionName' => '2.1.0',
	        'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-yingyonghui-release-v2.1.0.apk',
	        'force' => false,
	        'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'youyi' => array(
	        'versionCode' => 7,
	        'versionName' => '2.1.0',
	        'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-youyi-release-v2.1.0.apk',
	        'force' => false,
	        'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'huawei' => array(
	        'versionCode' => 7,
	        'versionName' => '2.1.0',
	        'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-huawei-release-v2.1.0.apk',
	        'force' => false,
	        'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
        'xiaomi' => array(
	        'versionCode' => 7,
	        'versionName' => '2.1.0',
	        'url' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/android/app-xiaomi-release-v2.1.0.apk',
	        'force' => false,
	        'intro' => "1、商品详情页新增查看大图功能，点击商品图片即可查看；\n2、优化商品详情页加载速度；\n3、优化地址管理功能；\n4、优化我的优惠券样式；\n5、订单详情页新增配送员的信息；\n6、解决全部订单列表没有已取消订单的问题；\n7、解决已知的bug。",
        ),
    );

    public static $NEW_CHANNEL = array(
        'baiduxi' => '百度',
        '360zhushou' => '360助手',
        'xiaomi' => '小米',
        'huawei' => '华为',
        'yingyongbao' => '应用宝'
    );

}