<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $pid;
    private $tid;
    private $wid;
    private $html;

    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->tid = Tool_Input::clean('r', 'tid', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->wid) || empty($this->tid) || empty($this->pid))
        {
            throw new Exception('参数错误！');
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/ajax/save_inventory_task');
    }

    protected function main()
    {
        $conf = array('wid' => $this->wid);
        $staff_list = Admin_Api::getStaffList(0, 0, $conf);
        $suids = Tool_Array::getFields($staff_list['list'], 'suid');

        $fields = array('alloc_suid', 'count(1) as num');
        $allocWhere = sprintf('alloc_suid in (%s) and step in (%d, %d) and plan_id = %d group by alloc_suid', implode(',', $suids), Conf_Stock::STOCKTAKING_TASK_STEP_ALLOCATED,
            Conf_Stock::STOCKTAKING_TASK_STEP_ONGOING, $this->pid);
        $allocData = Warehouse_Api::getInventoryTaskListByWhere($allocWhere, 0, $fields, array(), 0, 0);
        $allocNum = Tool_Array::list2Map($allocData, 'alloc_suid', 'num');

        $finishWhere = sprintf('alloc_suid in (%s) and step = %d and plan_id = %d group by alloc_suid ', implode(',', $suids), Conf_Stock::STOCKTAKING_TASK_STEP_FINISHED, $this->pid);
        $finishData = Warehouse_Api::getInventoryTaskListByWhere($finishWhere, 0, $fields, array(), 0, 0);
        $finishNum = Tool_Array::list2Map($finishData, 'alloc_suid', 'num');
        foreach ($staff_list['list'] as &$staff)
        {
            $staff['alloc_num'] = $allocNum[$staff['suid']];
            $staff['finish_num'] = $finishNum[$staff['suid']];
        }
        $this->smarty->assign('tid', $this->tid);
        $this->smarty->assign('staff_list', $staff_list['list']);
        $this->html = $this->smarty->fetch('warehouse/aj_get_inventory_staff_list.html');
    }
    
    protected function outputBody()
    {
        $result = array('wid' => $this->wid, 'html' => $this->html);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();