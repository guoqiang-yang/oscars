<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/4/13
 * Time: 14:48
 */

class Crm2_Relative extends Base_Func
{
    private $_dao;

    public function __construct()
    {
        $this->_dao = new Data_Dao('t_customer_relative');

        parent::__construct();
    }

    public function get($crid)
    {
        return $this->_dao->get($crid);
    }

    public function add($cid, $info)
    {
        $info['cid'] = $cid;

        return $this->_dao->add($info);
    }

    public function update($crid, $update)
    {
        return $this->_dao->update($crid, $update);
    }

    public function delete($crid)
    {
        return $this->_dao->delete($crid);
    }

    public function getListByCid($cid, $start = 0, $num = 20)
    {
        $where = sprintf('cid=%d AND status=%d', $cid, Conf_Base::STATUS_NORMAL);

        return $this->_dao->limit($start, $num)->order('crid', 'desc')->getListWhere($where);
    }

    public function getCustomerRelativeNum($cid)
    {
        $where = sprintf('cid=%d AND status=%d', $cid, Conf_Base::STATUS_NORMAL);

        return $this->_dao->getTotal($where);
    }
}