<?php

/**
 * 商品相关业务
 */
class Warehouse_Inventory_Task extends Base_Func
{
    private $_dao;

    public function __construct()
    {
        $this->_dao = new Data_Dao('t_inventory_task');

        parent::__construct();
    }

    public function add(array $info)
    {
        $info['ctime'] = date('Y-m-d H:i:s');
        return $this->_dao->add($info);
    }

    public function batchAdd($suid, $pid, $wid, $times, $taskNum)
    {
        assert(!empty($times));
        $tasks = array();
        for ($i = 0; $i < $taskNum; $i++)
        {
            $tasks[$i]['suid'] = $suid;
            $tasks[$i]['plan_id'] = $pid;
            $tasks[$i]['wid'] = $wid;
            $tasks[$i]['times'] = $times;
            $tasks[$i]['ctime'] = date('Y-m-d H:i:s');
        }

        return $this->_dao->batchAdd($tasks);
    }

    public function get($tid)
    {
        return $this->_dao->get($tid);
    }

    public function getList(array $conf, &$total, $order, $start = 0, $num = 20)
    {
        if (empty($order))
        {
            $order = 'order by tid desc';
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

    public function getListWhere($where, &$total, $fields, $order, $start = 0, $num = 20)
    {
        if (empty($order))
        {
            $order = 'order by tid desc';
        }

        // 查询数量
        $total = $this->_dao->getTotal($where);
        if (empty($total))
        {
            return array();
        }

        // 查询结果
        return $this->_dao->setFields($fields)->order($order)->limit($start, $num)->getListWhere($where, false);
    }

    public function update($tid, array $info)
    {
        return $this->_dao->update($tid, $info);
    }

    public function updateWhere($where, array $info, array $change=array())
    {
        return $this->_dao->updateWhere($where, $info, $change);
    }

    private function _getWhereByConf($conf)
    {
        // 解析 conf 到 条件 $where
        $where = 'status=' . Conf_Base::STATUS_NORMAL;

        if ($this->is($conf['plan_id']))
        {
            $where .= sprintf(' and plan_id=%d', $conf['plan_id']);
        }
        if ($this->is($conf['step']))
        {
            $where .= sprintf(' and step = %d', $conf['step']);
        }
        if ($this->is($conf['times']))
        {
            $where .= sprintf(' and times=%d', $conf['times']);
        }
        if ($this->is($conf['alloc_suid']))
        {
            $where .= sprintf(' and alloc_suid=%d', $conf['alloc_suid']);
        }
        if (isset($conf['num']))
        {
            $where .= sprintf(' and num=%d', $conf['num']);
        }

        return $where;
    }
}
