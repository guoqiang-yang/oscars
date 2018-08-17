<?php
/**
 * 拜访相关接口
 * Created by PhpStorm.
 * User: zouliangwei
 * Date: 2017/4/10
 * Time: 下午2:00
 */
class Crm2_Customer_Visit_Api extends Base_Api
{
    /**
     * 添加拜访
     * @param $cid
     * @param $suid
     * @param $info
     * @return mixed
     */
    public static function addCustomerVisit($cid, $info)
    {
        if(empty($info['cid']))
        {
            $info['cid'] = $cid;
        }
        $customer = Crm2_Api::getCustomerInfo($cid);
        $info['city_id'] = $customer['customer']['city_id'];

        $cv = new Crm2_Customer_Visit();
        return $cv->add($info);
    }

    public static function update($vid, $info)
    {
        $cv = new Crm2_Customer_Visit();

        return $cv->update($vid, $info);
    }

    public static function get($id)
    {
        $cv = new Crm2_Customer_Visit();

        $info = $cv->get($id);
        self::_formatPic($info);
        $item['can_edit'] = false;
        if (time() <= strtotime($item['ctime']) + Conf_Crm::EDIT_VISIT_INTERVAL)
        {
            $item['can_edit'] = true;
        }
        return $info;
    }

    public static function getList($suid, $start = 0, $num = 20)
    {
        $where = sprintf('suid=%d AND status=%d', $suid, Conf_Base::STATUS_NORMAL);
        $cv = new Crm2_Customer_Visit();
        $total = $cv->getTotal($where);
        $list = array();
        if ($total > 0)
        {
            $list = $cv->getListByWhere($where, $start, $num);
            if (!empty($list))
            {
                $cids = Tool_Array::getFields($list, 'cid');
                $cids = array_filter(array_unique($cids));
                $customerInfos = array();
                if (!empty($cids))
                {
                    $customerInfos = Crm2_Api::getCustomers($cids);
                    $customerInfos = Tool_Array::list2Map($customerInfos, 'cid');
                }
                $now = time();
                foreach ($list as &$item)
                {
                    $item['customer_info'] = $customerInfos[$item['cid']];
                    $item['image_list'] = array();
                    if (!empty($item['pic_ids']))
                    {
                        $picIds = explode(',', $item['pic_ids']);
                        foreach ($picIds as $picId)
                        {
                            $item['image_list'][] = Oss_Api::getImageUrl($picId);
                        }
                    }
                    $item['can_edit'] = false;
                    if ($now <= strtotime($item['visit_time']) + Conf_Crm::EDIT_VISIT_INTERVAL)
                    {
                        $item['can_edit'] = true;
                    }
                 }
            }
        }

        return array(
            'list' => $list,
            'total' => $total,
        );
    }

    public static function getListByWhereString($where, $start = 0, $num = 20)
    {
        if (!empty($where))
        {
            $where .= sprintf(' AND status=%d', Conf_Base::STATUS_NORMAL);
        }
        else
        {
            $where = sprintf('status=%d', Conf_Base::STATUS_NORMAL);
        }

        $cv = new Crm2_Customer_Visit();
        $list = $cv->getListByWhere($where, $start, $num);

        return $list;
    }

    public static function getListByWhere($conf,  &$total, $start = 0, $num = 20)
    {
        $cv = new Crm2_Customer_Visit();
        $list = $cv->getList($conf, $total, $start, $num);
        if(!empty($list)){
            $suids = Tool_Array::getFields($list, 'suid');
            $suids = array_filter(array_unique($suids));
            $adminList = Admin_Api::getStaffs($suids);
            $adminList = Tool_Array::list2Map($adminList, 'suid');
            $cids = Tool_Array::getFields($list, 'cid');
            $cids = array_filter(array_unique($cids));
            $customerInfos = array();
            if (!empty($cids))
            {
                $customerInfos = Crm2_Api::getCustomers($cids);
                $customerInfos = Tool_Array::list2Map($customerInfos, 'cid');
            }
            $now = time();
            foreach ($list as $key=>&$item)
            {
                $list[$key]['sale_name'] = $adminList[$item['suid']]['name'];
                $item['customer_info'] = $customerInfos[$item['cid']];
                self::_formatPic($item);
                $item['can_edit'] = false;
                if ($now <= strtotime($item['ctime']) + Conf_Crm::EDIT_VISIT_INTERVAL)
                {
                    $item['can_edit'] = true;
                }
            }
        }

        return $list;
    }

    private static function _formatPic(&$info)
    {
        $info['image_list'] = array();
        if (!empty($info['pic_ids']))
        {
            $picIds = explode(',', $info['pic_ids']);
            foreach ($picIds as $picId)
            {
                $info['image_list'][] = array('pic_tag' => $picId, 'url' =>Oss_Api::getImageUrl($picId));
            }
        }
    }

    public static function getCustomerVisitList($cid, $start = 0, $num = 20)
    {
        $where = sprintf('cid=%d AND status=%d', $cid, Conf_Base::STATUS_NORMAL);
        $cv = new Crm2_Customer_Visit();
        $total = $cv->getTotal($where);
        $list = array();
        if ($total > 0)
        {
            $typeList = Conf_Crm::getTypeList();
            $list = $cv->getListByWhere($where, $start, $num);
            if (!empty($list))
            {
                $cids = Tool_Array::getFields($list, 'cid');
                $cids = array_filter(array_unique($cids));
                $customerInfos = array();
                if (!empty($cids))
                {
                    $customerInfos = Crm2_Api::getCustomers($cids);
                }
                $suids = Tool_Array::getFields($list, 'suid');
                $suids = array_filter(array_unique($suids));
                $admins = Admin_Api::getStaffs($suids);
                $adminMaps = Tool_Array::list2Map($admins, 'suid');

                $now = time();
                foreach ($list as &$item)
                {
                    $item['customer_info'] = $customerInfos[$item['cid']];
                    $item['image_list'] = array();
                    if (!empty($item['pic_ids']))
                    {
                        $picIds = explode(',', $item['pic_ids']);
                        foreach ($picIds as $picId)
                        {
                            $item['image_list'][] = Oss_Api::getImageUrl($picId);
                        }
                    }
                    $item['can_edit'] = false;
                    if ($now <= strtotime($item['ctime']) + Conf_Crm::EDIT_VISIT_INTERVAL)
                    {
                        $item['can_edit'] = true;
                    }
                    $item['_visit_time'] = date('Y.m.d H:i', strtotime($item['visit_time']));
                    $item['_visit_type'] = $typeList[$item['visit_type']];
                    $item['_admin'] = $adminMaps[$item['suid']];
                }
            }
        }

        return array(
            'list' => $list,
            'total' => $total,
        );
    }
}