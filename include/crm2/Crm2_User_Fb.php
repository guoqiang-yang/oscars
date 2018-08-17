<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/10/11
 * Time: 下午5:05
 * 客户 - 问题反馈
 */

class Crm2_User_Fb extends Base_Func
{
    private $_dao = null;

    function __construct()
    {
        parent::__construct();
        $this->_dao = new Data_Dao('t_user_fb');
    }

    public function add($info)
    {
        return $this->_dao->add($info);
    }

    public function get($id)
    {
        return $this->_dao->get($id);
    }

    public function getList($searchConf,  $start = 0, $num = 20, $order = '', $fields = array('*'))
    {
        $where = $this->_getWhereByConf($searchConf);
        $total = $this->_dao->getTotal($where);
        if ($total <= 0)
        {
            return array('list' => array(), 'total' => 0);
        }

        $list = $this->_dao->limit($start, $num)->setFields($fields)->order('fid','desc')->getListWhere($where);

        return array('list' => $list, 'total' => $total);
    }
    public function update($id, $update, $change = array())
    {
        return $this->_dao->update($id, $update, $change);
    }
    private function _getWhereByConf($conf)
    {
        $where  = '1=1';

        if (!empty($conf['fid']))
        {
            $where .= sprintf(' AND fid=%d', $conf['fid']);
        }
        if (!empty($conf['contact_phone']))
        {
            $where .= sprintf(" AND contact_phone='%s'", $conf['contact_phone']);
        }
        if (!empty($conf['contact_name']))
        {
            $where .=" AND contact_name like '%".$conf['contact_name']."%'";
        }
        if (!empty($conf['uid']))
        {
            $where .= sprintf(' AND uid=%d', $conf['uid']);
        }
        if (!empty($conf['sale_id']))
        {
            $where .= sprintf(' AND sale_id=%d', $conf['sale_id']);
        }
        if (!empty($conf['ensure_id']))
        {
            $where .= sprintf(' AND ensure_id=%d', $conf['ensure_id']);
        }
        if (isset($conf['ensure_status']))
        {
            $where .= sprintf(' AND ensure_status=%d', $conf['ensure_status']);
        }
        if (!empty($conf['from_date']))
        {
            $where .= sprintf(' AND date(ctime)>="%s"', $conf['from_date']);
        }
        if (!empty($conf['end_date']))
        {
            $where .= sprintf(' AND date(ctime)<="%s"', $conf['end_date']);
        }


        return $where;
    }

}