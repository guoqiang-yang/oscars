<?php
/**
 * 页面列表
 */
class Conf_Operation
{
	private static $AppBanners = array(

		array('url'=>'haocai://products?cate1=1',
			'pic_url' => 'http://img.haocaisong.cn/static/banner2016051801.jpg@480w_480h',
			'online' => '2016-03-01',
			'offline' => '2016-07-01'),

		array('url'=>'haocai://products?cate1=2',
			'pic_url' => 'http://img.haocaisong.cn/static/banner2016051802.png@480w_480h',
			'online' => '2016-03-01',
			'offline' => '2016-07-01'),
	);

	public static function getAppBanners()
	{
		$today = date('Y-m-d');
		$banners = self::$AppBanners;
		foreach($banners as $idx=> $banner)
		{
			if ($banner['online']>$today || $banner['offline']<$today)
			{
				unset($banners[$idx]);
			}
		}
		return $banners;
	}

	private static $SaleProducts = array(
		array(
			'name' => '限时抢购',
			'url'=>'haocai://sale_products?title=限时抢购&type=1',
			//'pids' => array(10357, 10349),
		),
		array(
			'name' => '特价商品',
			'url'=>'haocai://sale_products?title=特价商品&type=2',
			//'pids' => array(10350, 10341),
		)
	);

	public static function getAppSaleProducts($cityid)
	{
		$saleProducts = self::$SaleProducts;

        // 取促销商品
        $searchConf1 = array(
			'sales_type' => 1,
		);
        $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE;
		$spDatas = Shop_Api::getProductList($searchConf1, 0, 6, 0, $statusTag);
        
        // 取特卖商品
        $searchConf2 = array(
			'sales_type' => 2,
		);
        $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE;
		$hotDatas = Shop_Api::getProductList($searchConf2, 0, 6, 0, $statusTag);
        
		foreach ($saleProducts as &$block)
		{
            if ($block['name'] ==  '限时抢购')
            {
                $block['products'] = Shop_Api::formatProductForApp($spDatas['list']);
                foreach ($block['products'] as &$p)
                {
                    $p['icon'] = '';
                }
            }
            else if ($block['name'] == '特价商品')
            {
                $block['products'] = Shop_Api::formatProductForApp($hotDatas['list']);
                foreach ($block['products'] as &$p)
                {
                    $p['icon'] = '';
                }
            }
		}

		return $saleProducts;
	}

}