<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $fromDate;
    private $endDate;
    private $leaderSuids;
    
    private $isSelectedAll = false;
    private $salers;
    private $performanceList;
    
    protected function getPara()
    {
        $this->fromDate = Tool_Input::clean('r', 'from_date', TYPE_STR);
        $this->endDate = Tool_Input::clean('r', 'end_date', TYPE_STR);
        
        $leaderSuids = Tool_Input::clean('r', 'saler_leaders', TYPE_STR);
        $this->leaderSuids = !empty($leaderSuids)? explode(',', $leaderSuids)
                                : array_keys(Conf_Admin::$LEADERS_SUID);
        
    }
    
    protected function checkPara()
    {
        if (empty($this->fromDate))
        {
            $this->fromDate = date("Y-m-01");
            $this->endDate = date("Y-m-d");
        }
        
        if (count(Conf_Admin::$LEADERS_SUID)==count($this->leaderSuids))
        {
            $this->isSelectedAll = true;
        }
        
    }


    protected function main()
    {
        //获取销售
        $this->_getSalers();
        
        //读取业绩
        $suids = Tool_Array::getFields($this->salers, 'suid');
        
        if (empty($suids))
        {
            $this->performanceList = array();
            return ;
        }
        
        $this->performanceList = Crm2_Api::getPerformanceList($suids, $this->fromDate, $this->endDate);
        
        $performanceMoreinfos = Crm2_Api::getPerformanceMoreinfos($suids, $this->fromDate, $this->endDate);
        
        foreach($this->performanceList as $_suid => &$_info)
        {
            $_info = array_merge($_info, $performanceMoreinfos[$_suid]);
        }
    }
    
    protected function outputBody()
    {
        $this->smarty->assign('performance_list', $this->performanceList);
        
        $this->smarty->assign('saler_groups', Conf_Admin::$LEADERS_SUID);
        $this->smarty->assign('saler_leaders', $this->leaderSuids);
        $this->smarty->assign('is_select_all', $this->isSelectedAll);
        $this->smarty->assign('from_date', $this->fromDate);
        $this->smarty->assign('end_date', $this->endDate);
        
        $this->smarty->display('crm2/manager_page.html');
    }
    
    private function _getSalers()
    {
        $as = new Admin_Staff();
     
        $leaderSuids = implode(',', $this->leaderSuids);
        $where = 'status=0 and department='. Conf_Admin::ROLE_SALES. ' and leader_suid in ('.$leaderSuids.')';
        
        $this->salers = $as->getByWhere($where, 0, 0, array('suid', 'name', 'leader_suid'));
        
    }
}

$app = new App();
$app->run();