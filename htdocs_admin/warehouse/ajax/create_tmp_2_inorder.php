<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $wid;
    private $buyDate;
    private $productList;
    
    private $inorderProductList;
    private $inorderSource; //采购单类型
    
    protected function getPara()
    {
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->buyDate = Tool_Input::clean('r', 'buy_date', TYPE_STR);
        $products = Tool_Input::clean('r', 'product_list', TYPE_STR);
        
        $this->productList = json_decode($products, true);
        
        $this->inorderSource = Conf_In_Order::SRC_TEMPORARY;
        
    }
    
    protected function checkPara()
    {
        if (empty($this->wid) || empty($this->productList) || empty($this->buyDate))
        {
            throw new Exception('para_error: 参数错误，请联系管理员！');
        }

//        if (Conf_Warehouse::isAgentWid($this->wid))
//        {
//            throw new Exception('经销商不能创建临时采购单！');
//        }
        
        // 检测商品基本信息，采购数量等
        $productsInOrders = array();
        foreach($this->productList as &$one)
        {
            if ($one['price'] <= 0)
            {
                throw new Exception('SKUID：'.$one['sid'].' 采购价为0，请填写！');
            }
            if (empty($one['order_info']))
            {
                throw new Exception('SKUID：'.$one['sid'].' 没有对应空采的销售单！');
            }
            
            $one['price'] = floatval($one['price'])*100;
            
            $totalVnum = 0;
            $orders = array();
            foreach($one['order_info'] as $o)
            {
                $totalVnum += $o['vnum'];
                $orders[] = $o['oid'].':'.$o['vnum'];
            }
            
            if ($totalVnum > $one['real_vnum'])
            {
                throw new Exception('采购数量不能小于选定订单的空采数量！');
            }
            
            $productsInOrders = array_merge($productsInOrders, $one['order_info']);
            
            $this->inorderProductList[] = array(
                'sid'=> $one['sid'],
                'source' => Conf_In_Order::SRC_TEMPORARY,
                'price' => $one['price'],
                'num' => $totalVnum,
                'sales_oids' => implode(',', $orders),
            );
            
            if ($one['real_vnum'] > $totalVnum)
            {
                $this->inorderProductList[] = array(
                    'sid'=> $one['sid'],
                    'source' => Conf_In_Order::SRC_COMMON,
                    'price' => $one['price'],
                    'num' => $one['real_vnum']-$totalVnum,
                );
                
                $this->inorderSource = Conf_In_Order::SRC_COMPOSITIVE;
            }
        }
        
        // 检测订单中对应的商品空采量是否合法
        $checkVal = Warehouse_Temp_Purchase_Api::isLegalTmpPurchase($productsInOrders, $this->wid);
        
        if ($checkVal['st'] != 0)
        {
            throw new Exception($checkVal['msg']);
        }
        
    }

    protected function main()
    {
//        if (ENV == 'online')
//        {
//            throw new Exception('waiting for me!!...');
//        }

        $supplierId = Conf_In_Order::$Temporary_Purchase_Suppliers[$this->wid];

        $sp = new Shop_Product();
        $sids = Tool_Array::getFields($this->productList, 'sid');
        $cityId = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$this->wid];
        $productInfo = Tool_Array::list2Map($sp->getBySku($sids, $cityId), 'sid');

        $managingModeFlag = 0;
        foreach($this->productList as &$_product)
        {
            if (empty($productInfo[$_product['sid']]))
            {
                throw new Exception('商品(sid:'.$_product['sid'].')不存在或者已下架！');
            }
            if (Conf_Base::switchForManagingMode())
            {
                if (empty($productInfo[$_product['sid']]['managing_mode']))
                {
                    throw new Exception('商品(pid:' . $_product['pid'] . ')经营模式属性不存在！');
                }
                if (empty($managingModeFlag))
                {
                    $managingModeFlag = $productInfo[$_product['sid']]['managing_mode'];
                }
                if ($managingModeFlag != $productInfo[$_product['sid']]['managing_mode'])
                {
                    throw new Exception('选择的商品经营模式不一致！');
                }
            }
            $_product['sale_price'] = $productInfo[$_product['sid']]['price'];
            $_product['num'] = $_product['real_vnum'];
        }

        Agent_Api::canDistributeGoods4Agent($this->wid, 0, Conf_Agent::Agent_Type_Stock_In, $this->productList, 'sale_price', false);

        if ($managingModeFlag == Conf_Base::MANAGING_MODE_POOL)
        {
            $supplierId = Conf_In_Order::$Temporary_Purchase_Joint_Suppliers[$this->wid];

        }

        if (empty($supplierId))
        {
            throw new Exception('该仓库没有配置临采供应商，请优先创建临采供应商账号，并通知技术人员添加！');
        }

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
            'source' => $this->inorderSource,
        );

        if (Conf_Base::switchForManagingMode())
        {
            $orderInfo['managing_mode'] = $supplierInfo['managing_mode'];
        }

        $this->oid = Warehouse_Api::addOrder($supplierId, $orderInfo, $this->inorderProductList);
        
        // 创建采购单成功, 更新销售商品表中商品数量
        if ($this->oid)
        {
            $oo = new Order_Order();
            
            foreach($this->productList as $product)
            {
                foreach($product['order_info'] as $oinfo)
                {
                    $upData = array(
                        'cost' => $product['price'],
                        'tmp_inorder_id' => $this->oid
                    );
                    $chgData = array(
                        'tmp_inorder_num' => $oinfo['vnum'],                    
                    );
                    $oo->updateOrderProductInfo($oinfo['oid'], $product['pid'], 0, $upData, $chgData);
                    
                }
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