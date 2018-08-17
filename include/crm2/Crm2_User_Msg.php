<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/10/14
 * Time: 上午10:16
 */
class Crm2_User_Msg extends Base_Func
{
    private $_dao = null;

    function __construct()
    {
        parent::__construct();
        $this->_dao = new Data_Dao('t_user_msg');
    }

    public function add($info)
    {
        return $this->_dao->add($info);
    }

    public function get($id)
    {
        return $this->_dao->get($id);
    }

    public function getByUidCid($uid,$cid,$type,$start,$num)
    {
        $type = implode(',',$type);
        $where = 'uid='.$uid.' AND cid='.$cid.' AND m_type in ('.$type.')';

        $total = $this->_dao->getTotal($where);
        $list = $this->_dao->limit($start,$num)->order('mtime','desc')->getListWhere($where);

        return array('list' => $list, 'total' => $total);
    }
    public function getMsgList($uid,$cid,$date)
    {
        $where = 'uid='.$uid.' AND cid='.$cid." AND mtime>'".$date."'";

        $list = $this->_dao->order('mtime','asc')->getListWhere($where);

        return $list;
    }
    public function getNewestWithType($uid,$cid,$type)
    {
        $type = implode(',',$type);
        $where = 'uid='.$uid.' AND cid='.$cid.' AND m_type in ('.$type.')';

        $list = $this->_dao->order('mtime','desc')->limit(0,1)->getListWhere($where);
        $list = array_values($list);

        return $list[0];
    }

}