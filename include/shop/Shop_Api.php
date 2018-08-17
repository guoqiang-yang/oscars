<?php

/**
 * 商店相关接口
 */
class Shop_Api extends Base_Api
{
    /**
     * 添加sku
     *
     * @param $info
     *
     * @return mixed
     */
    public static function addSku($info)
    {
        $sku = new Shop_Sku();

        return $sku->add($info);
    }

    /**
     * 添加商品
     * 参数有：sid，cost，price，status，sortby，carrier_fee，carrier_fee_ele，city_id
     *
     * @param       $sid
     * @param array $info
     *
     * @return array
     */
    public static function addProduct($sid, array $info)
    {
        $sku = new Shop_Sku();
        $skuInfo = $sku->get($sid);
        assert(!empty($skuInfo));

        $info['sid'] = $sid;
        $sp = new Shop_Product();
        $pid = $sp->add($info);

        return $pid;
    }

    /**
     * 更新sku信息
     *
     * @param       $sid
     * @param array $info
     *
     * @return boolean
     */
    public static function updateSku($sid, array $info)
    {
        $sku = new Shop_Sku();

        return $sku->update($sid, $info);
    }

    /**
     * 更新商品信息
     *
     * @param       $pid
     * @param array $info
     *
     * @return bool
     */
    public static function updateProduct($pid, array $info)
    {
        $sp = new Shop_Product();

        return $sp->update($pid, $info);
    }

    /**
     * 删除商品，目前不提供删除功能，只提供下架
     * 即使有，删除商品也不能删除sku了，因为sku和商品是一对多的关系
     *
     * @param $pid
     *
     * @throws Exception
     */
    public static function deleteProduct($pid)
    {
        $sp = new Shop_Product();

        $sp->delete($pid);
    }

    /**
     * 获取sku信息
     *
     * @param $sid
     * @param $withRelSkuInfo
     *
     * @return array
     */
    public static function getSkuInfo($sid, $withRelSkuInfo = FALSE)
    {
        $sp = new Shop_Sku();
        $skuInfo = $sp->get($sid);
        // 格式化图片信息
        Shop_Helper::formatPic($skuInfo, 'pic_ids', $withRelSkuInfo);

        return $skuInfo;
    }

    /**
     * 批量获取sku信息.
     *
     * @param $sids
     *
     * @return array
     */
    public static function getSkuInfos($sids)
    {
        $sp = new Shop_Sku();
        $skuInfos = $sp->getBulk($sids);

        foreach ($skuInfos as &$skuInfo)
        {
            Shop_Helper::formatPic($skuInfo);
        }

        return $skuInfos;
    }

    /**
     * 获取商品的成本.
     *
     * @param $sids
     * @param $wid
     *
     * @return array
     */
    public static function getCostBySids($sids, $wid)
    {
        $costs = array();
        if (empty($sids) || empty($wid))
            return FALSE;

        // 初始化
        foreach ($sids as $_sid)
        {
            $costs[$_sid] = 0;
        }

        //先从t_stock表获取
        $ws = new Warehouse_Stock();
        $stocks = $ws->getBulk($wid, $sids, array('sid', 'cost'));

        $waitDealSids = array();
        foreach ($stocks as $item)
        {
            if ($item['cost'] == 0)
            {
                $waitDealSids[] = $item['sid'];
            }
            else
            {
                $costs[$item['sid']] = $item['cost'];
            }
        }

        if (!empty($waitDealSids))
        {
            $sp = new Shop_Product();
            $pwhere = sprintf('sid in (%s) and city_id=%d', implode(',', $waitDealSids), Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$wid]);

            $products = Tool_Array::list2Map($sp->getListByWhere($pwhere), 'sid');

            foreach ($waitDealSids as $_sid)
            {
                $costs[$_sid] = isset($products[$_sid]) ? $products[$_sid]['cost'] : 0;
            }
        }

        return $costs;
    }

    /**
     * 获取商品信息
     *
     * @param $pid
     *
     * @return array
     */
    public static function getProductInfo($pid)
    {
        // 获取product信息
        $sp = new Shop_Product();
        $product = $sp->get($pid);

        // 获取sku信息
        $sid = $product['sid'];
        $sp = new Shop_Sku();
        $skuInfo = $sp->get($sid);
        // 格式化图片信息
        Shop_Helper::formatPic($skuInfo);
        if ($skuInfo['detail_pic_ids'] != '')
        {
            $skuInfo['_detail_pic'] = Oss_Api::getImageUrl($skuInfo['detail_pic_ids']);
        }
        else
        {
            $skuInfo['_detail_pic'] = '';
        }

        $bigPics = array();
        if (!empty($skuInfo['_pics']))
        {
            foreach ($skuInfo['_pics'] as $pic)
            {
                $bigPics[] = $pic['big'];
            }
        }
        $skuInfo['pics_json'] = json_encode($bigPics);

        $recommand = array();
        if (!empty($product['recommend_pids']))
        {
            $pids = explode(',', $product['recommend_pids']);
            $recommand = self::getProductInfos($pids);
        }

        return array(
            'product' => $product,
            'sku' => $skuInfo,
            'recommand' => $recommand,
        );
    }
    
    /**
     * 获取工人的基础搬运费.
     * 
     * @rule
     *      - 如果worker_ca_fee*没有数据，使用carray_fee*数据
     *      - 特殊梳理：电梯+板材截断 使用carray_fee [临时方案]
     * @param type $productInfo
     * @param type $orderProduct
     */
    public static function setBaseCarryFee4Worker(&$productInfo, $orderProduct)
    {
        if ($orderProduct['pid'] != $productInfo['pid']) return;
        
        $isTruncation = (strpos($orderProduct['note'], '截')!==false ||strpos($orderProduct['note'], '断')!==false ||strpos($orderProduct['note'], '裁')!==false) 
                         && strpos($orderProduct['note'], '不')===false? true: false;
        
        $cmpTime = !empty($orderProduct['ctime'])&&$orderProduct['ctime']!='0000-00-00 00:00:00'?
                        substr($orderProduct['ctime'], 0, 10): date('Y-m-d');
        
        if ($cmpTime >= '2017-12-01')
        {
            $productInfo['worker_ca_fee'] = !empty($productInfo['worker_ca_fee'])? $productInfo['worker_ca_fee']: $productInfo['carrier_fee'];
            $productInfo['worker_ca_fee_ele'] = (empty($productInfo['worker_ca_fee_ele'])||!$isTruncation)? 
                                                    $productInfo['carrier_fee_ele']: $productInfo['worker_ca_fee_ele'];
        }
        else
        {
            $productInfo['worker_ca_fee'] = $productInfo['carrier_fee'];
            $productInfo['worker_ca_fee_ele'] = $productInfo['carrier_fee_ele'];
        }
    }

    /**
     * 获取组合商品的信息.
     *
     * @param int $sid
     *
     * @return array
     */
    public static function getCombinSkuInfos($sid)
    {
        $ss = new Shop_Sku();
        $skuInfos = $ss->get($sid);

        if (empty($skuInfos) || empty($skuInfos['rel_sku']))
        {
            return array();
        }

        $skuInfos['_rel_sku'] = Shop_Helper::parseRelationSkus($skuInfos['rel_sku']);

        $partsSids = Tool_Array::getFields($skuInfos['_rel_sku'], 'sid');
        $partsSkuInfo = $ss->getBulk($partsSids);

        return array('combin' => array($sid => $skuInfos), 'parts' => $partsSkuInfo);
    }

    /**
     * 获取指定分类2下的品牌
     *
     * @param $cate2
     *
     * @return array 品牌列表.(每个品牌信息包含品牌名称和品牌对应的型号)
     */
    public static function getBrandList($cate2)
    {
        $sb = new Shop_Brand();

        return $sb->getListOfCate($cate2);
    }

    /**
     * 获取指定分类2下的正常品牌
     * @param $cate2
     *
     * @return array
     */
    public static function getNormalList($cate2)
    {
        $sb = new Shop_Brand();

        return $sb->getNormalListOfCate($cate2);
    }

    /**
     * 查询cate1下的所有商品.
     * @param $cate1
     *
     * @return array
     */
    public static function getBrandListByCate1($cate1)
    {
        $cate2s = array_keys(Conf_Sku::$CATE2[$cate1]);

        $sb = new Shop_Brand();

        $field = array('bid', 'name');
        $where = sprintf('status=0 and bid in (select bid from t_cate_brand where cate2 in (%s))', implode(',', $cate2s));

        $ret = $sb->getBrandByWhere($where, $field, 0, 0);

        return $ret;
    }

    /**
     * 获取品牌信息
     *
     * @param $bid
     *
     * @return array
     */
    public static function getBrand($bid)
    {
        $sb = new Shop_Brand();

        return $sb->get($bid);
    }

    /**
     * 获取多个品牌信息
     *
     * @param $bids
     *
     * @return array
     */
    public static function getBrandByIds($bids)
    {
        $sb = new Shop_Brand();

        return $sb->getBulk($bids);
    }

    /**
     * 添加品牌
     *
     * @param $name
     * @param $cate2
     *
     * @return mixed
     */
    public static function addBrand($name, $cate2)
    {
        $sm = new Shop_Brand();

        return $sm->add($name, $cate2);
    }

    /**
     * 删除品牌
     *
     * @param $bid
     * @param $cate2
     *
     * @return bool
     */
    public static function deleteBrand($bid, $cate2)
    {
        $sm = new Shop_Brand();
        $sm->delete($bid, $cate2);

        return TRUE;
    }

    /**
     * 更新品牌信息（只能更新对应的型号）
     *
     * @param       $bid
     * @param array $info
     *
     * @return bool
     */
    public static function updateBrand($bid, array $info)
    {
        $sb = new Shop_Brand();
        $sb->update($bid, $info);

        return TRUE;
    }

    public static function updateCateBrand($bid, array $info)
    {
        $sb = new Shop_Brand();
        $sb->updateCate($bid, $info);

        return TRUE;
    }

    /**
     * 获取指定分类2的型号列表
     *
     * @param $cate2
     *
     * @return array
     */
    public static function getModelList($cate2)
    {
        $sm = new Shop_Model();

        return $sm->getListOfCate($cate2);
    }

    /**
     * 获取多个型号列表信息
     *
     * @param $mids
     *
     * @return array
     */
    public static function getModelsById($mids)
    {
        $sm = new Shop_Model();

        return $sm->getBulk($mids);
    }

    /**
     * 添加型号
     *
     * @param $info
     *
     * @return array
     */
    public static function addModel($info)
    {
        $sm = new Shop_Model();

        return $sm->add($info);
    }

    /**
     * 更新型号信息
     *
     * @param $mid
     * @param $info
     *
     * @return bool
     */
    public static function updateModel($mid, $info)
    {
        $sm = new Shop_Model();
        $sm->update($mid, $info);

        return TRUE;
    }

    /**
     * 删除指定型号
     *
     * @param $mid
     *
     * @return bool
     */
    public static function deleteModel($mid)
    {
        $sm = new Shop_Model();
        $sm->delete($mid);

        return TRUE;
    }

    /**
     * 保存图片
     *
     * @param        $name
     * @param        $content
     * @param        $width
     * @param        $height
     * @param string $path
     *
     * @return int
     */
    public static function savePic($name, $content, $width, $height, $path = '')
    {
        $sp = new Shop_Picture();
        $ret = $sp->savePic($name, $content, $width, $height, array(), $path);

        return $ret;
    }

    /**
     * 保存sku图片
     *
     * @param        $name
     * @param        $content
     * @param        $width
     * @param        $height
     * @param string $path
     *
     * @return int
     */
    public static function saveSkuPic($name, $content, $width, $height, $path = '')
    {
        $sp = new Shop_Picture();
        $ret = $sp->saveSkuPic($name, $content, $width, $height, array(), $path);

        return $ret;
    }

    /**
     * 根据分类2和品牌获取型号列表
     *
     * @param $cate2
     * @param $bid
     *
     * @return array
     */
    public static function getMidByCate2AndBid($cate2, $bid)
    {
        $ss = new Shop_Sku();

        return $ss->getMidByCate2AndBid($cate2, $bid);
    }

    /**
     * 获取所有品牌
     * @return array
     */
    public static function getAllBrands()
    {
        $sb = new Shop_Brand();

        return $sb->getAllBrands();
    }

    /**
     * 获取所有型号
     * @return array
     */
    public static function getAllModels()
    {
        $sm = new Shop_Model();

        return $sm->getAll();
    }

    /**
     * 获取所有sku
     *
     * @param array $fields
     *
     * @return array
     */
    public static function getAllSku($fields = array('*'))
    {
        $ss = new Shop_Sku();

        return $ss->getAll($fields);
    }

    /**
     * 获取app上商品详情的url
     *
     * @param $pid
     * @param $city_id
     *
     * @return string
     */
    public function getUrlFroApp($pid, $city_id)
    {
        $url = 'http://' . C_H5_WWW_HOST . '/product/app_detail.php?pid=' . $pid . '&city_id=' . $city_id;

        return $url;
    }

    /**
     * 获取一些sku在指定仓库里的成本
     *
     * @param $sids
     * @param $wid
     *
     * @return array
     */
    public static function getCostOfSkusWarehouse($wid, $sids)
    {
        $ss = new Shop_Sku();

        return $ss->getCostOfSkusWarehouse($wid, $sids);
    }

    /**
     * 是否虚拟商品
     *
     * @param $sid
     *
     * @return bool
     */
    public static function isVirtual($sid)
    {
        $ss = new Shop_Sku();

        return $ss->isVirtual($sid);
    }

    /**
     * 切换商品上下架状态
     *
     * @param $pid
     * @param $status
     *
     * @throws Exception
     */
    public static function switchProductOnlineStatus($pid, $status)
    {
        $product = new Shop_Product();
        $sku = new Shop_Sku();

        if ($status == Conf_Base::STATUS_NORMAL)
        {
            $productInfo = $product->get($pid);
            $sku = $sku->get($productInfo['sid']);
            if ($sku['status'] == Conf_Base::STATUS_NORMAL)
            {
                $arr = array('status' => $status);
                $product->update($pid, $arr);
            }
            else
            {
                throw new Exception('shop:sku status error');
            }
        }
        else if ($status == Conf_Base::STATUS_OFFLINE)
        {
            $arr = array('status' => $status);
            $product->update($pid, $arr);
        }
    }

    /**
     * sku的title是否存在
     *
     * @param $title
     *
     * @return int
     */
    public static function isSkuTitleDuplicate($title)
    {
        $ss = new Shop_Sku();
        $where = array('title' => $title);
        $list = $ss->getListByWhere($where, array('sid'));
        if (!empty($list))
        {
            $item = array_shift($list);

            return $item['sid'];
        }

        return 0;
    }

    /**
     * 是否为北京南库（3#）砂石砖商品.
     *
     * @notice 【谨慎使用】仅用于南库商品相关计算，（供应商结账，南库商品进出）
     *
     * @param $pid
     * @param $wid
     *
     * @return int
     */
    public static function isSandCementBrickForWid3Product($pid, $wid)
    {
        $wids = array(Conf_Warehouse::WID_3);
        return (in_array($wid, $wids) && in_array($pid, Conf_Order::$SAND_CEMENT_BRICK_PIDS)) ? 1 : 0;
    }

    /**
     * 通过skuinfo判断是否为砂石砖（非批量）
     *
     * @param $skuinfo
     *
     * @return bool
     */
    public static function isSandCementBrickBySkuinfo($skuinfo)
    {
        return (!empty($skuinfo) && isset($skuinfo['cate2']) && array_key_exists($skuinfo['cate2'], Conf_Sku::$SAND_CEMENT_BRICK_CATE2)) ? TRUE : FALSE;
    }

    /**
     * 通过pids判断是否为砂石砖类，（武汉地区板材类type为2）
     *
     * @param $pids
     *
     * @return array
     */
    public static function isSandCementBrickByPids($pids)
    {
        if (empty($pids) || !is_array($pids))
        {
            return array();
        }

        $fields = array(
            'tp.pid', 'ts.cate2', 'tp.city_id'
        );
        $where = 'ts.sid in (select sid from t_product where pid in (' . implode(',', $pids) . ')) and ts.sid=tp.sid';

        $ss = new Shop_Sku();
        $pinfos = Tool_Array::list2Map($ss->getListByPidSid($fields, $where, 0, 0), 'pid');

        return self::_isSandCementBrick($pids, $pinfos);
    }

    /**
     * 通过sids判断是否为砂石砖类.
     *
     * @param type $sids
     *
     * @return type
     */
    public static function isSandCementBrickBySids($sids)
    {
        if (empty($sids) || !is_array($sids))
        {
            return array();
        }

        $ss = new Shop_Sku();
        $fields = array(
            'sid', 'cate2'
        );
        $where = 'sid in (' . implode(',', $sids) . ')';
        $sinfos = Tool_Array::list2Map($ss->getListByWhere($where, $fields), 'sid');

        return self::_isSandCementBrick($sids, $sinfos);
    }

    /**
     * 检测指定的sid在指定的城市里是否有商品
     *
     * @param $sid
     * @param $cityId
     *
     * @return bool
     */
    public static function isProductExist($sid, $cityId)
    {
        $sp = new Shop_Product();

        $conf = array(
            'city_id' => $cityId, 'sid' => $sid
        );
        $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE;
        $sp->getList($conf, $total, 0, 1, $statusTag);

        return $total > 0;
    }

    /**
     * 关键字查询sku（使用mysql的like）
     *
     * @param     $keyword
     * @param int $start
     * @param int $num
     *
     * @return array
     */
    public static function searchSku($keyword, $start = 0, $num = 20)
    {
        // 查询sku
        $ss = new Shop_Sku();
        $total = 0;
        $list = $ss->search($keyword, $total, $start, $num);
        foreach ($list as &$item)
        {
            Shop_Helper::formatPic($item);
        }

        $hasMore = $total > $start + $num;

        return array(
            'list' => $list, 'total' => $total, 'has_more' => $hasMore
        );
    }

    /**
     * 根据城市，sids获取商品列表
     *
     * @param     $sids
     * @param int $cityId
     * @param     $statusTag
     *
     * @return array
     */
    public static function getProductsBySids($sids, $cityId = Conf_City::BEIJING, $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE)
    {
        if (empty($sids))
        {
            return array();
        }

        $sp = new Shop_Product();

        return $sp->getBySku($sids, $cityId, $statusTag);
    }

    /**
     * 后台sku列表
     *
     * @param     $conf
     * @param int $start
     * @param int $num
     * @param int $wid
     *
     * @return array
     */
    public static function getSkuList($conf, $start = 0, $num = 20, $wid = 0)
    {
        $sku = new Shop_Sku();
        $list = $sku->getList($conf, $total, $start, $num);
        if (!empty($list))
        {
            if ($wid > 0)
            {
                $sids = Tool_Array::getFields($list, 'sid');
                $ws = new Warehouse_Stock();
                $stocks = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');
            }

            foreach ($list as &$skuInfo)
            {
                Shop_Helper::formatPic($skuInfo);
            }
        }

        return array(
            'list' => $list, 'total' => $total
        );
    }

    /**
     * 获取多个商品信息
     *
     * @param array   $pids
     * @param int     $platForm
     * @param boolean $isShowAll
     *
     * @return array
     */
    public static function getProductInfos(array $pids, $platForm = Conf_Activity_Flash_Sale::PALTFORM_WECHAT, $isShowAll = FALSE, $cityId = 0, $cid=0, $reqFor='show')
    {
        $data = array();

        if (empty($pids))
        {
            return array();
        }

        // 获取product信息
        $sp = new Shop_Product();
        $products = $sp->getBulk($pids);
        // 获取sku信息
        $sids = Tool_Array::getFields($products, 'sid');
        $sp = new Shop_Sku();
        $skuInfos = $sp->getBulk($sids);
        // 拼装信息
        if ($cityId <= 0)
        {
            $info = City_Api::getCity();
            $cityId = $info['city_id'];
        }
        self::decoratorProducts($products,$cid,$cityId,$platForm,$reqFor);
        foreach ($products as $pid => $product)
        {
            if (!$isShowAll && $product['status'] != Conf_Base::STATUS_NORMAL)
            {
                unset($products[$pid]);
                continue;
            }
            $sid = $product['sid'];
            $skuInfo = $skuInfos[$sid];
            Shop_Helper::formatPic($skuInfo, 'pic_ids', true);

            $arr = array(
                'product' => $product, 'sku' => $skuInfo
            );
            $data[$pid] = $arr;
        }

        return $data;
    }

    public static function getProductInfosBySids(array $sids, $cityId = Conf_City::BEIJING, $platForm = Conf_Activity_Flash_Sale::PALTFORM_WECHAT, $online = TRUE)
    {
        $data = array();

        if (empty($sids))
        {
            return array();
        }

        // 获取product信息
        $sp = new Shop_Product();
        $products = $sp->getBySku($sids, $cityId, $online);
        if (empty($products))
        {
            return array();
        }
        // 获取sku信息
        $sids = Tool_Array::getFields($products, 'sid');
        $sp = new Shop_Sku();
        $skuInfos = $sp->getBulk($sids);
        // 拼装信息
        $info = City_Api::getCity();
        $salesList = Shop_Api::getLowestPrice($info['city_id'], $platForm);
        foreach ($products as $pid => $product)
        {
            $sid = $product['sid'];
            $skuInfo = $skuInfos[$sid];
            if (empty($skuInfo))
            {
                continue;
            }

            Shop_Helper::formatPic($skuInfo);
            $bigPics = array();
            if (!empty($skuInfo['_pics']))
            {
                foreach ($skuInfo['_pics'] as $pic)
                {
                    $bigPics[] = $pic['big'];
                }
            }
            $skuInfo['pics_json'] = json_encode($bigPics);

            if (!empty($salesList[$pid]) && $salesList[$pid]['sale_price'] < $product['price'] && $salesList[$pid]['end_time'] > time())
            {
                $product['sale_price'] = $salesList[$pid]['sale_price'];
            }

            $arr = array(
                'product' => $product, 'sku' => $skuInfo
            );
            $data[$pid] = $arr;
        }

        return $data;
    }

    /**
     * 获取商品列表
     *
     * @param     $conf
     * @param int $start
     * @param int $num
     * @param int $wid
     * @param int $statusTag
     *
     * @return array
     */
    public static function getProductList($conf, $start = 0, $num = 20, $wid = 0, $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE, $cid = 0)
    {
        $data = array();
        $sp = new Shop_Product();
        $ss = new Shop_Sku();
        $total = 0;

        $skuInfos = $ss->getList($conf, $t, 0, 0);
        if (empty($skuInfos))
        {
            return array(
                'list' => array(), 'total' => 0, 'has_more' => FALSE
            );
        }

        $sids = Tool_Array::getFields($skuInfos, 'sid');

        $pconf = array(
            'sid' => $sids, 'city_id' => $conf['city_id'], 'sales_type' => $conf['sales_type']
        );
        $products = $sp->getList($pconf, $total, $start, $num, $statusTag);
        $hasMore = $total > $start + $num;
        if ($total <= 0)
        {
            return array(
                'list' => array(), 'total' => 0, 'has_more' => FALSE
            );
        }

        if ($wid > 0)
        {
            $ws = new Warehouse_Stock();
            $stocks = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');
        }

        $info = City_Api::getCity();
        $salesList = Shop_Api::getLowestPrice($info['city_id'], Conf_Activity_Flash_Sale::PALTFORM_BOTH);

        //补充常买
        if ($start == 0 && $cid > 0)
        {
            $cityInfo = City_Api::getCity();
            $cityId = $cityInfo['city_id'];
            $oftenBuy = Crm2_Api::getFrequentlyBuyItem($cid, $cityId, $conf);
            if (!empty($oftenBuy))
            {
                foreach ($products as $pid => $product)
                {
                    if ($pid == $oftenBuy['pid'])
                    {
                        $oftenBuy = $product;
                        unset($products[$pid]);
                        break;
                    }
                }
                $oftenBuy['often_buy'] = 1;
                array_unshift($products, $oftenBuy);
            }
        }

        foreach ($products as $product)
        {
            $pid = $product['pid'];
            $sid = intval($product['sid']);
            if (!isset($skuInfos[$sid]))
            {
                unset($products[$pid]);
            }
            $product['cost'] = isset($stocks[$sid]) && !empty($stocks[$sid]['cost']) ? $stocks[$sid]['cost'] : $product['cost'];
            $product['cost'] = intval($product['cost']);
            $product['purchase_price'] = !empty($stocks) && array_key_exists($sid, $stocks) ? $stocks[$sid]['purchase_price'] : 0;

            Shop_Helper::formatPic($skuInfos[$sid]);
            $bigPics = array();
            if (!empty($skuInfos[$sid]['_pics']))
            {
                foreach ($skuInfos[$sid]['_pics'] as $pic)
                {
                    $bigPics[] = $pic['big'];
                }
            }

            if (!empty($salesList[$pid]) && $salesList[$pid]['sale_price'] < $product['price'] && $salesList[$pid]['end_time'] > time())
            {
                $product['sale_price'] = intval($salesList[$pid]['sale_price']);
            }

            $skuInfos[$sid]['pics_json'] = json_encode($bigPics);
            $data[$pid] = array(
                'product' => $product, 'sku' => $skuInfos[$sid],
            );
        }

        return array(
            'list' => $data, 'total' => $total, 'has_more' => $hasMore
        );
    }

    //未使用sphix
    public static function searchProduct($keyword, $start = 0, $num = 20, $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE, $wid = 0, $cityId=0)
    {
        $data = array();
        $ss = new Shop_Sku();
        $sp = new Shop_Product();
        $total = 0;
        $productsBySids = array();
        $productByPid = array();

        $list = $ss->search($keyword, $t, 0, 0);
        $sids = Tool_Array::getFields($list, 'sid');

        $isPid = FALSE;
        if (is_numeric($keyword))
        {
            $productByPid = $sp->get($keyword);
            
            if (!empty($cityId) && $productByPid['city_id']!=$cityId)
            {
                $productByPid = array();
            }
            
            if (!empty($productByPid))
            {
                if (($statusTag & Conf_Product::PRODUCT_STATUS_ONLINE) && $productByPid['status'] == Conf_Base::STATUS_NORMAL)
                {
                    $sids[] = $productByPid['sid'];
                    $isPid = TRUE;
                }
                if (($statusTag & Conf_Product::PRODUCT_STATUS_OFFLINE) && $productByPid['status'] == Conf_Base::STATUS_OFFLINE)
                {
                    $sids[] = $productByPid['sid'];
                    $isPid = TRUE;
                }
                if (($statusTag & Conf_Product::PRODUCT_STATUS_DELETED) && $productByPid['status'] == Conf_Base::STATUS_DELETED)
                {
                    $sids[] = $productByPid['sid'];
                    $isPid = TRUE;
                }
            }
        }

        if (!empty($sids))
        {
            $conf = array('sid' => $sids, 'city_id'=>$cityId);
            $productsBySids = $sp->getList($conf, $total, $start, $num, $statusTag, 'order by frequency desc');
        }
        if (!empty($productByPid) && $isPid)
        {
            $productsBySids[$productByPid['pid']] = $productByPid;
            if (!empty($productByPid) && $isPid)
            {
                $productsBySids[$productByPid['pid']] = $productByPid;
                $list[$productByPid['sid']] = $ss->get($productByPid['sid']);
            }
        }
        $products = $productsBySids;
        $sids = Tool_Array::getFields($products, 'sid');
        // 补充product信息
        if (!empty($products))
        {
            if ($wid)
            {
                $ws = new Warehouse_Stock();
                $stocks = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');
            }

            $info = City_Api::getCity();
            $salesList = Shop_Api::getLowestPrice($info['city_id'], Conf_Activity_Flash_Sale::PALTFORM_WECHAT);

            foreach ($products as $pid => $product)
            {
                if ($product['city_id'] != $info['city_id'])
                {
                    continue;
                }
                $sid = intval($product['sid']);
                if (!in_array($sid, $sids))
                {
                    continue;
                }

                if (!empty($salesList[$pid]) && $salesList[$pid]['sale_price'] < $product['price'] && $salesList[$pid]['end_time'] > time())
                {
                    $product['sale_price'] = intval($salesList[$pid]['sale_price']);
                }

                $product['cost'] = isset($stocks[$sid]) && !empty($stocks[$sid]['cost']) ? $stocks[$sid]['cost'] : $product['cost'];
                $product['purchase_price'] = !empty($stocks) && array_key_exists($sid, $stocks) ? $stocks[$sid]['purchase_price'] : 0;
                Shop_Helper::formatPic($list[$sid]);
                $data[] = array(
                    'product' => $product, 'sku' => $list[$sid],
                );
            }
        }

        $hasMore = $total > $start + $num;

        return array(
            'list' => $data, 'total' => $total, 'has_more' => $hasMore
        );
    }

    /**
     * 使用sphinx搜索
     *
     * @param     $keyword
     * @param     $total
     * @param int $start
     * @param int $num
     *
     * @return array
     */
    public static function fulltextSearch($keyword, $start = 0, $num = 20, $addOffline = FALSE)
    {
        $s = new SphinxClient;
        $s->setServer("localhost", 9312);
        $s->setArrayResult(TRUE);
        $s->setMatchMode(SPH_MATCH_ANY);
        $s->setLimits(0, 500, 1000);
        $sphinxRes = $s->query($keyword, 'test1');

        $sidMatchArr = array();
        if (empty($sphinxRes['matches']))
        {
            return array();
        }
        foreach ($sphinxRes['matches'] as $match)
        {
            $sidMatchArr[$match['id']] = $match['weight'];
        }
        arsort($sidMatchArr);
        $total = count($sidMatchArr);
        $sidMatchArrLimit20 = array_slice($sidMatchArr, $start, $num, TRUE);
        $sidArr = array_keys($sidMatchArrLimit20);

        $result = self::getProductInfosBySids($sidArr);
        $hasMore = $total > $start + $num;

        return array(
            'list' => $result, 'total' => $total, 'has_more' => $hasMore
        );
    }

    public static function fulltextSearchBrandModel($keyword, $city_id = Conf_City::BEIJING)
    {

        $s_skus = self::fulltextSearchOnline($keyword, $city_id);
        $skus = $s_skus['skus'];
        if (empty($skus))
        {
            return array(
                'brand' => array(), 'model' => array()
            );
        }
        //获取sku信息列表
        $data = self::getSkuInfos($skus);

        //获取经过排序的规格名字列表
        $mids = Tool_Array::getFields($data, 'mids');
        $midsU = array();
        foreach ($mids as $a_mid)
        {
            $midsU = array_merge($midsU, explode(',', $a_mid));
        }

        $midsU = array_unique($midsU);
        $models = self::getModelsById($midsU);
        //排序与组成新数据同时进行
        foreach ($midsU as $item)
        {
            $num = 0;
            foreach ($mids as $mid)
            {
                //过滤掉为0的情况
                if ($item == $mid && $item != 0)
                {
                    $newM[$models[$item]['name']] = ++$num;
                }
            }
        }
        $newmodel = array_unique(array_keys($newM));

        //获取经过排序的品牌的列表
        $bids = Tool_Array::getFields($data, 'bid');
        $bidsU = array_unique($bids);
        $brands = self::getBrandByIds($bidsU);

        //这儿必须先进行排序
        foreach ($bidsU as $item)
        {
            $num = 0;
            foreach ($bids as $bid)
            {
                //过滤掉为0的情况
                if ($item == $bid && $item != 0)
                {
                    $newB[$item] = ++$num;
                }
            }
        }

        arsort($newB);

        foreach ($newB as $k => $v)
        {
            $newBrand[] = array(
                'bid' => $k, 'name' => $brands[$k]['name'],
            );
        }

        /*echo "<pre>";
        print_r($newBrand);
        echo "<pre>";*/
        $newBrand = !empty($newBrand) ? $newBrand : array_values(array());
        $newmodel = !empty($newmodel) ? $newmodel : array_values(array());

        return array(
            'brand' => $newBrand, 'model' => $newmodel
        );
    }

    public static function fulltextSearchOnline($keyword, $city_id, $start = 0, $num = 20)
    {
        $s = new SphinxClient;
        $s->setServer("localhost", 9312);
        $s->setArrayResult(TRUE);
        $s->setMatchMode(SPH_MATCH_ANY);
        $s->setLimits(0, 500, 1000);
        $sphinxRes = $s->query($keyword, 'test1');

        $sidMatchArr = array();
        if (empty($sphinxRes['matches']))
        {
            return array();
        }
        foreach ($sphinxRes['matches'] as $match)
        {
            $sidMatchArr[$match['id']] = $match['weight'];
        }
        arsort($sidMatchArr);

        $total = count($sidMatchArr);
        $sidArr = array_keys($sidMatchArr);

        $result = self::getProductInfosBySids($sidArr, $city_id);
        $n_result = array();
        //对搜索结果按原来的次序进行排序
        foreach ($sidArr as $sv)
        {
            foreach ($result as $rv)
            {
                if ($rv['product']['sid'] == $sv)
                {
                    $n_result[] = $rv;
                }
            }
        }
        $skus = array();
        $o_result = array();
        foreach ($n_result as $k => $rv)
        {
            if ($rv['product']['status'] == 0)
            {
                $skus[] = $rv['sku']['sid'];
                $o_result[$k] = $rv;
            }
        }
        $total = count($o_result);
        $o_result = array_slice($o_result, $start, $num, TRUE);

        $hasMore = $total > $start + $num;

        $result = array(
            'list' => $o_result, 'total' => $total, 'has_more' => $hasMore, 'skus' => $skus
        );

        return $result;
    }

    public static function getInvalidProductList($type, $start = 0, $num = 20)
    {
        $total = 0;
        $list = array();

        if ($type == 'no_picture')
        {
            // 查询sku
            $ss = new Shop_Sku();
            $where = sprintf(' (pic_ids="app_icon/default_pic.png" OR pic_ids="") AND status=%d', Conf_Base::STATUS_NORMAL);
            $list = $ss->getListByWhere($where);
        }
        else if ($type == 'no_cate')
        {
            $ss = new Shop_Sku();
            $conf = array(
                'cate1' => 0, 'cate2' => 0, 'status' => Conf_Base::STATUS_NORMAL
            );
            $list = $ss->getList($conf, $total, 0, 0);
        }
        else if ($type == 'no_cost')
        {
            $ss = new Shop_Product();
            $conf = array(
                'cost' => 0,
            );
            $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE;
            $plist = $ss->getList($conf, $total, $start, $num, $statusTag);
            $list = array();
            if (!empty($plist))
            {
                $sids = Tool_Array::getFields($plist, 'sid');

                $sk = new Shop_Sku();
                $list = $sk->getBulk($sids);
            }
        }
        else if ($type == 'duplicate_name')
        {
            $ss = new Shop_Sku();
            $skus = $ss->getAll();

            $titleArr = array();
            $listAll = array();
            if (!empty($skus))
            {
                foreach ($skus as $sid => $sku)
                {
                    $title = str_replace(' ', '', $sku['title'] . $sku['package']);
                    if (isset($titleArr[$title]))
                    {
                        $exSid = $titleArr[$title];
                        $listAll[$sid] = $sku;
                        $listAll[$exSid] = $skus[$exSid];
                    }
                    else
                    {
                        $titleArr[$title] = $sid;
                    }
                }
            }

            $total = count($listAll);
            $list = array_slice($listAll, $start, $num);
        }
        else if ($type == 'no_brand')
        {
            $ss = new Shop_Sku();
            $conf = array(
                'no_bid' => 1, 'status' => Conf_Base::STATUS_NORMAL
            );
            $list = $ss->getList($conf, $total, 0, 0);
        }
        else if ($type == 'no_model')
        {
            $ss = new Shop_Sku();
            $conf = array(
                'no_mid' => 1, 'status' => Conf_Base::STATUS_NORMAL
            );
            $list = $ss->getList($conf, $total, 0, 0);
        }
        else if ($type == 'new')
        {
            $sp = new Shop_Product();
            $ss = new Shop_Sku();
            $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE;
            $list = $sp->getList(array(), $total, $start, $num, $statusTag);
            $sids = Tool_Array::getFields($list, 'sid');
            $skuInfos = $ss->getBulk($sids);
            foreach ($list as $k => $item)
            {
                $sku = $skuInfos[$item['sid']];
                Shop_Helper::formatPic($sku);

                $list[$k]['_pic'] = $sku['_pic'];
            }
            $hasMore = $total > $start + $num;

            return array(
                'list' => $list, 'total' => $total, 'has_more' => $hasMore
            );
        }

        // 补充product信息
        $sp = new Shop_Product();
        $sids = Tool_Array::getFields($list, 'sid');
        if (!empty($sids))
        {
            $cityInfo = City_Api::getCity();
            $cityId = $cityInfo['city_id'];
            $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE;
            $products = $sp->getBySku($sids, $cityId, $statusTag);
            $products = Tool_Array::list2Map($products, 'sid');
            $total = count($products);

            foreach ($list as $idx => $item)
            {
                $sid = intval($item['sid']);
                if (empty($products[$sid]))
                {
                    unset($list[$idx]);
                    continue;
                }

                $product = $products[$sid];
                $list[$idx]['pid'] = $product['pid'];
                $list[$idx]['price'] = $product['price'];
                $list[$idx]['sales'] = $product['sales'];
                $list[$idx]['cost'] = isset($stocks[$sid]) && !empty($stocks[$sid]['cost']) ? $stocks[$sid]['cost'] : $product['cost'];

                Shop_Helper::formatPic($list[$idx]);
            }

            $list = array_slice($list, $start, $num);
        }

        $hasMore = $total > $start + $num;

        return array(
            'list' => $list, 'total' => $total, 'has_more' => $hasMore
        );
    }

    /**
     * 格式化商品的输出格式，用于app接口.
     *
     * @param array $products
     *  每个输入：product_item = {'product'=>array(), 'sku'=>array()}
     *  根据：Shop_Api::getProductList 返回结果定义
     */
    public static function formatProductForApp($products)
    {
        $ret = array();

        foreach ($products as $pinfo)
        {
            $pics = new stdClass();
            $pics->normal = $pinfo['sku']['_pic']['normal'];
            $pics->small = $pinfo['sku']['_pic']['small'];
            $pics->big = $pinfo['sku']['_pic']['big'];

            $ret[] = array(
                'pid' => $pinfo['product']['pid'], 'title' => $pinfo['sku']['title'], 'alias' => $pinfo['sku']['alias'], 'price' => $pinfo['product']['price'], 'unit' => $pinfo['sku']['unit'], 'pic' => $pics, 'icon' => $pinfo['product']['sales_type'] == 1 ? 'http://shop.haocaisong.cn/i/icon_1.png' : ($pinfo['product']['sales_type'] == 2 ? 'http://shop.haocaisong.cn/i/icon_2.png' : ''),
            );
        }

        return $ret;
    }

    /**
     * 格式化商品的输出格式，用于app的cate接口.
     *
     * @param array $products
     *  每个输入：product_item = {'product'=>array(), 'sku'=>array()}
     *  根据：Shop_Api::getProductList 返回结果定义
     */
    public static function formatProductForAppCate($products, $city = 101, $s_brand = '', $s_model = '', $end = 0, $platform = Conf_Activity_Flash_Sale::PALTFORM_APP)
    {
        if (empty($products))
        {
            return array();
        }
        $ret = array();
        $skus = array();
        foreach ($products as $product)
        {
            $skus[] = $product['sku'];
        }
        $bids = Tool_Array::getFields($skus, 'bid');
        $brand = Shop_Api::getBrandByIds($bids);
        $mids = Tool_Array::getFields($skus, 'mid');
        $model = Shop_Api::getModelsById($mids);
        $a_price = Shop_Api::getLowestPrice($city, $platform);
        $k = 0;
        foreach ($products as $pinfo)
        {
            if ($pinfo['product']['city_id'] == $city || in_array($pinfo['product']['pid'], array_keys($a_price)))
            {

                $pics = new stdClass();
                $pics->normal = $pinfo['sku']['_pic']['normal'];
                $pics->small = $pinfo['sku']['_pic']['small'];
                $pics->big = $pinfo['sku']['_pic']['big'];
                $ret[$k] = array(
                    'pid' => (int)$pinfo['product']['pid'], 'tagimg_url' => '', 'tagimg_isshow' => FALSE, 'image' => isset($pics->normal) ? $pics->normal : 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/app_icon/default_pic.png', 'brand_id' => $pinfo['sku']['bid'], 'brand' => $brand[$pinfo['sku']['bid']]['name'], 'title' => $pinfo['sku']['title'], 'model_id' => explode(',', $pinfo['sku']['mids']), '_model' => $pinfo['sku']['package'], 'model' => '',
                    'price' => (string)($pinfo['product']['ori_price'] / 100), 'sale_price' => (string)($pinfo['product']['price'] / 100), 'unit' => $pinfo['sku']['unit'], 'url' => self::getUrlFroApp($pinfo['product']['pid'], $city), 'sale_count' => 0, 'sales_type' => $pinfo['product']['sales_type'],
                );
                foreach ($ret[$k]['model_id'] as $smd)
                {
                    $ret[$k]['m_name'][] = $model[$smd]['name'];
                }
                if ($pinfo['product']['sales_type'] == 2)
                {
                    $ret[$k]['tagimg_url'] = 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/app_icon/icon_tag_remai.png';
                    $ret[$k]['tagimg_isshow'] = TRUE;
                }
                //获取限时抢购或楼层的价格
                foreach ($a_price as $ak => $av)
                {
                    if ($ak == $pinfo['product']['pid'] && ($av['sale_price'] / 100) < $ret[$k]['sale_price'])
                    {
                        $ret[$k]['price'] = $ret[$k]['sale_price'];
                        $ret[$k]['sale_price'] = round(($av['sale_price'] / 100), 2);
                    }
                }

                $k++;
            }
        }
        $newRet = array();
        //根据model和brand过滤掉，如
        foreach ($ret as &$rv)
        {
            if ((empty($s_brand) || $rv['brand_id'] == $s_brand) && (in_array($s_model, $rv['m_name']) || empty($s_model) || $rv['model_id'] == 0))
            {
                $rv['model_id'] = implode(',', $rv['model_id']);
                $newRet[] = $rv;
            }
        }

        $total = count($newRet);
        $hasMore = $total > $end;

        return array(
            'list' => $newRet, 'has_more' => $hasMore
        );
    }

    //给商品加上常买标记
    public static function getOftenBuy($product, $cid)
    {
        $num = 0;
        $oftenPids = Crm2_Api::getOftenBuyPids($cid);
        $oProduct = array();
        foreach ($product as $k => $v)
        {
            if (in_array($v['pid'], $oftenPids) && $num < 2)
            {
                $v['tagimg_url'] = 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/app_icon/icon_tag_changmai.png';
                $v['tagimg_isshow'] = TRUE;
                $oProduct[] = $v;
                unset($product[$k]);
                $num++;
            }
        };

        return array_merge($oProduct, $product);
    }

    public static function getBidsHasProducts($searchConf)
    {
        $sp = new Shop_Product();
        $ss = new Shop_Sku();

        $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE;
        $products = $sp->getList(array('city_id' => $searchConf['city_id']), $total, 0, 0, $statusTag);
        $sids = Tool_Array::getFields($products, 'sid');

        $searchConf['online'] = 1;
        if (!empty($sids))
        {
            $searchConf['sid'] = $sids;
        }

        unset($searchConf['bid']);
        unset($searchConf['mid']);

        $skuInfos = $ss->getList($searchConf, $t, 0, 0);

        return Tool_Array::getFields($skuInfos, 'bid');
    }

    public static function associationWord($keyword)
    {
        $bm = new Shop_Brand();
        $sm = new Shop_Sku();
        $mm = new Shop_Model();

        $keyword = addslashes($keyword);

        $s_models = $mm->getByName($keyword);
        $s_brands = $bm->getByName($keyword);
        if (!empty($s_brands))
        {
            $bids = array_keys($s_brands);
            $b2m = $sm->getMidByBids($bids);

            $bids = array_unique(Tool_Array::getFields($b2m, 'bid'));
            $mids = Tool_Array::getFields($b2m, 'mids');
            foreach ($mids as $mid)
            {
                $newMids[] = implode(',', $mid);
            }
        }
        else if (!empty($s_models))
        {
            $mids = array_keys($s_models);
            $b2m = $sm->getBidByMids($mids);

            $bids = array_unique(Tool_Array::getFields($b2m, 'bid'));
            $mids = Tool_Array::getFields($b2m, 'mids');
            foreach ($mids as $mid)
            {
                $newMids[] = implode(',', $mid);
            }
        }
        else
        {
            return array();
        }

        if (!empty($bids))
        {
            $brands = self::getBrandByIds($bids);
        }
        if (!empty($newMids))
        {

            $models = self::getModelsById(array_unique($newMids));
        }

        foreach ($b2m as $v)
        {
            foreach ($v['mids'] as $mv)
            {
                $new[] = $brands[$v['bid']]['name'] . $models[$mv]['name'];
            }
        }

        return array_values(array_filter(array_unique($new)));
    }

    public static function decoratorProducts(&$productList, $cid, $cityId, $platform, $reqFor='show')
    {
        $customeInfo = array();
        if($cid > 0)
        {
            $customeInfo = Crm2_Api::getCustomerInfo($cid);

        }
        if ($customeInfo['customer']['level_for_sys'] == Conf_User::CRM_SYS_LEVEL_WORK)
        {
            if($reqFor == 'show')
            {
                foreach ($productList as &$product)
                {
                    $product['ori_price'] = $product['sale_price'] = $product['price'];
                }
            }else{
                foreach ($productList as &$product)
                {
                    $product['sale_price'] = $product['work_price'] > 0 ? $product['work_price']: $product['price'];
                    $product['ori_price'] = $product['price'];
                }
            }
        }else{
            $salesList = self::getLowestPrice($cityId, $platform);
            foreach ($productList as &$product)
            {
                if(array_key_exists($product['pid'], $salesList) && $salesList[$product['pid']]['end_time'] > time())
                {
                    $product['sale_price'] = intval($salesList[$product['pid']]['sale_price']);
                }else{
                    $product['sale_price'] = $product['price'];
                }
                $product['ori_price'] = $product['sale_price'];
            }
        }
    }

    public static function getLowestPrice($city_id, $platform)
    {
        //从楼层和限时活动中分别查到活动价，取最低值
        $floor_price = Activity_Floor_Sale_Api::getOnlineProductPrice($city_id);
        $flash_price = Activity_Flash_Sale_Api::getOnlineProductPrice($city_id, $platform);
        $double11 = array();
        if (Conf_Activity::isDouble11Online())
        {
            $double11 = Conf_Activity::$CONF_DOUBLE11;
        }
        $toupiao = array();
        if (Conf_Activity::isToutiaoOnline())
        {
            $toupiao = Conf_Activity::$CONF_TOUTIAO1 + Conf_Activity::$CONF_TOUTIAO2;
        }

        $lowPrice = array();
        foreach ($floor_price as $pid => $info)
        {
            $lowPrice[$pid]['sale_price'] = $info['sale_price'];
            $lowPrice[$pid]['end_time'] = $info['end_time'];
        }

        foreach ($flash_price as $pid => $info)
        {
            if ($lowPrice[$pid]['sale_price'] > $info['sale_price'])
            {
                $lowPrice[$pid]['sale_price'] = $info['sale_price'];
            }
            else if (empty($lowPrice[$pid]['sale_price']))
            {
                $lowPrice[$pid]['sale_price'] = $info['sale_price'];
            }

            if ($lowPrice[$pid]['end_time'] < $info['end_time'])
            {
                $lowPrice[$pid]['end_time'] = $info['end_time'];
            }
            else if (empty($lowPrice[$pid]['end_time']))
            {
                $lowPrice[$pid]['end_time'] = $info['end_time'];
            }
        }

        if (!empty($double11))
        {
            foreach ($double11 as $pid => $price)
            {
                if ($lowPrice[$pid]['sale_price'] > $price)
                {
                    $lowPrice[$pid]['sale_price'] = $price;
                }
                else if (empty($lowPrice[$pid]['sale_price']))
                {
                    $lowPrice[$pid]['sale_price'] = $price;
                }

                $double11End = strtotime(Conf_Activity::$DOUBLE11_END);
                if ($lowPrice[$pid]['end_time'] < $double11End)
                {
                    $lowPrice[$pid]['end_time'] = $double11End;
                }
                else if (empty($lowPrice[$pid]['end_time']))
                {
                    $lowPrice[$pid]['end_time'] = $double11End;
                }
            }
        }

        if (!empty($toupiao))
        {
            foreach ($toupiao as $pid => $price)
            {
                if ($lowPrice[$pid]['sale_price'] > $price)
                {
                    $lowPrice[$pid]['sale_price'] = $price;
                }
                else if (empty($lowPrice[$pid]['sale_price']))
                {
                    $lowPrice[$pid]['sale_price'] = $price;
                }

                $toupiaoEnd = strtotime(Conf_Activity::$TOUTIAO_END);
                if ($lowPrice[$pid]['end_time'] < $toupiaoEnd)
                {
                    $lowPrice[$pid]['end_time'] = $toupiaoEnd;
                }
                else if (empty($lowPrice[$pid]['end_time']))
                {
                    $lowPrice[$pid]['end_time'] = $toupiaoEnd;
                }
            }
        }

        return $lowPrice;
    }

    //获取某城市按分类商品销售前10
    public static function getTopCategroyProduct($city_id)
    {
        $flag = TRUE;
        if ($_SERVER['SERVER_ADDR'] == '127.0.0.1')
        {
            $flag = FALSE;
        }
        $top_data = $flag ? Data_Memcache::getInstance()->get(sprintf('top_sale_product_%d', $city_id)) : array();
        if (empty($top_data))
        {
            $top_data = self::setTopCategoryProduct($city_id, $flag);
        }

        return $top_data;
    }

    public static function setTopCategoryProduct($city_id, $flag = TRUE)
    {
        $oneDao = Data_One::getInstance();
        $top_data = array();//二级分类销售前10材料，三级分类销售前10材料
        $where = sprintf('tp.status=%d AND tp.city_id=%d', Conf_Base::STATUS_NORMAL, $city_id);
        $ret = $oneDao->setDBMode()->select('t_product AS tp LEFT JOIN t_sku AS ts ON tp.sid=ts.sid', array('tp.pid,tp.sid,tp.frequency,ts.bid,ts.cate1,ts.cate2,ts.cate3,tp.city_id'), $where, 'ORDER BY tp.frequency DESC');
        $productList2 = (array)$ret['data'];
        foreach ($productList2 as $product)
        {
            if ($product['cate3'] > 0)
            {
                $cate3 = $product['cate3'];
                if (count($top_data[$cate3]) < 10)
                {
                    $top_data[$cate3][] = array('pid' => $product['pid'], 'frequency' => $product['frequency']);
                }
            }
            $cate2 = $product['cate2'];
            if (count($top_data[$cate2]) < 10)
            {
                $top_data[$cate2][] = array('pid' => $product['pid'], 'frequency' => $product['frequency']);
            }
        }
        $flag && Data_Memcache::getInstance()->set(sprintf('top_sale_product_%d', $city_id), $top_data, 86400);

        return $top_data;
    }

    //获取某城市按分类和品牌销售前10
    public static function getTopCategoryBrandProduct($city_id)
    {
        $flag = TRUE;
        if ($_SERVER['SERVER_ADDR'] == '127.0.0.1')
        {
            $flag = FALSE;
        }
        $top_data = $flag ? Data_Memcache::getInstance()->get(sprintf('top_sale_product_brand_%d', $city_id)) : array();
        if (empty($top_data))
        {
            $top_data = self::setTopCategoryBrandProduct($city_id, $flag);
        }

        return $top_data;
    }

    public static function setTopCategoryBrandProduct($city_id, $flag = TRUE)
    {
        $oneDao = Data_One::getInstance();
        $top_data = array();//二级分类销售前10材料，三级分类销售前10材料
        $where = sprintf('tp.status=%d AND tp.city_id=%d', Conf_Base::STATUS_NORMAL, $city_id);
        $ret = $oneDao->setDBMode()->select('t_product AS tp LEFT JOIN t_sku AS ts ON tp.sid=ts.sid', array('tp.pid,tp.sid,tp.frequency,ts.bid,ts.cate1,ts.cate2,ts.cate3,tp.city_id'), $where, 'ORDER BY tp.frequency DESC');
        $productList2 = (array)$ret['data'];
        foreach ($productList2 as $product)
        {
            if ($product['cate3'] > 0)
            {
                $cate3 = $product['cate3'];
                if (!isset($top_data[$cate3][$product['bid']]) || count($top_data[$cate3][$product['bid']]) < 10)
                {
                    $top_data[$cate3][$product['bid']][] = array('pid' => $product['pid'], 'frequency' => $product['frequency']);
                }
            }
            $cate2 = $product['cate2'];
            if (!isset($top_data[$cate2][$product['bid']]) || count($top_data[$cate2][$product['bid']]) < 10)
            {
                $top_data[$cate2][$product['bid']][] = array('pid' => $product['pid'], 'frequency' => $product['frequency']);
            }
        }
        $flag && Data_Memcache::getInstance()->set(sprintf('top_sale_product_brand_%d', $city_id), $top_data, 86400);

        return $top_data;
    }

    //返回值中，0-代表普通商品，1-砂石砖水泥，2-板材
    private static function _isSandCementBrick($ids, $infos)
    {

        $ret = array();
        foreach ($ids as $id)
        {
            //武汉板材也要算运费
            if ($infos[$id]['city_id'] == Conf_City::WUHAN)
            {
                if (array_key_exists($id, $infos) && array_key_exists($infos[$id]['cate2'], Conf_Sku::$PLATES_CATE2))
                {
                    $ret[$id] = 2;
                }
                else if (array_key_exists($id, $infos) && array_key_exists($infos[$id]['cate2'], Conf_Sku::$SAND_CEMENT_BRICK_CATE2))
                {
                    $ret[$id] = 1;
                }
            }
            else if ($infos[$id]['city_id'] == Conf_City::TIANJIN || $infos[$id]['city_id'] == Conf_City::LANGFANG || $infos[$id]['city_id'] == Conf_City::CHONGQING || $infos[$id]['city_id'] == Conf_City::CHENGDU || $infos[$id]['city_id'] == Conf_City::QINGDAO)
            {
                if (array_key_exists($id, $infos) && array_key_exists($infos[$id]['cate2'], Conf_Sku::$PLATES_CATE2))
                {
                    $ret[$id] = 2;
                }
                else if (array_key_exists($id, $infos) && array_key_exists($infos[$id]['cate2'], Conf_Sku::$SAND_CEMENT_BRICK_CATE2))
                {
                    $ret[$id] = 1;
                }
            }
            else
            {
                $ret[$id] = array_key_exists($id, $infos) && array_key_exists($infos[$id]['cate2'], Conf_Sku::$SAND_CEMENT_BRICK_CATE2) ? 1 : 0;
            }
        }

        return $ret;
    }
    
    
    /**
     * sku是否被加工过.
     * 
     * @param int $skuid
     */
    public static function skuHadProcessed($skuid)
    {
        $ss = new Shop_Sku();
        $skuInfo = $ss->get($skuid);
        
        $processTypes = array(Conf_Sku::SKU_TYPE_PROCESSED, Conf_Sku::SKU_TYPE_PACKAGE);
        if (empty($skuInfo) || empty($skuInfo['rel_sku']) || !in_array($skuInfo['type'], $processTypes))
        {
            return false;
        }
        
        $spop = new Shop_Processed_Order_Products();
        
        return $spop->skuHadProcessd($skuid);
        
    }
}
