<?php
/**
 * 订单信息相关业务逻辑
 */
class Order_Refund extends Base_Func
{
    private $orderProductDao;
    private $refundDao;

    public function __construct()
    {
        $this->orderProductDao = new Data_Dao('t_order_product');
        $this->refundDao = new Data_Dao('t_refund');

        parent::__construct();
    }

    public function add(array $info)
    {
        assert(!empty($info));
        
        assert( !empty($info['oid'])&&!empty($info['cid'])&&!empty($info['uid'])&&!empty($info['wid']) );
        
        if (empty($info['note'])) $info['note'] = '';

        return $this->refundDao->add($info);
    }

    public function delete($rid)
    {
        $res = $this->refundDao->delete($rid);
        if ($res)
        {
            $where = array('rid' => $rid);
            $this->orderProductDao->deleteWhere($where);
        }

        return $res;
    }

    public function rebut($rid)
    {
        $res = $this->refundDao->update($rid, array('status' => Conf_Base::STATUS_UN_AUDIT, 'freight' => 0, 'carry_fee' => 0));
        if ($res)
        {
            $where = array('rid' => $rid);
            $this->orderProductDao->updateWhere($where, array('status' => Conf_Base::STATUS_UN_AUDIT));
        }

        return $res;
    }

    public function update($rid, $info)
    {
        return $this->refundDao->update($rid, $info);
    }

    public function get($rid)
    {
       return $this->refundDao->get($rid);
    }

    public function getBulk($rids)
    {
        return $this->refundDao->getList($rids);
    }

    /**
     * 获取退款单商品列表
     *
     * @param $rid
     * @return array
     */
    public function getProductsOfRefund($rid)
    {
        if(empty($rid))
        {
            return array();
        }

        $where = sprintf(" rid='%d' AND status!='%d' AND num>0 ", $rid, Conf_Base::STATUS_DELETED);

        return $this->orderProductDao->getListWhere($where);
    }

    /**
     * 更新退款单商品
     *
     * @param $rid
     * @param $products
     * @param $wid
     * @return bool
     */
    public function updateRefundProducts($rid, $products, $wid)
    {
        $rid = intval($rid);
        assert($rid > 0);
        assert(!empty($products));

        //查询原有products
        $oldProducts = $this->getProductsOfRefund($rid);

        //原来有,现在没有的,删除
        $oldPids = Tool_Array::getFields($oldProducts, 'pid');
        $newPids = Tool_Array::getFields($products, 'pid');
        $delPids = array_diff($oldPids, $newPids);
        foreach ($delPids as $pid)
        {
            $where = array('rid' => $rid, 'pid' => $pid);
            $this->orderProductDao->deleteWhere($where);
        }

        if (Conf_Base::switchForManagingMode())
        {
            $pids = Tool_Array::getFields($products, 'pid');
            $sp =new Shop_Product();
            $productInfo = $sp->getBulk($pids);
        }
        
        //添加或更新
        foreach ($products as $product)
        {
            if ($product['num'] <= 0)
            {
                $where = array('rid' => $rid, 'pid' => $product['pid']);
                $this->orderProductDao->deleteWhere($where);
            }
            else
            {
                $product['rid'] = $rid;
                $product['wid'] = $wid;
                $product['status'] = Conf_Base::STATUS_NORMAL;
                $update = array('price', 'num', 'status', 'wid', 'apply_rnum');
                if (Conf_Base::switchForManagingMode())
                {
                    $product['managing_mode'] = $productInfo[$product['pid']]['managing_mode'];
                }
                $this->orderProductDao->add($product, $update);
            }
        }

        return true;
    }
    
    public function updateRefundProductByWhere($where, $upData, $chgData=array())
    {
        assert(!empty($where));
        assert(!empty($upData) || !empty($chgData));
        
        $affectedrows = $this->orderProductDao->updateWhere($where, $upData, $chgData);
        
        return $affectedrows;
    }
    
    /**
     * 更新退货商品, 退货商品不存在，插入改商品.
     * 
     * @param type $oid
     * @param type $pid
     * @param type $rid
     * @param type $upProductData
     */
    public function updateRefundProductWithInsert($oid, $pid, $rid, $upProductData)
    {
        assert(!empty($oid) && !empty($pid) && !empty($rid));
        assert(!empty($upProductData));
        
        $_where = 'oid='.$oid.' and pid='.$pid;
        $orderAndRefundProducts = $this->orderProductDao->getListWhere($_where);
        
        $productInOrder = array();
        $isUpdate = false;
        foreach($orderAndRefundProducts as $pinfo)
        {
            if ($pinfo['rid'] == 0)
            {
                $productInOrder = $pinfo;
                continue;
            }
            
            if ($pinfo['rid'] == $rid) //退货商品存在：（也包括之前被删除的商品）
            {
                if($pinfo['status'] != Conf_Base::STATUS_NORMAL)
                {
                    $upProductData['status'] = Conf_Base::STATUS_NORMAL;
                }
                
                $where = 'oid='.$oid.' and pid='.$pid.' and rid='.$rid;
                $this->orderProductDao->updateWhere($where, $upProductData);
                $isUpdate = true;
                break;
            }
        }
        
        if (!$isUpdate)
        {
            $num = 0;
            if (isset($upProductData['num']))
            {
                $num = $upProductData['num'];
            }
            else if (isset($upProductData['apply_rnum']))
            {
                $num = $upProductData['apply_rnum'];
            }
            else
            {
                if (isset($upProductData['picked']))
                {
                    $num = $upProductData['picked'];
                }
                if (isset($upProductData['damaged_num']))
                {
                    $num += $upProductData['damaged_num'];
                }
            }
            
            $insertData = array(
                'oid' => $oid,
                'pid' => $pid,
                'rid' => $rid,
                'num' => $num,
                'price' => $productInOrder['price'],
                'cost' => $productInOrder['cost'],
                'wid' => $productInOrder['wid'],
                'sid' => $productInOrder['sid'],
                'city_id' => $productInOrder['city_id'],
            );
            $this->orderProductDao->add(array_merge($insertData, $upProductData));
        }
        
    }

    /**
     * 获取用户的退款单金额和数量
     *
     * @param $cid
     * @return array
     */
    public function getSummaryOfCustomer($cid)
    {
        $cid = intval($cid);
        assert($cid > 0);

        $summary = array('total' => 0, 'amount' => 0);
        $where = array('cid' => $cid, 'status' => Conf_Base::STATUS_NORMAL);

        $datas = $this->refundDao->setFields(array('count(1)', 'sum(price)'))->limit(0, 1)->getListWhere($where);
        $data = array_shift($datas);
        $summary['total'] = intval($data['count(1)']);
        $summary['amount'] = intval($data['sum(price)']);

        return $summary;
    }

    /**
     * 根据条件获取退款单列表
     *
     * @param array $conf
     * @param $total
     * @param int $start
     * @param int $num
     * @return array
     */
    public function getList(array $conf, &$total, $start = 0, $num = 20, $status = '')
    {
        $where = $this->_getWhereByConf($conf, $status);
        $total = $this->refundDao->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }

        return $this->refundDao->order('rid', 'desc')->limit($start, $num)->getListWhere($where);
    }

	public function getListByWhere($where, &$total, $start = 0, $num = 20, $field=array('*'))
	{
		$total = $this->refundDao->getTotal($where);
		if ($total <= 0)
		{
			return array();
		}

		return $this->refundDao->setFields($field)->order('rid', 'desc')->limit($start, $num)->getListWhere($where);
	}

    public function getByRawWhere($kind, $where, $field = array('*'), $order = '')
    {
        $ret = $this->one->setDBMode()->select($kind, $field, $where, $order);

        return $ret['data'];
    }
    
    public function getTotalPrice($conf, $status = '')
    {
        $field = 'price+refund_carry_fee+refund_freight-refund_privilege-adjust';
        $where = $this->_getWhereByConf($conf, $status);
        
        return $this->refundDao->getSum($field, $where);
    }

    public function getSumByWhere($where, $field)
    {
        return $this->refundDao->setSlave()->getSum($field, $where);
    }

    /**
     * 获取用户的退款单列表
     *
     * @param $cid
     * @param $total
     * @param int $start
     * @param int $num
     * @return array
     */
    public function getListOfCustomer($cid, &$total, $start = 0, $num = 20, $status = '')
    {
        $cid = intval($cid);
        assert($cid > 0);

        $conf = array('cid' => $cid);
        $where = $this->_getWhereByConf($conf, $status);

        $total = $this->refundDao->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }

        return $this->refundDao->order('rid', 'desc')->limit($start, $num)->getListWhere($where);
    }

    /**
     * 获取订单的退款单
     *
     * @param $oid
     * @return array
     */
    public function getListOfOrder($oid, $status = '')
    {
        assert(!empty($oid));

        $conf = array('oid' => $oid);
        $where = $this->_getWhereByConf($conf, $status);

        return $this->refundDao->order('oid', 'desc')->getListWhere($where);
    }

    /**
     * 统计客户退货的相关数据.
     * 
     * @param array/int $cids
     */
    public function statRefundDatas4Customers($cids)
    {
        $strCids = is_array($cids)? implode(',', $cids): $cids;
                
        if (empty($strCids)) return array();
        
        $where = sprintf('status=0 and cid in (%s) and paid in (%d, %d)', 
                    $strCids, Conf_Refund::HAD_PAID, Conf_Refund::HAD_AUDIT);
        $groupby = ' group by cid';
        $fields = array('cid', 'sum(price) as refund_amount', 'count(1) as refund_order_num');
        
        return $this->refundDao->setFields($fields)->getListWhere($where.$groupby, false);
    }

////////////////////////////////////////////////////////////////////
/////////          私有方法                                  ////////
////////////////////////////////////////////////////////////////////

    /**
     * 解析conf条件到where
     *
     * @param $conf
     * @return string
     */
    private function _getWhereByConf($conf, $status = '')
    {
        if($status == 'rebut')
        {
            $where = 'status=' . Conf_Base::STATUS_UN_AUDIT;
        }elseif($status == 'all' ){
            $where = 'status<>'. Conf_Base::STATUS_DELETED;
        }else{
            $where = 'status=' . Conf_Base::STATUS_NORMAL;
        }
        
        // 按照rid查询，忽略其他的查询条件
        if ($this->is($conf['rid']))
        {
            $where .= ' and rid='. $conf['rid'];
            
            return $where;
        }
        
        if (!empty($conf['step']))
        {
            if ($conf['step'] == Conf_Refund::REFUND_STEP_PART_SHELVED)
            {
                $where .= sprintf(' and step in (%d, %d)', 
                        Conf_Refund::REFUND_STEP_IN_STOCK, Conf_Refund::REFUND_STEP_PART_SHELVED);
            }
            else if (isset($conf['paid']) && $conf['paid']==0)
            {
                $where .= sprintf(' and step >= '. $conf['step']);
            }
            else
            {
                $where .= sprintf(' and step="%d"', $conf['step']);
            }
        }
        if (!empty($conf['cid']))
        {
            $where .= sprintf(' and cid="%d"', $conf['cid']);
        }
        if (!empty($conf['uid']))
        {
            $where .= sprintf(' AND uid="%d"', $conf['uid']);
        }
        if (!empty($conf['oid']))
        {
            if (is_array($conf['oid']))
            {
                $where .= sprintf(' and oid in ("%s")', implode('","', $conf['oid']));
            }
            else
            {
                $where .= sprintf(' and oid="%d"',  $conf['oid']);
            }
        }
        if (!empty($conf['date']))
        {
            $where .= sprintf(' and date(ctime)=date("%s")', mysql_escape_string($conf['date']));
        }
        if (isset($conf['wid']) && !empty($conf['wid']))
        {
            $where .=sprintf(' and wid=%d', $conf['wid']);
        }
        if (!empty($conf['_raw_conf']))
        {
            $where .= sprintf(' and %s', $conf['_raw_conf']);
        }
	    if (!empty($conf['from_date']))
	    {
		    $where .= sprintf(' and date(ctime)>=date("%s")', mysql_escape_string($conf['from_date']));
	    }
	    if (!empty($conf['end_date']))
	    {
		    $where .= sprintf(' and date(ctime)<=date("%s")', mysql_escape_string($conf['end_date']));
	    }
	    if (!empty($conf['sales_suid']))
	    {
		    $oidSql = sprintf('select oid from t_order where status=0 and saler_suid=%d', mysql_escape_string($conf['sales_suid']));
		    $where .= sprintf(' and oid in (%s)', $oidSql);
	    }
        if (!empty($conf['city_id']))
        {
            $where .= sprintf(' and city_id=%d', $conf['city_id']);
        }
        if (isset($conf['paid']) && $conf['paid']!=Conf_Base::STATUS_ALL)
        {
            if ($conf['paid'] == Conf_Refund::UN_PAID)
            {
                $where .= sprintf(' and paid=%d', Conf_Refund::HAD_AUDIT);
            }
            else if ($conf['paid'] == Conf_Refund::HAD_AUDIT)
            {
                $where .= sprintf(' and paid=%d and step>=%d', Conf_Refund::UN_PAID, Conf_Refund::REFUND_STEP_IN_STOCK);
            }
            else
            {
                $where .= sprintf(' and paid=%d', $conf['paid']);
            }
        }
        if (!empty($conf['type']))
        {
            $where .= sprintf(' and type = %d', $conf['type']);
        }
        if (!empty($conf['reason_type']))
        {
            $where .= sprintf(' and reason_type = %d', $conf['reason_type']);
        }
        if (!empty($conf['reason']))
        {
            $where .= sprintf(' and reason = %d', $conf['reason']);
        }
        if (!empty($conf['from_in_finance_date']))
        {
            $where .= sprintf(' and date(to_finance_time)>=date("%s")', mysql_escape_string($conf['from_in_finance_date']));
        }
        if (!empty($conf['end_in_finance_date']))
        {
            $where .= sprintf(' and date(to_finance_time)<=date("%s")', mysql_escape_string($conf['end_in_finance_date']));
        }
        
        return $where;
    }
}
