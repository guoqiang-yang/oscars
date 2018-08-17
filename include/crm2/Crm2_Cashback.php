<?php
/**
 * 优惠券
 */
class Crm2_Cashback extends Base_Func
{
	public function add(array $info)
	{
		assert( !empty($info['cid']) );

		$info['ctime'] = $info['mtime'] = date('Y-m-d H:i:s');
		$update = array('order_amount', 'cashback', 'mtime');
		$res = $this->one->insert('t_cashback', $info, $update);
		return $res['affectedrows'];
	}

	public function delete($cid)
	{
		$cid = intval($cid);
		assert($cid > 0);

        // 物理删除，如果需要恢复，可重新计算
        $where = 'cid='. $cid;
        $this->one->delete('t_cashback', $where);
        
//		$where = array('cid' => $cid);
//		$update = array('status' => Conf_Base::STATUS_DELETED);
//		$ret = $this->one->update('t_cashback', $update, array(), $where);
//		return $ret['affectedrows'];
	}

	public function update($cid, array $info)
	{
		$cid = intval($cid);
		assert( $cid > 0 );
		assert( !empty($info) );

		$where = array('cid' => $cid);
		$ret = $this->one->update('t_cashback', $info, array(), $where);
		return $ret['affectedrows'];
	}

	public function get($cid)
	{
		$cid = intval($cid);
		assert($cid > 0);

		$where = array('cid' => $cid);
		$data = $this->one->select('t_cashback', array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'][0];
	}

	public function getBulk(array $cids)
	{
		assert(!empty($cids));

		$where = array('cid' => $cids);
		$data = $this->one->select('t_cashback', array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'];
	}
    
    /**
     * 合并两个用户的返现累计金额.
     * 
     * @param int $masterCid
     * @param int $slaveCid
     */
    public function merge($masterCid, $slaveCid)
    {
        if (empty($masterCid) || empty($slaveCid)) return;
        
        $cashs = $this->getBulk(array($masterCid, $slaveCid));
        
        $incrAmount = 0;
		$incrCashback = 0;
        foreach($cashs as $onerCash)
        {
            $incrAmount += $onerCash['order_amount'];
            $incrCashback += $onerCash['cashback'];
        }
        
        //更新合并数据
        $changeData = array('order_amount'=>$incrAmount, 'cashback'=>$incrCashback);
        $this->update($masterCid, $changeData);
        
        //删除slave—cashback
        $this->delete($slaveCid);
    }
}
