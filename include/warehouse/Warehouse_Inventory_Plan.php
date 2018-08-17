<?php

/**
 * 商品相关业务
 */
class Warehouse_Inventory_Plan extends Base_Func
{
	private $_dao;

	public function __construct()
	{
		$this->_dao = new Data_Dao('t_inventory_plan');

		parent::__construct();
	}

	public function add(array $info)
	{
		return $this->_dao->add($info);
	}

	public function get($pid)
    {
        return $this->_dao->get($pid);
    }

    public function getList(array $conf, &$total, $order, $start = 0, $num = 20)
    {
        if (empty($order))
        {
            $order = 'order by pid desc';
        }

        $where = $this->_getWhereByConf($conf);

        // 查询数量
        $total = $this->_dao->getTotal($where);
        if (empty($total))
        {
            return array();
        }

        // 查询结果
        return $this->_dao->order($order)->limit($start, $num)->getListWhere($where);
    }

    public function update($pid, array $info)
    {
        return $this->_dao->update($pid, $info);
    }

    private function _getWhereByConf($conf)
    {
        // 解析 conf 到 条件 $where
        $where = 'status=' . Conf_Base::STATUS_NORMAL;

        if ($this->is($conf['wid'])) {
            if (is_array($conf['wid']))
            {
                $where .= sprintf(' and wid in (%s)', implode(',', $conf['wid']));
            }
            else
            {
                $where .= sprintf(' and wid=%d', $conf['wid']);
            }
        }
        if ($this->is($conf['plan_type'])) {
            $where .= sprintf(' and plan_type=%d', $conf['plan_type']);
        }
        if ($this->is($conf['step'])) {
            $where .= sprintf(' and step=%d', $conf['step']);
        }
        if ($this->is($conf['start_time'])) {
            $where .= sprintf(' and ctime >= "%s"', $conf['start_time']);
        }
        if ($this->is($conf['end_time'])) {
            $where .= sprintf(' and ctime <= "%s"', $conf['end_time']);
        }

        return $where;
    }
}
