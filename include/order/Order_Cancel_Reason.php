<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/3/3
 * Time: 14:32
 */
class Order_Cancel_Reason extends Base_Func
{
    private $_dao;


    public function __construct()
    {
        $this->_dao = new Data_Dao('t_order_cancel_reason');

        parent::__construct();
    }

    public function add($info)
    {
        return $this->_dao->add($info);
    }

    public function getByOid($oid)
    {
        $where = array('oid' => $oid, 'status' => Conf_Base::STATUS_NORMAL);

        $list = $this->_dao->getListWhere($where);
        if (empty($list))
        {
            return array();
        }

        return array_shift($list);
    }

    public function getList($searchConf, $start = 0, $num = 20)
    {
        $where = $this->_getWhereByConf($searchConf);
        $total = $this->_dao->getTotal($where);
        if ($total <= 0)
        {
            return array('list' => array(), 'total' => 0);
        }

        $list = $this->_dao->limit($start, $num)->getListWhere($where);

        return array('list' => $list, 'total' => $total);
    }

    private function _getWhereByConf($searchConf)
    {
        $where = sprintf('status=%d', Conf_Base::STATUS_NORMAL);

        if (!empty($searchConf['oid']))
        {
            $where .= sprintf(' AND oid=%d', $searchConf['oid']);
        }
        if (!empty($searchConf['cid']))
        {
            $where .= sprintf(' AND cid=%d', $searchConf['cid']);
        }
        if (!empty($searchConf['uid']))
        {
            $where .= sprintf(' AND uid=%d', $searchConf['uid']);
        }
        if (!empty($searchConf['reason']))
        {
            $where .= sprintf(' AND reason=%d', $searchConf['reason']);
        }
        if (!empty($searchConf['start_date']))
        {
            $where .= sprintf(' AND ctime>="%s 00:00:00"', $searchConf['start_date']);
        }
        if (!empty($searchConf['end_date']))
        {
            $where .= sprintf(' AND ctime<="%s 23:59:59"', $searchConf['end_date']);
        }

        return $where;
    }
}