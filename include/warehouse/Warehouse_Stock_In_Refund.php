<?php
/**
 * 入库退货单.
 */


class Warehouse_Stock_In_Refund extends Base_Func
{
    
    const TABLE = 't_stock_in_refund';
    
    public function create($datas)
    {
        assert(!empty($datas));
//        assert(isset($datas['stockin_id']));
        assert(isset($datas['supplier_id']));
        assert(isset($datas['wid']));
        
        if (empty($datas['ctime']))
        {
            $datas['ctime'] = date('Y-m-d H:i:s');
        }
        
        $res = $this->one->insert(self::TABLE, $datas);
        
        return $res['insertid'];
    }
    
    public function get($srid)
    {
        $where = 'srid='.$srid;
        
        $data = $this->one->select(self::TABLE, array('*'), $where);
        
        return $data['data'][0];
    }

    public function getBulk($srids)
    {
        $where = 'srid in (' . implode(',', $srids) . ')';
        $data = $this->one->select(self::TABLE, array('*'), $where);

        return $data['data'];
    }
    
    public function getByStockids($stockids)
    {
        assert(!empty($stockids) && is_array($stockids));
        
        $where = 'status='. Conf_Base::STATUS_NORMAL.
                ' and stockin_id in ('. implode(',', $stockids).')';
        
        $data = $this->one->select(self::TABLE, array('*'), $where);
        
        return $data['data'];
    }
    
    public function update($srid, $data)
    {
        assert(!empty($data));
        
        $where = 'srid='. $srid;
        
        $ret = $this->one->update(self::TABLE, $data, array(), $where);
        
        return $ret['affectedrows'];
    }
    
    public function getList($conf, $start=0, $num=20, $order='')
    {
        $where = 'status='. Conf_Base::STATUS_NORMAL;
        
        if (isset($conf['stockin_id']) && !empty($conf['stockin_id']))
        {
            $where .= ' and stockin_id='. $conf['stockin_id'];
        }
        if (isset($conf['wid']) && !empty($conf['wid']))
        {
            if (is_array($conf['wid']))
            {
                $where .= sprintf(' and wid in (%s)', implode(',', $conf['wid']));
            }
            else
            {
                $where .= ' and wid='. $conf['wid'];
            }
        }
        if (isset($conf['supplier_id']) && !empty($conf['supplier_id']))
        {
            $where .= ' and supplier_id='. $conf['supplier_id'];
        }
        if ($this->is($conf['name']))
        {
            $where .= ' and suid in (select suid from t_staff_user where status = 0 and name like "%'. $conf['name'] . '%")';
        }
        if ($this->is($conf['step']))
        {
            $where .= ' and step='. $conf['step'];
        }
        
        $ret = array('total'=>0, 'data'=>array());
        
        // 查询数量
		$cdata = $this->one->select(self::TABLE, array('count(1)'), $where);
		$ret['total'] = intval($cdata['data'][0]['count(1)']);
		if (empty($ret['total']))
		{
			return $ret;
		}

		// 查询结果
        $order = !empty($order)? $order: 'order by srid desc';
		$data = $this->one->select(self::TABLE, array('*'), $where, $order, $start, $num);
		
		$ret['data'] = $data['data'];
        
        return $ret;
    }

    public function getSumByConf($conf, $field)
    {
        $where = $this->_getWhereByConf($conf);
        $res = $this->one->select(self::TABLE, $field, $where);
        return $res['data'][0]['price'];
    }

    private function _getWhereByConf($conf)
    {
        // 解析 conf 到 条件 $where
        $where = 'status=' . Conf_Base::STATUS_NORMAL;

        if ($this->is($conf['ids']))
        {
            $where .= sprintf(' and stockin_id in(%s)', implode(',',$conf['ids']));
        }

        return $where;
    }

    /**
     * 根据结算单ID查询入库单
     * @author libaolong
     * @param $id
     * @return mixed
     */
    public function getByStatementId($id)
    {
        $where = sprintf(' statement_id=%d ', $id);
        $data = $this->one->select(self::TABLE, array('*'), $where);

        return $data['data'];
    }
}