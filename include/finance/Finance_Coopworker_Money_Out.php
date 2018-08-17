<?php

/**
 * 第三方合作者的财务明细.
 */

class Finance_Coopworker_Money_Out extends Base_Func
{
    const TABLE = 't_coopworker_money_out_history';
    
    public function insert($data)
    {
        $data['obj_id'] = $this->is($data['obj_id'])? $data['obj_id']: $data['oid'];
        $data['obj_type'] = $this->is($data['obj_type'])? $data['obj_type']: Conf_Coopworker::OBJ_TYPE_ORDER;
        $data['ctime'] = date('Y-m-d H:i:s');
        $data['city_id'] = Appconf_Warehouse::getCityid4Wid($data['wid']);
        
        assert(!empty($data['obj_id']));
        
        $res = $this->one->insert(self::TABLE, $data);
        
		return $res['insertid'];
    }
    
    public function update($where, $data)
    {
        assert(!empty($data));
        assert(!empty($where));

        if (!$this->is($where['obj_id']) && isset($where['oid']))
        {
            $where['obj_id'] = $where['oid'];
            $where['obj_type'] = Conf_Coopworker::OBJ_TYPE_ORDER;
        }

        if (!$this->is($data['obj_id']) && isset($data['oid']))
        {
            $data['obj_id'] = $data['oid'];
            $data['obj_type'] = Conf_Coopworker::OBJ_TYPE_ORDER;
        }

        $ret = $this->one->update(self::TABLE, $data, array(), $where);
        
        return $ret['affectedrows'];
    }
    
    public function getList($search, $start=0, $num=20, $order='')
    {
        $where = 'status=0';
        
        if (isset($search['cuid']) && !empty($search['cuid']))
        {
            if (is_array($search['cuid']))
            {
                $where .= ' and cuid in ('. implode(',', $search['cuid']). ')';
            }
            else
            {
                $where .= ' and cuid='. $search['cuid'];
            }
        }
        if (isset($search['btime']) && !empty($search['btime']))
        {
            $where .= ' and date(ctime)>=date("'. $search['btime']. '")'; 
        }
        if (isset($search['etime']) && !empty($search['etime']))
        {
            $where .= ' and date(ctime)<=date("'. $search['etime']. '")'; 
        }
        if (isset($search['user_type']) && !empty($search['user_type']))
        {
            $where .= ' and user_type='.$search['user_type'];
        }
        if (isset($search['suid']) && !empty($search['suid']))
        {
            $where .= ' and suid='. $search['suid'];
        }
        if (!empty($search['wid']))
        {
            $where .= ' and wid='. $search['wid'];
        }
        
        $ret = array('total'=>0, 'data'=>array());
        $cRet = $this->one->select(self::TABLE, array('count(1)'), $where);
   
        if (empty($cRet['data'][0]['count(1)']))
        {
            return $ret;
        }
        
        $order = !empty($order)? $order: 'order by id desc';
        $dRet = $this->one->select(self::TABLE, array('*'), $where, $order, $start, $num);
        $ret['total'] = $cRet['data'][0]['count(1)'];
        $ret['data'] = $dRet['data'];
        
        return $ret;
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
}