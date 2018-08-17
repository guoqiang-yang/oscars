<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $pid;
    private $wid;
    private $products;

    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->products = json_decode(Tool_Input::clean('r', 'products', TYPE_STR), true);
    }

    protected function checkPara()
    {
        if (empty($this->pid) || empty($this->wid))
        {
            throw new Exception('参数错误！');
        }
    }
    
    protected function main()
    {
        Warehouse_Api::addInventoryPlanProducts($this->pid, $this->wid, $this->products);
        Warehouse_Api::updateInventoryPlan($this->pid, array('step' => Conf_Stock::STOCKTAKING_PLAN_STEP_ONGOING));
    }
    
    protected function outputBody()
    {
        $result = array('pid'=>$this->pid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();