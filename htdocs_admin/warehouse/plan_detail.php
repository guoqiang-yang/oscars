<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $pid;
    private $wid;
    private $plan;
    private $taskList;
    private $count;
    private $staffList;
    private $times;

    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->times = Tool_Input::clean('r', 'times', TYPE_UINT);
        if (!isset($_REQUEST['times']))
        {
            $this->times = Conf_Stock::$STOCKTAKING_TIMES;
        }
    }

    protected function main()
    {
        $this->plan = Warehouse_Api::getInventoryPlan($this->pid);
        $this->taskList = Warehouse_Api::getInventoryTaskListByPid($this->pid, $this->times);
        $this->count = array(
            'total' => count($this->taskList),
            'not_start' => 0,
            'allocated' => 0,
            'ongoing' => 0,
            'finished' => 0,
        );

        foreach ($this->taskList as $task)
        {
            if ($task['step'] == Conf_Stock::STOCKTAKING_TASK_STEP_NOT_START)
            {
                $this->count['not_start'] += 1;
            }
            else if ($task['step'] == Conf_Stock::STOCKTAKING_TASK_STEP_ALLOCATED)
            {
                $this->count['allocated'] += 1;
            }
            else if ($task['step'] == Conf_Stock::STOCKTAKING_TASK_STEP_ONGOING)
            {
                $this->count['ongoing'] += 1;
            }
            else if ($task['step'] == Conf_Stock::STOCKTAKING_TASK_STEP_FINISHED)
            {
                $this->count['finished'] += 1;
            }
        }

        $list = Admin_Api::getStaffList(0, 0, array('wid' => $this->wid));
        $this->staffList = Tool_Array::list2Map($list['list'], 'suid');

        $this->addFootJs(array('js/apps/stock.js'));
    }

    protected function outputBody()
    {
        $this->smarty->assign('conf_times', $this->times);
        $this->smarty->assign('plan', $this->plan);
        $this->smarty->assign('task_list', $this->taskList);
        $this->smarty->assign('step_list', Conf_Stock::$STOCKTAKING_TASK_STEPS);
        $this->smarty->assign('count', $this->count);
        $this->smarty->assign('times', Conf_Stock::$STOCKTAKING_TIMES);
        $this->smarty->assign('staff_list', $this->staffList);
        $this->smarty->display('warehouse/plan_detail.html');
    }
}

$app = new App();
$app->run();