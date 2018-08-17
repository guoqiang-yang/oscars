<?php
/**
 * 移库单
 */
class Warehouse_Stock_Shift extends Base_Func
{
	const TABLE = 't_stock_shift';
	
	public function insert($data)
	{
		assert(!empty($data));
		
		$data['ctime'] = date('Y-m-d H:i:s');
		$ret = $this->one->insert(self::TABLE, $data);
		
		return $ret['insertid'];
	}
	
	public function update($ssid, $data)
	{
		assert($ssid > 0);
		assert(!empty($data));
		
		$where = array('ssid' => $ssid);
		
        if (!empty($data['step']) && $data['step']==Conf_Stock_Shift::STEP_STOCK_OUT)
        {
            $data['out_time'] = date('Y-m-d H:i:s');
        }
        else if (!empty($data['step']) && $data['step']==Conf_Stock_Shift::STEP_STOCK_IN)
        {
            $data['in_time'] = date('Y-m-d H:i:s');
        }
        
		$ret = $this->one->update(self::TABLE, $data, array(), $where);
		
		return $ret['affectedrows'];
	}
	
	public function getById($ssid)
	{
		assert($ssid > 0);
		
		$where = 'ssid='.$ssid.' and status<>'.Conf_Base::STATUS_DELETED;
		$ret = $this->one->select(self::TABLE, array('*'), $where);
		
		return $ret['data'][0];
	}
    
    public function getBluk($ssids, $fields=array('*'))
    {
        assert(!empty($ssids));
        
        $where = 'status<>'.Conf_Base::STATUS_DELETED.' and ssid in ('. implode(',', $ssids). ')';
        $ret = $this->one->select(self::TABLE, $fields, $where);
        
        return Tool_Array::list2Map($ret['data'], 'ssid');
    }
	
    public function getByWhere($where, $start=0, $num=0, $fields=array('*'), $order='')
    {
        assert(!empty($where));
        
		$order = !empty($order)? $order: 'order by ssid desc';
		
		$data = $this->one->select(self::TABLE, $fields, $where, $order, $start, $num);
        
        return $data['data'];
    }
    
	public function getList($conf, $fields=array('*'), $order='', $start=0, $num=20)
	{
	    if(isset($conf['status']) && $conf['status'] <> Conf_Base::STATUS_ALL)
        {
            $where = 'status='.$conf['status'];
        }else{
            $where = 'status<>'.Conf_Base::STATUS_DELETED;
        }
		if (isset($conf['ssid']) && !empty($conf['ssid']))
		{
			$where .= ' and ssid='. $conf['ssid'];
		}
		else
		{
            if ($this->is($conf['sku_id']))
            {
                $subWhere = sprintf('select distinct(ssid) from t_stock_shift_product where status=0 and sid=%d', $conf['sku_id']);
                $where .= ' and ssid in ('. $subWhere .')';
            }
            if (isset($conf['no_src_and_des_wid']) && !empty($conf['no_src_and_des_wid']))
            {
                $where .= $conf['no_src_and_des_wid'];
            }
            else
            {
                if (isset($conf['src_wid']) && !empty($conf['src_wid']))
                {
                    if (is_array($conf['src_wid']))
                    {
                        $where .= sprintf(' and src_wid in (%s)', implode(',', $conf['src_wid']));
                    }
                    else
                    {
                        $where .= ' and src_wid='. $conf['src_wid'];
                    }
                }
                if (isset($conf['des_wid']) && !empty($conf['des_wid']))
                {
                    if (is_array($conf['des_wid']))
                    {
                        $where .= sprintf(' and des_wid in (%s)', implode(',', $conf['des_wid']));
                    }
                    else
                    {
                        $where .= ' and des_wid='. $conf['des_wid'];
                    }
                }
            }
			if (isset($conf['step']) && !empty($conf['step']))
			{
                if ($conf['step'] == Conf_Stock_Shift::STEP_PART_SHELVED)
                {
                    $where .= sprintf(' and step in (%d, %d)', 
                            Conf_Stock_Shift::STEP_STOCK_IN, Conf_Stock_Shift::STEP_PART_SHELVED);
                }
                else
                {
                    $where .= ' and step='. $conf['step'];
                }
			}
			if (isset($conf['create_suid']) && !empty($conf['create_suid']))
			{
				$where .= ' and create_suid='. $conf['create_suid'];
			}
		}
		
		$order = !empty($order)? $order: 'order by ssid desc';
		
		$cdata = $this->one->select(self::TABLE, array('count(1)'), $where);
		$total = intval($cdata['data'][0]['count(1)']);
        if (empty($total))
        {
            return array('total'=>0, 'data'=>array());
        }
        
		$ldata = $this->one->select(self::TABLE, $fields, $where, $order, $start, $num);
		
		return array('total'=>$total, 'data'=>$ldata['data']);
		
	}
}