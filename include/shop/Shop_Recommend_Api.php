<?php
/**
 * 用户推荐相关接口
 */
class Shop_Recommend_Api  extends Base_Api
{
    /**
     * 获取用户推荐品牌
     * @param $uid
     * @param $city_id
     * @return array
     */
    public static function getRecommendBrand($uid, $city_id)
    {
        $flag_status = true;
        if($uid > 0)
        {
            $customer_brand = Crm2_Api::getCustomerRecommendBrandByUid($uid, $city_id);
            if (!empty($customer_brand))
            {
                $flag_status = false;
            }
        }
        $responseData = array();
        if($flag_status)
        {
            $acb = new Activity_City_Brand();
            $_info = $acb->getByCity($city_id);
            $cate1 = Conf_Sku::$CATE1;
            foreach ($cate1 as $key => $item)
            {
                $data = array(
                    'cate1' => $key,
                    'name' => $cate1[$key]['name'],
                    'brandList' => array()
                );
                $brandlist = array();
                switch ($key)
                {
                    case 1:
                        $brandlist = $_info['_water_1'];
                        break;
                    case 2:
                        $brandlist = $_info['_electric_2'];
                        break;
                    case 3:
                        $brandlist = $_info['_wood_3'];
                        break;
                    case 4:
                        $brandlist = $_info['_tile_4'];
                        break;
                    case 5:
                        $brandlist = $_info['_oil_5'];
                        break;
                    case 6:
                        $brandlist = $_info['_tools_6'];
                        break;
                }
                foreach ($brandlist as $bid)
                {
                    $data['brandList'][] = array(
                        'bid' => $bid,
                        'name' => $_info['brandList'][$bid]['name']
                    );
                }
                $responseData[] = $data;
            }
        }
        return $responseData;
    }

    /**
     * 获取用户推荐材料
     * @param $uid
     * @param $city_id
     * @param $platForm
     * @param array $brandList
     * @return array
     */
    public static function getRecommendWare($uid, $city_id, $platForm, $brandList = '')
    {
        $responseData = array();
        if($uid > 0)
        {
            //获取客户购买历史
            $productHistoryList = Crm2_Api::getCustomerProductRelationByUid($uid, $city_id,0,0);
            if(empty($productHistoryList))
            {
                $responseData['defaultCate1'] = 1;
                $responseData['categoryList'] = self::_defaultRecommendProduct($uid, $city_id, $platForm, $brandList);
            }else{
                $cate1List = Conf_Sku::$CATE1;
                $cate2List = Conf_Sku::$CATE2;
                $cate3List = Conf_Sku::$CATE3;
                $lowPrice = Shop_Api::getLowestPrice($city_id, $platForm);//活动价
                //获取客户不显示的商品
                $displayProductList = Crm2_Api::getDeleteCustomerProductRelationByUid($uid,0,0);
                $cart_list = array();//购物车
                $cart_pids = array();
                $cartProudctList = Cart_Api::getUserCart($uid, $city_id);
                $mapByProducts = array();
                if(!empty($cartProudctList))
                {
                    $mapByProducts = Tool_Array::list2Map($cartProudctList, 'pid');
                    $cart_pids = Tool_Array::getFields($cartProudctList, 'pid');
                }
                $responseData['defaultCate1'] = Crm2_Api::getDefaultCate1ByUid($uid, $city_id);
                $pids1 = self::_searchMultiArray($productHistoryList, 'pid');
                //获取推荐材料
                $top_data = Shop_Api::getTopCategroyProduct($city_id);
                $brand_top_data = Shop_Api::getTopCategoryBrandProduct($city_id);//品牌top10
                $brand_select_list = array();//选中的品牌
                $recommend = Crm2_Api::getCustomerRecommendBrandByUid($uid, $city_id);
                if(!empty($recommend) && $recommend['value'] != '1')
                {
                    $recommend = json_decode($recommend['value'], true);
                    $bid_arr = Tool_Array::list2Map($recommend, 'categoryId');
                    foreach ($bid_arr as $cate1 => $brandList)
                    {
                        $brand_select_list[$cate1] = $brandList['brand'][0];
                    }
                }
                $pids2 = self::_searchMultiArray($top_data, 'pid');
                $pids3 = self::_searchMultiArray($brand_top_data, 'pid');
                $pids = array_merge($pids1, $pids2, $cart_pids, $pids3);
                $product_list = Shop_Api::getProductInfos($pids);//获取商品详情
                //获取主材、辅材分类关系
                $cate_relation_list = Conf_Order::getCateRelationOfRecommend();
                $brand_relation_list = array();
                //购物车数据处理
                if(!empty($cart_pids))
                {
                    foreach ($cart_pids as $cart_pid) {
                        $product = $product_list[$cart_pid];
                        if($product['sku']['cate3']>0)
                        {
                            $cart_list[$product['sku']['cate3']][] = $cart_pid;
                        }else{
                            $cart_list[$product['sku']['cate2']][] = $cart_pid;
                        }
                    }
                }
                //购买记录数据处理及主辅材品牌推荐关系
                $product_cate_list = array();
                //购买记录分类频次
                $tmp_cate2List = array();
                foreach ($productHistoryList as $item)
                {
                    if($item['cate3'] > 0)
                    {
                        if(in_array($item['cate3'], array_keys($cate_relation_list)) && $item['bid'] > 0 && !isset($brand_relation_list[$cate_relation_list[$item['cate3']]]))
                        {
                            $brand_relation_list[$cate_relation_list[$item['cate3']]] = $item['bid'];
                        }
                        $product_cate_list[$item['cate3']][] = $item['pid'];
                    }
                    else
                    {
                        if(in_array($item['cate2'], array_keys($cate_relation_list)) && $item['bid'] > 0 && !isset($brand_relation_list[$cate_relation_list[$item['cate2']]]))
                        {
                            $brand_relation_list[$cate_relation_list[$item['cate2']]] = $item['bid'];
                        }
                        $product_cate_list[$item['cate2']][] = $item['pid'];
                    }
                    $tmp_cate2List[$item['cate1']][$item['cate2']] += $item['frequency'];
                }
                //根据购买记录二级分类频次排序
                $cate2List = self::_getCate2ByFrequency($cate2List, $tmp_cate2List);
                unset($productHistoryList);
                //初始化数据
                foreach ($cate1List as $key => $item){
                    $responseData['categoryList'][$key-1] = array(
                        'cate1' => $key,
                        'name' => $item['name'],
                        'list' => array()
                    );
                    $tmp_key = 0;
                    foreach ($cate2List[$key] as $key2 => $item2)
                    {
//                        $brandList = self::_getBrandListByCate2($key2);
//                        $data_brands = array();
//                        foreach ($brandList as $value)
//                        {
//                            $data_brands[] = array(
//                                'bid' => $value['bid'],
//                                'name' => $value['name'],
//                                'is_select' => array_rand(array(0,1),1),
//                            );
//                        }
                        $responseData['categoryList'][$key-1]['list'][$tmp_key] = array(
                            'cate2' => $key2,
                            'name' => $item2['name'],
//                            'brands' => $data_brands,
//                            'relation_cate_name' => isset($cate_relation_list[$key2]) ? $cate2List[$key][$cate_relation_list[$key2]]['name'] : '',
                            'list' => array()
                        );

                        if(isset($cate3List[$key2]))
                        {
                            foreach ($cate3List[$key2] as $key3 => $item3)
                            {
                                $num = 0;
                                $tmp_pids3 = array();
                                //购物车
                                if(isset($cart_list[$key3]))
                                {
                                    foreach($cart_list[$key3] as $pid)
                                    {
                                        $product = $product_list[$pid];
                                        if(empty($product))
                                        {
                                            continue;
                                        }
                                        $responseData['categoryList'][$key-1]['list'][$tmp_key]['list'][] = array(
                                            'pid' => $product['product']['pid'],
                                            'title' => $product['sku']['title'],
                                            'alias' => $product['sku']['alias'],
                                            'sale_price' => (string)round(($lowPrice[$pid]['sale_price'] ? $lowPrice[$pid]['sale_price'] : $product['product']['price'])/100,2),
                                            'unit' => $product['sku']['unit'],
                                            'image' => $product['sku']['_pic']['small'],
                                            'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                                            'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
                                            'num' => $mapByProducts[$pid]['num'],
                                        );
                                        $tmp_pids3[] = $pid;
                                    }
                                }
                                //购买记录
//                                if(isset($product_cate_list[$key3]))
//                                {
//                                    foreach ($product_cate_list[$key3] as $value)
//                                    {
//                                        if(in_array($value, $tmp_pids3))
//                                        {
//                                            $num++;
//                                            if($num >= 2)
//                                            {
//                                               break;
//                                            }else{
//                                               continue;
//                                            }
//                                        }
//                                        $product = $product_list[$value];
//                                        $responseData['categoryList'][$key-1]['list'][$tmp_key]['list'][] = array(
//                                            'pid' => $product['product']['pid'],
//                                            'title' => $product['sku']['title'],
//                                            'alias' => $product['sku']['alias'],
//                                            'sale_price' => (string)round(($lowPrice[$value]['sale_price'] ? $lowPrice[$value]['sale_price'] : $product['product']['price'])/100,2),
//                                            'unit' => $product['sku']['unit'],
//                                            'image' => $product['sku']['_pic']['small'],
//                                            'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
//                                            'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
//                                            'num' => $mapByProducts[$product['product']['pid']]['num'],
//                                        );
//                                        $tmp_pids3[] = $value;
//                                        $num++;
//                                        if($num >= 2)
//                                        {
//                                            break;
//                                        }
//                                    }
//                                }
                                //主辅材关联推荐
//                                if(!$num && isset($brand_relation_list[$key2]) && isset($brand_top_data[$key3][$brand_relation_list[$key2]]))
//                                {
//                                    foreach ($brand_top_data[$key3][$brand_relation_list[$key2]] as $value)
//                                    {
//                                        if(in_array($value['pid'], $tmp_pids3))
//                                        {
//                                            $num++;
//                                            if($num >= 2)
//                                            {
//                                               break;
//                                            }else{
//                                               continue;
//                                            }
//                                        }
//                                        $product = $product_list[$value['pid']];
//                                        if(!in_array($product['product']['pid'], $displayProductList)) {
//                                            $responseData['categoryList'][$key - 1]['list'][$tmp_key]['list'][] = array(
//                                                'pid' => $product['product']['pid'],
//                                                'title' => $product['sku']['title'],
//                                                'alias' => $product['sku']['alias'],
//                                                'sale_price' => (string)round(($lowPrice[$value['pid']]['sale_price'] ? $lowPrice[$value['pid']]['sale_price'] : $product['product']['price'])/100,2),
//                                                'unit' => $product['sku']['unit'],
//                                                'image' => $product['sku']['_pic']['small'],
//                                                'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
//                                                'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : '')
//                                            );
//                                        }
//                                        $tmp_pids3[] = $value;
//                                        $num++;
//                                        if($num >= 2)
//                                        {
//                                            break;
//                                        }
//                                    }
//                                }
                                //选中品牌推荐
                                if(!$num && isset($brand_select_list[$key]))
                                {
                                    foreach ($brand_top_data[$key3][$brand_select_list[$key]] as $value)
                                    {
                                        if(in_array($value['pid'], $tmp_pids3))
                                        {
                                            $num++;
                                            if($num >= 2)
                                            {
                                                break;
                                            }else{
                                                continue;
                                            }
                                        }
                                        $product = $product_list[$value['pid']];
                                        if(empty($product))
                                        {
                                            continue;
                                        }
                                        if(!in_array($product['product']['pid'], $displayProductList)) {
                                            $responseData['categoryList'][$key - 1]['list'][$tmp_key]['list'][] = array(
                                                'pid' => $product['product']['pid'],
                                                'title' => $product['sku']['title'],
                                                'alias' => $product['sku']['alias'],
                                                'sale_price' => (string)round(($lowPrice[$value['pid']]['sale_price'] ? $lowPrice[$value['pid']]['sale_price'] : $product['product']['price'])/100,2),
                                                'unit' => $product['sku']['unit'],
                                                'image' => $product['sku']['_pic']['small'],
                                                'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                                                'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : '')
                                            );
                                        }
                                        $tmp_pids3[] = $value;
                                        $num++;
                                        if($num >= 2)
                                        {
                                            break;
                                        }
                                    }
                                }
                                //默认推荐
                                if(!$num)
                                {
                                    foreach($top_data[$key3] as $value)
                                    {
                                        if(in_array($value['pid'], $tmp_pids3))
                                        {
                                            $num++;
                                            if($num >= 2)
                                            {
                                                break;
                                            }else{
                                                continue;
                                            }
                                        }
                                        $product = $product_list[$value['pid']];
                                        if(empty($product))
                                        {
                                            continue;
                                        }
                                        if(!in_array($product['product']['pid'], $displayProductList)) {
                                            $responseData['categoryList'][$key - 1]['list'][$tmp_key]['list'][] = array(
                                                'pid' => $product['product']['pid'],
                                                'title' => $product['sku']['title'],
                                                'alias' => $product['sku']['alias'],
                                                'sale_price' => (string)round(($lowPrice[$value]['sale_price'] ? $lowPrice[$value]['sale_price'] : $product['product']['price'])/100,2),
                                                'unit' => $product['sku']['unit'],
                                                'image' => $product['sku']['_pic']['small'],
                                                'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                                                'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
                                                'num' => $mapByProducts[$product['product']['pid']]['num'],
                                            );
                                        }
                                        $tmp_pids3[] = $value;
                                        $num++;
                                        if($num >= 2)
                                        {
                                            break;
                                        }
                                    }
                                }

                            }
                        }else{
                            $num = 0;
                            $tmp_pids2 = array();
                            //购物车
                            if(isset($cart_list[$key2]))
                            {
                                foreach($cart_list[$key2] as $pid)
                                {
                                    $product = $product_list[$pid];
                                    if(empty($product))
                                    {
                                        continue;
                                    }
                                    $responseData['categoryList'][$key-1]['list'][$tmp_key]['list'][] = array(
                                        'pid' => $product['product']['pid'],
                                        'title' => $product['sku']['title'],
                                        'alias' => $product['sku']['alias'],
                                        'sale_price' => (string)round(($lowPrice[$pid]['sale_price'] ? $lowPrice[$pid]['sale_price'] : $product['product']['price'])/100,2),
                                        'unit' => $product['sku']['unit'],
                                        'image' => $product['sku']['_pic']['small'],
                                        'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                                        'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
                                        'num' => $mapByProducts[$pid]['num'],
                                    );
                                    $tmp_pids2[] = $pid;
                                }
                            }
//                            //购买记录
//                            if(isset($product_cate_list[$key2]))
//                            {
//                                foreach ($product_cate_list[$key2] as $value)
//                                {
//                                    if(in_array($value, $tmp_pids2))
//                                    {
//                                        $num++;
//                                        if($num >= 3)
//                                        {
//                                            break;
//                                        }else{
//                                            continue;
//                                        }
//                                    }
//                                    $product = $product_list[$value];
//                                    $responseData['categoryList'][$key - 1]['list'][$tmp_key]['list'][] = array(
//                                        'pid' => $product['product']['pid'],
//                                        'title' => $product['sku']['title'],
//                                        'alias' => $product['sku']['alias'],
//                                        'sale_price' => (string)round(($lowPrice[$value]['sale_price'] ? $lowPrice[$value]['sale_price'] : $product['product']['price'])/100,2),
//                                        'unit' => $product['sku']['unit'],
//                                        'image' => $product['sku']['_pic']['small'],
//                                        'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
//                                        'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : '')
//                                    );
//                                    $tmp_pids2[] = $value;
//                                    $num++;
//                                    if($num >= 3)
//                                    {
//                                        break;
//                                    }
//                                }
//                            }
//                            //主辅材关联推荐
//                            if(!$num && isset($brand_relation_list[$key2]) && isset($brand_top_data[$key2]))
//                            {
//                                foreach ($brand_top_data[$key2][$brand_relation_list[$key2]] as $value)
//                                {
//                                    if(in_array($value['pid'], $tmp_pids2))
//                                    {
//                                        $num++;
//                                        if($num >= 2)
//                                        {
//                                            break;
//                                        }else{
//                                            continue;
//                                        }
//                                    }
//                                    $product = $product_list[$value['pid']];
//                                    if(!in_array($product['product']['pid'], $displayProductList)) {
//                                        $responseData['categoryList'][$key - 1]['list'][$tmp_key]['list'][] = array(
//                                            'pid' => $product['product']['pid'],
//                                            'title' => $product['sku']['title'],
//                                            'alias' => $product['sku']['alias'],
//                                            'sale_price' => (string)round(($lowPrice[$value['pid']]['sale_price'] ? $lowPrice[$value['pid']]['sale_price'] : $product['product']['price'])/100,2),
//                                            'unit' => $product['sku']['unit'],
//                                            'image' => $product['sku']['_pic']['small'],
//                                            'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
//                                            'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : '')
//                                        );
//                                    }
//                                    $tmp_pids2[] = $value;
//                                    $num++;
//                                    if($num >= 2)
//                                    {
//                                        break;
//                                    }
//                                }
//                            }
                            //选中品牌推荐
                            if(!$num && isset($brand_select_list[$key]))
                            {
                                foreach ($brand_top_data[$key2][$brand_select_list[$key]] as $value)
                                {
                                    if(in_array($value['pid'], $tmp_pids2))
                                    {
                                        $num++;
                                        if($num >= 3)
                                        {
                                            break;
                                        }else{
                                            continue;
                                        }
                                    }
                                    $product = $product_list[$value['pid']];
                                    if(empty($product))
                                    {
                                        continue;
                                    }
                                    if(!in_array($product['product']['pid'], $displayProductList)) {
                                        $responseData['categoryList'][$key - 1]['list'][$tmp_key]['list'][] = array(
                                            'pid' => $product['product']['pid'],
                                            'title' => $product['sku']['title'],
                                            'alias' => $product['sku']['alias'],
                                            'sale_price' => (string)round(($lowPrice[$value['pid']]['sale_price'] ? $lowPrice[$value['pid']]['sale_price'] : $product['product']['price'])/100,2),
                                            'unit' => $product['sku']['unit'],
                                            'image' => $product['sku']['_pic']['small'],
                                            'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                                            'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
                                            'num' => $mapByProducts[$product['product']['pid']]['num'],
                                        );
                                    }
                                    $tmp_pids2[] = $value;
                                    $num++;
                                    if($num >= 3)
                                    {
                                        break;
                                    }
                                }
                            }
                            if(!$num)
                            {
                                foreach($top_data[$key2] as $value)
                                {
                                    if(in_array($value['pid'], $tmp_pids2))
                                    {
                                        $num++;
                                        if($num >= 3)
                                        {
                                            break;
                                        }else{
                                            continue;
                                        }
                                    }
                                    $product = $product_list[$value['pid']];
                                    if(empty($product))
                                    {
                                        continue;
                                    }
                                    if(!in_array($product['product']['pid'], $displayProductList)) {
                                        $responseData['categoryList'][$key - 1]['list'][$tmp_key]['list'][] = array(
                                            'pid' => $product['product']['pid'],
                                            'title' => $product['sku']['title'],
                                            'alias' => $product['sku']['alias'],
                                            'sale_price' => (string)round(($lowPrice[$value['pid']]['sale_price'] ? $lowPrice[$value['pid']]['sale_price'] : $product['product']['price'])/100,2),
                                            'unit' => $product['sku']['unit'],
                                            'image' => $product['sku']['_pic']['small'],
                                            'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                                            'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
                                            'num' => $mapByProducts[$product['product']['pid']]['num'],
                                        );
                                    }
                                    $tmp_pids2[] = $value;
                                    $num++;
                                    if($num >= 3)
                                    {
                                        break;
                                    }
                                }
                            }

                        }
                        $tmp_key++;
                    }
                }
            }
        }else{
            $responseData['categoryList'] = self::_defaultRecommendProduct($uid, $city_id, $platForm, $brandList);;
            $responseData['defaultCate1'] = 1;
        }
        return $responseData;
    }

    /**
     * 获取多维数组指定下标的值
     * @param array $array
     * @param $search
     * @param string $mode
     * @return array
     */
    private function _searchMultiArray(array $array, $search, $mode = 'key') {
        $res = array();
        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $key => $value) {
            if ($search === ${${"mode"}}){
                if($mode == 'key'){
                    $res[] = $value;
                }else{
                    $res[] = $key;
                }
            }
        }
        return $res;
    }

    /**
     * 二级分类排序
     */
    private function _getCate2ByFrequency($cate2List, $tmpCate2List)
    {
        $cate1List = Conf_Sku::$CATE1;
        $arrData = array();
        foreach ($cate1List as $key => $item)
        {
            if(empty($tmpCate2List[$key]))
            {
                $arrData[$key] = $cate2List[$key];
            }else{
                arsort($tmpCate2List[$key]);
                foreach ($tmpCate2List[$key] as $key2 => $item2)
                {
                    $arrData[$key][$key2] = $cate2List[$key][$key2];
                }
                $cate2_ids = array_keys($tmpCate2List[$key]);
                foreach ($cate2List[$key] as $key2 => $item2)
                {
                    if(!in_array($key2, $cate2_ids))
                    {
                        $arrData[$key][$key2] = $item2;
                    }
                }
            }
        }
        return $arrData;
    }

    /**
     * 获取默认推荐材料
     * @param $responseData
     * @param $uid
     * @param $city_id
     * @param $platForm
     * @param $brandList
     */
    private function _defaultRecommendProduct($uid, $city_id, $platForm, $brandList = '')
    {
        $categoryList = array();
        $cate1List = Conf_Sku::$CATE1;
        $cate2List = Conf_Sku::$CATE2;
        $cate3List = Conf_Sku::$CATE3;
        $top_data = Shop_Api::getTopCategroyProduct($city_id);//分类top10
        $brand_top_data = Shop_Api::getTopCategoryBrandProduct($city_id);//品牌top10
        $brand_select_list = array();//选中的品牌
        $cart_list = array();//购物车
        $cart_pids = array();
        $displayProductList = array();
        $lowPrice = Shop_Api::getLowestPrice($city_id, $platForm);//活动价
        $cartProudctList = Cart_Api::getUserCart($uid, $city_id);
        //获取主材、辅材分类关系
        $cate_relation_list = Conf_Order::getCateRelationOfRecommend();
        if(!empty($cartProudctList))
        {
            $cart_pids = Tool_Array::getFields($cartProudctList, 'pid');
        }
        if($uid > 0)
        {
            //获取客户不显示的商品
            $displayProductList = Crm2_Api::getDeleteCustomerProductRelationByUid($uid,0,0);
            $recommend = Crm2_Api::getCustomerRecommendBrandByUid($uid, $city_id);
            if(!empty($recommend) && $recommend['value'] != '1')
            {
                $recommend = json_decode($recommend['value'], true);
                $bid_arr = Tool_Array::list2Map($recommend, 'categoryId');
                foreach ($bid_arr as $cate1 => $brandList)
                {
                    $brand_select_list[$cate1] = $brandList['brand'][0];
                }
            }
        }else{
            if(!empty($brandList))
            {
                $recommend = json_decode($brandList, true);
                foreach ($recommend as $item)
                {
                    $brand_select_list[$item['categoryId']] = $item['brand'][0];
                }
            }
        }
        $pids = self::_searchMultiArray($top_data, 'pid');
        $pids2 = self::_searchMultiArray($brand_top_data, 'pid');
        $pids = array_merge($pids, $pids2, $cart_pids);
        $product_list = Shop_Api::getProductInfos($pids);//获取商品详情
        if(!empty($cart_pids))
        {
            foreach ($cart_pids as $cart_pid) {
                $product = $product_list[$cart_pid];
                if($product['sku']['cate3']>0)
                {
                    $cart_list[$product['sku']['cate3']][] = $cart_pid;
                }else{
                    $cart_list[$product['sku']['cate2']][] = $cart_pid;
                }
            }
        }

        $mapByProducts = array();
        if(!empty($cartProudctList))
        {
            $mapByProducts = Tool_Array::list2Map($cartProudctList, 'pid');
        }
        foreach ($cate1List as $key => $item){
            $categoryList[$key-1] = array(
                'cate1' => $key,
                'name' => $item['name'],
                'list' => array()
            );
            $tmp_key = 0;
            foreach ($cate2List[$key] as $key2 => $item2)
            {
//                $brandList = self::_getBrandListByCate2($key2);
//                $data_brands = array();
//                foreach ($brandList as $value)
//                {
//                    $data_brands[] = array(
//                        'bid' => $value['bid'],
//                        'name' => $value['name'],
//                        'is_select' => array_rand(array(0,1),1),
//                    );
//                }
                $categoryList[$key-1]['list'][$tmp_key] = array(
                    'cate2' => $key2,
                    'name' => $item2['name'],
//                    'brands' => $data_brands,
//                    'relation_cate_name' => isset($cate_relation_list[$key2]) ? $cate2List[$key][$cate_relation_list[$key2]]['name'] : '',
                    'list' => array()
                );
                if(isset($cate3List[$key2]))
                {
                    foreach ($cate3List[$key2] as $key3 => $item3)
                    {
                        $num = 0;
                        $tmp_pids3 = array();
                        if(isset($cart_list[$key3]))
                        {
                            foreach($cart_list[$key3] as $pid)
                            {
                                $product = $product_list[$pid];
                                $categoryList[$key-1]['list'][$tmp_key]['list'][] = array(
                                    'pid' => $product['product']['pid'],
                                    'title' => $product['sku']['title'],
                                    'alias' => $product['sku']['alias'],
                                    'sale_price' => (string)round(($lowPrice[$pid]['sale_price'] ? $lowPrice[$pid]['sale_price'] : $product['product']['price'])/100,2),
                                    'unit' => $product['sku']['unit'],
                                    'image' => $product['sku']['_pic']['small'],
                                    'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                                    'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
                                    'num' => $mapByProducts[$product['product']['pid']]['num'],
                                );
                                $tmp_pids3[] = $pid;
                            }
                        }
                        if(isset($brand_select_list[$key]))
                        {
                            foreach ($brand_top_data[$key3][$brand_select_list[$key]] as $value)
                            {
                                if(in_array($value['pid'], $tmp_pids3))
                                {
                                    $num++;
                                    if($num >= 2)
                                    {
                                        break;
                                    }else{
                                        continue;
                                    }
                                }
                                $product = $product_list[$value['pid']];
                                if(!in_array($product['product']['pid'], $displayProductList)) {
                                    $categoryList[$key - 1]['list'][$tmp_key]['list'][] = array(
                                        'pid' => $product['product']['pid'],
                                        'title' => $product['sku']['title'],
                                        'alias' => $product['sku']['alias'],
                                        'sale_price' => (string)round(($lowPrice[$value['pid']]['sale_price'] ? $lowPrice[$value['pid']]['sale_price'] : $product['product']['price'])/100,2),
                                        'unit' => $product['sku']['unit'],
                                        'image' => $product['sku']['_pic']['small'],
                                        'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                                        'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
                                        'num' => $mapByProducts[$product['product']['pid']]['num'],
                                    );
                                }
                                $num++;
                                if($num >= 2)
                                {
                                    break;
                                }
                            }
                        }
                        if($num == 0)
                        {
                            foreach($top_data[$key3] as $value)
                            {
                                if(in_array($value['pid'], $tmp_pids3))
                                {
                                    $num++;
                                    if($num >= 2)
                                    {
                                        break;
                                    }else{
                                        continue;
                                    }
                                }
                                $product = $product_list[$value['pid']];
                                if(!in_array($product['product']['pid'], $displayProductList)) {
                                    $categoryList[$key - 1]['list'][$tmp_key]['list'][] = array(
                                        'pid' => $product['product']['pid'],
                                        'title' => $product['sku']['title'],
                                        'alias' => $product['sku']['alias'],
                                        'sale_price' => (string)round(($lowPrice[$value['pid']]['sale_price'] ? $lowPrice[$value['pid']]['sale_price'] : $product['product']['price'])/100,2),
                                        'unit' => $product['sku']['unit'],
                                        'image' => $product['sku']['_pic']['small'],
                                        'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                                        'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
                                        'num' => $mapByProducts[$product['product']['pid']]['num'],
                                    );
                                }
                                $num++;
                                if($num >= 2)
                                {
                                    break;
                                }
                            }
                        }
                    }
                }else{
                    $num = 0;
                    $tmp_pids2 = array();
                    if(isset($cart_list[$key2]))
                    {
                        foreach($cart_list[$key2] as $pid)
                        {
                            $product = $product_list[$pid];
                            $categoryList[$key-1]['list'][$tmp_key]['list'][] = array(
                                'pid' => $product['product']['pid'],
                                'title' => $product['sku']['title'],
                                'alias' => $product['sku']['alias'],
                                'sale_price' => (string)round(($lowPrice[$pid]['sale_price'] ? $lowPrice[$pid]['sale_price'] : $product['product']['price'])/100,2),
                                'unit' => $product['sku']['unit'],
                                'image' => $product['sku']['_pic']['small'],
                                'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                                'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
                                'num' => $mapByProducts[$product['product']['pid']]['num'],
                            );
                            $tmp_pids2[] = $pid;
                        }
                    }
                    if(isset($brand_select_list[$key]))
                    {
                        foreach ($brand_top_data[$key2][$brand_select_list[$key]] as $value)
                        {
                            if(in_array($value['pid'], $tmp_pids2))
                            {
                                $num++;
                                if($num >= 3)
                                {
                                    break;
                                }else{
                                    continue;
                                }
                            }
                            $product = $product_list[$value['pid']];
                            if(!in_array($product['product']['pid'], $displayProductList)) {
                                $categoryList[$key - 1]['list'][$tmp_key]['list'][] = array(
                                    'pid' => $product['product']['pid'],
                                    'title' => $product['sku']['title'],
                                    'alias' => $product['sku']['alias'],
                                    'sale_price' => (string)round(($lowPrice[$value['pid']]['sale_price'] ? $lowPrice[$value['pid']]['sale_price'] : $product['product']['price'])/100,2),
                                    'unit' => $product['sku']['unit'],
                                    'image' => $product['sku']['_pic']['small'],
                                    'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                                    'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
                                    'num' => $mapByProducts[$product['product']['pid']]['num'],
                                );
                                $tmp_pids2[] = $value['pid'];
                            }
                            $num++;
                            if($num >= 3)
                            {
                                break;
                            }
                        }
                    }
                    if($num == 0){
                        foreach($top_data[$key2] as $value)
                        {
                            if(in_array($value['pid'], $tmp_pids2))
                            {
                                $num++;
                                if($num >= 3)
                                {
                                    break;
                                }else{
                                    continue;
                                }
                            }
                            $product = $product_list[$value['pid']];
                            if(!in_array($product['product']['pid'], $displayProductList)) {
                                $categoryList[$key - 1]['list'][$tmp_key]['list'][] = array(
                                    'pid' => $product['product']['pid'],
                                    'title' => $product['sku']['title'],
                                    'alias' => $product['sku']['alias'],
                                    'sale_price' => (string)round(($lowPrice[$value['pid']]['sale_price'] ? $lowPrice[$value['pid']]['sale_price'] : $product['product']['price'])/100,2),
                                    'unit' => $product['sku']['unit'],
                                    'image' => $product['sku']['_pic']['small'],
                                    'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                                    'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
                                    'num' => $mapByProducts[$product['product']['pid']]['num'],
                                );
                            }
                            $num++;
                            if($num >= 3)
                            {
                                break;
                            }
                        }
                    }

                }
                $tmp_key++;
            }
        }

        return $categoryList;
    }

    /**
     * 获取用户二级分类下材料
     * @param $cate2
     * @param $uid
     * @param $city_id
     * @param $platForm
     * @param $conf
     * @param int $start
     * @param int $page_num
     * @param string $brandList
     * @return mixed
     */
    public static function getRecommendProductList($cate2, $uid, $city_id, $platForm, $conf, $start = 0, $page_num = 20, $brandSelectList = '')
    {
        $wareList = array();
        $responseData['has_more'] = false;
        //获取品牌
        $brand_list = self::_getBrandListByCate2($cate2, $city_id);
        //默认显示推荐品牌
        if(empty($conf['bid']))
        {
            $conf['bid'] = Crm2_Api::getCustomerDefaultRecommendBrandByUid($uid, $cate2, $city_id, $brand_list, $brandSelectList);
        }

        //获取型号
        $tmp_model_list = Shop_Api::getModelList($cate2);
        if (!empty($tmp_model_list) && $cate2 > 0 && $conf['bid'] > 0)
        {
            $bidModels = Shop_Api::getMidByCate2AndBid($cate2, $conf['bid']);
            foreach ($tmp_model_list as $k => $info)
            {
                if (!in_array($info['mid'], $bidModels))
                {
                    unset($tmp_model_list[$k]);
                }
            }
        }
        $conf['mid'] = isset($conf['mid']) ? $conf['mid'] : 0;
        $searchConf = array(
            'cate2' => $cate2,
            'bid' => $conf['bid'],
            'mid' => $conf['mid'],
            'city_id' => $city_id
        );
        if (!empty($brand_list))
        {
            $bids = Shop_Api::getBidsHasProducts($searchConf);
            foreach ($brand_list as $k => $brand)
            {
                if (!in_array($brand['bid'], $bids))
                {
                    unset($brand_list[$k]);
                }
            }
        }

        $lowPrice = Shop_Api::getLowestPrice($city_id, $platForm);//活动价
        foreach ($brand_list as $brand)
        {
            $responseData['brand'][] = array(
                'bid' => $brand['bid'],
                'name' => $brand['name']
            );
        }

        //获取类型
        $cate3_list = Conf_Sku::$CATE3[$cate2];
        foreach ($cate3_list as $key => &$cate3)
        {
            $cate3['id'] = $key;
        }
        $cate3_list = Tool_Array::sortByField($cate3_list, 'sortby');
        if(!empty($cate3_list) && empty($conf['cate3']))
        {
            $tmp_cate = current($cate3_list);
            $conf['cate3'] = $tmp_cate['id'];
        }
        if(isset($cate3_list))
        {
            foreach ($cate3_list as $cate)
            {
                $responseData['subCateList'][] = array(
                    'subCateId' => $cate['id'],
                    'name' => $cate['name']
                );
            }
            $responseData['defaultSubCateId'] = $conf['cate3'];
        }
        $p_total = self::_getProductTotalBySearch($conf);
        if($uid > 0)
        {
            $u_total = Crm2_Api::getCustomerProductRelationTotalByUid($uid, $conf);
            if($u_total >0)
            {
                $cartProudctList = Cart_Api::getUserCart($uid, $city_id);
                $mapByProducts = array();
                if(!empty($cartProudctList))
                {
                    $mapByProducts = Tool_Array::list2Map($cartProudctList, 'pid');
                }
                //获取客户购买历史
                $productHistoryList = Crm2_Api::getCustomerProductRelationByUid($uid, $city_id, $start, $page_num, $conf);
                //判断客户购买商品总数是否比请求的数大
                if(($start+$page_num) <= $u_total)
                {
                    $pids = Tool_Array::getFields($productHistoryList, 'pid');
                    $productList = Shop_Api::getProductInfos($pids);
                    foreach ($productHistoryList as $item)
                    {
                        $product = $productList[$item['pid']];
                        $wareList[] = array(
                            'pid' => $product['product']['pid'],
                            'title' => $product['sku']['title'],
                            'alias' => $product['sku']['alias'],
                            'sale_price' => (string)round(($lowPrice[$item['pid']]['sale_price'] ? $lowPrice[$item['pid']]['sale_price'] : $product['product']['price'])/100,2),
                            'unit' => $product['sku']['unit'],
                            'image' => $product['sku']['_pic']['small'],
                            'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                            'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
                            'num' => $mapByProducts[$product['product']['pid']]['num'],
                        );
                    }
                    $responseData['has_more'] = true;
                }else{
                    //如果当前请求数比购买大又比分页数小,合并
                    if(($start+$page_num) > $u_total && ($start+$page_num-$u_total) < $page_num)
                    {
                        $pids = Tool_Array::getFields($productHistoryList, 'pid');
                        $productList = Shop_Api::getProductInfos($pids);
                        foreach ($productHistoryList as $item)
                        {
                            $product = $productList[$item['pid']];
                            $wareList[] = array(
                                'pid' => $product['product']['pid'],
                                'title' => $product['sku']['title'],
                                'alias' => $product['sku']['alias'],
                                'sale_price' => (string)round(($lowPrice[$item['pid']]['sale_price'] ? $lowPrice[$item['pid']]['sale_price'] : $product['product']['price'])/100,2),
                                'unit' => $product['sku']['unit'],
                                'image' => $product['sku']['_pic']['small'],
                                'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                                'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
                                'num' => $mapByProducts[$product['product']['pid']]['num'],
                            );
                        }
                        $num = $page_num - $u_total + $start;
                        $tmp_product_list = self::_getProductListBySearch($uid, $city_id, $conf, 0, $num, $lowPrice);
                        $wareList = array_merge($wareList,$tmp_product_list);
                    }else{
                        $start = $start - $u_total;
                        $wareList = self::_getProductListBySearch($uid, $city_id, $conf, $start, $page_num, $lowPrice);
                    }
                    if(($start+$page_num) < $p_total)
                    {
                        $responseData['has_more'] = true;
                    }
                }
            }else{
                $wareList = self::_getProductListBySearch($uid, $city_id, $conf, $start, $page_num, $lowPrice);
                if(($start+$page_num) < $p_total)
                {
                    $responseData['has_more'] = true;
                }
            }
        }else{
            $wareList = self::_getProductListBySearch($uid, $city_id, $conf, $start, $page_num, $lowPrice);
            if(($start+$page_num) < $p_total)
            {
                $responseData['has_more'] = true;
            }
        }
        $responseData['defaultBrand'] = $conf['bid'];
        $responseData['defaultModel'] = $conf['mid'];
        $responseData['wareList'] = $wareList;

        $mids = Tool_Array::getFields($tmp_model_list, 'mid');
        $model_list = Shop_Api::getModelsById($mids);
        $responseData['model'][] = array(
            'mid' => 0,
            'name' => '全部',
        );//默认全部
        foreach ($model_list as $model)
        {
            $responseData['model'][] = array(
                'mid' => $model['mid'],
                'name' => $model['name']
            );
        }

        return $responseData;
    }

    /**
     * 获取二级分类下品牌列表
     */
    private function _getBrandListByCate2($cate2_id, $city_id)
    {
        $brand_list = Shop_Api::getBrandList($cate2_id);
        if (!empty($brand_list))
        {
            $bids = Shop_Api::getBidsHasProducts(array('cate2' => $cate2_id, 'city_id' => $city_id));
            foreach ($brand_list as $k => $brand)
            {
                if (!in_array($brand['bid'], $bids))
                {
                    unset($brand_list[$k]);
                }
            }
        }
        return $brand_list;
    }

    /**
     * 根据条件获取材料列表
     * @param $uid
     * @param $city_id
     * @param $conf
     * @param int $start
     * @param int $num
     * @param array $lowPrice
     * @return array
     */
    private function _getProductListBySearch($uid, $city_id, $conf, $start=0, $num=20, $lowPrice = array())
    {
        $oneDao = Data_One::getInstance();
        $where = 'tp.status='.Conf_Base::STATUS_NORMAL;
        if(!empty($conf))
        {
            if(!empty($conf['bid']))
            {
                $where .= sprintf(' AND ts.bid=%d', $conf['bid']);
            }
            if(!empty($conf['city_id']))
            {
                $where .= sprintf(' AND tp.city_id=%d', $conf['city_id']);
            }
            if(!empty($conf['cate2']))
            {
                $where .= sprintf(' AND ts.cate2=%d', $conf['cate2']);
            }
            if(!empty($conf['cate3']))
            {
                $where .= sprintf(' AND ts.cate3=%d', $conf['cate3']);
            }
            if(!empty($conf['mid']))
            {
                $where .= sprintf(' AND FIND_IN_SET(%d,ts.mids)', $conf['mid']);
            }
        }
        if($uid > 0)
        {
            $where .= sprintf(' AND tp.pid NOT IN(SELECT pid FROM t_user_product_relation WHERE uid=%d and status=%d)', $uid, Conf_Base::STATUS_NORMAL);
        }
        $ret = $oneDao->setDBMode()->select('t_product AS tp LEFT JOIN t_sku AS ts ON tp.sid=ts.sid', array('tp.pid'), $where, 'ORDER BY tp.frequency DESC,tp.pid ASC', $start, $num);
        $productList = (array)$ret['data'];
        if(!empty($productList))
        {
            $cartProudctList = Cart_Api::getUserCart($uid, $city_id);
            $mapByProducts = array();
            if(!empty($cartProudctList))
            {
                $mapByProducts = Tool_Array::list2Map($cartProudctList, 'pid');
            }

            $pids = Tool_Array::getFields($productList, 'pid');
            $tmp_product_list = Shop_Api::getProductInfos($pids);
            $tmp_product_list2 = $productList;
            $productList = array();
            foreach($tmp_product_list2 as $value)
            {
                $product = $tmp_product_list[$value['pid']];
                $productList[] = array(
                    'pid' => $product['product']['pid'],
                    'title' => $product['sku']['title'],
                    'alias' => $product['sku']['alias'],
                    'sale_price' => (string)round(($lowPrice[$value['pid']]['sale_price'] ? $lowPrice[$value['pid']]['sale_price'] : $product['product']['price'])/100,2),
                    'unit' => $product['sku']['unit'],
                    'image' => $product['sku']['_pic']['small'],
                    'url' => 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $product['product']['pid'] . '&city_id=' . $city_id,
                    'icon' => $product['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($product['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
                    'num' => $mapByProducts[$product['product']['pid']]['num'],
                );
            }
        }
        return $productList;
    }

    /**
     * 根据条件获取材料总数
     * @param $conf
     * @return mixed
     */
    private function _getProductTotalBySearch($conf)
    {
        $oneDao = Data_One::getInstance();
        $where = 'tp.status='.Conf_Base::STATUS_NORMAL;
        if(!empty($conf))
        {
            if(!empty($conf['bid']))
            {
                $where .= sprintf(' AND ts.bid=%d', $conf['bid']);
            }
            if(!empty($conf['city_id']))
            {
                $where .= sprintf(' AND tp.city_id=%d', $conf['city_id']);
            }
            if(!empty($conf['cate2']))
            {
                $where .= sprintf(' AND ts.cate2=%d', $conf['cate2']);
            }
            if(!empty($conf['cate3']))
            {
                $where .= sprintf(' AND ts.cate3=%d', $conf['cate3']);
            }
            if(!empty($conf['mid']))
            {
                $where .= sprintf(' AND FIND_IN_SET(%d,ts.mids)', $conf['mid']);
            }
        }
        $ret = $oneDao->setDBMode()->select('t_product AS tp LEFT JOIN t_sku AS ts ON tp.sid=ts.sid', array('count(*) as total'), $where, 'ORDER BY tp.frequency DESC');
        return $ret['data'][0]['total'];
    }

}
