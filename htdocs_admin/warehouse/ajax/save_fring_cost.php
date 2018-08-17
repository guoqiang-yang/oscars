<?php

/**
 * 修改附加成本.
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $sid;
    private $wid;
    private $newFringCost;
    
    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->newFringCost = Tool_Input::clean('r', 'newFringCost', TYPE_STR);
    }
    
    protected function checkPara()
    {
        if (empty($this->sid))
        {
            throw new Exception('sid 不可为空');
        }

        if (empty($this->wid))
        {
            throw new Exception('wid 不可为空');
        }

        if (empty($this->newFringCost))
        {
            throw new Exception('请填写附加成本');
        }
    }
    
    protected function main()
    {
        $upData = array(
          'fring_cost'=>$this->newFringCost*100,
        );

        $warehouseStock  = new Warehouse_Stock();
        $warehouseStock->save($this->wid, $this->sid, $upData);
    }
    
    protected function outputBody()
    {
        $result = array('sid' => $this->sid,'wid' => $this->wid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App();
$app->run();