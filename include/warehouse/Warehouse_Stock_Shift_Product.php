<?php
/**
 * 移库单
 */
class Warehouse_Stock_Shift_Product extends Base_Func
{
	const TABLE = 't_stock_shift_product';
	
	public function add($ssid, $products)
	{
		assert($ssid);
		assert($products);
        
		$updateFields = array('num', 'from_location');

		foreach($products as $one)
		{
			$one['ssid'] = $ssid;
			$one['ctime'] = date('Y-m-d H:i:s');
			$ret = $this->one->insert(self::TABLE, $one, $updateFields);
		}
			
		return $ret['affectedrows'];
	}
	
    public function update($ssid, $sid, $upData)
    {
        assert(!empty($ssid) && !empty($sid));
        assert(!empty($upData));
        
        $where = array(
            'status' => Conf_Base::STATUS_NORMAL,
            'ssid' => $ssid,
            'sid' => $sid,
        );
        $ret = $this->one->update(self::TABLE, $upData, array(), $where);
        
        return $ret['affectedrows'];
    }
    
    public function delShiftAllProduct($ssid)
    {
        assert(!empty($ssid));
        
        $where = 'ssid='. $ssid;
        
        $upData = array(
            'status' => Conf_Base::STATUS_DELETED,
            'from_location' => '',
            'vnum' => 0,
        );
        
        $ret = $this->one->update(self::TABLE, $upData, array(), $where);
        
        return $ret['affectedrows'];
    }
    
	public function get($ssid, $order = '')
	{
		assert($ssid > 0);
		
		$where = array('ssid' => $ssid, 'status'=>0);
		$ret = $this->one->select(self::TABLE, array('*'), $where, $order);
		
		return $ret['data'];
	}
	
	public function getBulk($ssids)
	{
		assert(!empty($ssids));
		
		$where = array('ssid' => $ssids);
		$ret = $this->one->select(self::TABLE, array('*'), $where);
		
		return $ret['data'];
	}
    
    public function getSumById($field, $ssid)
    {
        $where = 'ssid='. $ssid. ' and status=0';
        $field[0] = $field[0]. ' as sum';
        $ret = $this->one->select(self::TABLE, $field, $where);
        
        return !empty($ret['data'])? intval($ret['data'][0]['sum']): 0;
    }
    
    public function getByRawWhere($where, $field=array('*'), $start=0, $num=0, $order='')
    {
        $order = !empty($order)? $order: 'order by ssid';
        
        $ret = $this->one->select(self::TABLE, $field, $where, $order, $num ,$start);
        
        return $ret['data'];
    }


    public function del($ssid, $sids)
	{
		$isExist = $this->isExistProductsInShift($ssid, $sids);
		
		$st = false;
		if ($isExist)
		{
			if (! is_array($sids))
			{
				$sids = array($sids);
			}
			
			$where = 'ssid='. $ssid;
			$where .= ' and sid in ('. implode(',', $sids). ')';
			
			$ret = $this->one->delete(self::TABLE, $where);
			
			$st = $ret['affectedrows']? true: false;
		}
		
		return $st;
	}
	
	/**
	 * 移库单是否存在改商品.
	 */
	public function isExistProductsInShift($ssid, $sids)
	{
		if (! is_array($sids))
		{
			$sids = array($sids);
		}
		
		$where = 'ssid='. $ssid;
		$where .= ' and sid in ('. implode(',', $sids). ')';
		
		$ret = $this->one->select(self::TABLE, array('count(1)'), $where);

		return count($sids)==$ret['data'][0]['count(1)'];
	}
}