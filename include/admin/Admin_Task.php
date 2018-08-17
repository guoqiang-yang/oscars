<?php

class Admin_Task extends Base_Func
{
    private $_taskDao;
    private $_taskHistoryDao;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_taskDao = new Data_Dao('t_admin_task');
        $this->_taskHistoryDao = new Data_Dao('t_admin_task_history');
    }
    
    /**
     * 创建任务.
     * 
     * @param array $taskParams
     */
    public function create($taskParams)
    {
        assert(!empty($taskParams));
        assert(!empty($taskParams['exec_suid'])||!empty($taskParams['exec_role']));
        assert(!empty($taskParams['create_suid']));
        
        $taskParams['ctime'] = date('Y-m-d H:i:s');
        $taskParams['pic_ids'] = $this->is($taskParams['pic_ids'])? $taskParams['pic_ids']: '';
        
        $tid = $this->_taskDao->add($taskParams);
        
        return $tid;
    }
    
    public function update($tid, $upData)
    {
        assert(!empty($tid));
        
        $affectedrows = $this->_taskDao->update($tid, $upData);
        
        return $affectedrows;
    }


    public function get($tid)
    {
        assert(intval($tid)>0);
        
        return $this->_taskDao->get($tid);
    }
    
    public function getByWhere($where, $start=0, $num=20, $order='')
    {
        assert(!empty($where));
        
        $ret = $this->_taskDao
                    ->order(empty($order)? 'order by tid desc': $order)
                    ->limit($start, $num)
                    ->getListWhere($where);
        
        return $ret;
    }
    
    /**
     * 获取任务列表.
     * 
     * @param array $searchConf
     */
    public function getList($searchConf, $start=0, $num=20)
    {
        $where = $this->_genWhereForSearchTasks($searchConf);
        
        $total = $this->_taskDao->getTotal($where);
        
        if (isset($searchConf['exec_status']) && $searchConf['exec_status']==Conf_Admin_Task::ST_WAIT_DEAL
            && isset($searchConf['objtype']) && $searchConf['objtype']==Conf_Admin_Task::OBJTYPE_CUSTOMER)
        {
            $order = 'order by level desc, tid asc';
        }
        else
        {
            $order = 'order by level desc, tid desc';
        }
        
        $list = $this->_taskDao
                    ->order($order)
                    ->limit($start, $num)
                    ->getListWhere($where);
        
        return array('total'=>$total, 'data'=>$list);
    }
    
    private function _genWhereForSearchTasks($searchConf)
    {
        $where = '1=1 ';
        if ($this->is($searchConf['tid']))
        {
            $where = 'tid='.$searchConf['tid'];
            
            return $where;
        }
        
        if ($this->is($searchConf['objtype']))
        {
            $where .= ' and objtype='. $searchConf['objtype'];
        }
        
        if ($this->is($searchConf['objid']))
        {
            $where .= ' and objid='. $searchConf['objid'];
        }
        
        if ($this->is($searchConf['short_desc']))
        {
            $where .= ' and short_desc='. $searchConf['short_desc'];
        }
        
        if ($this->is($searchConf['suid']))
        {
            $execRole_where = $this->is($searchConf['exec_role'])? ' exec_suid=0 and exec_role='.$searchConf['exec_role']: '';
            if (!$this->is($searchConf['exec_status']))
            {
                $where .= ' and (create_suid='. $searchConf['suid'];
                $where .= !empty($execRole_where)? " or ($execRole_where) ": '';
                $where .= ' or exec_suid='. $searchConf['suid'].')';
            }
            else if ($searchConf['exec_status']==Conf_Admin_Task::ST_CREATE)
            {
                $where .= ' and create_suid='. $searchConf['suid'];
            }
            else if ($searchConf['exec_status']==Conf_Admin_Task::ST_CLOSE
                    || $searchConf['exec_status']==Conf_Admin_Task::ST_DELETE)
            {
                $where .= ' and create_suid='. $searchConf['suid'];
                $where .= ' and exec_status='. $searchConf['exec_status'];
            }
            else
            {
                if (empty($execRole_where))
                {
                    $where .= ' and exec_suid='. $searchConf['suid'];
                }
                else
                {
                    $where .= ' and (exec_suid='.$searchConf['suid'].' or ('.$execRole_where.') )';
                }
                $where .= ' and exec_status='. $searchConf['exec_status'];
            }
        }
        else
        {
            if ($this->is($searchConf['exec_status']) 
                && $searchConf['exec_status']!=Conf_Admin_Task::ST_CREATE)
            {
                $where .= ' and exec_status='. $searchConf['exec_status'];
            }
        }
        
        return $where;
    }
    
    
    //////////////////////////// history ////////////////////////
    
    /**
     * 添加一条任务历史记录.
     * 
     * @param array $taskHistory
     */
    public function addHistory($taskHistory)
    {
        assert(!empty($taskHistory));
        assert(!empty($taskHistory['tid']));
        
        if (!$this->is($taskHistory['note']))
        {
            $taskHistory['note'] = '';
        }
        $taskHistory['ctime'] = date('Y-m-d H:i:s');
        
        $this->_taskHistoryDao->add($taskHistory);
        
    }
    
    public function getHistory($tid)
    {
        assert(!empty($tid));
        
        $where = 'tid='. $tid;
        $history = $this->_taskHistoryDao
                    ->order('id', 'desc')
                    ->getListWhere($where);
        
        return $history;
    }
}