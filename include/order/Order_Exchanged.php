<?php
/**
 * 换货单信息相关业务逻辑
 */
class Order_Exchanged extends Base_Func
{
    private $exchangedDao;

    public function __construct()
    {
        $this->exchangedDao = new Data_Dao('t_exchanged');

        parent::__construct();
    }

    public function add(array $info)
    {
        assert(!empty($info));
        
        assert( !empty($info['oid'])&&!empty($info['cid'])&&!empty($info['uid'])&&!empty($info['wid']) );
        
        if ($this->is($info['note'])) $info['note'] = '';
        $info['ctime'] = date('Y-m-d H:i:s',time());

        return $this->exchangedDao->add($info);
    }

    public function delete($eid)
    {
        $res = $this->exchangedDao->delete($eid);

        return $res;
    }

    public function update($eid, $info)
    {
        return $this->exchangedDao->update($eid, $info);
    }

    public function get($eid)
    {
       return $this->exchangedDao->setSlave()->get($eid);
    }

    public function getBulk($eids)
    {
        return $this->exchangedDao->setSlave()->getList($eids);
    }

    /**
     * 根据条件获取换货单列表
     *
     * @param array $conf
     * @param $total
     * @param int $start
     * @param int $num
     * @return array
     */
    public function getList($conf, &$total, $start = 0, $num = 20)
    {
        $where = $this->_getWhereByConf($conf);

        $total = $this->exchangedDao->setSlave()->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }

        return $this->exchangedDao->order('eid', 'desc')->limit($start, $num)->getListWhere($where);
    }

	public function getListByWhere($where, &$total, $start = 0, $num = 20)
	{
		$total = $this->exchangedDao->setSlave()->getTotal($where);
		if ($total <= 0)
		{
			return array();
		}

		return $this->exchangedDao->setSlave()->order('eid', 'desc')->limit($start, $num)->getListWhere($where);
	}

    /**
     * 获取用户的换货单列表
     *
     * @param $cid
     * @param $total
     * @param int $start
     * @param int $num
     * @return array
     */
    public function getListOfCustomer($cid, &$total, $start = 0, $num = 20)
    {
        $cid = intval($cid);
        assert($cid > 0);

        $conf = array('cid' => $cid);
        $where = $this->_getWhereByConf($conf);

        $total = $this->exchangedDao->setSlave()->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }

        return $this->exchangedDao->setSlave()->order('eid', 'desc')->limit($start, $num)->getListWhere($where);
    }

    /**
     * 获取订单的换货单
     *
     * @param $oid
     * @return array
     */
    public function getListOfOrder($oid)
    {
        assert(!empty($oid));

        $conf = array('oid' => $oid);
        $where = $this->_getWhereByConf($conf);

        return $this->exchangedDao->setSlave()->order('eid', 'desc')->getListWhere($where);
    }

    /*
     * 获取退货单的关联换货单
     */
    public function getInfoByRid($rid)
    {
        assert(!empty($rid));
        $where = array('refund_id' => $rid);
        $ret = $this->exchangedDao->setSlave()->getListWhere($where);
        if(empty($ret))
        {
            return array();
        }else{
            return current($ret);
        }
    }


////////////////////////////////////////////////////////////////////
/////////          私有方法                                  ////////
////////////////////////////////////////////////////////////////////

    /**
     * 解析conf条件到where
     *
     * @param $conf
     * @return string
     */
    private function _getWhereByConf($conf)
    {
        if(is_string($conf))
        {
            return $conf;
        }
        $where = 'exchanged_status=' . Conf_Base::STATUS_NORMAL;
        if (!empty($conf['step']))
        {
            $where .= sprintf(' and step="%d"', $conf['step']);
        }
        if (!empty($conf['cid']))
        {
            $where .= sprintf(' and cid="%d"', $conf['cid']);
        }
        if (!empty($conf['oid']))
        {
            if (is_array($conf['oid']))
            {
                $where .= sprintf(' and oid in (%s)', implode(',', $conf['oid']));
            }
            else
            {
                $where .= sprintf(' and oid="%d"',  $conf['oid']);
            }
        }
        if (isset($conf['wid']) && !empty($conf['wid']))
        {
            $where .=sprintf(' and wid=%d', $conf['wid']);
        }
	    if (!empty($conf['exchanged_time']))
	    {
		    $where .= sprintf(' and date(exchanged_time,"Y-m-d")>="%s"', mysql_escape_string($conf['exchanged_time']));
	    }
	    if (!empty($conf['exchanged_time_end']))
	    {
		    $where .= sprintf(' and date(exchanged_time,"Y-m-d")<="%s"', mysql_escape_string($conf['exchanged_time_end']));
	    }
        if (!empty($conf['city_id']))
        {
            $where .= sprintf(' and city_id=%d', $conf['city_id']);
        }

        if (!empty($conf['eid']))
        {
            $where .= sprintf(' and eid=%d', $conf['eid']);
        }
        if ($this->is($conf['aftersale_oid']))
        {
            $where .= ' and aftersale_oid='.$conf['aftersale_oid'];
        }
        if ($this->is($conf['refund_id']))
        {
            $where .= ' and refund_id='. $conf['refund_id'];
        }
        
        return $where;
    }
}
