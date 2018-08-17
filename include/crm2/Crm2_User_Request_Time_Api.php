<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/10/17
 * Time: 下午2:15
 */
class Crm2_User_Request_Time_Api extends Base_Api
{
    //记录用户上次请求center页面的时间，有则更改，无则增加,返回上次访问的时间戳
    public static function recordLastTime($uid, $name='', $update=false)
    {
        $aa = new Crm2_User_Request_Time();
        $dateTime = '0000-00-00 00:00:00';
        //name为空，则返回所有uid下的最早的时间
        if (empty($name))
        {
            $data = $aa->getByUid($uid);
            $dateTime = $data[0]['value'];
            if (!empty($data[0]['value']))
            {
                foreach ($data as  $item)
                {
                    $dateTime = $dateTime > $item['value'] ? $item['value']:$dateTime;
                }
            }
        }
        else
        {

            $data = $aa->getByUidName($uid, $name);
            $dateTime = $data[0]['value'];

        }
        $info = array( 'value' =>   date('Y-m-d H:i:s',time()),);
        if (empty($dateTime))
        {
            $info['name'] = $name;
            $info['uid'] = $uid;
            $aa->add($info);
        }
        else
        {
            if ($update)
            {
                $aa->update($data[0]['id'],$info);

            }
        }
        return $dateTime;
    }
    public static function getTimeByUid($uid)
    {
        $aa = new Crm2_User_Request_Time();
        $data = $aa->getByUid($uid);
        $new = array(
            1 => '0000-00-00 00:00:00',
            2 => '0000-00-00 00:00:00',
            3 => '0000-00-00 00:00:00',
            4 => '0000-00-00 00:00:00',
        );
        foreach ($data as $item) {
            switch ($item['name'])
            {
                case Conf_User_Msg::$MSG_CATE1[Conf_User_Msg::$MSG_CX]:
                    $new[1] = $item['value'];
                    break;
                case Conf_User_Msg::$MSG_CATE1[Conf_User_Msg::$MSG_WL]:
                    $new[2] = $item['value'];
                    break;
                case Conf_User_Msg::$MSG_CATE1[Conf_User_Msg::$MSG_ZC]:
                    $new[3] = $item['value'];
                    break;
                case Conf_User_Msg::$MSG_CATE1[Conf_User_Msg::$MSG_XT]:
                    $new[4] = $item['value'];
                    break;
            }
        }
        return $new;
    }
    //判断是否首次app登陆
    public static function isFristLg($uid)
    {
        $aa = new Crm2_User_Request_Time();
        $name = Conf_User_Msg::$FIRST_LG;
        $data = $aa->getByUidName($uid, $name);
        if (empty($data))
        {
            $info = array(
                'uid' => $uid,
                'name' => $name,
                'value' => date('Y-m-d H:i:s'),
            );
            $aa->add($info);
            return true;
        }
        else
        {
            return false;
        }
    }
}