<?php

/**
 * 入库结算单信息相关业务逻辑
 */
class Finance_StockIn_Statements extends Base_Func
{
	private $statementsDao;
	public function __construct()
	{
		$this->statementsDao = new Data_Dao('t_stockin_statements');

		parent::__construct();
	}

	public function get($id)
	{
		return $this->statementsDao->setSlave()->get($id);
	}

	public function getBulk($ids, $filed=array('*'), $orderby=array('id', 'desc') )
	{
		return $this->statementsDao
				->setSlave()
                ->setFields($filed)
                ->order($orderby[0], $orderby[1])
                ->getList($ids);
	}

	public function add($info)
	{
		return $this->statementsDao->add($info);
	}

	public function update($id, $update, $change = array())
	{
		return $this->statementsDao->update($id, $update, $change);
	}

	public function delete($id)
    {
        return $this->statementsDao->delete($id);
    }

	public function getList($ids)
	{
		return $this->statementsDao->setSlave()->getList($ids);
	}

	public function getListRawWhere($where, &$total, $start = 0, $num = 20, $order, $fields = array('*'))
	{
        $where = self::_getWhereFromConf($where);
		$total = $this->statementsDao->setSlave()->getTotal($where);
		if ($total <= 0)
		{
			return array();
		}

		if (empty($order))
		{
			$order = array('id', 'desc');
		}

		return $this->statementsDao->setSlave()->order($order[0], $order[1])->limit($start, $num)->setFields($fields)->getListWhere($where);
	}

	public function getListRawWhereWithoutTotal($where, $order, $start = 0, $num = 20, $fields = array('*'))
	{
		if (empty($order))
		{
			$order = array('id', 'asc');
		}
        $where = self::_getWhereFromConf($where);
		return $this->statementsDao->setSlave()->order($order[0], $order[1])->limit($start, $num)->setFields($fields)->getListWhere($where);
	}

	public function updateByWhere($info, $change, $where)
	{
	    $where = self::_getWhereFromConf($where);
		return $this->statementsDao->updateWhere($where, $info, $change);
	}

    //////////////////////////////////////////////////////////////////////////
    //////                                                            ////////
    //////                  私有方法都放在下                            /////////
    /////                                                            /////////
    //////////////////////////////////////////////////////////////////////////

    /**
     * 根据conf获取where语句
     *
     * @param $conf
     * @return string
     */
    private function _getWhereFromConf($conf)
    {
        if(is_array($conf))
        {
            $conf['status'] = Conf_Base::STATUS_NORMAL;
        }elseif (is_string($conf))
        {
            $conf .= sprintf(' and status="%d"', Conf_Base::STATUS_NORMAL);
        }


        return $conf;
    }
}
