<?php

/**
 * 订单信息相关业务逻辑
 */
class Order_Order extends Base_Func
{
    private $orderDao;
    private $orderProductDao;
    private $orderPrivilegeDao;
    private $salePrivilegeDao;

    public function __construct()
    {
        $this->orderDao = new Data_Dao('t_order');
        $this->orderProductDao = new Data_Dao('t_order_product');
        $this->orderPrivilegeDao = new Data_Dao('t_order_privilege');
        $this->salePrivilegeDao = new Order_Sale_Preferential();

        parent::__construct();
    }

    public function get($oid, $isMaster = FALSE, $useCache=true)
    {
        if (!$isMaster)
        {
            return $this->orderDao->setSlave()->get($oid, $useCache);
        }
        else
        {
            return $this->orderDao->get($oid, $useCache);
        }
    }

    public function getBulk($oids, $filed = array('*'), $orderby = array(
        'oid',
        'desc'
    ))
    {
        return $this->orderDao->setSlave()->setFields($filed)->order($orderby[0], $orderby[1])->getList($oids);
    }

    public function add($info)
    {
        //多城市
        if (empty($info['city_id']))
        {
            $city = new City_City();
            $info['city_id'] = $city->getCity();
        }
        //设置默认仓库
        $info['wid'] = Appconf_Warehouse::getDefaultWid4Order($info['city_id'], $info['wid']);
        
        return $this->orderDao->add($info);
    }

    public function delete($oid)
    {
        $where = array('oid' => $oid);
        $this->orderProductDao->deleteWhere($where);
        $this->orderPrivilegeDao->deleteWhere($where);
        $preferentialList = $this->salePrivilegeDao->getItem($oid);
        $saleDao = new Data_Dao('t_sale_privilege_config');
        $order = Order_Api::getOrderInfo($oid);
        if(!empty($preferentialList))
        {
            foreach ($preferentialList as $item)
            {
                if($item['ctime'] >= date('Y-m-01 00:00:00'))
                {
                    $saleDao->updateWhere('month="'.date('Ym').'" AND suid='.$item['send_suid'].' AND city_id='.$order['city_id'],
                        array(),array('available_amount'=>$item['amount']));
                    break;
                }
            }
            $this->salePrivilegeDao->delete($oid);
        }

        $info = array(
            'status' => Conf_Base::STATUS_DELETED,
            'sale_privilege' => 0,
            'privilege' => 0
        );

        return $this->orderDao->update($oid, $info);
    }

    
    public function recoveryOrder($oid, $upOrderData=array(), $upOrderProductData=array())
    {
        assert(!empty($oid));
        
        if (empty($upOrderData))
        {
            $upOrderData = array(
                'status' => Conf_Base::STATUS_NORMAL,
                'step' => Conf_Order::ORDER_STEP_NEW,
                'paid' => Conf_Order::UN_PAID,
                'real_amount' => 0,
                'payment_type' => 0,
            );
        }
        if (empty($upOrderProductData))
        {
            $upOrderProductData = array(
                'status' => Conf_Base::STATUS_NORMAL
            );
        }
        
        $this->orderDao->update($oid, $upOrderData);
        
        $this->orderProductDao->updateWhere('oid='. $oid, $upOrderProductData);
        
        return true;
    }
    
    //@todo Del addby guoqiang/2018-02-01
    public function reset($oid, $step = NULL)
    {
        return false;
        
        
        $where = array('oid' => $oid);
        $this->orderProductDao->updateWhere($where, array('status' => Conf_Base::STATUS_NORMAL));

        $updateOrder = array(
            'status' => Conf_Base::STATUS_NORMAL,
        );
        if (!is_null($step) && $step < Conf_Order::ORDER_STEP_PICKED && $step >= Conf_Order::ORDER_STEP_NEW)
        {
            $updateOrder['step'] = $step;
        }

        return $this->orderDao->update($oid, $updateOrder);
    }

    public function cancel($oid)
    {
        $where = array('oid' => $oid);
        $info = array('status' => Conf_Base::STATUS_CANCEL);
        $this->orderProductDao->updateWhere($where, $info);
        $this->orderPrivilegeDao->deleteWhere($where);
        $preferentialList = $this->salePrivilegeDao->getItem($oid);
        $saleDao = new Data_Dao('t_sale_privilege_config');
        $order = Order_Api::getOrderInfo($oid);
        if(!empty($preferentialList))
        {
            foreach ($preferentialList as $item)
            {
                if($item['ctime'] >= date('Y-m-01 00:00:00'))
                {
                    $saleDao->updateWhere('month="'.date('Ym').'" AND suid='.$item['send_suid'].' AND city_id='.$order['city_id'],
                        array(),array('available_amount'=>$item['amount']));
                    break;
                }
            }
            $this->salePrivilegeDao->delete($oid);
        }

        $info = array(
            'status' => Conf_Base::STATUS_CANCEL,
            'sale_privilege' => 0,
            'privilege' => 0
        );

        return $this->orderDao->update($oid, $info);
    }

    public function update($oid, $update, $change = array())
    {
        return $this->orderDao->update($oid, $update, $change);
    }
    
    /**
     * 根据oids批量更新.
     * 
     * @param array $oids
     * @param array $updateData
     */
    public function updateByOids($oids, $updateData)
    {
        assert(!empty($oids) && !empty($updateFields));
        assert(is_array($oids) && is_array($updateFields));
        
        $where = 'oid in ('. implode(',', $oids).')';
        
        return $this->orderDao->updateWhere($where, $updateFields);
    }

    public function updateOrderProduct($oid, $sid, $update, $change = array())
    {
        $where = array(
            'oid' => $oid,
            'sid' => $sid
        );

        return $this->orderProductDao->updateWhere($where, $update, $change);
    }

    public function updateOrderDeleteProductStatus($oid, $sid, $update)
    {
        $where = array(
            'oid' => $oid,
            'sid' => $sid,
            'status' => Conf_Base::STATUS_DELETED
        );
        return $this->orderProductDao->updateWhere($where, $update);
    }

    public function getList($oids)
    {
        return $this->orderDao->setSlave()->getList($oids);
    }

    public function getListRawWhere($where, &$total, $order, $start = 0, $num = 20, $fields = array('*'), $withPk = true)
    {
        $total = $this->orderDao->setSlave()->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }

        if (empty($order))
        {
            $order = array(
                'oid',
                'asc'
            );
        }

        return $this->orderDao->setSlave()->order($order[0], $order[1])->limit($start, $num)->setFields($fields)->getListWhere($where, $withPk);
    }

    public function getOrderByWhere($where, $start = 0, $num = 20, $fields = array('*'), $order='')
    {
        return $this->getListRawWhereWithoutTotal($where, $order, $start, $num, $fields);
    }
    
    // @notice 不在使用：请使用 getOrderByWhere
    public function getListRawWhereWithoutTotal($where, $order, $start = 0, $num = 20, $fields = array('*'))
    {
        if (empty($order))
        {
            $order = array(
                'oid',
                'asc'
            );
        }

        return $this->orderDao->setSlave()->order($order[0], $order[1])->limit($start, $num)->setFields($fields)->getListWhere($where);
    }

    public function updateByWhere($info, $change, $where)
    {
        return $this->orderDao->updateWhere($where, $info, $change);
    }

    /**
     * 获取当前最大oid
     *
     * @return mixed
     */
    public function getMaxOid()
    {
        $where = sprintf(' status="%d" ', Conf_Base::STATUS_NORMAL);
        $list = $this->orderDao->setSlave()->setFields(array('oid'))->order('oid', 'desc')->limit(0, 1)->getListWhere($where);
        $item = array_shift($list);

        return $item['oid'];
    }

    /**
     * 客户订单统计.
     *
     * @param int    $cid
     * @param string $date 从指定时间到现在的订单统计
     *
     * @return array
     */
    public function getSummaryOfCustomer($cid, $date = '', $step = Conf_Order::ORDER_STEP_SURE)
    {
        $cid = intval($cid);
        assert($cid > 0);

        $step = $step ? $step : Conf_Order::ORDER_STEP_SURE;

        $where = sprintf('cid=%d and status=%d and step>=%d', $cid, Conf_Base::STATUS_NORMAL, $step);
        if (!empty($date))
        {
            $where .= " and ctime>='$date'";
        }

        return $this->orderDao->setSlave()->getTotal($where);
    }

    /**
     * 获取用户的订单
     *
     * @param       $cid
     * @param       $total
     * @param array $order
     * @param int   $start
     * @param int   $num
     * @param array $fields
     * @param array $extConf
     *
     * @return array
     */
    public function getCustomerOrderList($cid, &$total, $order = array(), $start = 0, $num = 20, $fields = array('*'), $extConf = array())
    {
        assert($cid > 0);

        $conf = array(
            'cid' => $cid,
            'status' => array(
                Conf_Base::STATUS_NORMAL,
                Conf_Base::STATUS_CANCEL
            ),
            'step' => Conf_Order::ORDER_STEP_ALL_SURE,
        );
        if (!empty($extConf['has_paid']))
        {
            $conf['has_paid'] = $extConf['has_paid'];
        }
        if (!empty($extConf['is_complate']))
        {
            $conf['is_complate'] = $extConf['is_complate'];
        }

        $where = $this->_getWhereFromConf($conf);

        $total = $this->orderDao->setSlave()->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }

        return $this->orderDao->setSlave()->order($order[0], $order[1])->limit($start, $num)->setFields($fields)->getListWhere($where);
    }

    /**
     * @param array  $list
     * @param string $idField
     *
     * @return array
     *
     * 补充订单信息
     */
    public function appendInfos(array &$list, $idField = 'oid')
    {
        $oids = Tool_Array::getFields($list, $idField);
        if (empty($oids))
        {
            return array();
        }

        $orders = $this->getBulk($oids);
        $orders = Tool_Array::list2Map($orders, 'oid');
        foreach ($list as &$item)
        {
            $oid = $item[$idField];
            if (empty($orders[$oid]))
            {
                continue;
            }

            $order = $orders[$oid];
            $item['_order'] = $order;
        }
    }

    public function getSumByConf($conf, $field, $suser)
    {
        $this->_formatOrderConf($conf, $suser);
        $where = $this->_getWhereFromConf($conf);

        return $this->orderDao->setSlave()->getSum($field, $where);
    }

    public function getSumByWhere($where, $field)
    {
        return $this->orderDao->setSlave()->getSum($field, $where);
    }

    public function getTotalByWhere($where)
    {
        return $this->orderDao->setSlave()->getTotal($where);
    }

    /**
     * 根据条件获取订单列表
     *
     * @param array $conf
     * @param       $total
     * @param       $order
     * @param int   $start
     * @param int   $num
     * @param array $fields
     * @param array $suser
     *
     * @return array
     */
    public function getOrderListByConf(array $conf, &$total, $order, $start = 0, $num = 20, $fields = array('*'), $suser = array())
    {
        if (empty($order))
        {
            $order = array(
                'oid',
                'desc'
            );
        }
        if (!empty($conf['maybe_ate']))
        {
            $order = array(
                'delivery_date',
                'asc'
            );
        }

        $where = $this->_getWhereFromConf($conf);

        //有账期
        if ($conf['has_pdays'] == 1)
        {
            $cc = new Crm2_Customer();
            $w = 'payment_days > 0 and status = 0';
            $customers = $cc->getByWhere($w, array('cid'));
            $cids = Tool_Array::getFields($customers, 'cid');

            $where .= sprintf(' AND cid in (%s)', implode(',', $cids));
        }
        //无账期
        else if ($conf['has_pdays'] == 2)
        {
            $cc = new Crm2_Customer();
            $w = 'payment_days > 0 and status = 0';
            $customers = $cc->getByWhere($w, array('cid'));
            $cids = Tool_Array::getFields($customers, 'cid');

            $where .= sprintf(' AND cid not in (%s)', implode(',', $cids));
        }

        $total = $this->orderDao->getTotal($where);
        if (empty($total))
        {
            return array();
        }

        return $this->orderDao->setSlave()->order($order[0], $order[1])->setFields($fields)->limit($start, $num)->getListWhere($where);
    }

    public function getOrderNumByConf(array $conf, $suser = array())
    {
        $this->_formatOrderConf($conf, $suser);
        $where = $this->_getWhereFromConf($conf);
        $total = $this->orderDao->setSlave()->getTotal($where);

        return $total;
    }

    private function _formatOrderConf(&$conf, $suser)
    {
        $roles = explode(',', $suser['role']);
        //客服也不限制
        if (in_array(Conf_Admin::ROLE_CITY_ADMIN, $roles))
        {
            //多城市
            $city = new City_City();
            $conf['city_id'] = $city->getCity();
        }
    }

    /**
     * 统计的一个联表查询（暂时不重构了）
     *
     * @param     $where
     * @param     $total
     * @param     $order
     * @param int $start
     * @param int $num
     *
     * @return array
     */
    public function getProductAndOrderWhere($where, &$total, $order, $start = 0, $num = 20)
    {
        if (empty($order))
        {
            $order = 'order by oid desc';
        }

        $total = 0;

        // 查询结果
        $data = $this->one->select('t_order as o left join t_order_product as p ON o.oid=p.oid', array('p.oid, p.price, p.cost, p.num, o.delivery_date, p.pid, o.privilege, o.customer_carriage, o.freight, p.rid'), $where, $order, $start, $num);
        if (empty($data['data']))
        {
            return array();
        }

        return $data['data'];
    }

    /**
     * 获取订单的商品列表
     *
     * @param      $oid
     * @param bool $isGetRefund
     * @param int  $status
     * @param bool $useSlave 是否使用从库
     */
    public function getProductsOfOrder($oid, $isGetRefund = FALSE, $status = Conf_Base::STATUS_NORMAL, $useSlave = TRUE)
    {
        $oid = intval($oid);
        assert($oid > 0);

        if ($status == Conf_Base::STATUS_ALL)
        {
            $where = 'oid=' . $oid;
        }
        else
        {
            $where = sprintf('oid=%d and status=%d', $oid, $status);
        }
        $where .= !$isGetRefund ? ' and rid=0' : '';

        $dao = $useSlave ? $this->orderProductDao->setSlave() : $this->orderProductDao;

        return $dao->order('ctime', 'asc')->getListWhere($where);
    }

    /**
     * 获取订单id批量获取pid
     *
     * @param            $oids
     * @param bool|false $isGetRefund
     * @param            $status
     *
     * @return array
     */
    public function getPidsbyOids($oids, $isGetRefund = FALSE, $status = Conf_Base::STATUS_NORMAL)
    {
        $soid = implode(',', $oids);
        assert($soid != '');
        $soid = rtrim($soid, ',');
        $where = sprintf('oid in (%s) and status=%d', $soid, $status);
        $where .= !$isGetRefund ? ' and rid=0' : '';

        return $this->orderProductDao->setSlave()->order('ctime', 'asc')->setFields(array(
                                                                                        'oid',
                                                                                        'pid',
                                                                                        'num'
                                                                                    ))->getListWhere($where);
    }

    /**
     * 更新订单商品的仓库和成本
     *
     * @param $oid
     * @param $pid
     * @param $wid
     * @param $cost
     *
     * @return int
     */
    public function updateOrderProductWidAndCost($oid, $pid, $wid, $cost)
    {
        $where = array(
            'oid' => $oid,
            'pid' => $pid
        );
        $info = array(
            'wid' => $wid,
            'cost' => $cost
        );

        return $this->orderProductDao->updateWhere($where, $info);
    }

    /**
     * 删除订单商品
     *
     * @param     $oid
     * @param int $pid
     * @param bool $phyDel
     *
     * @return mixed
     */
    public function deleteOrderProduct($oid, $pid, $phyDel=false)
    {
        $oid = intval($oid);
        if (!is_array($pid))
        {
            $pid = intval($pid);
        }

        assert($oid > 0);
        assert($pid > 0);

        $where = array(
            'oid' => $oid,
            'pid' => $pid
        );
        $update = array(
            'status' => Conf_Base::STATUS_DELETED,
            'vnum' => 0,
            'picked' => 0
        );

        if (!$phyDel)
        {
            return $this->orderProductDao->updateWhere($where, $update);
        }
        else
        {
            return $this->orderProductDao->deleteWhere($where, $phyDel);
        }
    }

    /**
     * 向订单中添加商品
     *
     * @param $oid
     * @param $info
     *
     * @return mixed
     */
    public function addOrderProduct($oid, $info, $city_id = 0)
    {
        assert(!empty($oid));
        assert(!empty($info));
        assert(!empty($info['pid']));
        assert(!empty($info['price']));

        $info['oid'] = intval($oid);
        $info['sid'] = intval($info['sid']);
        $info['pid'] = intval($info['pid']);
        $info['price'] = intval($info['price']);
        $info['ori_price'] = intval($info['ori_price']);
        $info['cost'] = intval($info['cost']);
        $info['num'] = intval($info['num']);
        $info['vnum'] = intval($info['vnum']);
        $info['status'] = Conf_Base::STATUS_NORMAL;

        $update = array(
            'sid',
            'price',
            'ori_price',
            'num',
            'vnum',
            'status',
            'note'
        );

        //多城市
        $city = new City_City();
        if (empty($city_id))
        {
            $info['city_id'] = $city->getCity();
        }
        else
        {
            $info['city_id'] = $city_id;
        }

        return $this->orderProductDao->add($info, $update);
    }

    public function addClicentOrderProduct($oid, $info, $cuts = array())
    {
        assert(!empty($oid));
        assert(!empty($info));
        assert(!empty($info['pid']));
        assert(!empty($info['price']));

        $arr = array();
        $arr['oid'] = intval($oid);
        $arr['pid'] = intval($info['pid']);
        $arr['sid'] = $info['sid'];
        $arr['price'] = intval($info['price']);
        $arr['ori_price'] = intval($info['ori_price']);
        $arr['num'] = intval($info['num']);
        $arr['vnum'] = intval($info['vnum']);
        $arr['status'] = Conf_Base::STATUS_NORMAL;
        if (Conf_Base::switchForManagingMode())
        {
            $arr['managing_mode'] = intval($info['managing_mode']);
        }
        if (!empty($cuts[$info['pid']]))
        {
            $arr['note'] = '裁断数量:' . $cuts[$info['pid']];
        }

        $update = array(
            'price',
            'ori_price',
            'num',
            'vnum',
            'status',
            'note'
        );

        //多城市

        //$city = new City_City();
        $arr['city_id'] = $info['product']['city_id'];

        return $this->orderProductDao->add($arr, $update);
    }

    /**
     * 更新订单商品表数据.
     *
     * @param type $oid 订单id
     * @param type $pid 商品id
     * @param type $rid 退款单id
     * @param type $upData
     * @param type $changeData
     */
    public function updateOrderProductInfo($oid, $pid, $rid, $upData, $changeData = array())
    {
        assert(!empty($oid));
        assert(!empty($upData) || !empty($changeData));

        $where = array(
            'status' => Conf_Base::STATUS_NORMAL,
            'oid' => $oid,
            'rid' => $rid,
        );
        if (!empty($pid))
        {
            $where['pid'] = $pid;
        }

        $affectedRow = $this->orderProductDao->updateWhere($where, $upData, $changeData);

        return $affectedRow;
    }

    /**
     * 更新订单商品的vnum
     *
     * @param $oid
     * @param $pid
     * @param $vnum
     *
     * @return int
     */
    public function updateOrderProductVnum($oid, $pid, $vnum)
    {
        $oid = intval($oid);
        $pid = intval($pid);
        assert($oid > 0);
        assert($pid > 0);

        $where = array(
            'oid' => $oid,
            'pid' => $pid
        );
        $update = array('vnum' => $vnum);

        return $this->orderProductDao->updateWhere($where, $update);
    }

    /**
     * 更新订单商品的num
     *
     * @param $oid
     * @param $pid
     * @param $num
     *
     * @return int
     */
    public function updateOrderProductNum($oid, $pid, $num)
    {
        $oid = intval($oid);
        $pid = intval($pid);
        assert($oid > 0);
        assert($pid > 0);

        $where = array(
            'oid' => $oid,
            'pid' => $pid
        );
        $update = array('num' => $num);

        return $this->orderProductDao->updateWhere($where, $update);
    }

    public function updateOrderProductPickedNum($oid, $pid, $num)
    {
        $oid = intval($oid);
        $pid = intval($pid);
        assert($oid > 0);
        assert($pid > 0);

        $where = array(
            'oid' => $oid,
            'pid' => $pid
        );
        $update = array(
            'picked' => $num,
            'picked_time' => date('Y-m-d H:i:s')
        );

        return $this->orderProductDao->updateWhere($where, $update);
    }

    public function updateOrderProductBySid($oid, $rid, $sid, $upData, $chgData = array())
    {
        assert(!empty($oid));
        assert(!empty($sid));
        assert(!empty($upData) || !empty($chgData));

        $where = sprintf('status=0 and oid=%d and rid=%d and sid=%d', $oid, $rid, $sid);

        return $this->orderProductDao->updateWhere($where, $upData, $chgData);
    }

    /**
     * 根据多个oid获取订单商品
     *
     * @param       $oids
     * @param array $order
     * @param array $fields
     *
     * @return array
     */
    public function getProductsByOids($oids, $order = array(), $fields = array('*'), $cityId = 0)
    {
        $where = array(
            'oid' => $oids,
            'status' => Conf_Base::STATUS_NORMAL
        );
        if ($cityId > 0)
        {
            $where['city_id'] = $cityId;
        }
        if (empty($order))
        {
            $order = array(
                'pid',
                'asc'
            );
        }

        return $this->orderProductDao->setFields($fields)->order($order[0], $order[1])->getListWhere($where);
    }

    public function getOrderProductsByRawWhere($where, $start = 0, $num = 0, $field = array('*'), $order = array('oid','desc') )
    {
        $res = array(
            'data' => array(),
            'total' => 0
        );

        //$res['total'] = $this->orderProductDao->getTotal($where);

        $res['data'] = $this->orderProductDao->setFields($field)->order($order[0], $order[1])->limit($start, $num)->getListWhere($where);

        return $res;
    }

    public function getByRawWhere($kind, $where, $field = array('*'), $order = '', $start=0, $num=0)
    {
        $ret = $this->one->setDBMode()->select($kind, $field, $where, $order, $start, $num);

        return $ret['data'];
    }

    /**
     * 根据where语句获取商品列表
     *
     * @param        $where
     * @param int    $start
     * @param int    $num
     * @param string $groupBy
     * @param array  $order
     *
     * @return array
     */
    public function getOrderProductsByWhere($where, $start = 0, $num = 20, $groupBy = '', $order = array(
        'oid',
        'desc'
    ), $fields = array('*'))
    {
        $res = array(
            'data' => array(),
            'total' => 0,
            'total_price' => 0,
            'total_cost' => 0
        );

        if (!empty($groupBy))
        {
            $where .= ' group by ' . $groupBy . ' ';
            $totalData = $this->orderProductDao->setFields(array(
                                                               'sum(num)',
                                                               'count(1)'
                                                           ))->getListWhere($where);
            $total = count($totalData);
        }
        else
        {
            $total = $this->orderProductDao->getTotal($where);
        }

        $totalInfoList = $this->orderProductDao->setFields(array(
                                                               'sum(num*price)',
                                                               'sum(num*cost)'
                                                           ))->limit(0, 1)->getListWhere($where);
        $totalInfo = array_shift($totalInfoList);
        $totalPrice = $totalInfo['sum(num*price)'];
        $totalCost = $totalInfo['sum(num*cost)'];
        $totalNum = $totalInfo['sum(num)'];

        if ($total <= 0)
        {
            return $res;
        }

        $data = $this->orderProductDao->setFields($fields)->order($order[0], $order[1])->limit($start, $num)->getListWhere($where);

        return array(
            'total' => $total,
            'total_num' => $totalNum,
            'total_price' => $totalPrice,
            'total_cost' => $totalCost,
            'data' => $data
        );
    }

    /**
     * 热卖排行
     *
     * @param       $where
     * @param int   $start
     * @param int   $num
     * @param array $fields
     *
     * @return array
     */
    public function getHotSaleList($where, $start = 0, $num = 20, $fields = array('*'))
    {
        $where .= ' and oid in (select oid from t_order where status=0 and step>=5) ';
        $total = $this->orderProductDao->getTotal($where, 'distinct(pid)');
        if ($total <= 0)
        {
            return array(
                'total' => 0,
                'data' => array()
            );
        }

        $where .= ' group by pid ';
        $data = $this->orderProductDao->limit($start, $num)->setFields($fields)->order('total', 'desc')->getListWhere($where);

        return array(
            'total' => $total,
            'data' => $data
        );
    }

    /**
     * 是否首单
     *
     * @param $orderInfo
     *
     * @return bool
     */
    public function isFristOrder($orderInfo)
    {
        $firstOrder = FALSE;

        $where = sprintf(' cid=%d AND status=%d AND step>=%d', $orderInfo['cid'], Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_NEW);

        $orders = $this->orderDao->limit(0, 1)->setFields(array('oid'))->getListWhere($where);
        if (empty($orders))
        {
            $firstOrder = TRUE;
        }

        if (!empty($orders))
        {
            $order = array_shift($orders);
            if ($order['oid'] == $orderInfo['oid'])
            {
                $firstOrder = TRUE;
            }
        }

        return $firstOrder;
    }

    /**
     * 获取最新客服确认的订单
     *
     * @param $wid
     */
    public function getLatestSureOid($wid = 0)
    {
        $where = sprintf('status=0 and step>=%d', Conf_Order::ORDER_STEP_SURE);
        if ($wid)
        {
            $where .= sprintf(' and wid=%d', $wid);
        }
        $order = 'sure_time';
        $list = $this->orderDao->order($order, 'desc')->limit(0, 1)->getListWhere($where);
        $oid = $list[0]['oid'];

        return $oid;
    }

    public function getOrderListForStatistic($cid)
    {
        assert($cid > 0);

        $fields = array(
            'cid',
            'oid',
            'ctime',
            'price',
            'refund',
            'privilege',
            'paid'
        );
        $where = sprintf('cid=%d AND status=%d AND step>=%d', $cid, Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_SURE);

        return $this->orderDao->order('oid', 'asc')->setFields($fields)->getListWhere($where);
    }


    //////////////////////////////////////////////////////////////////////////
    //////                                                            ////////
    //////                  私有方法都放在下                            /////////
    /////                                                            /////////
    //////////////////////////////////////////////////////////////////////////

    /**
     * 根据conf获取where语句
     *
     * @param array $conf
     *
     * @return string
     */
    private function _getWhereFromConf(array $conf)
    {
        $where = '1=1';
        if (!empty($conf['district']))
        {
            $where .= sprintf(' and district="%s"', mysql_escape_string($conf['district']));
        }
        if (!empty($conf['area']))
        {
            $where .= sprintf(' and area="%s"', mysql_escape_string($conf['area']));
        }
        if (isset($conf['step']))
        {
            if ($conf['step'] == Conf_Order::ORDER_STEP_ALL_SURE)
            {
                $where .= sprintf(' and step<>"%d"', Conf_Order::ORDER_STEP_EMPTY);
            }
            else
            {
                $where .= sprintf(' and step="%d"', $conf['step']);
            }
        }
        if (isset($conf['status']))
        {
            if (is_array($conf['status']))
            {
                $where .= sprintf(' and status in (%s)', implode(',', $conf['status']));
            }
            else
            {
                $where .= sprintf(' and status="%d"', $conf['status']);
            }
        }
        if (!empty($conf['cid']))
        {
            $where .= sprintf(' and cid="%d"', $conf['cid']);
        }
        if (!empty($conf['oid']))
        {
            $where .= sprintf(' and oid="%d"', $conf['oid']);
        }
        if (!empty($conf['from_date']))
        {
            $where .= sprintf(' and delivery_date>="%s 00:00:00" ', mysql_escape_string($conf['from_date']));
        }
        if (!empty($conf['end_date']))
        {
            $where .= sprintf(' and delivery_date<="%s 23:59:59" ', mysql_escape_string($conf['end_date']));
        }
        if (!empty($conf['from_time']))
        {
            $where .= sprintf(' and hour(delivery_date)>="%s"', mysql_escape_string($conf['from_time']));
        }
        if (!empty($conf['end_time']))
        {
            $where .= sprintf(' and hour(delivery_date_end)<="%s"', mysql_escape_string($conf['end_time']));
        }
        if (!empty($conf['from_ctime']))
        {
            $where .= sprintf(' and ctime>="%s 00:00:00" ', mysql_escape_string($conf['from_ctime']));
        }
        if (!empty($conf['end_ctime']))
        {
            $where .= sprintf(' and ctime<="%s 23:59:59" ', mysql_escape_string($conf['end_ctime']));
        }
        if (!empty($conf['community_id']))
        {
            $where .= sprintf(' and community_id="%d" ', $conf['community_id']);
        }
        if (!empty($conf['from_confirm_date']))
        {
            $where .= sprintf(' and sure_time>="%s 00:00:00" ', mysql_escape_string($conf['from_confirm_date']));
        }
        if (!empty($conf['end_confirm_date']))
        {
            $where .= sprintf(' and sure_time<="%s 23:59:59" ', mysql_escape_string($conf['end_confirm_date']));
        }

        if (!empty($conf['delivery_date']))
        {
            if (!empty($conf['time_interval']))
            {
                $time = strtotime($conf['delivery_date']) + $conf['time_interval'] * 3600;
                $date = date('Y-m-d H:00:00', $time);
                $where .= sprintf(' and delivery_date="%s"', $date);
            }
            else
            {
                $where .= sprintf(' and delivery_date>="%s 00:00:00" and delivery_date<="%s 23:59:59" ', mysql_escape_string($conf['delivery_date']), mysql_escape_string($conf['delivery_date']));
            }
        }
        if (!empty($conf['driver_phone']))
        {
            $where .= sprintf(' and driver_phone like "%%%s%%"', mysql_escape_string($conf['driver_phone']));
        }
        if (isset($conf['paid']))
        {
            $where .= sprintf(' and paid=%d', $conf['paid']);
        }
        if (isset($conf['wid']))
        {
            if(is_array($conf['wid']))
            {
                $where .= sprintf(' and wid in(%s)', implode(',', $conf['wid']));
            }elseif($conf['wid'] != 0) {
                $where .= sprintf(' and wid=%d', $conf['wid']);
            }
        }
        if (empty($conf['cid']) && !empty($conf['_raw_conf']))
        {
            $where .= sprintf(' and %s', $conf['_raw_conf']);
        }
        if (!empty($conf['construction']))
        {
            $where .= sprintf(' and address like "%%%s%%"', mysql_escape_string($conf['construction']));
        }
        if ($conf['maybe_late'])
        {
            $where .= sprintf(' and step<5 and delivery_date<"%s"', date("Y-m-d H:i:s", time() + 3600));
        }
        if ($conf['has_paid'])
        {
            if ($conf['has_paid'] == 1)
            {
                $where .= sprintf(' and paid="%d"', Conf_Order::HAD_PAID);
            }
            else if ($conf['has_paid'] == 2)
            {
                $where .= sprintf(' and paid!="%d"', Conf_Order::HAD_PAID);
            }
        }
        if (!empty($conf['owe']))
        {
            $where .= sprintf(' and step>="%d" and paid!=1', Conf_Order::ORDER_STEP_SURE);
        }
        if (!empty($conf['bid']))
        {
            $where .= sprintf(' and bid="%d" ', $conf['bid']);
        }
        if ($conf['is_complate'] == 1)
        {
            $where .= sprintf(' and (paid=%d or status=%d)', Conf_Order::HAD_PAID, Conf_Base::STATUS_CANCEL);
        }
        if ($conf['saler_suid'])
        {
            $where .= sprintf(' and saler_suid=%d', $conf['saler_suid']);
        }
        if ($conf['sure_suid'])
        {
            $where .= sprintf(' and sure_suid=%d', $conf['sure_suid']);
        }
        if ($conf['print'] == -1)
        {
            $where .= sprintf(' and has_print<=0');
        }
        if ($conf['source'] != 0)
        {
            if ($conf['source'] == 1)
            {
                $conf['source'] = 0;
            }
            $where .= sprintf(' and source=%d', $conf['source']);
        }
        if (1 == $conf['source_oid'])
        {
            $where .= sprintf(' and source_oid>0');
        }
        if (!empty($conf['suid']))
        {
            if ($conf['suid'] == 1)
            {
                $where .= sprintf(' AND suid>0');
            }
            else
            {
                $where .= sprintf(' AND suid=%d', $conf['suid']);
            }
        }
        if ($this->is($conf['is_guaranteed']))
        {
            $where .= sprintf(' AND is_guaranteed=%d', $conf['is_guaranteed']);
        }
        if ($this->is($conf['city_id']))
        {
            $where .= sprintf(' AND city_id=%d', $conf['city_id']);
        }
        if (!empty($conf['back_unpaid']))
        {
            $where .= sprintf(' AND paid!=%d AND date(back_time)="%s"', Conf_Order::HAD_PAID, date('Y-m-d'));
        }

        return $where;
    }

    public function search4Business($bid, $cid, $searchConf, $start = 0, $num = 20)
    {
        assert(!empty($bid));

        $where = 'status=' . Conf_Base::STATUS_NORMAL;

        if (!empty($cid))
        {
            $where .= sprintf(' and cid=%d', $cid);
        }
        else
        {
            $where .= sprintf(' and cid in  (select cid from t_customer where bid=%d)', $bid);
        }

        if ($this->is($searchConf['has_paid']) && $searchConf['has_paid'] != 999)
        {
            if ($searchConf['has_paid'] == 1)
            {
                $where .= sprintf(' and paid=1');
            }
            else
            {
                $where .= sprintf(' and paid!=1');
            }
        }

        if ($this->is($searchConf['address']))
        {
            $where .= sprintf(' and address like "%%%s%%"', $searchConf['address']);
        }

        if ($this->is($searchConf['from_date']))
        {
            $where .= sprintf(' and delivery_date>="%s 00:00:00" ', mysql_escape_string($searchConf['from_date']));
        }
        if ($this->is($searchConf['end_date']))
        {
            $where .= sprintf(' and delivery_date<="%s 23:59:59" ', mysql_escape_string($searchConf['end_date']));
        }

        if ($this->is($searchConf['step']))
        {
            $where .= sprintf(' and step=%d', $searchConf['step']);
        }
        else
        {
            $where .= sprintf(' and step>=%d', Conf_Order::ORDER_STEP_NEW);
        }

        $total = $this->orderDao->getTotal($where);

        $data = array();
        if ($total)
        {
            $data = $this->orderDao->order('oid', 'desc')->limit($start, $num)->getListWhere($where);
        }

        return array(
            'total' => $total,
            'data' => $data
        );
    }

    /**
     * 判断订单有没有优惠
     *
     * @param $oid
     * @param $type
     */

    public function hasPrivilegeOfOrder($oid, $type)
    {
        $res = $this->orderPrivilegeDao->setSlave()->getTotal('oid="' . $oid . '" AND type="' . $type . '"');
        if ($res > 0)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * 获取客户已参加某个优惠活动的次数
     */
    public function getCustomerOrderPrivilegeCount($cid, $type, $activity_id, $oid)
    {
        $where = sprintf("p.cid=%d AND p.type=%d AND p.activity_id=%d", $cid, $type, $activity_id);
        if($oid >0)
        {
            $where .= sprintf(" AND p.oid <>%d", $oid);
        }
        $where .= sprintf(" AND o.status=%d", Conf_Base::STATUS_NORMAL);
        // 查询结果
        $data = $this->one->setDBMode()->select('t_order_privilege as p left join t_order as o USING(oid)', array('count(oid)'), $where);
        if (empty($data['data']))
        {
            return 0;
        }

        return $total = intval($data['data'][0]['count(oid)']);
    }

    /**
     * 获取促销活动订单列表
     */
    public function getPromotionOrdersByActivityId($aid, $order, $start, $num)
    {
        $pa = new Activity_Promotion_Manjian();
        $promotion_info = $pa->getItem($aid);
        if ($promotion_info['activity_type'] == Conf_Activity::AT_PROMOTION_TYPE_DISCOUNT)
        {
            $where = 'from_oid>0 AND aid=' . $aid;
            $cc = new Data_Dao('t_coupon');
            $total = $cc->getTotal($where, 'DISTINCT(from_oid)');

            // 查询数量
            if ($total <= 0)
            {
                return array(
                    'total' => $total,
                    'list' => array()
                );
            }
            if (empty($order))
            {
                $order = 'order by tc.from_oid desc';
            }
            $where2 = 'tc.from_oid>0 AND tc.aid=' . $aid;
            // 查询结果
            $data = $this->one->setDBMode()->select('t_coupon as tc left join t_order as o ON o.oid=tc.from_oid', array('DISTINCT(tc.from_oid),o.*'), $where2, $order, $start, $num);

            if (empty($data['data']))
            {
                return array();
            }

            $field = 'price+freight+customer_carriage-privilege-refund-real_amount';
            $where3 = 'oid IN(SELECT DISTINCT(from_oid) FROM t_coupon WHERE ' . $where . ')';
        }
        else
        {
            // 查询数量
            $where = 'activity_id=' . $aid;
            $total = $this->orderPrivilegeDao->getTotal($where);
            if ($total <= 0)
            {
                return array(
                    'total' => $total,
                    'list' => array()
                );
            }
            if (empty($order))
            {
                $order = 'order by p.oid desc';
            }
            $where2 = 'p.' . $where;

            // 查询结果
            $data = $this->one->setDBMode()->select('t_order_privilege as p left join t_order as o ON o.oid=p.oid', array('o.*'), $where2, $order, $start, $num);
            if (empty($data['data']))
            {
                return array();
            }

            $field = 'price+freight+customer_carriage-privilege-refund-real_amount';
            $where3 = 'oid IN(SELECT oid FROM t_order_privilege WHERE ' . $where . ')';
        }
        $list = $data['data'];
        $sum = 0;
        $priceTotal = 0;

        $sum = $this->orderDao->getSum($field, $where3);
        $field = 'price';
        $priceTotal = $this->orderDao->getSum($field, $where3);

        //把录单人suid转换成名称信息
        $as = new Admin_Staff();
        $as->appendSuers($list);
        $as->appendSuers($list, 'sure_suid', '', TRUE);    //客服确认人
        $as->appendSuers($list, 'saler_suid', '', TRUE);    //销售
        //格式化订单信息 日期, 状态
        Order_Helper::formatOrders($list);

        return array(
            'total' => $total,
            'list' => $list,
            'sum' => $sum,
            'priceTotal' => $priceTotal
        );
    }

    /**
     * 获取券使用订单列表
     */
    public function getPromotionOrdersByCouponId($tid, $order, $start, $num)
    {
        $cc = new Data_Dao('t_coupon');
        // 查询数量
        $where = 'oid>0 AND tid=' . $tid;
        $total = $cc->getTotal($where);
        if ($total <= 0)
        {
            return array(
                'total' => $total,
                'list' => array()
            );
        }
        if (empty($order))
        {
            $order = 'order by tc.oid desc';
        }
        $where2 = 'tc.oid>0 AND tc.tid=' . $tid;

        // 查询结果
        $data = $this->one->setDBMode()->select('t_coupon as tc left join t_order as o ON o.oid=tc.oid', array('o.*'), $where2, $order, $start, $num);
        if (empty($data['data']))
        {
            return array();
        }

        $field = 'price+freight+customer_carriage-privilege-refund-real_amount';
        $where3 = 'oid IN(SELECT oid FROM t_coupon WHERE ' . $where . ')';

        $list = $data['data'];
        $sum = 0;
        $priceTotal = 0;

        $sum = $this->orderDao->getSum($field, $where3);
        $field = 'price';
        $priceTotal = $this->orderDao->getSum($field, $where3);

        //把录单人suid转换成名称信息
        $as = new Admin_Staff();
        $as->appendSuers($list);
        $as->appendSuers($list, 'sure_suid', '', TRUE);    //客服确认人
        $as->appendSuers($list, 'saler_suid', '', TRUE);    //销售
        //格式化订单信息 日期, 状态
        Order_Helper::formatOrders($list);

        return array(
            'total' => $total,
            'list' => $list,
            'sum' => $sum,
            'priceTotal' => $priceTotal
        );
    }

    public function getOrderProductOfPicking($sid, $wid)
    {
        //获取订单信息
        $referDeliveryData = date("Y-m-d", strtotime("-1 month"));

        $where = sprintf('status=0 and wid=%d and step>=%d and step<%d and delivery_date>"%s 00:00:00"', $wid, Conf_Order::ORDER_STEP_SURE, Conf_Order::ORDER_STEP_PICKED, $referDeliveryData);
        $orders = $this->orderDao->setFields(array(
                                                 'oid',
                                                 'delivery_date',
                                                 'delivery_date_end'
                                             ))->getListWhere($where);
        //获取订单商品信息
        $oids = Tool_Array::getFields($orders, 'oid');
        $list = array();
        if (!empty($oids))
        {
            $where = sprintf('sid=%d and rid=0 and status=0 and oid in (%s)', $sid, implode(',', $oids));
            $list = $this->orderProductDao->getListWhere($where);

            //补充
            foreach ($list as &$item)
            {
                $oid = $item['oid'];
                $order = $orders[$oid];
                $item['delivery_date_end'] = $order['delivery_date_end'];
            }
        }

        return $list;
    }

    public function getByRids($rids)
    {
        return $this->orderDao->getList($rids);
    }

    public function getByAfterSaleId($afterSaleId, $afterSaleType)
    {
        $where = sprintf('status = %d and aftersale_type= %d and aftersale_id = %d', Conf_Base::STATUS_NORMAL, $afterSaleType, $afterSaleId);
        if (is_array($afterSaleId))
        {
            $where = sprintf('status = %d and aftersale_type= %d and aftersale_id IN ("%s")', Conf_Base::STATUS_NORMAL, $afterSaleType, implode('","', $afterSaleId));
        }

        return $this->orderDao->getListWhere($where);
    }

    /**
     * 获取 所有的客户已回单并已付款的总金额
     *
     *
     */
    public function getAllCustomerAmount($cids)
    {
        $fields = array('sum(price+freight+customer_carriage-privilege-refund) as amount', 'cid');
        if(!empty($cids)){
            $where = sprintf(' status=%d AND step=%d AND paid=%d AND cid IN(%s) GROUP BY cid', Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_FINISHED, Conf_Order::HAD_PAID, implode(',', $cids));
        }else{
            $where = sprintf(' status=%d AND step=%d AND paid=%d GROUP BY cid', Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_FINISHED, Conf_Order::HAD_PAID);
        }
        $order = 'order by cid desc';
        $list = $this->one->select('t_order', $fields, $where, $order);
        return Tool_Array::list2Map($list['data'], 'cid');
    }
    
    /**
     * 客服绩效
     * @author wangxuemin
     * @param array $conf
     * @param array $fields
     * @param array $suser
     * @return array
     */
    public function getAchievement(array $conf, $fields = array('*'), $suser = array())
    {
        if (empty($order))
        {
            $order = array(
                'oid',
                'desc'
            );
        }
        if (!empty($conf['maybe_ate']))
        {
            $order = array(
                'delivery_date',
                'asc'
            );
        }
        $where = $this->_getWhereFromConf($conf);
        $where .= sprintf(' and step>=5 ');
        return $this->orderDao->setSlave()->setFields($fields)->getListWhere($where);
    }
}
