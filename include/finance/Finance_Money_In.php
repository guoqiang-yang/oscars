<?php
/**
 * 收款单记录.
 */

class Finance_Money_In extends Base_Func
{
	const TABLE = 't_money_in_history';
	
	/**
	 * 插入数据.
	 * 
	 * @param array $data
	 *	
	 * @notice $data['objid'] 销售单: oid; 退款单: rid; 财务入账/调帐: 0
	 */
	public function add(array $data)
	{
		assert(!empty($data));
		assert(isset($data['cid']));
		assert(isset($data['objid']));
		assert(isset($data['price']));
		assert(isset($data['type']));
		
		// 取最近一条记录，用于计算amount
		$recentData = $this->getRecentOfUser($data['cid']);
		$_amount = isset($recentData['amount'])? $recentData['amount']: 0;
        
        $data['city_id'] = $this->_getCityId4AddMoneyInData($data);
		$data['amount'] = $_amount + $data['price'];
		$data['ctime'] = isset($data['ctime'])? $data['ctime']: date('Y-m-d H:i:s');
		if (empty($data['note']))
		{
			$data['note'] = '';
		}
		$res = $this->one->insert(self::TABLE, $data);
		$id = $res['insertid'];
		
		return array('id'=>$id, 'insert_data'=>$data);
	}
    
    private function _getCityId4AddMoneyInData($moneyInData)
    {
        if (!empty($moneyInData['city_id'])) return $moneyInData['city_id'];
        
        if (!empty($moneyInData['wid'])) return Appconf_Warehouse::getCityid4Wid ($moneyInData['wid']);
        
        if (!empty($moneyInData['objid']))
        {
            if ($moneyInData['type']==Conf_Money_In::FINANCE_REFUND)
            {
                $or = new Order_Refund();
                $refund = $or->get($moneyInData['objid']);
                
                return $refund['city_id'];
            }
            else 
            {
                $oo = new Order_Order();
                $order = $oo->get($moneyInData['objid']);
                
                return $order['city_id'];
            }
        }
        
        if (!empty($moneyInData['oid']))
        {
            $oo = new Order_Order();
            $order = $oo->get($moneyInData['oid']);

            return $order['city_id'];
        }
        
        return 0;
    }
	
	public function update($where, $info, $change=array())
	{
		assert( !empty($where));
		assert( !empty($info) || !empty($change));
		
		$ret = $this->one->update(self::TABLE, $info, $change, $where);
		
		return $ret['affectedrows'];
	}
	
	public function getByObjid($objid, $type)
	{
		$where = sprintf('objid=%d AND type=%d AND status=%d', $objid, $type, Conf_Base::STATUS_NORMAL);
		
		$ret = $this->one->select(self::TABLE, array('*'), $where);
		if (!empty($ret['data']))
		{
			return $ret['data'][0];
		}
		
		return array();
	}
    
    public function getByObjidAllData($objid, $type=null)
    {
        if (!is_null($type))
        {
            $where = sprintf('objid=%d AND type=%d AND status=%d', $objid, $type, Conf_Base::STATUS_NORMAL);
        }
        else
        {
            $where = sprintf('objid=%d AND status=%d', $objid, Conf_Base::STATUS_NORMAL);
        }
		$ret = $this->one->select(self::TABLE, array('*'), $where);
		
        return $ret['data'];
    }
    
    public function getById($id)
    {
        $where = 'id='. $id;
        
        $ret = $this->one->select(self::TABLE, array('*'), $where);
        if (!empty($ret['data']))
		{
			return $ret['data'][0];
		}
		
		return array();
    }
    
    public function openGet($where, $field=array('*'), $order='', $start=0, $num=0)
    {
        $total = 0;
        if ($num)
        {
            $tRet = $this->one->select(self::TABLE, array('count(1)'), $where);
            $total = $tRet['data'][0]['count(1)'];
        }
        
        $_order = !empty($order)? $order: 'order by id desc';
        $dRet = $this->one->select(self::TABLE, $field, $where, $_order, $start, $num);
        
        return array('total'=>$total, 'data'=>$dRet['data']);
    }

	/**
	 * 取最近的一条记录.
	 * 
	 * @param int $cid 客户id
	 */
	public function getRecentOfUser($cid)
	{
		$where = 'status=0 and cid='.$cid;
		$order = 'order by id desc';
		
		$ret = $this->one->select(self::TABLE, array('*'), $where, $order, 0, 1);
		
		return !empty($ret['data'])? $ret['data'][0]: array();
	}
	
	public function getCustomerBillList($searchConf, $order='', $start=0, $num=20)
	{
		$_order = !empty($order)? $order: 'order by id desc';
		
		$where = 'status=0';
		if (!empty($searchConf['cid']))
		{
			$where .= ' and cid='. $searchConf['cid'];
		}
		if (isset($searchConf['from_date']) && !empty($searchConf['from_date']))
		{
			$where .= ' and date(ctime)>=date("'. $searchConf['from_date']. '")';
		}
		if (isset($searchConf['end_date']) && !empty($searchConf['end_date']))
		{
			$where .= ' and date(ctime)<=date("'. $searchConf['end_date']. '")';
		}
		if (isset($searchConf['type']) && $searchConf['type']!=Conf_Base::STATUS_ALL)
        {
            $where .= !is_array($searchConf['type'])? ' and type='. $searchConf['type']:
                        ' and type in ('. implode(',', $searchConf['type']). ')';
        }
        if($this->is($searchConf['wid']))
        {
            $where .= ' and wid='. $searchConf['wid'];
        }
        if ($this->is($searchConf['payment_type']))
        {
            if (is_array($searchConf['payment_type']))
            {
                $where .= sprintf(' and payment_type in (%s)', implode(',', $searchConf['payment_type']));
            }
            else
            {
                $where .= sprintf(' and payment_type="%d" ', $searchConf['payment_type']);
            }
        }
        
		$countRet = $this->one->select(self::TABLE, array('count(1)'), $where);
		$total = $countRet['data'][0]['count(1)'];
		$retList = $this->one->select(self::TABLE, array('*'), $where, $_order, $start, $num);
		
		return array('total'=>$total, 'list'=>$retList['data']);
	}
    
    public function getCustomerListForFinance($table, $where, $group, $start=0, $num=20)
    {
        $order = 'order by amount desc';
        
        $countRet = $this->one->select($table, array('count(1)'), $where.$group);
        
        $totalPrice = $this->one->select($table, array('sum(price)'), $where);
    
		$total = $countRet['rownum'];
		$retList = $this->one->select($table, array('*'), $where.$group, $order, $start, $num);
		
		return array('total'=>$total, 'list'=>$retList['data'], 'total_price'=>$totalPrice['data'][0]['sum(price)']);
    }
}