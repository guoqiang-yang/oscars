<?php

include_once('../../../global.php');

Class App extends App_Admin_Ajax
{
    private $objId;
    private $objType;
    private $sid;
    private $num;
    private $price;

    protected function getPara()
    {
        $this->objType = Tool_Input::clean('r', 'obj_type', TYPE_UINT);
        $this->objId = Tool_Input::clean('r', 'obj_id', TYPE_UINT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->num = Tool_Input::clean('r', 'num', TYPE_UINT);
        $this->price = Tool_Input::clean('r', 'price', TYPE_NUM);
    }

    protected function checkPara()
    {
        if ($this->num <= 0)
        {
            throw new Exception('数量必须大于0！');
        }
    }

    protected function checkAuth()
    {
        switch ($_REQUEST['obj_type'])
        {
            case 1:     //调拨单
                parent::checkAuth('/warehouse/stock_shift');
                break;
            case 2:     //其他出库单
                parent::checkAuth('/warehouse/ajax/save_other_stock_out_order');
                break;
            case 3:     //其他入库单
                parent::checkAuth('/warehouse/ajax/save_other_stock_in_order');
                break;
            case 4:     //采购单
                parent::checkAuth('/warehouse/edit_in_order');
                break;
            default:
                break;
        }
    }

    protected function main()
    {
        switch ($this->objType)
        {
            case 1:     //调拨单
                $this->_addStockShiftProduct();
                break;
            case 2:     //其他出库单
                $this->_addOtherStockProduct();
                break;
            case 3:     //其他入库单
                $this->_addOtherStockProduct();
                break;
            case 4:     //采购单
                $this->_addInOrderProduct();
                break;
            default:
                break;
        }
    }

    protected function outputBody()
    {
        $result = array('obj_id' => $this->objId);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }

    //添加调拨单商品
    private function _addStockShiftProduct()
    {
        $wss = new Warehouse_Stock_Shift();
        $shiftInfo = $wss->getById($this->objId);
        $cost = Shop_Cost_Api::getSimpleCost($shiftInfo['src_wid'], array($this->sid));
        $sp = new Shop_Product();
        $productInfo = Tool_Array::list2Map($sp->getBySku(array($this->sid), Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$shiftInfo['des_wid']], 7), 'sid');
        
        $product[] = array(
            'sid' => $this->sid,
            'num' => $this->num,
            'cost' => intval($cost[$this->sid]),
            'price' => intval($productInfo[$this->sid]['price']),
            'managing_mode' => $productInfo[$this->sid]['managing_mode'],
        );
        
        // 经销商退货，订货要余额充足
        Agent_Api::canDistributeGoods4Agent($shiftInfo['des_wid'], $this->objId, Conf_Agent::Agent_Type_StockShift_In, $product, 'price');
//        if (Conf_Warehouse::isAgentWid($shiftInfo['des_wid']))
//        {
//            foreach($product as $_pp)
//            {
//                if ($_pp['price'] == 0)
//                {
//                    throw new Exception('商品售价为0元，不能调拨，请联系运营人员！');
//                }
//            }
//            $aa = new Agent_Agent();
//            $agentInfo = $aa->getVaildAgentByWid($shiftInfo['des_wid']);
//            if (empty($agentInfo))
//            {
//                throw new Exception('仓库：#'.$shiftInfo['des_wid']. ' 经销商不存在');
//            }
//            $productsSalesPrices = intval($productInfo[$this->sid]['price'])*$this->num;
//            $wssp = new Warehouse_Stock_Shift_Product();
//            $salesPriceHadInorder = $wssp->getSumById(array('sum(num*price)'), $this->objId);
//            
//            if ($productsSalesPrices+$salesPriceHadInorder > $agentInfo['account_balance'])
//            {
//                throw new Exception('经销商余额不足，创建采购单失败！');
//            }
//        }
        
        Warehouse_Api::addStockShiftProducts($this->objId, $product);
        //添加调拔单日志
        $info = array(
            'admin_id' => $this->_uid,
            'obj_id' => $this->objId,
            'obj_type' => Conf_Admin_Log::OBJTYPE_SATOCK_SHIFT,
            'action_type' => 2,
            'wid' => $shiftInfo['src_wid'],
            'params' => json_encode(array(
                'id' => $this->objId,
                'json' => json_encode(array('type'=>'add', 'products' =>array('sid'=>$product[0]['sid'],'num'=>$product[0]['num'])))
            )),
        );
        Admin_Common_Api::addAminLog($info);
    }

    //添加其他出库商品
    private function _addOtherStockProduct()
    {
        $product[] = array(
            'sid' => $this->sid,
            'num' => $this->num,
        );

        $wosoo = new Warehouse_Other_Stock_Out_Order();
        $order = $wosoo->get($this->objId);

        if ($order['type'] == Conf_Stock::OTHER_STOCK_OUT_TYPE_BROKEN)
        {
            Warehouse_Api::saveOtherStockOutOrderBrokenProducts($this->objId, $product);
        }
        else
        {
            Warehouse_Api::saveOtherStockOutOrderProducts($this->objId, $product);
        }
    }

    //采购单添加商品
    private function _addInOrderProduct()
    {
        if ($this->price <= 0)
        {
            throw new Exception('单价必须大于0！');
        }
        $wss = new Warehouse_In_Order();
        $orderInfo = $wss->get($this->objId);
        $sp = new Shop_Product();
        $productInfo = Tool_Array::list2Map($sp->getBySku(array($this->sid), Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$orderInfo['wid']], 3), 'sid');
        if (empty($productInfo[$this->sid]))
        {
            throw new Exception('商品已删除！');
        }
        if (Conf_Base::switchForManagingMode() && $productInfo[$this->sid]['managing_mode'] != $orderInfo['managing_mode'])
        {
            throw new Exception('商品的属性与供应商属性不一致!');
        }
        $_stockInfo = Warehouse_Api::getStockDetail($orderInfo['wid'], $this->sid);
        if(isset($_stockInfo['stock'][$orderInfo['wid']]['outsourcer_id']) && $_stockInfo['stock'][$orderInfo['wid']]['outsourcer_id']>0)
        {
            throw new Exception('商品为外包供应商商品');
        }

        $product[] = array(
            'sid' => $this->sid,
            'num' => $this->num,
            'price' => $this->price*100,
            'sale_price' => intval($productInfo[$this->sid]['price']),
        );
        
        // 经销商退货，订货要余额充足
        Agent_Api::canDistributeGoods4Agent($orderInfo['wid'], $this->objId, Conf_Agent::Agent_Type_Stock_In, $product, 'sale_price');
//        if (Conf_Warehouse::isAgentWid($orderInfo['wid']))
//        {
//            foreach($product as $_pp)
//            {
//                if ($_pp['price'] == 0)
//                {
//                    throw new Exception('商品售价为0元，不能调拨，请联系运营人员！');
//                }
//            }
//            
//            $aa = new Agent_Agent();
//            $agentInfo = $aa->getVaildAgentByWid($orderInfo['wid']);
//            if (empty($agentInfo))
//            {
//                throw new Exception('仓库：#'.$orderInfo['wid']. ' 经销商不存在');
//            }
//            $productsSalesPrices = intval($productInfo[$this->sid]['price'])*$this->num;
//            $wssp = new Warehouse_In_Order_Product();
//            $salesPriceHadInorder = $wssp->getSumByOid(array('sum(num*sale_price)'), $this->objId);
//            
//            if ($productsSalesPrices+$salesPriceHadInorder > $agentInfo['account_balance'])
//            {
//                throw new Exception('经销商余额不足，创建采购单失败！');
//            }
//        }
        
        Warehouse_Api::addProducts($this->objId, Conf_In_Order::SRC_COMMON, $product);

        // 非临采供应商，添加商品到供应商关系
        $inorderInfo = Warehouse_Api::getOrderBase($this->objId);

        if(!in_array($inorderInfo['sid'], Conf_In_Order::$Temporary_Purchase_Suppliers))
        {
            $addProducts = array();
            foreach($product as $one)
            {
                if ($one['num'] <= 0) continue;

                $addProducts[] = array(
                    'sku_id' => $one['sid'],
                    'purchase_price' => $one['price'],
                );
            }
            $wssl = new Warehouse_Supplier_Sku_List();
            $wssl->addSkuWhenUnExist($inorderInfo['sid'], $addProducts);
        }
    }
}

$app = new App();
$app->run();