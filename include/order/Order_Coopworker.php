<?php

class Order_Coopworker extends Base_Func
{
    const TABLE = 't_coopworker_order';
    
    public function saveWorkerForOrder($data)
    {
        assert(!empty($data));
        assert(!empty($data['oid']));
        assert(!empty($data['cuid']));
        
        //$data['occupied'] = 1;    //暂时不开启这项
        $data['ctime'] = date('Y-m-d H:i:s');
        $data['note'] = isset($data['note'])? $data['note']: '';
        
        $res = $this->one->insert(self::TABLE, $data);
		$id = $res['insertid'];
        
		return $id;
    }
    
    public function update($oid, $cuid, $type, $data)
    {
        assert(!empty($oid));
        assert(!empty($cuid));
        assert(!empty($type));
        assert(!empty($data));
        
        $where['oid'] = $oid;
        $where['cuid'] = $cuid;
        $where['type'] = $type;
        
        $ret = $this->one->update(self::TABLE, $data, array(), $where);
                
        return $ret['affectedrows'];
    }
    
    /**
     * 取订单第三方工人.
     * 
     * @param int $oid
     * @param int $type {1:司机 2:搬运工}
     */
    public function getByOid($oid, $cuid=0, $type=0)
    {
        $where = array(
            'oid' => $oid,
            'status' => 0,
        );
        if (!empty($cuid))
        {
            $where['cuid'] = $cuid;
        }
        if (!empty($type))
        {
            $where['type'] = $type;
        }
        
        $ret = $this->one->select(self::TABLE, array('*'), $where);
        
        return $ret['data'];
    }
    
    public function getByOids($oids, $type=0)
    {
        assert(!empty($oids));
        
        $where = 'status=0 and oid in ('. implode(',', $oids).')';
        
        if ($type != 0)
        {
            $where .= ' and type='. $type;
        }
        
        $ret = $this->one->select(self::TABLE, array('*'), $where);
        
        return $ret['data'];
    }
    
    public function getByWorker($cuid, $searchConf, $start=0, $num=20, $order='')
    {
        $where = 'status=0';
        
        if (!empty($cuid))
        {
            if (is_array($cuid))
            {
                $where .= ' and cuid in ('. implode(',', $cuid). ')';
            }
            else
            {
                $where .= ' and cuid='. $cuid;
            }
        }
        
        if (isset($searchConf['type']) && !empty($searchConf['type']))
        {
            $where .= ' and type = '. $searchConf['type'];
        }
        if (isset($searchConf['paid']))
        {
            $where .= ' and paid='. $searchConf['paid'];
        }
        if (isset($searchConf['btime']) && !empty($searchConf['btime']))
        {
            $where .= ' and date(ctime)>=date("'. $searchConf['btime']. '")';
        }
        if (isset($searchConf['etime']) && !empty($searchConf['etime']))
        {
            $where .= ' and date(ctime)<=date("'. $searchConf['etime']. '")';
        }

        $ret = array('total'=>0, 'data'=>array());
        $cRet = $this->one->select(self::TABLE, array('count(1)'), $where);

        if ($cRet['data'][0]['count(1)'] == 0)
        {
            return $ret;
        }
        
        $order = !empty($order)? $order: 'order by id desc';
        $dRet = $this->one->select(self::TABLE, array('*'), $where, $order, $start, $num);
        $ret['total'] = $cRet['data'][0]['count(1)'];
        $ret['data'] = $dRet['data'];
        
        return $ret;
    }

    public function getByWorkerWithOrder($cuid, $searchConf, $start=0, $num=20, $order='')
    {
        $where = 'co.status=0';

        if (!empty($cuid))
        {
            $where .= ' and co.cuid='. $cuid;
        }

        if (isset($searchConf['type']) && !empty($searchConf['type']))
        {
            $where .= ' and co.type = '. $searchConf['type'];
        }
        if (isset($searchConf['paid']))
        {
            $where .= ' and co.paid='. $searchConf['paid'];
        }
        if (isset($searchConf['btime']) && !empty($searchConf['btime']))
        {
            $where .= ' and date(o.delivery_date)>=date("'. $searchConf['btime']. '")';
        }
        if (isset($searchConf['etime']) && !empty($searchConf['etime']))
        {
            $where .= ' and date(o.delivery_date)<=date("'. $searchConf['etime']. '")';
        }

        $ret = array('total'=>0, 'data'=>array());

        $table = ' t_coopworker_order as co INNER JOIN t_order as o ON co.oid=o.oid ';
        $cRet = $this->one->setDBMode()->select($table, array('count(1)'), $where);

        if ($cRet['data'][0]['count(1)'] == 0)
        {
            return $ret;
        }

        $order = !empty($order)? $order: 'order by id desc';
        $dRet = $this->one->setDBMode()->select($table, array('o.*'), $where, $order, $start, $num);
        $ret['total'] = $cRet['data'][0]['count(1)'];
        $ret['data'] = $dRet['data'];

        return $ret;
    }
}