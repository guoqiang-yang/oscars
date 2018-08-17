<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $supplierId;
    private $wid;
    private $productList;
    
    private $totalPrice = 0;
    private $totalNum = 0;
    private $inorderId = 0;
    
    protected function getPara()
    {
        $this->supplierId = Tool_Input::clean('r', 'supplier_id', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->productList = json_decode(Tool_Input::clean('r', 'products', TYPE_STR), true);
    }
    
    protected function checkPara()
    {
        if (empty($this->wid))
        {
            throw new Exception('仓库为空，请核对！');
        }
        if (empty($this->supplierId))
        {
            throw new Exception('供应商为空，请核对！');
        }
        if (empty($this->productList))
        {
            throw new Exception('商品列表为空，请核对！');
        }
        
        $ws = new Warehouse_Stock();
        $sids = Tool_Array::getFields($this->productList, 'sid');
        $stockInfo = Tool_Array::list2Map($ws->getBulk($this->wid, $sids), 'sid');
        
        foreach($this->productList as &$one)
        {
            if ($one['price'] <= 0 || $one['num'] <= 0)
            {
                throw new Exception('SKU ID: '. $one['sid']. ' 价格或数量异常！');
            }
            
            if($stockInfo[$one['sid']]['outsourcer_id'] > 0)
            {
                throw new Exception('商品sid:'.$one['sid'].'是外包供应商商品');
            }
            
            $one['source'] = Conf_In_Order::SRC_COMMON;
            $one['ctime'] = date('Y-m-d H:i:s');
            
            $this->totalPrice += $one['price'];
            $this->totalNum++;
        }
    }


    protected function main()
    {
        $supplierInfo = Warehouse_Api::getSupplier($this->supplierId);
        if (empty($supplierInfo) || $supplierInfo['status']!=Conf_Base::STATUS_NORMAL)
        {
            throw new Exception('供应商状态异常！');
        }
        
        $inorderInfo = array(
            'wid' => $this->wid,
            'contact_name' => $supplierInfo['contact_name'],
            'contact_phone' => $supplierInfo['phone'],
            'delivery_date' => date('Y-m-d H:i:s', time()+$supplierInfo['delivery_hours']*3600),
            'product_num' => $this->totalNum,
            'in_order_type' => Conf_In_Order::IN_ORDER_TYPE_ORDER,
            'buyer_uid' => $this->_uid,
            'step' => Conf_In_Order::ORDER_STEP_NEW,
            'payment_type' => Conf_Stock::PAYMENT_TRANSFER,
            'source' => Conf_In_Order::SRC_COMMON,
            'ctime' => date('Y-m-d H:i:s'),
        );

        if (Conf_Base::switchForManagingMode())
        {
            $inorderInfo['managing_mode'] = $supplierInfo['managing_mode'];
        }

        $this->inorderId = Warehouse_Api::addOrder($this->supplierId, $inorderInfo, $this->productList);
        
    }
    
    protected function outputBody()
    {
        $result = array('id'=>  $this->inorderId);
        
		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
    }
}

$app = new App();
$app->run();