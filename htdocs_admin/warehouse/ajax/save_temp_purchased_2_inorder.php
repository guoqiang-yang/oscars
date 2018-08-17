<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $wid;
    private $buyDate;
    private $productList;
    
    
    protected function getPara()
    {
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->buyDate = Tool_Input::clean('r', 'buy_date', TYPE_STR);
        $products = Tool_Input::clean('r', 'product_list', TYPE_STR);
        
        $this->productList = json_decode($products, true);
        
    }
    
    protected function checkPara()
    {
        if (empty($this->wid) || empty($this->productList) || empty($this->buyDate))
        {
            throw new Exception('para_error: 参数错误，请联系管理员！');
        }
        
        if (!Conf_Warehouse::isUpgradeWarehouse($this->wid))
        {
            throw new Exception('该库暂不支持创建临采单');
        }
    }

    protected function main()
    {
        $productList = array();
        foreach($this->productList as &$p)
        {
            $sid = $p['sid'];
            $p['price'] = floatval($p['price'])*100;
            
            $_product = array(
                'sid' => $p['sid'],
                'price' => $p['price'],
                'num' => $p['num'],
                'sales_oids' => $p['oid'],
                'source' => Conf_In_Order::SRC_TEMPORARY,
            );
            
            if (array_key_exists($sid, $productList))
            {
                if ($_product['price'] != $productList[$sid]['price'])
                {
                    throw new Exception('相同商品的采购单价不同，请核实！SID:'.$sid);
                }
                $productList[$sid]['num'] += $_product['num'];
                $productList[$sid]['sales_oids'] .= ','.$p['oid'];
            }
            else
            {
                $productList[$sid] = $_product;
            }
            
        }
        
        $supplierId = Conf_In_Order::$Temporary_Purchase_Suppliers[$this->wid];
        $supplierInfo = Warehouse_Api::getSupplier($supplierId);
        
        $orderInfo = array(
            'sid' => $supplierId,
            'contact_name' => $supplierInfo['contact_name'],
            'contact_phone' => $supplierInfo['phone'],
            'delivery_date' => $this->buyDate,
            'freight' => 0,
            'privilege' => 0,
            'privilege_note' => '',
            'note' => '',
            'payment_type' => Conf_Stock::PAYMENT_CASH,
            'wid' => $this->wid,
            'buyer_uid' => $this->_uid,
            'step' => Conf_In_Order::ORDER_STEP_SURE,
            'source' => Conf_In_Order::SRC_TEMPORARY, //临采采购单
        );
        $this->oid = Warehouse_Api::addOrder($supplierId, $orderInfo, $productList);
        
        // 创建采购单成功, 更新销售商品表中商品数量
        if ($this->oid)
        {
            $oo = new Order_Order();
            
            foreach($this->productList as $product)
            {
                $upData = array(
                    'cost' => $product['price'],
                );
                $chgData = array(
                    'tmp_inorder_num' => $product['num'],                    
                );
                $oo->updateOrderProductInfo($product['oid'], $product['pid'], 0, $upData, $chgData);
            }
        }
    }
    
    protected function outputBody()
    {
        $result = array('oid'=>$this->oid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
    }
}

$app = new App();
$app->run();