<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/4/13
 * Time: 14:47
 */
class Crm2_Relative_Api extends Base_Api
{
    public static function get($crid)
    {
        $cr = new Crm2_Relative();

        return $cr->get($crid);
    }
    public static function add($cid, $info)
    {
        $cr = new Crm2_Relative();

        return $cr->add($cid, $info);
    }

    public static function update($crid, $info)
    {
        $cr = new Crm2_Relative();

        return $cr->update($crid, $info);
    }

    public static function delete($crid)
    {
        $cr = new Crm2_Relative();

        return $cr->delete($crid);
    }

    public static function getList($cid, $start = 0, $num = 20)
    {
        $cr = new Crm2_Relative();

        $total = $cr->getCustomerRelativeNum($cid);
        if  ($total <= 0)
        {
            return array('total' => 0, 'list' => array());
        }

        $list = $cr->getListByCid($cid, $start, $num);
        $relationList = Conf_Crm::getRelationList();
        foreach ($list as &$item)
        {
            $item['_relation'] = $relationList[$item['relation']];
        }

        return array('total' => $total, 'list' => $list);
    }
}