<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/4/13
 * Time: 14:48
 */

class Crm2_User_Product_Relation extends Base_Func
{
    const DB_NAME = 't_user_product_relation';

    public function set($uid, $pid, $info)
    {
        $res = $this->one->select(self::DB_NAME, array('*'), array('uid'=> $uid, 'pid' => $pid));
        if (empty($res['data']))
        {
            $info['ctime'] = date('Y-m-d H:i:s', time());
            $ret = $this->one->setDefaultDB()->insert(self::DB_NAME, $info);
        }else{
            $ret = $this->one->setDefaultDB()->update(self::DB_NAME, $info, array(), array('uid'=> $uid, 'pid' => $pid));
        }
        return intval($ret['affectedrows']);
    }

    public function delete($uid, $pid)
    {
        $ret = $this->one->delete(self::DB_NAME, array('uid' => $uid, 'pid' => $pid));
        return intval($ret['affectedrows']);
    }

    public function getListByUid($uid, $start = 0, $num = 20, $conf = array())
    {
        $where = sprintf('pr.uid=%d AND pr.status=%d AND tp.status=%d', $uid, Conf_Base::STATUS_NORMAL, Conf_Base::STATUS_NORMAL);

        if(!empty($conf))
        {
            if(!empty($conf['bid']))
            {
                $where .= sprintf(' AND ts.bid=%d', $conf['bid']);
            }
            if(!empty($conf['cate2']))
            {
                $where .= sprintf(' AND ts.cate2=%d', $conf['cate2']);
            }
            if(!empty($conf['cate3']))
            {
                $where .= sprintf(' AND ts.cate3=%d', $conf['cate3']);
            }
            if(!empty($conf['mid']))
            {
                $where .= sprintf(' AND FIND_IN_SET(%d,ts.mids)', $conf['mid']);
            }
            if(!empty($conf['city_id']))
            {
                $where .= sprintf(' AND pr.city_id=%d', $conf['city_id']);
            }
        }
        $ret = $this->one->setDBMode()->select('t_user_product_relation AS pr LEFT JOIN t_product AS tp ON pr.pid=tp.pid LEFT JOIN t_sku AS ts ON pr.sid=ts.sid', array('pr.pid,pr.sid,pr.frequency,ts.cate1,ts.cate2,ts.cate3,ts.bid'), $where, 'ORDER BY pr.frequency DESC', $start, $num);
        return $ret['data'];
    }

    public function getDeleteListByUid($uid, $start = 0, $num = 20)
    {
        $where = sprintf('uid=%d AND status=%d', $uid, Conf_Base::STATUS_DELETED);
        $ret = $this->one->select(self::DB_NAME, array('pid'), $where, '', $start, $num);
        if(empty($ret['data']))
        {
            return array();
        }else{
            return Tool_Array::getFields($ret['data'], 'pid');
        }
    }

    public function getTotalByUid($uid, $conf = array())
    {
        $where = sprintf('pr.uid=%d AND pr.status=%d AND tp.status=%d', $uid, Conf_Base::STATUS_NORMAL, Conf_Base::STATUS_NORMAL);

        if(!empty($conf))
        {
            if(!empty($conf['bid']))
            {
                $where .= sprintf(' AND ts.bid=%d', $conf['bid']);
            }
            if(!empty($conf['cate2']))
            {
                $where .= sprintf(' AND ts.cate2=%d', $conf['cate2']);
            }
            if(!empty($conf['cate3']))
            {
                $where .= sprintf(' AND ts.cate3=%d', $conf['cate3']);
            }
            if(!empty($conf['mid']))
            {
                $where .= sprintf(' AND ts.mid=%d', $conf['mid']);
            }
            if(!empty($conf['city_id']))
            {
                $where .= sprintf(' AND pr.city_id=%d', $conf['city_id']);
            }
        }
        $ret = $this->one->setDBMode()->select('t_user_product_relation AS pr LEFT JOIN t_product AS tp ON pr.pid=tp.pid LEFT JOIN t_sku AS ts ON pr.sid=ts.sid', array('count(*) as total'), $where, 'ORDER BY pr.frequency DESC');
        return $ret['data'][0]['total'];
    }

    public function getCateFirstBrandByUid($uid, $cate2, $city_id)
    {
        $where = sprintf('pr.uid=%d AND pr.status=%d AND ts.cate2=%d AND pr.city_id=%d AND ts.bid>0', $uid, Conf_Base::STATUS_NORMAL, $cate2, $city_id);
        $ret = $this->one->setDBMode()->select('t_user_product_relation AS pr LEFT JOIN t_sku AS ts ON pr.sid=ts.sid', array('ts.bid'), $where, 'ORDER BY pr.frequency DESC', 0, 1);
        if(empty($ret['data']))
        {
            return 0;
        }else{
            return $ret['data'][0]['bid'];
        }
    }

    public function getDefaultCate1ByUid($uid, $city_id)
    {
        $where = sprintf('pr.uid=%d AND pr.status=%d AND pr.city_id=%d GROUP BY ts.cate1', $uid, Conf_Base::STATUS_NORMAL, $city_id);
        $ret = $this->one->setDBMode()->select('t_user_product_relation AS pr LEFT JOIN t_sku AS ts ON pr.sid=ts.sid', array('ts.cate1,count(pr.frequency) as num'), $where, 'ORDER BY num DESC', 0, 1);
        if(empty($ret['data']))
        {
            return 1;
        }else{
            return $ret['data'][0]['cate1'];
        }
    }
}