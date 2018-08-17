<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $oid;
    
    private $order;
    private $products;
    private $validAreas;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }
    
    protected function main()
    {
        $this->order = Order_Api::getOrderInfo($this->oid);
        $this->products = Order_Picking_Api::getOrderProducts4Picking($this->oid);
        $this->validAreas = array_keys($this->products);
        
        // 获取订单的第三方工人（司机，搬运工）
		$coopworders = Logistics_Coopworker_Api::getOrderOfWorkers($this->oid, 0, TRUE, Conf_Coopworker::OBJ_TYPE_ORDER);

		$this->order['driver_list'] = array();
		$this->order['carrier_list'] = array();
		foreach ($coopworders as $oner)
		{
			if ($oner['type'] == Conf_Base::COOPWORKER_DRIVER)
			{
				$this->order['driver_list'][] = array(
                    'cuid' => $oner['cuid'], 
                    'name' => $oner['info']['name'], 
                    'phone' => $oner['info']['mobile'], 
                    'price' => $oner['price'] / 100, 
                    'paid' => $oner['paid'], 
                    'user_type' => $oner['user_type'],
                );
			}
			else if ($oner['type'] == Conf_Base::COOPWORKER_CARRIER)
			{
				$this->order['carrier_list'][] = array(
                    'cuid' => $oner['cuid'], 
                    'name' => $oner['info']['name'], 
                    'phone' => $oner['info']['mobile'], 
                    'price' => $oner['price'] / 100, 
                    'paid' => $oner['paid'], 
                    'user_type' => $oner['user_type'],
                );
			}
		}
        
        $this->addFootJs(array('js/apps/order.js', 'js/apps/picking.js'));
        
        
        foreach ($this->products as &$plist)
        {
            foreach ($plist as &$item)
            {
                if (!empty($item['sku']['rel_sku']))
                {
                    $item['sku']['_rel_sku'] = Shop_Helper::parseRelationSkus($item['sku']['rel_sku'], true);
                }
            }
        }
    }
    
    protected function outputBody()
    {
        
        $this->smarty->assign('order', $this->order);
        $this->smarty->assign('products', $this->products);
        $this->smarty->assign('valid_areas', $this->validAreas);
        
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
        $this->smarty->assign('delivery_types', Conf_Order::$DELIVERY_TYPES);
        $this->smarty->assign('buytype_desc', Conf_Product::getBuyTypeDesc());
        $this->smarty->assign('sand_series', Conf_Order::$SAND_CEMENT_BRICK_PIDS);
        
        $this->smarty->display('order/picking_detail.html');
    }
    
}

$app = new App();
$app->run();