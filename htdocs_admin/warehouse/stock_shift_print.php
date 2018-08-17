<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $ssid;
    private $shiftInfos = array();
    private $createUser;
    protected $headTmpl = 'head/head_none.html';
    protected $tailTmpl = 'tail/tail_none.html';


    protected function getPara()
    {
        $this->ssid = Tool_Input::clean('r', 'ssid', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->ssid))
        {
            throw new Exception('stock shift id is empty');
        }
    }

    protected function main()
    {
        $this->shiftInfos = Warehouse_Api::getStockShiftInfo($this->ssid);
        $this->shiftInfos['create_time_desc'] = substr($this->shiftInfos['ctime'],0,10);
        if($this->shiftInfos['create_suid']>0)
        {
            $oa = new Admin_Staff();
            $this->createUser = $oa->get($this->shiftInfos['create_suid']);
        }
    }

    protected function outputBody()
    {
        $this->smarty->assign('shift_info', $this->shiftInfos);
        $this->smarty->assign('warehouse_list', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('create_user', $this->createUser);
        $this->smarty->assign('steps', Conf_Stock_Shift::$Step_Descs);
        $this->smarty->display('warehouse/stock_shift_print.html');
    }

}

$app = new App();
$app->run();