<?php
/**
 * 日程相关接口
 * Created by PhpStorm.
 * User: zouliangwei
 * Date: 2017/4/10
 * Time: 下午2:00
 */
class Crm2_Sale_Schedule_Api extends Base_Api
{
    /**
     * 添加日程
     * @param $suid
     * @param $info
     * @return mixed
     */
    public static function addSaleSchedule($suid, $info)
    {
        assert($suid > 0);
        $cv = new Crm2_Sale_Schedule();
        return $cv->add($info);
    }

    public static function getListByDate($date, $suid, $start = 0, $num = 20)
    {
        $where = sprintf('status=%d AND suid=%d AND date(schedule_time)="%s"', Conf_Base::STATUS_NORMAL, $suid, $date);
        $css = new Crm2_Sale_Schedule();
        $list = $css->getListByWhere($where, $start, $num);
        $total = $css->getTotal($where);

        if (!empty($list))
        {
            $cids = Tool_Array::getFields($list, 'cid');
            $cids = array_filter(array_unique($cids));
            if (!empty($cids))
            {
                $customers = Crm2_Api::getCustomers($cids);
                foreach ($list as &$item)
                {
                    $item['customer_info'] = $customers[$item['cid']];
                }
            }
        }

        return array(
            'list' => $list,
            'total' => $total,
        );
    }

    public static function get($id)
    {
        $css = new Crm2_Sale_Schedule();

        return $css->get($id);
    }

    public static function getBulk($ids)
    {
        $css = new Crm2_Sale_Schedule();

        return $css->getBulk($ids);
    }

    public static function update($id, $info)
    {
        $css = new Crm2_Sale_Schedule();

        return $css->update($id, $info);
    }

    public static function getListByWhere($where, $start = 0, $num = 20)
    {
        $css = new Crm2_Sale_Schedule();

        return $css->getListByWhere($where, $start, $num);
    }

    public static function getSaleScheduleList($conf, &$total, $start = 0, $num = 20)
    {
        $css = new Crm2_Sale_Schedule();
        $scheduleList = $css->getList($conf, $total, $start, $num);
        if(!empty($scheduleList)){
            $suids = Tool_Array::getFields($scheduleList, 'suid');
            $adminList = Admin_Api::getStaffs($suids);
            $adminList = Tool_Array::list2Map($adminList, 'suid');
            $cids = array_unique(Tool_Array::getFields($scheduleList, 'cid'));
            $customerList = Crm2_Api::getCustomers($cids);
            if(!empty($customerList))
            {
                $customerList = Tool_Array::list2Map($customerList, 'cid');
            }
            foreach ($scheduleList as $key=>$item)
            {
                $scheduleList[$key]['sale_name'] = $adminList[$item['suid']]['name'];
                if($item['cid']>0)
                {
                    $scheduleList[$key]['customer_name'] = $customerList[$item['cid']]['name'];
                }
            }
        }
        return $scheduleList;
    }
}