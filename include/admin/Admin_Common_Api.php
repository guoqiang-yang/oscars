<?php

/**
 * 后台管理 通用功能接口API.
 * 
 */
class Admin_Common_Api extends Base_Api
{
    
    /**
     * 写后台log - 操作日志.
     */
    public static function addAminLog($info)
    {
        if (empty($info['obj_id']))
        {
            throw new Exception('请输入对象ID');
        }
        if (!Conf_Admin_Log::hasObjType($info['obj_type']))
        {
            throw new Exception('未定义日志对象的类型');
        }
        if (!Conf_Admin_Log::hasActionType($info['obj_type'], $info['action_type']))
        {
            throw new Exception('未定义日志对象的操作类型');
        }
        
        
        $year = date('Y');
        $alc = new Admin_Log_Common($year);
        $lid = $alc->add($info);
        
        return $lid;
    }
    
    /**
     * 获取后台操作日志.
     * 
     */
    public static function fetchAdminLog($conf, $start=0, $num=20)
    {
        $year = date('Y');
        $alc = new Admin_Log_Common($year);
        
        $logs = array();
        $total = $alc->getSumByWhere($conf);
        
        if ($total > 0)
        {
            $logs = $alc->getByWhere($conf, $start, $num);

            $as = new Admin_Staff();
            $adminField = array('suid', 'name');
            $adminIds = Tool_Array::getFields($logs, 'admin_id');
            $adminInfos = Tool_Array::list2Map($as->getUsers($adminIds, $adminField), 'suid');

            foreach($logs as &$log)
            {
                $log['_user'] = $adminInfos[$log['admin_id']];
            }
        }
        
        return array('total'=>$total, 'data'=>$logs);
    }
}