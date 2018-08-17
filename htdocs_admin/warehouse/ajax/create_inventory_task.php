<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $pid;
    private $wid;
    private $times;
    private $num;
    private $products;
    private $allocMethod;

    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->times = Tool_Input::clean('r', 'times', TYPE_UINT);
        $this->allocMethod = Tool_Input::clean('r', 'alloc_method', TYPE_UINT);
        if ($this->allocMethod == 1)
        {
            $this->num = Tool_Input::clean('r', 'task_num', TYPE_UINT);
        }
        else if ($this->allocMethod == 2)
        {
            $this->num = Tool_Input::clean('r', 'product_num', TYPE_UINT);
        }

        $this->products = json_decode(Tool_Input::clean('r', 'products', TYPE_STR), true);
    }
    

    protected function checkPara()
    {
        if (empty($this->pid) || empty($this->wid) || empty($this->products))
        {
            throw new Exception('参数错误！');
        }
    }
    
    protected function main()
    {
        Warehouse_Api::createInventoryTask($this->_uid, $this->pid, $this->wid, $this->times, $this->products, $this->allocMethod, $this->num);
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