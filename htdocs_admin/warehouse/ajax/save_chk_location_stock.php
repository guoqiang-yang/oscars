<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $sid;
    private $location;
    private $num;
    private $wid;
    private $reason;
    private $note;
    private $inventoryType;
    private $oldNum;
    
    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->location = Tool_Input::clean('r', 'loc', TYPE_STR);
        $this->num = Tool_Input::clean('r', 'num', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->reason = Tool_Input::clean('r', 'reason', TYPE_UINT);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
        $this->inventoryType = Tool_Input::clean('r', 'inventory_type', TYPE_STR);
        $this->oldNum = Tool_Input::clean('r', 'old_num', TYPE_UINT);
    }

    protected function checkAuth()
    {
        if ($_REQUEST['inventory_type'] == 'profit')
        {
            parent::checkAuth('hc_inventory_profit');
        }
        elseif ($_REQUEST['inventory_type'] == 'loss')
        {
            parent::checkAuth('hc_inventory_profit');
        }
    }

    protected function checkPara()
    {
        if (empty($this->sid) || empty($this->location))
        {
            throw new Exception('没有选定操作对象！');
        }
        
        if (!Conf_Warehouse::isUpgradeWarehouse($this->wid))
        {
            throw new Exception('只支持新库盘库！');
        }
        if (empty($this->reason))
        {
            throw new Exception('请选择盈亏原因！');
        }
        if (empty($this->note))
        {
            throw new Exception('请填写备注！');
        }

        if (strpos($this->location, Conf_Warehouse::VFLAG_PREFIX)!==false && $this->location != Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_LOSS]['flag'])
        {
            throw new Exception('虚拟货位不能盘库，请执行上架操作');
        }
        if ($this->inventoryType == 'profit' && $this->num <= $this->oldNum)
        {
            throw new Exception('盘盈填写的数量不能小于或等于原货位数量!');
        }
        elseif ($this->inventoryType == 'loss' && $this->num >= $this->oldNum)
        {
            throw new Exception('盘亏填写的数量不能大于或等于原货位数量!');
        }
    }
    
    protected function main()
    {
        Warehouse_Location_Api::saveCheckLocation($this->sid, $this->location, 
                $this->wid, $this->num, $this->note, $this->_uid, $this->reason);
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