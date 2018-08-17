<?php

/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/10/17
 * Time: 下午2:15
 */
class Crm2_User_Request_Time extends Base_Func
{
    private $_dao = null;

    function __construct()
    {
        parent::__construct();
        $this->_dao = new Data_Dao('t_user_kvstore');
    }

    public function add($info)
    {
        return $this->_dao->add($info);
    }

    public function getByUid($uid)
    {
        $where = "uid=".$uid;
        return $this->_dao->getListWhere($where,false);
    }

    public function getByUidName($uid, $name)
    {
        $where = sprintf("name='%s' And uid=%d", $name, $uid);
        return $this->_dao->getListWhere($where,false);
    }

    public function Update($id,$info)
    {
        $data = $this->_dao->update($id, $info);
        return $data;
    }
}