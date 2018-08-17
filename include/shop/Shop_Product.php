<?php

/**
 * 商品相关业务
 */
class Shop_Product extends Base_Func
{
    private $productDao;

    public function __construct()
    {
        $this->productDao = new Data_Dao('t_product');

        parent::__construct();
    }

    /**
     * 添加商品
     * @param array $info
     *
     * @return mixed
     */
    public function add(array $info)
    {
        assert(!empty($info['sid']));
        if (!$this->is($info['buy_type']))
        {
            $info['buy_type'] = Conf_Product::BUY_TYPE_COMMON;
        }
        if (isset($info['price']) && $info['price'] == 0)
        {
            $info['status'] = Conf_Base::STATUS_OFFLINE;
        }

        return $this->productDao->add($info);
    }

    /**
     * 修改商品
     * @param       $pid
     * @param array $info
     *
     * @return int
     */
    public function update($pid, array $info)
    {
        return $this->productDao->update($pid, $info);
    }

    /**
     * 获取商品信息
     * @param $pid
     *
     * @return array
     */
    public function get($pid)
    {
        return $this->productDao->get($pid);
    }

    public function getByUniqKey($sid, $cityId)
    {
        assert(!empty($sid) && !empty($cityId));
        
        $where = 'sid='. $sid. ' and city_id='. $cityId;
        
        $ret = $this->productDao->getListWhere($where);
        
        return current($ret);
    }
    
    /**
     * 获取多个商品信息
     * @param array $pids
     *
     * @return array
     */
    public function getBulk(array $pids)
    {
        return $this->productDao->getList($pids);
    }

    /**
     * 获取全部商品信息
     * @param $fields
     *
     * @return array
     */
    public function getAll($fields = array('*'))
    {
        return $this->productDao->setFields($fields)->getAll();
    }

    /**
     * 删除商品信息
     * @param $pid
     *
     * @return bool
     */
    public function delete($pid)
    {
        return $this->productDao->delete($pid);
    }

    /**
     * 根据where条件查询商品信息
     * @param $where
     *
     * @return array
     */
    public function getListByWhere($where)
    {
        return $this->productDao->getListWhere($where);
    }

    /**
     * 根据sku查
     *
     * @param array $sids
     * @param $statusTag
     * @param $cityId
     *
     * @return array
     */
    public function getBySku(array $sids, $cityId = 101, $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE)
    {
        if ($cityId == Conf_City::OTHER)
        {
            $cityId = Conf_City::BEIJING;
        }
        
        $conf = array(
            'sid' => $sids,
            'city_id' => $cityId,
        );

        $data = $this->getList($conf, $total, 0, 0, $statusTag);

        return $data;
    }

    /**
     * 根据Conf数组查询商品
     * @param     $conf
     * @param int $total
     * @param int $start
     * @param int $num
     * @param int $stausTag
     *
     * @return array
     */
    public function getList($conf, &$total, $start = 0, $num = 20, $stausTag = Conf_Product::PRODUCT_STATUS_ONLINE, $order = 'order by sortby desc, sid desc')
    {
        //默认当前城市
        if (empty($conf['city_id']))
        {
            $city = new City_City();
            $conf['city_id'] = $city->getCity();
        }

        $where = $this->_getWhereByConf($conf, $stausTag);
        $total = $this->productDao->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }
        $products = $this->productDao->order($order)->limit($start, $num)->getListWhere($where);

        return $products;
    }

    /**
     * 根据conf生成where语句
     * @param     $conf
     * @param int $stausTag
     *
     * @return string
     */
    private function _getWhereByConf($conf, $stausTag = Conf_Product::PRODUCT_STATUS_ONLINE)
    {
        $where = '1=1';

        $online = $stausTag & Conf_Product::PRODUCT_STATUS_ONLINE;
        $offline = $stausTag & Conf_Product::PRODUCT_STATUS_OFFLINE;
        $deleted = $stausTag & Conf_Product::PRODUCT_STATUS_DELETED;
        $statusArr = array();
        if ($online && $offline && $deleted)
        {
            //获取全部商品，where不用status
        }
        else
        {
            if ($online)
            {
                $statusArr[] = Conf_Base::STATUS_NORMAL;
            }
            if ($offline)
            {
                $statusArr[] = Conf_Base::STATUS_OFFLINE;
            }
            if ($deleted)
            {
                $statusArr[] = Conf_Base::STATUS_DELETED;
            }
        }
        if (!empty($statusArr))
        {
            $where .= sprintf(' AND status in (%s)', implode(',', $statusArr));
        }
        if (!empty($conf['sid']))
        {
            if (is_array($conf['sid']))
            {
                $where .= " AND sid in ('" . implode("','", $conf['sid']) . "') ";
            }
            else
            {
                $where .= sprintf(' AND sid=%d', $conf['sid']);
            }
        }
        if (!empty($conf['city_id']))
        {
            $where .= sprintf(' AND city_id=%d', $conf['city_id']);
        }
        if (isset($conf['cost']))
        {
            $where .= sprintf(' AND cost=%d', $conf['cost']);
        }
        if (!empty($conf['sales_type']))
        {
            $where .= sprintf(' AND sales_type=%d', $conf['sales_type']);
        }

        return $where;
    }

    function getSandSids()
    {
        $pids = Conf_Order::$SAND_CEMENT_BRICK_PIDS;
        $products = $this->getBulk($pids);
        $sids = Tool_Array::getFields($products, 'sid');
        return $sids;
    }

    function getProductByWidSid($wid, $sid)
    {
        static $productsMap;
        if (empty($productsMap))
        {
            $products = $this->getAll();
            foreach($products as $product)
            {
                $_sid = $product['sid'];
                $_cityId = $product['city_id'];
                $productsMap[$_cityId][$_sid] = $product;
            }

            $products = null;
            unset($products);
        }

        if (!isset(Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$wid]))
        {
            return null;
        }
        $_cityId = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$wid];
        return isset($productsMap[$_cityId][$sid]) ? $productsMap[$_cityId][$sid] : null;
    }
}