<?php

/**
 * 临时采购 - 未采购.
 */

class Warehouse_Temporary_Purchase extends Base_Func
{
    const TABLE = 't_temporary_purchase';
    
    public function save($datas)
    {
        assert(!empty($datas));
        assert(!empty($datas['sid']));
        assert(!empty($datas['num']));
        assert(!empty($datas['wid']));
 
        $datas['ctime'] = date('Y-m-d H:i:s');
        $datas['status'] = Conf_Base::STATUS_NORMAL;
        
        $updateField = array('status');
        $changeData = array(
            'num' => $datas['num'],
        );
        
        $this->one->insert(self::TABLE, $datas, $updateField, $changeData);
    }
    
    public function get($wid=0, $cate1=0, $start=0, $num=0, $order='')
    {
        $response = array('total'=>0, 'data'=>array());
        
        $where = 'status=0 and num>0';
        $where .= $wid!=0? ' and wid='. $wid: '';
        $where .= $cate1!=0? ' and cate1='.$cate1: '';
        
        $cRet = $this->one->select(self::TABLE, array('count(1)'), $where);
        
        $response['total'] = $total = intval($cRet['data'][0]['count(1)']);
        if (empty($response['total']))
        {
            return $response;
        }
        
        $order = !empty($order)? $order: 'order by title';
        $ret = $this->one->select(self::TABLE, array('*'), $where, $order, $start, $num);
        $response['data'] = $ret['data'];
        
        return $response;
    }
    
    public function update($sid, $wid, $upField=array(), $chgField=array())
    {
        assert(!empty($sid));
        assert(!empty($wid));
        
        if (empty($upField) && empty($chgField))
        {
            return 0;
        }
        
        $where = 'wid='. $wid. ' and sid='. $sid;
        $ret = $this->one->update(self::TABLE, $upField, $chgField, $where);
        
        // 更新num字段，确保num的数量是大于等于0的
        $_where = 'num<0 and status=0';
        $this->one->update(self::TABLE, array('num'=>0), array(), $_where);
        
        return $ret['affectedrows'];
    }

    /**
     * 获取普采缺货列表
     *
     * @param $conf
     * @param $start
     * @param $num
     * @return array
     */
    public function getStockOutProduct($conf, $start, $num)
    {
        $orderProductDao = new Data_Dao('t_order_product');
        $orderDao = new Data_Dao('t_order');

        $where  = sprintf(' 1=1 and tmp_inorder_num=0 and outsourcer_id=0 and status=0 ');

        if (!empty($conf['oid']))
        {
            $where .= sprintf(' and oid=%d ', $conf['oid']);
        }

        if (!empty($conf['sid']))
        {
            $where .= sprintf(' and sid=%d ', $conf['sid']);
        }

        if ($conf['status'] != Conf_Base::STATUS_ALL)
        {
            $where .= sprintf(' and vnum_deal_type=%d ', $conf['status']);
        }

        if (!empty($conf['bdate']))
        {
            $where .= sprintf(' and oid in(select oid from t_order where step<%d and delivery_date>="%s" and wid=%d and status=%d) ', 
                    Conf_Order::ORDER_STEP_PICKED, $conf['bdate'], $conf['wid'], Conf_Base::STATUS_NORMAL);
        }

        if (!empty($conf['edate']))
        {
            $where .= sprintf(" and delivery_date_end<='%s' ", $conf['edate']);
        }

        $orderProductList = $orderProductDao->setFields(array('oid','sid','wid','pid','vnum_deal_type','vnum','num'))
                                            ->order(' order by ctime desc ')
                                            ->limit($start, $num)
                                            ->getListWhere($where);

        foreach ($orderProductList as &$item)
        {
            $order = $orderDao->get($item['oid']);
            $item['delivery_date_end'] = $order['delivery_date_end'];
            $item['delivery_date'] = $order['delivery_date'];
            $item['step'] = $order['step'];
        }

        return $orderProductList;
    }
    
}