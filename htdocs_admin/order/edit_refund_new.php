<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $rid;
	private $oid;
	private $refund;
	private $order;
	private $orderProducts;
	private $refundProducts;
	private $refundInfo;
	private $refundCoupons = array();
	private $refundPrice;
	private $refundAmount;
	private $maxRefundFeight;
	private $maxRefundCarryFee;

    private $unRefundProducts = array();
	private $deliverTime;
	private $relInfo = array();
    private $exchangedInfo = array();

	protected function getPara()
	{
		$this->rid = Tool_Input::clean('r', 'rid', TYPE_UINT);
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

    protected function checkAuth()
    {
        parent::checkAuth(array('hc_refund_show_detail', '/order/edit_refund_new'));
    }


    protected function main()
	{
		if ($this->rid)
		{
			$this->refund = Order_Api::getRefund($this->rid);
			$oid = $this->refund['info']['oid'];
            $this->exchangedInfo = Exchanged_Api::getExchangedInfoByRid($this->rid);
		}
		else if ($this->oid)
		{
			$oid = $this->oid;
		}
        if (empty($oid))
        {
            throw new Exception('退货单异常，请联系技术人员处理！');
        }
        
        // 订单信息
        $this->order = Order_Api::getOrderInfo($oid);
        
        $_refundDate = !empty($this->rid)?substr($this->refund['info']['ctime'], 0, 10): date('Y-m-d');
        $_deliveryDate = substr($this->order['delivery_date'], 0, 10);
        $this->order['refund_waring'] = strtotime($_refundDate)-strtotime($_deliveryDate)>=16*24*3600? true: false;
        
        $orderProducts = Order_Api::getOrderProducts($oid);
        $this->orderProducts = $orderProducts['products'];
        $this->refundProducts = $orderProducts['refund_products'];
        $this->unRefundProducts = $this->orderProducts;
        Refund_Api::appendRefundInfo4OrderProducts($this->orderProducts, $this->refundProducts);
        
		//补全退款信息
		if (!empty($this->refund))
		{
			foreach ($this->refund['products'] as &$_product)
			{
				$pid = $_product['pid'];
				$_product['order_num'] = $this->orderProducts[$pid]['num'];
				$_product['has_refund_num'] = $this->orderProducts[$pid]['has_refund_num'];
                
                if ($this->refund['info']['step'] < Conf_Refund::REFUND_STEP_IN_STOCK)
                {
                    $_product['has_refund_num'] -= $_product['num'];
                }
                
                // 处理未出现在当前退货单的商品
                if (array_key_exists($pid, $this->unRefundProducts))
                {
                    unset($this->unRefundProducts[$pid]);
                }
			}
		}
        
		if ($this->refund['info']['step'] == Conf_Refund::REFUND_STEP_IN_STOCK)
		{
			$this->refundInfo = Refund_Api::calRefundAmount($this->refund['info']);
		}

		if ($this->refund['info']['paid'] == Conf_Refund::HAD_PAID)
		{
			if (!empty($this->refund['info']['refund_coupon']))
			{
				$refundCouponIds = json_decode($this->refund['info']['refund_coupon'], true);
				$this->refundCoupons = Coupon_Api::getBulk($refundCouponIds);
			}
		}
        
		$this->refundPrice = Refund_Api::calRefundPrice($this->refund['info']);
		$data = Refund_Api::calRefund2Amount($this->refund['info']);
		$this->refundAmount = $data['real_pay'];

		$data = Refund_Api::getMaxRefundInfo($oid, $this->rid);
		$this->maxRefundFeight = $data['freight'];
		$this->maxRefundCarryFee = $data['carry_fee'];
        $this->deliverTime = Order_Api::getDeliveryTime4Admin();

        if ($this->refund['info']['step'] > Conf_Refund::REFUND_STEP_NEW)
        {
            $this->refund['info']['add_order'] = Order_Api::getOrderInfo($this->refund['info']['oid']);
        }

        if (!empty($this->refund) && $this->refund['info']['type'] == Conf_Refund::REFUND_TYPE_ALONE)
        {
            $kv = new Data_Kvdb();
            $data = $kv->get(Conf_Refund::REL_ORDER_INFO . $this->refund['info']['rid']);
            $relOrderInfo = json_decode($data['value'], true);
            $this->refund['rel_info']['delivery_date'] = $relOrderInfo['delivery_date'];
            $this->refund['rel_info']['delivery_time'] = $relOrderInfo['delivery_time'];
            $this->refund['rel_info']['delivery_time_end'] = $relOrderInfo['delivery_time_end'];
        }

        if (!empty($this->refund['info']['rel_oid']))
        {
            $this->refund['rel_info'] = Order_Api::getOrderInfo($this->refund['info']['rel_oid']);
            $this->refund['rel_info']['delivery_time'] = date('G', strtotime($this->refund['rel_info']['delivery_date']));
            $this->refund['rel_info']['delivery_time_end'] = date('G', strtotime($this->refund['rel_info']['delivery_date_end']));
            $this->refund['rel_info']['delivery_date'] = date('Y-m-d', strtotime($this->refund['rel_info']['delivery_date']));
        }
		$this->addFootJs(array('js/apps/refund.js', 'js/apps/delivery_date_check.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		//$opButtonHtml = Order_Helper::getRefundButtonHtml($this->_user, $this->refund['info']);

        $wid = !empty($this->rid)? $this->refund['info']['wid']: $this->order['wid'];
        
		$this->smarty->assign('order', $this->order);
		$this->smarty->assign('refund', $this->refund);
		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
		$this->smarty->assign('refund_steps', Conf_Refund::getRefundStepNames());
		//$this->smarty->assign('op_button_html', $opButtonHtml);
		$this->smarty->assign('curr_wid', $wid);
        $this->smarty->assign('warehouse_list', Conf_Warehouse::getWarehouseByAttr('customer'));
        $this->smarty->assign('can_change_warehouse', Conf_Warehouse::isOwnWid($wid));
		$this->smarty->assign('refund_to_balance', $this->order['real_amount'] > $this->order['total_order_price'] ? 1 : 0);
		$this->smarty->assign('order_products', $this->orderProducts);
		$this->smarty->assign('refund_reason', Conf_Refund::$REFUND_REASON);
		$this->smarty->assign('refund_info', $this->refundInfo);
		$this->smarty->assign('refund_price', $this->refundPrice);
		$this->smarty->assign('refund_amount', $this->refundAmount);
		$this->smarty->assign('refund_coupon', $this->refundCoupons);
		$this->smarty->assign('max_refund_freight', $this->maxRefundFeight);
		$this->smarty->assign('max_refund_carry_fee', $this->maxRefundCarryFee);
		$this->smarty->assign('_is_upgrade_wid', Conf_Warehouse::isUpgradeWarehouse($wid));
        $this->smarty->assign('is_sand_cement_pids', Conf_Order::$SAND_CEMENT_BRICK_PIDS);
        
        $this->smarty->assign('raw_rid', $this->rid);
        $this->smarty->assign('unrefund_products', $this->unRefundProducts);
		$this->smarty->assign('refund_reason_types', Conf_Refund::$REFUND_REASON_TYPE);
		$this->smarty->assign('refund_reason_detail', json_encode(Conf_Refund::$REFUND_REASON_DETAIL));
        $this->smarty->assign('refund_types', Conf_Refund::$REFUND_TYPES);
        $this->smarty->assign('delivery_time', $this->deliverTime);
        $this->smarty->assign('today', date('Y-m-d'));
        $this->smarty->assign('hour', date('G'));
        $this->smarty->assign('rel_info', $this->relInfo);
        $this->smarty->assign('exchanged_info', $this->exchangedInfo);

		$this->smarty->display('order/edit_refund_new.html');
	}
}

$app = new App('pri');
$app->run();
