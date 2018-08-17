<?php
/**
 * 微信菜单
 */
class Conf_Weixin_Menu
{
	const WEIXIN_MENU_URL = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=%s';

	public static function getWeixinMenu()
	{
		foreach (self::$MENU['button'] as &$menu)
		{
			$menu['name'] = urlencode($menu['name']);
			if (isset($menu['sub_button']))
			{
				foreach ($menu['sub_button'] as &$subMenu)
				{
					$subMenu['name'] = urlencode($subMenu['name']);
				}
			}
		}

		return urldecode(json_encode(self::$MENU));
	}

	private static $MENU = array(
		'button' => array(
			array(
				'type' => 'view',
				'name' => '选购辅材',
				'url' => 'http://shop.haocaisong.cn/index/index.php?r=686',
			),
			array(
				'type' => 'view',
				'name' => '我的订单',
				'url' => 'http://shop.haocaisong.cn/order/history.php',
			),
			array(
				'name' => '好材服务',
				'sub_button' => array(
					array(
						'type' => 'view',
						'name' => '优惠政策',
						'url' => 'http://shop.haocaisong.cn/policy/privilege.php',
					),
					array(
						'type' => 'view',
						'name' => '配送政策',
						'url' => 'http://shop.haocaisong.cn/policy/freight.php',
					),
					array(
						'type' => 'view',
						'name' => '上楼政策',
						'url' => 'http://shop.haocaisong.cn/policy/carry_fee.php',
					),
					array(
						'type' => 'view',
						'name' => '售后服务',
						'url' => 'http://shop.haocaisong.cn/policy/after_sale.php',
					),
                    array(
                        'type' => 'view',
                        'name' => '下载App',
                        'url' => 'http://a.app.qq.com/o/simple.jsp?pkgname=com.haocai.app',
                    ),
//					array(
//						'type' => 'view',
//						'name' => '关于我们',
//						'url' => 'https://mp.weixin.qq.com/s?__biz=MzA4MjEwMjc1MA==&mid=509631887&idx=5&sn=fb92eda1f6c1391d2fb466c5e309c18c',
//					),
				),
			),
		)
	);

}