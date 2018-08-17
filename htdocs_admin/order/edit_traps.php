<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $tid;
	private $oid;
	private $traps;
	private $order;
	private $orderProducts;
    private $relInfo;
    private $deliverTime = array();

	protected function getPara()
	{
		$this->tid = Tool_Input::clean('r', 'tid', TYPE_UINT);
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

    protected function main()
	{
		if ($this->tid)
		{
			$this->traps = Traps_Api::getTraps($this->tid);
            if(empty($this->traps) || $this->traps['info']['traps_status'] >0)
            {
                throw new ErrorException('补漏单不存在或者已被删除');
            }
            if($this->traps['info']['step'] > Conf_Traps::TRAPS_STEP_NEW)
            {
                $orderInfo = Order_Api::getOrderInfo($this->traps['info']['aftersale_oid']);
                $this->traps['order_step'] = $orderInfo['step'];
                $this->traps['_aftersale_step'] = Conf_Order::$ORDER_STEPS[$orderInfo['step']];
            }
            $oid = $this->traps['info']['oid'];
            $this->orderProducts = $this->traps['products'];
		}
		else if ($this->oid)
		{
			$oid = $this->oid;
		}
        if (empty($oid))
        {
            throw new Exception('补漏单异常，请联系技术人员处理！');
        }
        
        // 订单信息
        $this->order = Order_Api::getOrderInfo($oid);
        if(empty($this->tid))
        {
            $orderProducts = Order_Api::getOrderProducts($oid);
            foreach ($orderProducts['products'] as $pid=>$product)
            {
                $cate1_name = Conf_Sku::$CATE1[$product['sku']['cate1']]['name'];
                $cate2_name = Conf_Sku::$CATE2[$product['sku']['cate1']][$product['sku']['cate2']]['name'];
                if(isset($traps_products[$pid]))
                {
                    $this->orderProducts[] = array(
                        'pid'=>$pid,'pname'=>$product['sku']['title'],
                        'cate'=>$cate1_name.'-'.$cate2_name,
                        'price'=>$traps_products[$pid]['price'],
                        'num'=>$product['num'],
                        'traps_num' => $traps_products[$pid]['num']
                    );
                }else{
                    $this->orderProducts[] = array(
                        'pid'=>$pid,'pname'=>$product['sku']['title'],
                        'cate'=>$cate1_name.'-'.$cate2_name,
                        'price'=>$product['price'],
                        'num'=>$product['num'],
                        'traps_num' => ''
                    );
                }
            }
        }
//        $relProducts = Shop_Api::getProductInfosBySids(array_keys(Conf_Refund::$VIRTUAL_PRODUCTS), $this->order['city_id'], Conf_Activity_Flash_Sale::PALTFORM_WECHAT, false);
//        foreach ($relProducts as $product)
//        {
//            $this->relInfo[$product['product']['pid']]['pid'] = $product['product']['pid'];
//            $this->relInfo[$product['product']['pid']]['sid'] = $product['product']['sid'];
//            $this->relInfo[$product['product']['pid']]['price'] = $product['product']['price'];
//            $this->relInfo[$product['product']['pid']]['sku']['title'] = $product['sku']['title'];
//            $this->relInfo[$product['product']['pid']]['sku']['cate1'] = $product['sku']['cate1'];
//            $this->relInfo[$product['product']['pid']]['sku']['cate2'] = $product['sku']['cate2'];
//            if (isset($traps_products[$product['product']['pid']]))
//            {
//                $this->relInfo[$product['product']['pid']]['num'] = $traps_products[$product['product']['pid']]['num'];
//            }
//        }
        $this->deliverTime = Order_Api::getDeliveryTime4Admin();

		$this->addFootJs(array('js/apps/traps.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$opButtonHtml = Order_Helper::getTrapsButtonHtml($this->_user, $this->traps['info']);

		$wid = $this->order['wid'];

		$this->smarty->assign('order', $this->order);
        $this->smarty->assign('order_products', json_encode($this->orderProducts));
		$this->smarty->assign('traps', $this->traps);
        $this->smarty->assign('traps_types', Conf_Traps::$TRAPS_TYPES);
		$this->smarty->assign('traps_steps', Conf_Traps::getTrapsStepNames());
		$this->smarty->assign('op_button_html', $opButtonHtml);
		$this->smarty->assign('curr_wid', $wid);
		$this->smarty->assign('warehouse_list', Conf_Warehouse::getWarehouseByAttr('customer'));
		$this->smarty->assign('traps_reason_types', Conf_Traps::$TRAPS_REASON_TYPE);
		$this->smarty->assign('traps_reason_detail', json_encode(Conf_Traps::$TRAPS_REASON_DETAIL));
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
        $this->smarty->assign('rel_info', $this->relInfo);
        $this->smarty->assign('delivery_time', $this->deliverTime);

		$this->smarty->display('order/edit_traps.html');
	}
}

$app = new App('pri');
$app->run();
