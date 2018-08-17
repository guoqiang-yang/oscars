<?php

/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/3/7
 * Time: 11:09
 */
class Shop_Helper
{
    public static function formatPic(&$info, $f = 'pic_ids', $withRelSkuInfo = FALSE)
    {
        $pictag = trim($info[$f]);
        $info['_pic'] = array();

        $pictags = explode(',', $pictag);
        if (!empty($pictag))
        {
            foreach ($pictags as $pic)
            {
                $item = array(
                    'pic_tag' => $pic,
                    'small' => Data_Pic::getPicUrlFromOss($pic, 'small'),
                    'middle' => Data_Pic::getPicUrlFromOss($pic, 'middle'),
                    'normal' => Data_Pic::getPicUrlFromOss($pic, 'normal'),
                    'big' => Data_Pic::getPicUrlFromOss($pic, 'big'),
                    'detail' => Data_Pic::getPicUrlFromOss($pic, 'detail'),
                    'src' => Data_Pic::getPicUrlFromOss($pic, ''),
                );

                $info['_pics'][] = $item;
                empty($info['_pic']) && $info['_pic'] = $item;
            }
        }
        else
        {
            $pic = 'app_icon/default_pic.png';
            $item = array(
                'pic_tag' => $pic,
                'small' => Data_Pic::getUrlFromOss($pic, 'small'),
                'middle' => Data_Pic::getUrlFromOss($pic, 'middle'),
                'normal' => Data_Pic::getUrlFromOss($pic, 'normal'),
                'big' => Data_Pic::getUrlFromOss($pic, 'big'),
                'src' => Data_Pic::getUrlFromOss($pic, ''),
            );

            $info['_pics'][] = $item;
            empty($info['_pic']) && $info['_pic'] = $item;
        }

        // 关联sku
        $info['_rel_sku'] = self::parseRelationSkus($info['rel_sku'], $withRelSkuInfo);
    }

    /**
     * 计算套餐中每个商品的价格.
     * 
     * @rule
     *      套餐售价/套餐原价*单品售价
     * 
     * @param type $productList
     *      $item => array('sid'=>xxx, 'num'=>xxx, 'price'=>xxx)   //必须字段
     * 
     *  @retuan 
     *      $item['_package'] = array('pid'=>xxx, 'sid'=>xxx, 'cate1'=>xxx, 'cate2'=>xxx, 'num'=>xxx, 'price'=>xxx)
     */
    public static function appendSinglePrice4PackageProducts(&$productList, $cityId)
    {
        $sids = array();
        $pids = array();
        
        if (empty($productList) || empty($cityId))
        {
            return;
        }
        
        foreach($productList as $item)
        {
            if (empty($item['sid']) || empty($item['num']) || empty($item['price']))
            {
                return;
            }
            
            $sids[] = $item['sid'];
            $pids[] = $item['pid'];
        }
        unset($item);
        
        // 获取套餐商品等相关信息
        $packageSkus = array();
        $relPackageSids = array();
        
        $ss = new Shop_Sku();
        $sp = new Shop_Product();
        
        $skuInfos = $ss->getBulk($sids);
        foreach($skuInfos as $item)
        {
            if ($item['type']!=Conf_Sku::SKU_TYPE_PACKAGE || empty($item['rel_sku']))
            {
                continue;
            }
            
            $packageSkus[$item['sid']] = self::parseRelationSkus($item['rel_sku']);
            
            $relPackageSids[] = $item['sid'];
            $relPackageSids = array_merge($relPackageSids, Tool_Array::getFields($packageSkus[$item['sid']], 'sid'));
        }
        unset($item);

        // 无套餐商品
        if (empty($packageSkus)){ return;    }
        
        $packageSkuInfos = $ss->getBulk($relPackageSids);
        $productsWithSidKey = Tool_Array::list2Map($sp->getBySku(array_unique($relPackageSids), $cityId, 7), 'sid');

        $packageOriPrices = array();
        foreach($packageSkus as $_combSid => $_rels)
        {
            $packageOriPrices[$_combSid] = 0;
            foreach($_rels as $_relInfo)
            {
                $packageOriPrices[$_combSid] += $_relInfo['num']*$productsWithSidKey[$_relInfo['sid']]['price'];
            }
        }

        // append 套餐商品组合数据
        foreach($productList as &$item)
        {
            $_sid = $item['sid'];
            
            if (!array_key_exists($_sid, $packageSkus) || !array_key_exists($_sid, $productsWithSidKey))
            {
                continue;
            }
            
            //$packageOriPrice = $productsWithSidKey[$_sid]['ori_price'];
            $packageOriPrice = $packageOriPrices[$_sid];
            
            $_package = $packageSkus[$_sid];
            foreach($_package as &$pItem)
            {
                $pItem['pid'] = $productsWithSidKey[$pItem['sid']]['pid'];
                $pItem['num'] = $pItem['num'] * $item['num'];
                //$pItem['o_price'] = $productsWithSidKey[$pItem['sid']]['price'];
                $pItem['price'] = $packageOriPrice==0? $productsWithSidKey[$pItem['sid']]['price']: 
                                    round($item['price']/$packageOriPrice*$productsWithSidKey[$pItem['sid']]['price']);
                $pItem['cate1'] = $packageSkuInfos[$pItem['sid']]['cate1'];
                $pItem['cate2'] = $packageSkuInfos[$pItem['sid']]['cate2'];
            }
            
            $item['_package'] = $_package;
        }
    }


    /**
     * 解析关联sku信息.
     *
     * rel_sku: sku1:1,...,sku2:2
     *
     * @param type $relSkuStr
     * @param type $withMoreInfo
     */
    public static function parseRelationSkus($relSkuStr, $withMoreInfo = FALSE)
    {
        $relSkus = array();
        if (!empty($relSkuStr))
        {
            $sids = array();
            $skuNums = explode(',', $relSkuStr);
            foreach ($skuNums as $item)
            {
                list($sid, $num) = explode(':', $item);
                $relSkus[] = array('sid' => $sid, 'num' => intval($num));
                $sids[] = $sid;
            }

            if ($withMoreInfo)
            {
                $ss = new Shop_Sku();
                $skuInfos = $ss->getBulk($sids);

                foreach ($relSkus as &$one)
                {
                    $one['title'] = isset($skuInfos[$one['sid']]) ? $skuInfos[$one['sid']]['title'] : '';
                    $one['unit'] = isset($skuInfos[$one['sid']]) ? $skuInfos[$one['sid']]['unit'] : '个';
                }
            }
        }

        return $relSkus;
    }

    /**
     * 生成relation sku格式.
     *
     * @param array $relSkus {array(sky=>xx, num=>xx),...}
     */
    public static function genRelationSkus($relSkus)
    {
        if (empty($relSkus))
            return '';

        $_relSkus = array();
        foreach ($relSkus as $item)
        {
            if (!isset($item['sid']) && !isset($item['num']) && !is_numeric($item['num']))
                return '';

            $_relSkus[] = $item['sid'] . ':' . $item['num'];
        }

        return implode(',', $_relSkus);
    }

    public static function formatSkuPic($pid)
    {
        if (empty($pid))
        {
            return;
        }

        return Data_Pic::getPicUrlFromOss($pid, 'normal');
    }

    public static function sortProductsForPrint(&$products)
    {
        $newProducts = array();

        $sortBy = array(403, 404, 302, 1, 2, 3, 4, 5, 6,99);
        foreach ($sortBy as $cate)
        {
            foreach ($products as $idx => $product)
            {
                if ($cate >= 100 && $cate == $product['sku']['cate2'] || $cate < 100 && $cate == $product['sku']['cate1'])
                {
                    $newProducts[] = $product;
                    unset($products[$idx]);
                }

                if ($product['product']['pid'] == Conf_Activity::DALIBAO_PID)
                {
                    unset($products[$idx]);
                }
            }
        }

        $products = $newProducts;
    }

    public static function appendSkuInfo(&$skuList)
    {
        $ss = new Shop_Sku();
        $sids = Tool_Array::getFields($skuList, 'sid');
        $skus = $ss->getBulk($sids);

        foreach ($skuList as &$skuInfo)
        {
            $sid = $skuInfo['sid'];
            $skuInfo['_sku'] = $skus[$sid];
        }
    }
    
    /**
     * 计算商品的体积，重量.
     * 
     * @param array $productList 商品列表.
     */
    public static function calVolAndWeight4ProductList($productList)
    {
        $ret = array('v'=>0, 'w'=>0);   //v:体积-立方米；w:重量-千克
        
        $pid2Num = array();
        foreach($productList as $pItem)
        {
            if (empty($pItem['pid']) || empty($pItem['num'])) continue;
            
            $pid2Num[$pItem['pid']] = $pItem['num'];
        }
        
        if (empty($pid2Num)) return $ret;
        
        $sp = new Shop_Product();
        $ss = new Shop_Sku();
        $productInfos = $sp->getBulk(array_keys($pid2Num));
        
        $sid2Num = array();
        foreach($productInfos as $pinfo)
        {
            $sid2Num[$pinfo['sid']] = $pid2Num[$pinfo['pid']];
        }
        $skuInfos = $ss->getBulk(array_keys($sid2Num));
        
        // 计算商品重量 && 体积（ps：体积=长*宽*高）
        foreach($skuInfos as $item)
        {
            $ret['w'] += $sid2Num[$item['sid']] * $item['weight']/1000;
            $ret['v'] += $sid2Num[$item['sid']] * $item['length']*$item['width']*$item['height']/1000000;
        }
        
        return $ret;
    }
    
    /**
     * 计算skus的体积，重量.
     */
    public static function calVolAndWeight4SkuList($skuList)
    {
        $ret = array('v'=>0, 'w'=>0);   //v:体积-立方米；w:重量-千克
        
        $sid2Num = array();
        foreach($skuList as $sitem)
        {
            if (empty($sitem['sid']) || empty($sitem['num'])) continue;
            
            $sid2Num[$sitem['sid']] = $sitem['num'];
        }
        
        if (empty($sid2Num)) return $ret;
        
        $ss = new Shop_Sku();
        $skuInfos = $ss->getBulk(array_keys($sid2Num));
        
        // 计算商品重量 && 体积（ps：体积=长*宽*高）
        foreach($skuInfos as $item)
        {
            $ret['w'] += $sid2Num[$item['sid']] * $item['weight']/1000;
            $ret['v'] += $sid2Num[$item['sid']] * $item['length']*$item['width']*$item['height']/1000000;
        }
        
        return $ret;
    }
    
    /**
     * 统计商品‘指定分类2’的价格
     */
    public static function statProductPrice4SpecCate2($products)
    {
        $ret = array('sand_brick'=>0, 'plate'=>0);
        
        $pids = Tool_Array::getFields($products, 'pid');
        
        if (empty($pids)) return $ret;
        
        $sp = new Shop_Product();
        $ss = new Shop_Sku();
        $productInfos = $sp->getBulk($pids);
        
        $sids = Tool_Array::getFields($productInfos, 'sid');
        $skuInfos = $ss->getBulk(array_values($sids));
        
        foreach($products as $item)
        {
            $pid = $item['pid'];
            $sid = $productInfos[$pid]['sid'];
            $price = !empty($item['price'])? $item['price']: $productInfos[$pid]['price'];
            
            if (array_key_exists($skuInfos[$sid]['cate2'], Conf_Sku::$SAND_CEMENT_BRICK_CATE2))
            {
                $ret['sand_brick'] += $price* $item['num'];
            }
            else if (array_key_exists($skuInfos[$sid]['cate2'], Conf_Sku::$PLATES_CATE2))
            {
                $ret['plate'] += $price* $item['num'];
            }
        }
        
        return $ret;
    }
}