<?php

class Admin_Task_Api extends Base_Api
{
    /**
     * 创建任务.
     * 
     * @param array $taskParam
     * @param int $staffUid
     */
    public static function create($taskParam, $staffUid)
    {
        $at = new Admin_Task();
        $tid = $at->create($taskParam);
        
        // 添加历史
        $historyData = array(
            'tid' => $tid,
            'suid' => $staffUid,
            'exec_suid' => $taskParam['exec_suid'],
            'old_exec_status' => Conf_Admin_Task::ST_CREATE,
            'new_exec_status' => Conf_Admin_Task::ST_WAIT_DEAL,
            'note' => '新建任务',
              
        );
        $at->addHistory($historyData);
        
        return $tid;
    }
    
    /**
     * 更新任务.
     * 
     * @param array $taskInfo
     * @param array $upData
     * @param int $staffUid
     */
    public static function update($taskInfo, $upData, $staffUid, $note='')
    {
        $tid = $taskInfo['tid'];
        assert(!empty($tid));
        
        $at = new Admin_Task();
        
        if (!empty($upData))
        {
            $at->update($tid, $upData);
        }
        
        // 添加历史
        $historyData = array(
            'tid' => $tid,
            'suid' => $staffUid,
            'exec_suid' => isset($upData['exec_suid'])? $upData['exec_suid']: $taskInfo['exec_suid'],
            'old_exec_status' => $taskInfo['exec_status'],
            'new_exec_status' => isset($upData['exec_status'])? $upData['exec_status']: $taskInfo['exec_status'],
            'note' => !empty($note)? $note: '更新',
        );
        $at->addHistory($historyData);
        
    }
    
    /**
     * 获取任务列表.
     * 
     * @param array $searchConf
     * @param array $staffInfo
     * @param int $start
     * @param int $num
     * @param string $order
     */
    public static function getList($searchConf, $start=0, $num=20, $pagetype='page')
    {
        $at = new Admin_Task();
        $taskList = $at->getList($searchConf, $start, $num);
        
        foreach($taskList['data'] as &$_taskInfo)
        {
            self::_setObjtypeDesc($_taskInfo, $pagetype);
        }
        
        $as =  new Admin_Staff();
        $as->appendSuers($taskList['data'], 'create_suid', 'exec_suid', true);
        
        return $taskList;
    }
    
    /**
     * 写订单类型的描述.
     */
    private static function _setObjtypeDesc(&$taskInfo, $pagetype='page')
    {
        $link = array_key_exists($taskInfo['objtype'], Conf_Admin_Task::$Objtype_Link)? 
                (is_array(Conf_Admin_Task::$Objtype_Link[$taskInfo['objtype']])?
                    Conf_Admin_Task::$Objtype_Link[$taskInfo['objtype']][$pagetype]: Conf_Admin_Task::$Objtype_Link[$taskInfo['objtype']])
                    : '';
        $link .= !empty($link)&&!empty($taskInfo['objid'])? $taskInfo['objid']: '';
        
        $taskInfo['_objtype'] = array(
            'alias' => Conf_Admin_Task::$Objtype_Desc[$taskInfo['objtype']].'-'.$taskInfo['objid'],
            'link' => $link,
        );
        
    }
    
    /**
     * 按定义的对象id获取任务列表.
     * 
     * @param array $objid
     * @param int $objtype
     */
    public static function getListByObjids($objid, $objtype)
    {
//        assert(!empty($objid));
        assert(!empty($objtype));

        if (empty($objid))
        {
            return array();
        }
        
        $objid = !is_array($objid)? array($objid): $objid;
        $execStatus = array(
            Conf_Admin_Task::ST_WAIT_DEAL,
            Conf_Admin_Task::ST_COMPLETE,
            Conf_Admin_Task::ST_CLOSE,
        );
        $where = sprintf('objtype=%d and objid in (%s) and exec_status in (%s)',
                    $objtype, implode(',', $objid), implode(',', $execStatus));
        
        $as = new Admin_Task();
        $_taskList = $as->getByWhere($where, 0, 0);
        
        $taskList = array();
        $allShortDescs = Conf_Admin_Task::getShortDescOfObjtype($objtype);
        
        foreach($_taskList as $_task)
        {
            $_task['_short_desc'] = $allShortDescs[$objtype][$_task['short_desc']];
            $_task['_exec_status'] = Conf_Admin_Task::$Exec_Task_Desc[$_task['exec_status']];
            $taskList[$_task['objid']][] = $_task;
        }
        
        return $taskList;
    }
    
    /**
     * 获取任务详情.
     * 
     * @param array $tid
     */
    public static function getDetail($tid)
    {
        $at = new Admin_Task();
        $taskDetail = array($at->get($tid));
        
        self::_setObjtypeDesc($taskDetail[0]);
        
        // 图片信息
        if (!empty($taskDetail[0]['pic_ids']))
        {
            $pics = explode(',', $taskDetail[0]['pic_ids']);
            
            foreach($pics as $_pic)
            {
                
                $taskDetail[0]['pic_urls'][] = 'http://'.PIC_HOST.'/wx_pic/'.substr($_pic, 0, 8).'/'.$_pic;
            }
        }
        
        $as =  new Admin_Staff();
        $as->appendSuers($taskDetail, 'create_suid', 'exec_suid', true);
        
        return $taskDetail[0];
    }
    
    /**
     * 获取任务的变更历史.
     * 
     * @param int $tid
     */
    public static function getHistory($tid)
    {
        $at = new Admin_Task();
        $taskHistory = $at->getHistory($tid);
        
        $as =  new Admin_Staff();
        $as->appendSuers($taskHistory, 'suid', 'exec_suid', true);
        
        return $taskHistory;
    }
    
}