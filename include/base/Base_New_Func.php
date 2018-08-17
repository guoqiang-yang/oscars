<?php

/**
 * 新增类，封装了增删改查的一些基础方法，避免每次新增Func层都要写一遍
 *
 */
class Base_New_Func
{
    protected $_dao;
    private $_start = 0;
    private $_num = 20;
    private $_order = '';
    private $_field = array('*');

    public function __construct($dao)
    {
        $this->_dao = new Data_Dao($dao);
    }

    public function is($param)
    {
        return (isset($param) && !empty($param)) ? TRUE : FALSE;
    }

    public function add($info)
    {
        return $this->_dao->add($info);
    }

    public function update($id, $update, $change = array())
    {
        return $this->_dao->update($id, $update, $change);
    }

    public function updateWhere($where, $update, $change = array())
    {
        return $this->_dao->updateWhere($where, $update, $change);
    }

    public function delete($id)
    {
        return $this->_dao->delete($id);
    }

    public function deleteWhere($where)
    {
        if (is_array($where))
        {
            $where = $this->genWhereByArr($where);
        }
        return $this->_dao->deleteWhere($where);
    }

    public function get($id)
    {
        return $this->_dao->get($id);
    }

    public function getBulk($ids)
    {
        return $this->_dao->getList($ids);
    }

    public function limit($start, $num)
    {
        $this->_start = $start;
        $this->_num = $num;

        return $this;
    }

    public function order($order)
    {
        $this->_order = $order;

        return $this;
    }

    public function field($field)
    {
        $this->_field = $field;

        return $this;
    }

    public function getList($where, $withPK=true)
    {
        if (is_array($where))
        {
            $where = $this->genWhereByArr($where);
        }
        $list = $this->_dao->order($this->_order)->limit($this->_start, $this->_num)->setFields($this->_field)->getListWhere($where, $withPK);
        $this->_resetPara();

        return $list;
    }

    public function getTotal($where)
    {
        if (is_array($where))
        {
            $where = $this->genWhereByArr($where);
        }

        return $this->_dao->getTotal($where);
    }

    protected function genWhereByArr($searchConfArr)
    {
        $where = '';

        foreach ($searchConfArr as $key => $val)
        {
            if (empty($where))
            {
                $where .= sprintf('`%s`="%s"', $key, $val);
            }
            else
            {
                $where .= sprintf(' AND `%s`="%s"', $key, $val);
            }
        }

        return $where;
    }

    public function first($where)
    {
        $first = array();

        $list = $this->limit(0, 1)->getList($where);
        if (!empty($list))
        {
            $first = array_shift($list);
        }
        $this->_resetPara();

        return $first;
    }

    private function _resetPara()
    {
        $this->_start = 0;
        $this->_num = 20;
        $this->_order = '';
        $this->_field = array('*');
    }
}
