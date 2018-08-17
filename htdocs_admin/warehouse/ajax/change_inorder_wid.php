<?php

/**
 * 修改已经入库的采购单的库（含部分入库）.
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $srcWid;
    private $descWid;
    
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->srcWid = Tool_Input::clean('r', 'sre_wid', TYPE_UINT);
        $this->descWid = Tool_Input::clean('r', 'desc_wid', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (empty($this->oid) || empty($this->srcWid) || empty($this->descWid))
        {
            throw new Exception('对不起，兄弟，参数就不对！');
        }
        
        if ($this->srcWid == $this->descWid)
        {
            throw new Exception('跟你说了，你没有修过仓库，还提交！！');
        }
        
    }
    
    protected function main()
    {
        throw new Exception('由于库房已经升级，不能再修改！');
        
        Warehouse_Api::changeInorderWidWithStockin($this->oid, $this->descWid, $this->_user);
    }
    
    
    protected function outputBody()
	{
		$result = array('ret'=>1);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App();
$app->run();