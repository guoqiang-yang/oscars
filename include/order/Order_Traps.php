<?php
/**
 * 补漏单信息相关业务逻辑
 */
class Order_Traps extends Base_Func
{
    private $trapsDao;

    public function __construct()
    {
        $this->trapsDao = new Data_Dao('t_traps');

        parent::__construct();
    }

    public function add(array $info)
    {
        assert(!empty($info));
        
        assert( !empty($info['oid'])&&!empty($info['cid'])&&!empty($info['wid']) );
        
        $info['ctime'] = date('Y-m-d H:i:s',time());

        return $this->trapsDao->add($info);
    }

    public function delete($tid)
    {
        $res = $this->trapsDao->delete($tid);

        return $res;
    }

    public function update($tid, $info)
    {
        return $this->trapsDao->update($tid, $info);
    }

    public function get($tid)
    {
       return $this->trapsDao->setSlave()->get($tid);
    }

    public function getBulk($tids)
    {
        return $this->trapsDao->setSlave()->getList($tids);
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

        $total = $this->trapsDao->setSlave()->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }

        return $this->trapsDao->order('tid', 'desc')->limit($start, $num)->getListWhere($where);
    }

	public function getListByWhere($where, &$total, $start = 0, $num = 20)
	{
		$total = $this->trapsDao->setSlave()->getTotal($where);
		if ($total <= 0)
		{
			return array();
		}

		return $this->trapsDao->setSlave()->order('tid', 'desc')->limit($start, $num)->getListWhere($where);
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

        $total = $this->trapsDao->setSlave()->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }

        return $this->trapsDao->setSlave()->order('tid', 'desc')->limit($start, $num)->getListWhere($where);
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

        return $this->trapsDao->setSlave()->order('tid', 'desc')->getListWhere($where);
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
        $where = 'traps_status=' . Conf_Base::STATUS_NORMAL;
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
	    if (!empty($conf['traps_time']))
	    {
		    $where .= sprintf(' and date(traps_time,"Y-m-d")>="%s"', mysql_escape_string($conf['traps_time']));
	    }
	    if (!empty($conf['traps_time_end']))
	    {
		    $where .= sprintf(' and date(traps_time,"Y-m-d")<="%s"', mysql_escape_string($conf['traps_time_end']));
	    }
        if (!empty($conf['city_id']))
        {
            $where .= sprintf(' and city_id=%d', $conf['city_id']);
        }

        if (!empty($conf['tid']))
        {
            $where .= sprintf(' and tid=%d', $conf['tid']);
        }
        if ($this->is($conf['aftersale_oid']))
        {
            $where .= ' and aftersale_oid='.$conf['aftersale_oid'];
        }
        
        return $where;
    }
}
