<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $html;
    private $pid;

    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
    }

    protected function main()
    {
        $plan = Warehouse_Api::getInventoryPlan($this->pid);
        $staff = Admin_Api::getStaffList();
        $staffList =Tool_Array::list2Map($staff['list'], 'suid', 'name');

        $taskList = Warehouse_Api::getInventoryTaskListByPid($this->pid);
        $count = array(
            'total' => count($taskList),
            'staff_num' => count(array_filter(array_unique(Tool_Array::getFields($taskList, 'alloc_suid')))),
        );

        $this->smarty->assign('times', Conf_Stock::$STOCKTAKING_TIMES);
        $this->smarty->assign('count', $count);
        $this->smarty->assign('plan_types', Conf_Stock::$STOCKTAKING_PLANS);
        $this->smarty->assign('step_list', Conf_Stock::$STOCKTAKING_TASK_STEPS);
        $this->smarty->assign('task_list', $taskList);
        $this->smarty->assign('staff_list', $staffList);
        $this->smarty->assign('plan', $plan);
        $this->html = $this->smarty->fetch('warehouse/aj_get_inventory_plan_degree.html');
    }
    
    protected function outputBody()
    {
        $st = !empty($this->html)? 1 : 0;
        $result = array('st'=>$st, 'html'=> $this->html);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();