<?php

/**
 * 标记空采（补货/临采）.
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $pid;
    private $flag;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->flag = Tool_Input::clean('r', 'flag', TYPE_STR);
    }
    
    protected function checkPara()
    {
        if (empty($this->oid) || empty($this->pid))
        {
            throw new Exception('系统错误，请联系技术人员处理！');
        }
        if (!in_array($this->flag, array('lack')))
        {
            throw new Exception('操作非法');
        }
    }
    
    protected function main()
    {
        Order_Picking_Api::markFlag4Vnum($this->oid, $this->pid, $this->flag);
    }
    
    protected function outputBody()
    {
        $result = array('st' => 1);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();