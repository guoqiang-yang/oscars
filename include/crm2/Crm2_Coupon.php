<?php
/**
 * 优惠券
 */
class Crm2_Coupon extends Base_Func
{
	public function add(array $info)
	{
		assert( !empty($info) );

		$info['ctime'] = $info['mtime'] = date('Y-m-d H:i:s');
		$res = $this->one->insert('t_coupon', $info);
		$uid = $res['insertid'];
		return $uid;
	}

	public function delete($id)
	{
		$id = intval($id);
		assert($id > 0);

		$where = array('id' => $id);
		$update = array('status' => Conf_Base::STATUS_DELETED);
		$ret = $this->one->update('t_coupon', $update, array(), $where);
		return $ret['affectedrows'];
	}

	public function update($id, array $info)
	{
		assert( !empty($id) );
		assert( !empty($info) );

		$where = array('id' => $id);
		$ret = $this->one->update('t_coupon', $info, array(), $where);
		return $ret['affectedrows'];
	}

	public function updateByOid($oid, array $info)
	{
		assert( !empty($oid) );
		assert( !empty($info) );

		$where = array('oid' => $oid);
		$ret = $this->one->update('t_coupon', $info, array(), $where);
		return $ret['affectedrows'];
	}
    
    /**
     * 标记优惠券，建立订单和优惠券关系 (预使用状态).
     * 
     * @param int $cid
     * @param int $oid
     * @param array $couponIds
     */
    public function markCouponByOid($cid, $oid, $couponIds, $isOccupied=true)
    {
        assert( !empty($cid) );
        assert( !empty($oid) );
        assert( !empty($couponIds) );
        
        $where = "cid=$cid and id in (". implode(',', $couponIds). ")";
        $info['oid'] = $oid;
        $info['occupied'] = $isOccupied? 1: 0;
        
        $ret = $this->one->update('t_coupon', $info, array(), $where);
        
        return $ret['affectedrows'];
    }

    public function get($id)
	{
		$id = intval($id);
		assert($id > 0);

		$where = array('id' => $id);
		$data = $this->one->select('t_coupon', array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'][0];
	}

	public function getList($where, $fields=array('*'), $order='', $start=0, $num=20)
	{
		$where = !empty($where)? $where: '1=1';
		$where .= ' and status=0';
        //查询数量
        $cdata = $this->one->select('t_coupon', array('count(1)'), $where);
        $total = intval($cdata['data'][0]['count(1)']);
        if (empty($total))
        {
            return array('total'=>0, 'data'=>array());
        }
        
		//查询结果 list
        if (empty($order)){
            $order = 'order by id desc';
        }
        if (empty($fields) || !is_array($fields)){
            $fields = array('*');
        }
		
        $ldata = $this->one->select('t_coupon', $fields, $where, $order, $start, $num);
        
        return array('total'=>$total, 'data'=>$ldata['data']);
	}
	
	public function getOfCustomer($cid, $all=false, $audit=true, $used=false, $expire=false)
	{
		assert(!empty($cid));

		$where = is_array($cid) ? sprintf( 'cid in (%s)', implode(',', $cid) ):sprintf( 'cid=%d', $cid );
		if (!$all)
		{
			$where .= sprintf(' and status=%d', $audit ? Conf_Base::STATUS_NORMAL:Conf_Base::STATUS_WAIT_AUDIT);
			$where .= sprintf(' and used=%d', $used ? 1:0);

			if ($expire)
			{
				$where .= sprintf(' and deadline<"%s"', date('Y-m-d H:i:s'));
			}
			else
			{
				$where .= sprintf(' and deadline>"%s"', date('Y-m-d H:i:s'));
			}
		}

		$order = 'order by amount desc, used asc';
		$data = $this->one->select('t_coupon', array('*'), $where, $order);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'];
	}

	public function getOfCustomerType($cid, $type)
	{
		assert(!empty($cid));

		$where = is_array($cid) ? sprintf( 'cid in (%s)', implode(',', $cid) ):sprintf( 'cid=%d', $cid );
		$where .= sprintf(' and type=%d', $type);

		$order = 'order by amount desc';
		$data = $this->one->select('t_coupon', array('*'), $where, $order);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'];
	}

	public function appendCoupon(&$list, $field = 'cid')
	{
		$cids = Tool_Array::getFields($list, $field);
		if(empty($cids)) return;

		$couponList = $this->getOfCustomer($cids);

		$couponMap = array();
		foreach ($couponList as $coupon)
		{
			$cid = $coupon['cid'];
			$couponMap[$cid]['num'] += 1;
			$couponMap[$cid]['amount'] += $coupon['amount'];
			$couponMap[$cid][$coupon['amount']] ++;
		}

		foreach ($list as &$item)
		{
			$cid = $item[$field];
			if (!isset($couponMap[$cid]))
			{
				$item['_coupon'] = array('num'=>0, 'amount'=>0);
			}
			else
			{
				$item['_coupon'] = $couponMap[$cid];
			}
		}
	}
}
