<?php

/**
 * Sku相关业务逻辑
 */
class Shop_Sku extends Base_Func
{
    private $skuDao;

    public function __construct()
    {
        $this->skuDao = new Data_Dao('t_sku');

        parent::__construct();
    }

    /**
     * 添加
     * @param array $info
     *
     * @return mixed
     */
    public function add(array $info)
    {
        assert(!empty($info['title']));

        return $this->skuDao->add($info);
    }

    /**
     * 修改
     * @param       $sid
     * @param array $info
     *
     * @return int
     */
    public function update($sid, array $info)
    {
        return $this->skuDao->update($sid, $info);
    }

    /**
     * 查
     * @param $sid
     *
     * @return array
     */
    public function get($sid)
    {
        return $this->skuDao->get($sid);
    }

    /**
     * 查多个
     * @param array $sids
     *
     * @return array
     */
    public function getBulk(array $sids)
    {
        return $this->skuDao->getList($sids);
    }

    /**
     * 查全部
     * @param $fields
     *
     * @return array
     */
    public function getAll($fields = array('*'))
    {
        return $this->skuDao->setFields($fields)->getAll();
    }

    /**
     * 根据条件数组查询
     * @param array $conf
     * @param       $total
     * @param int   $start
     * @param int   $num
     *
     * @return array
     */
    public function getList(array $conf, &$total, $start = 0, $num = 20)
    {
        $where = $this->_getWhereByConf($conf);
        $total = $this->skuDao->getTotal($where);

        if ($total <= 0)
        {
            return array();
        }

        return $this->skuDao->order('sid', 'desc')->limit($start, $num)->getListWhere($where);
    }

    /**
     * 根据where查询
     * @param       $where
     * @param array $fields
     *
     * @return array
     */
    public function getListByWhere($where, $fields = array('*'), $withPK=true)
    {
        return $this->skuDao->setFields($fields)->getListWhere($where, $withPK);
    }

    /**
     * 连表查询商品/SKU 信息
     * @param     $fields
     * @param     $where
     * @param int $start
     * @param int $num
     *
     * @return mixed
     */
    public function getListByPidSid($fields, $where, $start = 0, $num = 20)
    {
        $ret = $this->one->setDBMode()->select('t_sku as ts, t_product as tp', $fields, $where, '', $start, $num);

        return $ret['data'];
    }

    /**
     * 根据品牌id查询sku
     * @param       $bid
     * @param array $fields
     *
     * @return array
     */
    public function getListOfBrand($bid, $fields = array('*'))
    {
        $bid = intval($bid);
        assert(!empty($bid));

        $where = array('bid' => $bid);

        return $this->getListByWhere($where, $fields);
    }

    /**
     * 根据多个品牌id查询sku
     * @param array $bids
     * @param array $fields
     *
     * @return array
     */
    public function getListOfBrands(array $bids, $fields = array('*'))
    {
        $bids = array_unique(array_filter(array_map('intval', $bids)));
        if (empty($bids))
        {
            return array();
        }

        $where = array('bid' => $bids);

        return $this->getListByWhere($where, $fields);
    }

    /**
     * 使用sphinx搜索
     * @param     $keyword
     * @param     $total
     * @param int $start
     * @param int $num
     *
     * @return array
     */
    public function fulltextSearch($keyword, &$total, $start = 0, $num = 20)
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

        $where = " sid in ('" . implode("','", $sidArr) . "')  AND status!=" . Conf_Base::STATUS_DELETED;

        $result = array();
        $searchResult = $this->skuDao->getListWhere($where);
        if (empty($searchResult))
        {
            return array();
        }

        foreach ($sidMatchArrLimit20 as $sid => $match)
        {
            if (empty($searchResult[$sid]))
            {
                continue;
            }

            $result[$sid] = $searchResult[$sid];
        }

        return $result;
    }

    /**
     * 根据cate2和品牌获取型号列表，用于型号过滤
     *
     * @param $cate2
     * @param $bid
     *
     * @return array
     */
    public function getMidByCate2AndBid($cate2, $bid)
    {
        $where = array('cate2' => $cate2);
        if (!empty($bid))
        {
            $where['bid'] = $bid;
        }

        $ret = $this->skuDao->order('sid', 'desc')->setFields(array('sid', 'mids'))->getListWhere($where);
        $midsArr = array();
        foreach ($ret as $item)
        {
            if (!empty($item['mids']))
            {
                $mids = explode(',', $item['mids']);
                $midsArr = array_merge($midsArr, $mids);
            }
        }

        return array_unique(array_filter($midsArr));
    }

    /**
     * 根据品牌ids查sku
     * @param $bids
     *
     * @return array
     */
    public function getMidByBids($bids)
    {
        $new = array();
        $bids = implode(',', $bids);

        $where = 'bid in (' . $bids . ')';
        $ret = $this->skuDao->order('sid', 'desc')->setFields(array('sid', 'mids', 'bid'))->getListWhere($where);
        foreach ($ret as $rv)
        {
            $new[] = array(
                'bid' => $rv['bid'],
                'mids' => explode(',', $rv['mids']),
            );
        }

        return array_values($new);
    }

    /**
     * 根据品牌ids查对应型号
     * @param $mids
     *
     * @return array
     */
    public function getBidByMids($mids)
    {
        $new = array();
        $skus = $this->skuDao->order('sid', 'desc')->setFields(array('sid', 'mids', 'bid'))->getAll();
        $ret = array();
        foreach ($skus as $kv)
        {
            $a_mid = explode(',', $kv['mids']);
            foreach ($mids as $mid)
            {
                if (in_array($mid, $a_mid))
                {
                    $ret[] = $kv;
                }
            }
        }

        foreach ($ret as $rv)
        {
            $new[] = array(
                'bid' => $rv['bid'],
                'mids' => explode(',', $rv['mids']),
            );
        }

        return array_values($new);
    }

    /**
     * 使用mysql的where搜索
     *
     * @param     $keyword
     * @param     $total
     * @param int $start
     * @param int $num
     *
     * @return array
     */
    public function search($keyword, &$total, $start = 0, $num = 20)
    {
        $where = sprintf('sid="%d" or status!=%d', $keyword, Conf_Base::STATUS_DELETED);
        $keywords = array_filter(explode(' ', $keyword));
        foreach ($keywords as $k)
        {
            $where .= sprintf(' and (title like "%%%s%%" or alias like "%%%s%%")', $k, $k);
        }

        $total = $this->skuDao->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }

        return $this->skuDao->order('sid', 'desc')->limit($start, $num)->getListWhere($where);
    }

    /**
     * 根据conf获取where语句
     * @param $conf
     *
     * @return string
     */
    private function _getWhereByConf($conf)
    {
        $where = '1=1';
        $where .= sprintf(' AND (status=%d or status=%d)', Conf_Base::STATUS_NORMAL, Conf_Base::STATUS_OFFLINE);

        if (!empty($conf['cate3']))
        {
            $where .= sprintf(' and cate3="%d"', mysql_escape_string($conf['cate3']));
        }
        else if (!empty($conf['cate2']))
        {
            $where .= sprintf(' and cate2="%d"', mysql_escape_string($conf['cate2']));
        }
        elseif (!empty($conf['cate1']))
        {
            $where .= sprintf(' and cate1="%d"', mysql_escape_string($conf['cate1']));
        }
        if ($conf['bid'] > 0)
        {
            $where .= sprintf(' and bid="%d"', mysql_escape_string($conf['bid']));
        }
        if ($conf['mid'] > 0)
        {
            $where .= sprintf(' and find_in_set("%d", mids)', mysql_escape_string($conf['mid']));
        }
        if (isset($conf['pic_ids']))
        {
            $where .= sprintf(' and pic_ids="%s"', $conf['pic_ids']);
        }
        if (!empty($conf['no_bid']))
        {
            $where .= sprintf(' AND bid=0');
        }
        if (!empty($conf['no_mid']))
        {
            $where .= sprintf(' AND mid=0');
        }
        if (isset($conf['sid']))
        {
            if (is_array($conf['sid']))
            {
                $where .= sprintf(' AND sid IN (%s)', implode(',', $conf['sid']));
            }
            else
            {
                $where .= sprintf(' AND status="%d"', $conf['sid']);
            }
        }

        return $where;
    }

    /**
     * 是否虚拟商品
     * @param $sid
     *
     * @return bool
     */
    public function isVirtual($sid)
    {
        static $virtualSkus;

        if (empty($virtualSkus))
        {
            $cate1s = implode(',', array_keys(Conf_Sku::$CATE1_VIRTUAL));
            $where = sprintf('cate1 in (%s)', $cate1s);
            $virtualSkus = $this->skuDao->setFields(array('sid'))->getListWhere($where);
        }

        return isset($virtualSkus[$sid]);
    }

    /**
     * 获取指定仓库中多个sku的成本
     * @param       $wid
     * @param array $sids
     *
     * @return array
     */
    public function getCostOfSkusWarehouse($wid, array $sids)
    {
        $costs = array();

        //获取这些sku的stock列表
        $ws = new Warehouse_Stock();
        $stocks = $ws->getBulk($wid, $sids);
        $stocks = Tool_Array::list2Map($stocks, 'sid');

        //获取这些sku的product列表
        if (empty(Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$wid]))
        {
            return $costs;
        }

        $cityId = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$wid];
        $ss = new Shop_Product();
        $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE;
        $products = $ss->getBySku($sids, $cityId, $statusTag);
        $products = Tool_Array::list2Map($products, 'sid');

        //处理结果
        foreach ($sids as $sid)
        {
            if (!empty($stocks[$sid]['cost']))
            {
                $costs[$sid] = intval($stocks[$sid]['cost']);
            }
            elseif (!empty($products[$sid]['cost']))
            {
                $costs[$sid] = intval($products[$sid]['cost']);
            }
            else
            {
                $costs[$sid] = 0;
            }
        }

        return $costs;
    }

    /**
     * 获取所有sku成本
     *
     * @return array 格式: {$wid:{$sid:cost}}
     */
    public function getAllCost()
    {
        //获取所有stock列表
        $ws = new Warehouse_Stock();
        $costsMap = $ws->getAllStock(array('sid','wid','cost'));

        //获取所有商品列表
        $ss = new Shop_Product();
        $products = $ss->getAll(array('pid','sid','city_id','cost'));
        foreach($products as $product)
        {
            $cityId = $product['city_id'];
            if (empty(Conf_Warehouse::$WAREHOUSE_CITY[$cityId]) || empty($product['cost'])) continue;

            $wids = Conf_Warehouse::$WAREHOUSE_CITY[$cityId];
            $sid = $product['sid'];
            foreach ($wids as $wid)
            {
                if (empty($costsMap[$wid][$sid]['cost']))
                {
                    $costsMap[$wid][$sid]['cost'] = intval($product['cost']);
                }
            }

        }

        return $costsMap;
    }

    /**
     * 获取指定sku在指定城市中的平均成本
     * @param     $sid
     * @param int $cityId
     *
     * @return float|int
     */
    public function getCostOfSku($sid, $cityId = Conf_City::BEIJING)
    {
        //获取这些sku的product列表
        $sp = new Shop_Product();
        $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE;
        $products = $sp->getBySku(array($sid), $cityId, $statusTag);
        $product = array_shift($products);
        if (!empty($product['cost']))
        {
            return $product['cost'];
        }

        //获取这些sku的stock列表
        $wids = Conf_Warehouse::$WAREHOUSE_CITY[$cityId];
        $ws = new Warehouse_Stock();
        $stocks = $ws->getAll($sid);
        $num = $cost = 0;
        foreach ($stocks as $stock)
        {
            if (!in_array($stock['wid'], $wids))
            {
                continue;
            }
            if (!empty($stock['cost']))
            {
                $cost += $stock['cost'];
                $num++;
            }
        }

        return $num ? $cost / $num : 0;
    }
}
