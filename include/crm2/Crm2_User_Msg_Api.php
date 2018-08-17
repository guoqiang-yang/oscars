<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/10/14
 * Time: 上午10:17
 */
class Crm2_User_Msg_Api extends Base_Api
{
   
    public static function getOne($pk)
    {
        $aa = new Crm2_User_Msg();
        $data = $aa->get($pk);
        return $data;
    }
    public static function addMsg($uid, $cid, $msgType, $content = array())
    {
        $um = new Crm2_User_Msg();

        $info = array(
            'uid' => $uid,
            'cid' => $cid,
            'm_type' => $msgType,
            'content' => json_encode($content),
        );

        return $um->add($info);
    }

    public static function getMSgList($uid, $cid, $type, $start = 0, $num = 0)
    {
        $um = new Crm2_User_Msg();
        if ($type == 1)
        {
            $_type = array(7);
        }
        else if ($type == 2)
        {
            $_type = array(1,2);
        }
        else if ($type == 3)
        {
            $_type = array(3,4);
        }
        else if ($type == 4)
        {
            $_type = array(5,6);
        }
        $data = $um->getByUidCid($uid, $cid, $_type, $start, $num);

        foreach ($data['list'] as &$info) {
            if ($type == 1)
            {
                /*$info['url'] = 'www.baidu.com';
                $info['desc'] = '这只是测试而已这只是测试而已这只是测试而已这只是测试而已这只是测试而已这只是测试而已';
                $info['imgurl'] = 'http://img.haocaisong.cn/test_pic/1/72/17213.jpg@100w_100h_1e_0c';
                $info['m_type'] = '优惠促销';*/
                $data = array();
            }
            else
            {
                $desc = Conf_User_Msg::$MSG_DESC[$info['m_type']];
                if (!empty($info['content']))
                {
                    $content = json_decode($info['content'],true);
                    if (!empty($content))
                    {
                        foreach ($content as $k => $v)
                        {
                            $desc = str_replace('{' . $k . '}', $v, $desc);
                        }
                    }
                }
                $info['oid'] = '';

                if (!empty($content['ocode']))
                {
                    $info['oid'] = substr($content['ocode'],9);
                }

                $info['desc'] = $desc;
                $info['m_type'] = Conf_User_Msg::$MSG_TYPE[$info['m_type']];
            }

        }
        if ($type == 2)
        {
            $oids = Tool_Array::getFields($data['list'],'oid');
            $products = Order_Api::getProductByOids($oids);
            $pids = array_unique(Tool_Array::getFields($products,'pid'));

            $productsInfo = Shop_Api::getProductInfos($pids);

            foreach ($data['list'] as  &$info) {
                foreach ($products as  $pro ) {
                    if ($pro['oid'] == $info['oid'])
                    {
                        $info['imgurl'] = $productsInfo[$pro['pid']]['sku']['_pic']['middle'];
                    }
                }
                if (empty($info['imgurl']))
                {
                    $info['imgurl'] = '';
                }
            }
        }
        $total = $data['total'];
        $has_more = $total > ($start + $num);
        return array('list' => array_values($data['list']), 'total' => $total, 'has_more' => $has_more);
    }
    //获取每个类型的未读消息的总数及首条消息
    public static function getMSgNum($uid, $cid, array $date)
    {
        $um = new Crm2_User_Msg();
        $nowYear = date('Y');
        $nowDay = date('Y-m-d');
        //最早的时间
        $latestTime = $date[1];
        foreach ($date as $t_item)
        {
            $latestTime = $latestTime > $t_item ? $t_item : $latestTime;
        }

        $data = $um->getMsgList($uid, $cid, $latestTime);

        //获得每种类型的最新的消息
        //优惠促销
        $new1 = $um->getNewestWithType($uid, $cid, array(7));
        //物流
        $new2 = $um->getNewestWithType($uid, $cid, array(1,2));
        //优惠券
        $new3 = $um->getNewestWithType($uid, $cid, array(3,4));
        //系统消息
        $new4 = $um->getNewestWithType($uid, $cid, array(5,6));

        $promotion = array(
                        'num' => 0,
                        'info' => array(),
                        'title' => '优惠促销',
                        'headimg' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/app_icon/icon_list_youhui.png',
                    );
        $logistics = array(
                        'num' => 0,
                        'info' => $new2,
                        'title' => '物流通知',
                        'headimg' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/app_icon/icon_list_wuliu.png',

                    );
        $coupon = array(
                        'num' => 0,
                        'info' => $new3,
                        'title' => '我的资产',
                        'headimg' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/app_icon/icon_list_zichan.png',
                    );
        $system = array(
                        'num' => 0,
                        'info' => $new4,
                        'title' => '系统通知',
                        'headimg' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/app_icon/icon_list_tongzhi.png',
                    );

        foreach ($data as &$info)
        {
            if (($info['m_type'] == 1 || $info['m_type'] == 2) && $info['mtime'] > $date[2])
            {
                $logistics['num']++;
            }
            if (($info['m_type'] == 3 || $info['m_type'] == 4) && $info['mtime'] > $date[3])
            {
                $coupon['num']++;
            }
            if (($info['m_type'] == 5 || $info['m_type'] == 6) && $info['mtime'] > $date[4])
            {
                $system['num']++;
            }
            if (($info['m_type'] == 7) && $info['mtime'] > $date[1])
            {
                $promotion['num']++;
            }
        }

        $data = array('1' => $promotion, '2' => $logistics, '3' => $coupon,  '4' => $system);
        foreach ($data as $key => &$item)
        {

            if (empty($item['info']))
            {
                $item['has_msg'] = false;
            }
            else
            {
                $item['has_msg'] = true;
            }
            $shortTime = strtotime($item['info']['ctime']);
            /*if (!empty($item['_info']) && $item['num'] > 0)
            {*/
            if ($nowYear == date('Y', $shortTime))
            {
                if ($nowDay == date('Y-m-d', $shortTime))
                {
                    $time = date('H:i', $shortTime);
                }
                else
                {
                    $time = date('m月d日', $shortTime);
                }
            }
            else
            {
                    $time = date('Y年m月d日', $shortTime);
            }
            $item['info']['_ctime'] = $time;
            $desc = Conf_User_Msg::$MSG_DESC[$item['info']['m_type']];
            if (!empty($item['info']['content']))
            {
                $content = json_decode($item['info']['content'],true);
                if (!empty($content))
                {
                    foreach ($content as $k => $v)
                    {
                        $desc = str_replace('{' . $k . '}', $v, $desc);
                    }
                }
            }
            $item['info']['desc'] = $desc;
            $item['type'] = $key;

        }
        $data = array_values($data);
        return $data;
    }

}