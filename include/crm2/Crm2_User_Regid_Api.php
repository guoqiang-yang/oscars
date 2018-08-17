<?php

/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/10/18
 * Time: 上午10:16
 */
class Crm2_User_Regid_Api
{
    //存在切为空则更新，不存在则增加
    public static function recordUserRegid($uid,$info)
    {
        $aa = new Crm2_User_Regid();
        $data = $aa->getByUid($uid);
        if (empty($data))
        {
            $info['uid'] = $uid;
            $ret = $aa->add($info);
        }
        else if ((isset($info['regid']) && $info['regid'] != $data[0]['regid'] && $info['regid'] != 'null')
            || (isset($info['device_id']) && $info['device_id'] != $data[0]['device_id']) && $info['device_id'] != 'null')
        {
            $ret = $aa->update($uid,$info);
        }
        return $ret;
    }
    public static function getByUid($uid)
    {
        $aa = new Crm2_User_Regid();
        return $aa->getByUid($uid);
    }
}