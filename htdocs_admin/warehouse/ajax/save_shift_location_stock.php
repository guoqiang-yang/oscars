<?php

include_once('../../../global.php');


class App extends App_Admin_Ajax
{
    private $sid;
    private $srcLoc;
    private $desLoc;
    private $num;
    private $wid;
    private $note;
    
    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->srcLoc = Tool_Input::clean('r', 'src_loc', TYPE_STR);
        $this->desLoc = Tool_Input::clean('r', 'des_loc', TYPE_STR);
        $this->num = Tool_Input::clean('r', 'num', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->note = Tool_Input::clean('r', 'note', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (empty($this->sid) || empty($this->wid))
        {
            throw new Exception('系统错误，请联系管理员！');
        }
        if (empty($this->desLoc) || $this->srcLoc==$this->desLoc)
        {
            throw new Exception('货位信息错误，请核对！');
        }
        if (empty($this->num))
        {
            throw new Exception('转移数量不能为 0!');
        }
        if (!Conf_Warehouse::isUpgradeWarehouse($this->wid))
        {
            throw new Exception('该库不支持此服务！');
        }

        $canShiftVFLoc = array(Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_DAMAGED]['flag'],
            Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_LOSS]['flag']);
        if ((strpos($this->srcLoc, Conf_Warehouse::VFLAG_PREFIX)!==FALSE
            || strpos($this->desLoc, Conf_Warehouse::VFLAG_PREFIX)!==FALSE)
        && !in_array($this->srcLoc, $canShiftVFLoc) && !in_array($this->desLoc, $canShiftVFLoc))
        {
            throw new Exception('【虚拟货架】上的商品不能移动！');
        }

        if (in_array($this->srcLoc, $canShiftVFLoc) && in_array($this->desLoc, $canShiftVFLoc))
        {
            throw new Exception('残损货位和与盘亏货位不能互转！');
        }
    }
    
    
    protected function main()
    {
        Warehouse_Location_Api::saveShiftLocation($this->sid, $this->wid, 
                $this->srcLoc, $this->desLoc, $this->num, $this->_uid, $this->note);
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