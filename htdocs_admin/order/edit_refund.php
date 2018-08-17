<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $rid;
	private $oid;
	private $refund;
	private $order;
    private $orderProducts;
    private $refundProducts;

	protected function getPara()
	{
		$this->rid = Tool_Input::clean('r', 'rid', TYPE_UINT);
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

	protected function main()
	{
		if ($this->rid)
		{
			$this->refund = Order_Api::getRefund($this->rid);
			$oid = $this->refund['info']['oid'];

			if ($this->refund['info']['reason'] > 0)
			{
				header('Location: /order/edit_refund_new.php?rid=' . $this->rid);
				exit;
			}
		}
		else if($this->oid)
		{
			$oid = $this->oid;
		}
		if ($oid)
		{
			$this->order = Order_Api::getOrderInfo($oid);
            $orderProducts = Order_Api::getOrderProducts($oid);
            $this->orderProducts = $orderProducts['products'];
            $this->refundProducts = $orderProducts['refund_products'];
            
            foreach($this->orderProducts as $sid => &$productInfo)
            {
                $productInfo['has_refund_num'] = 0;
                foreach($orderProducts['refund_products'] as $rkey => $rproduct)
                {
                    list($_rid, $_sid) = explode('#', $rkey);
                    if ($_sid == $sid)
                    {
                        if($rproduct['num'] == 0)
                        {
                            continue;
                        }
                        $has_refund_num = $rproduct['picked']+$rproduct['damaged_num'];
                        if($has_refund_num > 0)
                        {
                            $productInfo['has_refund_num'] += $has_refund_num;
                        }else{
                            $productInfo['has_refund_num'] += $rproduct['apply_rnum'];
                        }
                    }
                }  
            }
		}

        //补全退款信息
        if (!empty($this->refund))
        {
            foreach($this->refund['products'] as &$_product)
            {
                $pid = $_product['pid'];
                $_product['order_num'] = $this->orderProducts[$pid]['num'];
                $_product['has_refund_num'] = $this->orderProducts[$pid]['has_refund_num'];
            }
        }
        
		$this->addFootJs(array('js/apps/order.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$opButtonHtml = Order_Helper::getRefundButtonHtml($this->_user, $this->refund['info']);
        
        $_wid = $this->getWarehouseId();
        $wid = !empty($this->rid)? $this->refund['info']['wid']: 
                ($_wid!=0? $_wid: $this->order['wid']);
        
		$this->smarty->assign('order', $this->order);
		$this->smarty->assign('refund', $this->refund);
		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
		$this->smarty->assign('refund_steps', Conf_Refund::getRefundStepNames());
		$this->smarty->assign('op_button_html', $opButtonHtml);
        $this->smarty->assign('curr_wid', $wid);
        $this->smarty->assign('warehouse_list', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('refund_to_balance', $this->order['real_amount']>$this->order['total_order_price']?1:0);
        $this->smarty->assign('order_products', $this->orderProducts);

        $this->smarty->assign('_is_upgrade_wid', Conf_Warehouse::isUpgradeWarehouse($wid));
        
		$this->smarty->display('order/edit_refund.html');
	}
}

$app = new App('pri');
$app->run();
