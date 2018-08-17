<?php

/**
 * 商品相关业务
 */
class Warehouse_Other_Stock_Out_Products extends Base_Func
{
	private $_dao;

	public function __construct()
	{
		$this->_dao = new Data_Dao('t_other_stock_products');

		parent::__construct();
	}

    public function add($oid, $products)
    {
        assert($oid);
        assert($products);

        foreach($products as $one)
        {
            $one['oid'] = $oid;
            $one['ctime'] = date('Y-m-d H:i:s');
            $info = array('cost');
            $change = array('num' => $one['num']);
            $ret = $this->_dao->add($one, $info, $change);
        }

        return $ret;
    }

    public function getList($conf)
    {
        assert($conf);

        $where = $this->_getWhereByConf($conf);

        // 查询结果
        return $this->_dao->getListWhere($where, false);
    }

    public function update($oid, array $info)
    {
        return $this->_dao->update($oid, $info);
    }

    public function updateByConf($conf, $info)
    {
        $where = $this->_getWhereByConf($conf);

        return $this->_dao->updateWhere($where, $info);
    }

    private function _getWhereByConf($conf)
    {
        // 解析 conf 到 条件 $where
        $where = '1=1';

        if (!empty($conf['oid']))
        {
            if (is_array($conf['oid']))
            {
                $where .= ' and oid in (' . implode(',', $conf['oid']) . ')';
            }
            else
            {
                $where .= ' and oid = ' . $conf['oid'];
            }
        }
        if (!empty($conf['sid']))
        {
            if (is_array($conf['sid']))
            {
                $where .= sprintf(' and sid in (%s)', implode(',', $conf['sid']));
            }
            else
            {
                $where .= ' and sid = ' . $conf['sid'];
            }
        }
        if (isset($conf['status']))
        {
            $where .= sprintf(' and status = %d', $conf['status']);
        }

        return $where;
    }

    public function deleteWhere($where)
    {
        return $this->_dao->deleteWhere($where);
    }
}
