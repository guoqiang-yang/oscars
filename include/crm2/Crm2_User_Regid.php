<?php

/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/10/18
 * Time: 上午10:16
 */
class Crm2_User_Regid extends Base_Func
{
    private $_dao = null;

    function __construct()
    {
        parent::__construct();
        $this->_dao = new Data_Dao('t_user_regid');
    }

    public function add($info)
    {
        return $this->_dao->add($info);
    }

    public function getByUid($uid, $start=0, $num=1)
    {
        $where = "uid=".$uid;
        return $this->_dao->limit($start,$num)->getListWhere($where,false);
    }

    public function Update($id,$info)
    {
        $data = $this->_dao->update($id, $info);
        return $data;
    }
}