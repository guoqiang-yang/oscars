<?php

/**
 * 付款单记录.
 */

class Finance_Money_Out extends Base_Func
{
	const TABLE = 't_money_out_history';
	
	public function add(array $data)
	{
		assert(!empty($data));
		assert(isset($data['sid']));
		assert(isset($data['objid']));
		assert(isset($data['price']));
		assert(isset($data['type']));
		assert(isset($data['amount']));
		
		
		$data['ctime'] = isset($data['ctime'])? $data['ctime']: date('Y-m-d H:i:s');
		if (empty($data['note']))
		{
			$data['note'] = '';
		}
		$res = $this->one->insert(self::TABLE, $data);
		$id = $res['insertid'];
		
		return array('id'=>$id);
	}
    
    public function update ($where, $upData=array(), $change=array())
    {
        assert(!empty($upData) || !empty($change));
        assert(!empty($where));
        
        
        $res = $this->one->update(self::TABLE, $upData, $change, $where);
        
        return $res['affectedrows'];
    }
    
    public function openGet($where, $field=array('*'))
    {
        if (empty($where))
        {
            $where = 'status='. Conf_Base::STATUS_NORMAL;
        }
        
        $res = $this->one->select(self::TABLE, $field, $where);
        
        return $res['data'];
    }
	
	public function getSupplierBillList($searchConf, $order, $start=0, $num=20)
	{
		$_order = !empty($order)? $order: 'order by id desc';
		
		$where = 'status=0';
		if (!empty($searchConf['sid']))
		{
			$where .= ' and sid='. $searchConf['sid'];
		}
		if (isset($searchConf['from_date']) && !empty($searchConf['from_date']))
		{
			$where .= ' and date(ctime)>=date("'. $searchConf['from_date']. '")';
		}
		if (isset($searchConf['end_date']) && !empty($searchConf['end_date']))
		{
			$where .= ' and date(ctime)<=date("'. $searchConf['end_date']. '")';
		}
        if (!empty($searchConf['payment_type']))
        {
            $where .= ' and payment_type='. $searchConf['payment_type'];
        }
        if (!empty($searchConf['paid_source']))
        {
            $where .= ' and paid_source='. ($searchConf['paid_source']!=999?$searchConf['paid_source']:0);
        }
		
		$countRet = $this->one->select(self::TABLE, array('count(1)'), $where);
		$total = $countRet['data'][0]['count(1)'];
        
        $prices = array();
        if (isset($searchConf['from_date']) && !empty($searchConf['from_date']))
        {
            $_where = $where.' group by type';
            $_prices = $this->one->select(self::TABLE, array('type', 'sum(price)'), $_where);
            
            foreach($_prices['data'] as $pval)
            {
                $prices[] = array(
                    'type' => $pval['type'],
                    'price' => $pval['sum(price)'],
                    'type_name' => Conf_Money_Out::$STATUS_DESC[$pval['type']],
                );
            }
        }
        
		$retList = $this->one->select(self::TABLE, array('*'), $where, $_order, $start, $num);
		
		return array('total'=>$total, 'list'=>$retList['data'], 'prices'=>$prices);
	}
}