<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $addOids;
    
    private $orderInfo;
    private $orderFreight;
    private $orderProduct;
    private $refund;
    
    private $response;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->addOids = explode(',', Tool_Input::clean('r', 'add_oids', TYPE_UINT));
        
        $this->response = array(
            'errno' => 0,
            'data' => array(),
        );
    }

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/logistics/ajax/save_order_line');
    }

    protected function main()
    {
        $search = array('oid' => $this->oid);
        $orderInfo = Logistics_Order_Api::getUnlineOrders($search);
        $this->orderInfo = $orderInfo[$this->oid];
        
        $this->orderFreight = Order_Community_Api::getDistanceAndFeeListNew($this->orderInfo['community_id'], $this->orderInfo['wid']);
        if ($orderInfo[$this->oid]['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_REFUND)
        {
            $this->orderProduct = Refund_Api::getRefundProductByRid($orderInfo[$this->oid]['aftersale_id']);
        }
        else
        {
            $this->orderProduct = Logistics_Order_Api::getOrderProduct($this->oid);
        }

        if ($this->orderInfo['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED)
        {
            $exchangeInfo = Exchanged_Api::getExchanged($this->orderInfo['aftersale_id']);
            $this->refund = Refund_Api::getRefundProductByRid($exchangeInfo['info']['refund_id']);
        }

        //$this->_getProductsSummary();
        $this->_getProductsDetail();
        
        $this->response['data']['had_add'] = in_array($this->oid, $this->addOids)?1:0;
    }
    
    protected function outputBody()
    {
		$response = new Response_Ajax();
		$response->setContent($this->response);
		$response->send();
        
		exit;
    }
    
    // 商品摘要
    private function _getProductsSummary()
    {
        $this->smarty->assign('order_info', $this->orderInfo);
        $this->smarty->assign('summary', $this->orderProduct['summary']);
        $this->smarty->assign('order_freight', $this->orderFreight);
        $this->smarty->assign('type', 'summary');
        $this->smarty->assign('warehouses', Conf_Warehouse::$WAREHOUSES);
        
        $this->response['data']['summary'] = $this->smarty->fetch('logistics/aj_order_detail.html');
    }
    
    // 商品详情
    private function _getProductsDetail()
    {
        if ($this->orderInfo['aftersale_type'] == Conf_Order::AFTERSALE_TYPE_EXCHANGED)
        {
            $this->smarty->assign('refund', $this->refund);
        }
        $this->smarty->assign('order_info', $this->orderInfo);
        $this->smarty->assign('order_products', $this->orderProduct);
        $this->smarty->assign('order_freight', $this->orderFreight);
        
        $this->smarty->assign('type', 'detail');
        $this->smarty->assign('warehouses', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('aftersale_types', Conf_Order::$AFTERSALE_TYPES);
        
        $this->response['data']['detail'] = $this->smarty->fetch('logistics/aj_order_detail.html');
    }
}

$app = new App();
$app->run();