<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $products;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->products = json_decode(Tool_Input::clean('r', 'products', TYPE_STR), true);
    }
    
    protected function checkPara()
    {
        if (empty($this->oid))
        {
            throw new Exception('参数错误！');
        }
        if (empty($this->products))
        {
            throw new Exception('上架数量不能为0！');
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('hc_shelved_other_stock_in_product');
    }

    protected function main()
    {
        $wosoo = new Warehouse_Other_Stock_Out_Order();
        $order = $wosoo->get($this->oid);
        $wl = new Warehouse_Location();

        foreach ($this->products as $_p)
        {
            $ret = $wl->checkLocaton($_p['loc']);
            if (!$ret)
            {
                throw new Exception('货位'. $_p['loc'] .'不合法！');
            }
        }

        // Cost-FIFO入队；使用库均成本更新单据商品成本
        $fifoQueueDatas = array();
        $aveCosts = Shop_Cost_Api::getAveCost($order['wid'], Tool_Array::getFields($this->products, 'sid'));
        
        foreach ($this->products as $_product)
        {
            $_product['cost'] = $aveCosts[$_product['sid']];
            Warehouse_Api::updateOtherStockInOrderProducts($this->oid, $_product, $this->_uid);
            Warehouse_Location_Api::updateLocationStockWithHistory($_product['sid'], $_product['loc'], $order['wid'], $_product['num'],
                Conf_Warehouse::STOCK_HISTORY_OTHER_STOCK_IN, $this->_uid, $this->oid, $note='', $reason=23);
            
            $fifoQueueDatas[] = array(
                'sid' => $_product['sid'],
                'num' => $_product['num'],
                'cost' => $_product['cost'],
            );
        }
        Warehouse_Api::checkOtherStockInOrderShelvedStatus($this->oid);
        
        // Cost-FIFO 入队
        $billInfo = array('in_id'=>$this->oid, 'in_type'=>Conf_Warehouse::STOCK_HISTORY_OTHER_STOCK_IN);
        Shop_Cost_Api::enqueueSids4FifoCost($order['wid'], $billInfo, $fifoQueueDatas);
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