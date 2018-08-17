<?php

/**
 * 商品相关业务
 */
class Warehouse_Other_Stock_Out_Order extends Base_Func
{
	private $_dao;

	public function __construct()
	{
		$this->_dao = new Data_Dao('t_other_stock_order');

		parent::__construct();
	}

	public function add(array $info)
	{
		return $this->_dao->add($info);
	}

	public function get($oid)
    {
        return $this->_dao->get($oid);
    }

    public function getList(array $conf, &$total, $order, $start = 0, $num = 20)
    {
        if (empty($order))
        {
            $order = 'order by oid desc';
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

    public function getListWhere($where, &$total, $order='', $start = 0, $num = 20)
    {
        if (empty($order))
        {
            $order = 'order by oid desc';
        }

        // 查询数量
        $total = $this->_dao->getTotal($where);
        if (empty($total))
        {
            return array();
        }
        return $this->_dao->order($order)->limit($start, $num)->getListWhere($where);
    }

    public function update($oid, array $info)
    {
        return $this->_dao->update($oid, $info);
    }

    private function _getWhereByConf($conf)
    {
        // 解析 conf 到 条件 $where
        $where = 'status=' . Conf_Base::STATUS_NORMAL;

        if (!empty($conf['wid']))
        {
            if (is_array($conf['wid']))
            {
                $where .= sprintf(' and wid in (%s)', implode(',', $conf['wid']));
            }
            else
            {
                $where .= ' and wid = ' . $conf['wid'];
            }
        }
        if (!empty($conf['step']))
        {
            $where .= ' and step = ' . $conf['step'];
        }
        if (!empty($conf['type']))
        {
            $where .= ' and type = ' . $conf['type'];
        }
        if (!empty($conf['order_type']))
        {
            $where .= ' and order_type = ' . $conf['order_type'];
        }

        return $where;
    }
}
