<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $oid;
    private $wid;
    private $pid;
    private $buyNum;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->buyNum = Tool_Input::clean('r', 'buy_num', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        
        if (empty($this->oid) || empty($this->wid) || empty($this->pid))
        {
            throw new Exception('common:params error');
        }
        if (empty($this->buyNum))
        {
            throw new Exception('购买数量不能为0！请刷页面查看最新临采列表！');
        }
        if ($this->_user['wid']!=0 && $this->_user['wid']!=$this->wid)
        {
            throw new Exception('你不属于该仓库，不能更新！');
        }
    }
    
    protected function main()
    {
        Warehouse_Temp_Purchase_Api::saveHadBought($this->oid, $this->pid, $this->buyNum);
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