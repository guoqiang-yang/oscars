<?php

/**
 * 获取占用某个指定商品的订单.
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $pid;
    private $sid;
    private $oid;
    private $wid;
    private $title;
    
    private $html;


    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/location_list');
    }
    
    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->title = Tool_Input::clean('r', 'title', TYPE_STR);
    }
    
    protected function main()
    {
        $wl = new Warehouse_Location();
        $locStock = $wl->getBySid($this->sid, $this->wid, 'actual');

        $wso = new Warehouse_Stock_Occupied();
        $products = $wso->getOccupiedBySidWid($this->sid, $this->wid);

        // 解析货位信息
        Warehouse_Location_Api::parseLocationAndNum($products);
        
        foreach($products as &$pinfo)
        {
            $pinfo['_delivery_time'] = substr($pinfo['delivery_date'], 0, 13).'点-'
                                       .substr($pinfo['delivery_date_end'], 11, 2).'点';
        }
        
        $this->smarty->assign('loc_stocks', $locStock);
        $this->smarty->assign('products', $products);
        $this->smarty->assign('pid', $this->pid);
        $this->smarty->assign('sid', $this->sid);
        $this->smarty->assign('title', $this->title);
        $this->smarty->assign('oid', $this->oid);
        
        $this->html = $this->smarty->fetch('order/aj_get_occupied_product_by_order.html');
    }
    
    protected function outputBody()
    {
        $ret = array('html' => $this->html);
        
        $response = new Response_Ajax();
		$response->setContent($ret);
		$response->send();
        
		exit;
    }
    
}

$app = new App();
$app->run();