<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/1/11
 * Time: 10:26
 */
class Permission_Role extends Base_Func
{
    private $_dao;


    public function __construct()
    {
        parent::__construct();
        
        $this->_dao = new Data_Dao('t_role');        
    }

    public function add($info)
    {
        return $this->_dao->add($info);
    }

    public function delete($id)
    {
        return $this->_dao->delete($id);
    }

    public function update($id, $update, $change = array())
    {
        return $this->_dao->update($id, $update, $change);
    }

    public function get($id)
    {
        return $this->_dao->get($id);
    }

    public function getByKeys($keys, $fields=array('*'))
    {
        $where = array('rkey' => $keys);
        $list = $this->_dao->setFields($fields)->getListWhere($where);
        if (empty($list))
        {
            return array();
        }

        return Tool_Array::list2Map($list, 'rkey');
    }

    public function getBulk($ids)
    {
        return $this->_dao->getList($ids);
    }

    public function getAll()
    {
        return $this->_dao->getAll();
    }

    public function getByWhere($where, $start=0, $num=20, $field=array('*'))
    {
        return $this->_dao->setFields($field)->limit($start, $num)->getListWhere($where, false);
    }
    
    public function getList($search, $start = 0, $num = 20)
    {
        $where = $this->_getWhereByConf($search);
        $total = $this->_dao->getTotal($where);
        if ($total <= 0)
        {
            return array('total' => 0, 'list' => array());
        }

        $list = $this->_dao->limit($start, $num)->getListWhere($where);

        return array('total' => $total, 'list' => $list);
    }

    public function getListWhere($where, $field, $start, $num)
    {
        $total = $this->_dao->getTotal($where);
        if ($total <= 0)
        {
            return array('total' => 0, 'list' => array());
        }
        $list = $this->_dao->setFields($field)->limit($start, $num)->getListWhere($where);

        return array('total' => $total, 'list' => $list);
    }

    private function _getWhereByConf($conf)
    {
        $where = sprintf('status=%d', Conf_Base::STATUS_NORMAL);

        if (!empty($conf['role']))
        {
            $where .= sprintf(' AND role LIKE "%%%s%%"', $conf['role']);
        }
        if (!empty($conf['department']))
        {
            $where .= sprintf(' AND department=%d', $conf['department']);
        }

        return $where;
    }
}