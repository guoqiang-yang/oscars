<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $searchConf;
    
    private $orderLineList;
    private $objType = Conf_Coopworker::OBJ_TYPE_ORDER;
    private $wid;
    
    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->isAdmin = Admin_Role_Api::isAdmin($this->_uid);
        $this->wid = $this->getWarehouseId();
        $this->searchConf = array(
            'wid' => $this->wid,
            'id' => Tool_Input::clean('r', 'id', TYPE_UINT),
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'btime' => Tool_Input::clean('r', 'btime', TYPE_STR),
            'etime' => Tool_Input::clean('r', 'etime', TYPE_STR),
            'car_model' => Tool_Input::clean('r', 'car_model', TYPE_UINT),
            'address' => Tool_Input::clean('r', 'address', TYPE_UINT),
        );
        if (empty($this->searchConf['wid']))
        {
            $this->searchConf['wid'] = array_keys(App_Admin_Web::getAllowedWids4User());
        }
        
        $this->searchConf['car_model_type'] = isset($_REQUEST['car_model_type'])?$_REQUEST['car_model_type']:Conf_Base::STATUS_ALL;
        $this->searchConf['btime'] = !empty($this->searchConf['btime'])? $this->searchConf['btime']: date('Y-m-d');
        
    }
    
    protected function main()
    {   
        $this->orderLineList = Logistics_Order_Api::searchOrderLine($this->searchConf, $this->start, $this->num, '', $this->objType);
        
        $this->addFootJs(array('js/apps/logistics.js'));
        
    }
    
    protected function outputBody()
    {
        if (empty($this->wid))
        {
            $this->searchConf['wid'] = 0;
        }
        $total = $this->orderLineList['total'];
        $app = '/logistics/order_line_list.php?' . http_build_query($this->searchConf) . '&order=' . $total;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $total, $app);
        
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('warehouses',  App_Admin_Web::getAllowedWids4User());
        $this->smarty->assign('car_models', Conf_Driver::$CAR_MODEL);
        $this->smarty->assign('isAdmin', $this->isAdmin);

        $this->smarty->assign('total', $total);
        $this->smarty->assign('line_list', $this->orderLineList['data']);    
        
        $this->smarty->display('logistics/order_line_list.html');
    }
    
    
}
 
$app = new App();
$app->run();