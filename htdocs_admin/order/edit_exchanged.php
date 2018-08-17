<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $eid;
	private $oid;
	private $exchanged;
	private $order;
	private $orderProducts;
	private $refundProducts;
    private $deliverTime = array();
	private $relInfo;

	protected function getPara()
	{
		$this->eid = Tool_Input::clean('r', 'eid', TYPE_UINT);
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

    protected function main()
	{
		if ($this->eid)
		{
			$this->exchanged = Exchanged_Api::getExchanged($this->eid);
            if(empty($this->exchanged) || $this->exchanged['info']['exchanged_status'] >0)
            {
                throw new ErrorException('换货单不存在或者已被删除、取消');
            }
            if($this->exchanged['info']['refund_id'] > 0)
            {
                $refundInfo = Order_Api::getRefund($this->exchanged['info']['refund_id']);
                $this->exchanged['refund_step'] = $refundInfo['info']['step'];
                $this->exchanged['_refund_step'] = Conf_Refund::$REFUND_STEPS[$refundInfo['info']['step']];
            }
            if($this->exchanged['info']['aftersale_oid'] > 0)
            {
                $orderInfo = Order_Api::getOrderInfo($this->exchanged['info']['aftersale_oid']);
                $this->exchanged['order_step'] = $orderInfo['step'];
                $this->exchanged['_aftersale_step'] = Conf_Order::$ORDER_STEPS[$orderInfo['step']];
            }
            $oid = $this->exchanged['info']['oid'];

		}
		else if ($this->oid)
		{
			$oid = $this->oid;
		}

        if (empty($oid))
        {
            throw new Exception('换货单异常，请联系技术人员处理！');
        }
        
        // 订单信息
        $this->order = Order_Api::getOrderInfo($oid);
        $orderProducts = Order_Api::getOrderProducts($oid);
        $this->orderProducts = $orderProducts['products'];
        $this->refundProducts = $orderProducts['refund_products'];
        if($this->exchanged['info']['need_storage'] == 1) {
            foreach ($this->orderProducts as $pid => &$productInfo) {
                if (in_array($productInfo['sid'], array_keys(Conf_Exchanged::$VIRTUAL_PRODUCTS))) {
                    unset($this->orderProducts[$pid]);
                }
                $productInfo['has_refund_num'] = 0;
                foreach ($this->refundProducts as $rkey => $rproduct) {
                    list($_rid, $_pid) = explode('#', $rkey);
                    if ($_pid == $pid) {
                        if ($rproduct['num'] == 0) {
                            continue;
                        }
                        $has_refund_num = $rproduct['picked'] + $rproduct['damaged_num'];
                        if ($has_refund_num > 0) {
                            $productInfo['has_refund_num'] += $has_refund_num;
                        } else {
                            $productInfo['has_refund_num'] += $rproduct['apply_rnum'];
                        }
                    }
                }
                if (isset($this->exchanged['refund_products'][$productInfo['pid']])) {
                    $productInfo['apply_rnum'] = $this->exchanged['refund_products'][$productInfo['pid']]['num'];
                }
            }
        }else{
            foreach ($this->orderProducts as $pid => &$productInfo) {
                $productInfo['has_refund_num'] = 0;
                foreach ($this->refundProducts as $rkey => $rproduct) {
                    list($_rid, $_pid) = explode('#', $rkey);
                    if ($_pid == $pid) {
                        if ($rproduct['num'] == 0) {
                            continue;
                        }
                        $has_refund_num = $rproduct['picked'] + $rproduct['damaged_num'];
                        if ($has_refund_num > 0) {
                            $productInfo['has_refund_num'] += $has_refund_num;
                        } else {
                            $productInfo['has_refund_num'] += $rproduct['apply_rnum'];
                        }
                    }
                }
                if (isset($this->exchanged['exchanged_products'][$pid])) {
                    $productInfo['apply_rnum'] = $this->exchanged['exchanged_products'][$pid]['num'];
                }
            }
        }
        $this->orderProducts = Tool_Array::sortByField($this->orderProducts,'apply_rnum','desc');
//        $relProducts = Shop_Api::getProductInfosBySids(array_keys(Conf_Exchanged::$VIRTUAL_PRODUCTS), $this->order['city_id'], Conf_Activity_Flash_Sale::PALTFORM_WECHAT, false);
//        foreach ($relProducts as $product)
//        {
//            $this->relInfo[$product['product']['pid']]['pid'] = $product['product']['pid'];
//            $this->relInfo[$product['product']['pid']]['sid'] = $product['product']['sid'];
//            $this->relInfo[$product['product']['pid']]['price'] = $product['product']['price'];
//            $this->relInfo[$product['product']['pid']]['sku']['title'] = $product['sku']['title'];
//            $this->relInfo[$product['product']['pid']]['sku']['cate1'] = $product['sku']['cate1'];
//            $this->relInfo[$product['product']['pid']]['sku']['cate2'] = $product['sku']['cate2'];
//            foreach ($this->exchanged['exchanged_products'] as $key=>$product2)
//            {
//                if ($product['product']['pid'] == $product2['pid'])
//                {
//                    $this->relInfo[$product['product']['pid']]['num'] = $product2['num'];
//                    unset($this->exchanged['exchanged_products'][$key]);
//                }
//            }
//        }

        $this->deliverTime = Order_Api::getDeliveryTime4Admin();

		$this->addFootJs(array('js/apps/exchanged.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$opButtonHtml = Order_Helper::getExchangedButtonHtml($this->_user, $this->exchanged['info']);

		$wid = $this->order['wid'];

		$this->smarty->assign('order', $this->order);
        $this->smarty->assign('order_products', $this->orderProducts);
		$this->smarty->assign('exchanged', $this->exchanged);
        $this->smarty->assign('exchanged_types', Conf_Exchanged::$EXCHANGED_TYPES);
		$this->smarty->assign('exchanged_steps', Conf_Exchanged::getExchangedStepNames());
		$this->smarty->assign('op_button_html', $opButtonHtml);
		$this->smarty->assign('curr_wid', $wid);
		$this->smarty->assign('warehouse_list', Conf_Warehouse::getWarehouseByAttr('customer'));
        $this->smarty->assign('can_change_warehouse', Conf_Warehouse::isOwnWid($wid));
		$this->smarty->assign('exchanged_reason_types', Conf_Exchanged::$EXCHANGED_REASON_TYPE);
		$this->smarty->assign('exchanged_reason_detail', json_encode(Conf_Exchanged::$EXCHANGED_REASON_DETAIL));
        $this->smarty->assign('exchanged_products', json_encode($this->exchanged['exchanged_products']));
        $this->smarty->assign('refund_products', json_encode($this->exchanged['refund_products']));
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
        $this->smarty->assign('rel_info', $this->relInfo);
        $this->smarty->assign('delivery_time', $this->deliverTime);

		$this->smarty->display('order/edit_exchanged.html');
	}
}

$app = new App('pri');
$app->run();
