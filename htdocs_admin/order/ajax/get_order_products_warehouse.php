<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $orderProducts = array();
    private $productsWarehouseList = array();
    private $wareHouse;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (empty($this->oid))
        {
            throw new Exception('订单oid为空');
        }
    }

//    protected function checkAuth($permission = '')
//    {
//        parent::checkAuth('/order/ajax/get_order_products_warehouse');
//    }

    protected function main()
    {
        $orderProducts = Order_Api::getOrderProducts($this->oid);
        $sids = Tool_Array::getFields($orderProducts['products'], 'sid');
        $this->orderProducts = Tool_Array::list2Map($orderProducts['products'], 'sid');
        $productsWarehouseList = Warehouse_Api::getStockAllBySids($sids);
        $this->wareHouse = Conf_Warehouse::getWarehouseByAttr('order_stock');
        foreach ($productsWarehouseList as $item)
        {
            if(in_array($item['sid'], Conf_Order::$Virtual_Skuid_4_Tmp_Purchase))
            {
                unset($this->orderProducts[$item['sid']]);
                continue;
            }
            if(in_array($item['wid'],array_keys($this->wareHouse)))
            {
                $num = $item['num']-$item['occupied']-$item['damaged_num'];
                $this->orderProducts[$item['sid']]['ware'][$item['wid']] = $num;
            }
        }
    }
    
    protected function outputBody()
    {
        $this->smarty->assign('product_list', $this->orderProducts);
        $this->smarty->assign('wids', $this->wareHouse);
        $output['html'] = $this->smarty->fetch('order/aj_get_order_products_warehouse.html');
		$response = new Response_Ajax();
		$response->setContent($output);
		$response->send();
		exit;
    }
}

$app = new App();
$app->run();