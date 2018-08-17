<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
    private $giftProducts;
    private $discountProducts;
    private $activityProducts;
    private $checkNum;
	private $html;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->giftProducts = json_decode(Tool_Input::clean('r', 'gift_products', TYPE_STR));
        $this->discountProducts = json_decode(Tool_Input::clean('r', 'discount_products', TYPE_STR));
        $this->checkNum = Tool_Input::clean('r', 'check_num', TYPE_UINT);
	}

    protected function checkPara()
    {
        if (empty($this->oid))
        {
            throw new Exception('对不起，订单id为空');
        }
        if(!empty($this->giftProducts))
        {
            foreach ($this->giftProducts as $product)
            {
                $this->activityProducts['gift_products'][] = array(
                    'pid' => $product[0],
                    'price' => $product[1],
                    'num' => $product[2]
                );
            }
        }elseif($this->checkNum > 0)
        {
            $this->activityProducts['gift_products'] = 1;
        }
        if(!empty($this->discountProducts))
        {
            foreach ($this->discountProducts as $product)
            {
                $this->activityProducts['discount_products'][] = array(
                    'pid' => $product[0],
                    'price' => $product[1],
                    'num' => $product[2]
                );
            }
        }elseif($this->checkNum > 0)
        {
            $this->activityProducts['discount_products'] = 1;
        }
    }


    protected function main()
	{
        $order = Order_Api::getOrderInfo($this->oid);
        if ($order['step'] < Conf_Order::ORDER_STEP_SURE) {
            $products = Order_Api::getOrderProducts($this->oid);
            $products = Privilege_Api::getRealBuyProducts($products['products'], $this->oid);
            $promotionPrivilege = Privilege_2_Api::computePromotionPrivilege($order['cid'], $products, $order, false, $this->activityProducts);
            if($this->checkNum > 0)
            {
                if(empty($this->activityProducts['gift_products']))
                {
                    $promotionPrivilege['gift_products'] = array();
                }else{
                    $gift_pids = Tool_Array::getFields($this->activityProducts['gift_products'],'pid');
                    foreach ($promotionPrivilege['gift_products'] as $key => $activityInfo)
                    {
                        foreach ($activityInfo as $pid=>$product)
                        {
                            if(!in_array($pid, $gift_pids)){
                                unset($promotionPrivilege['gift_products'][$key][$pid]);
                            }
                            if(isset($promotionPrivilege['gift_products'][$key]) && empty($promotionPrivilege['gift_products'][$key]))
                            {
                                unset($promotionPrivilege['gift_products'][$key]);
                            }
                        }
                    }
                }
                if(empty($this->activityProducts['discount_products']))
                {
                    $promotionPrivilege['special_price_products'] = array();
                }else{
                    $discount_pids = Tool_Array::getFields($this->activityProducts['discount_products'],'pid');
                    foreach ($promotionPrivilege['special_price_products'] as $key => $activityInfo)
                    {
                        foreach ($activityInfo as $pid=>$product)
                        {
                            if(!in_array($pid, $discount_pids)){
                                unset($promotionPrivilege['special_price_products'][$key][$pid]);
                            }
                            if(isset($promotionPrivilege['special_price_products'][$key]) && empty($promotionPrivilege['special_price_products'][$key]))
                            {
                                unset($promotionPrivilege['special_price_products'][$key]);
                            }
                        }
                    }
                }
            }
            if ($promotionPrivilege['coupon_privilege'] > 0)
            {
                $promotionPrivilege['coupon_nums'] = 1;
            }
            $this->smarty->assign('privilege', $promotionPrivilege);
        }else{
            $orderPrivileges = Privilege_Api::getOrderPrivilege($this->oid);
            $orderActivityProductDao = new Data_Dao('t_order_activity_product');
            $activityProducts = $orderActivityProductDao->getListWhere(array('oid' => $this->oid));
            if(!empty($activityProducts))
            {
                $pids = Tool_Array::getFields($activityProducts, 'pid');
                $productInfos = Shop_Api::getProductInfos($pids,Conf_Activity_Flash_Sale::PALTFORM_WECHAT, true);
                foreach ($activityProducts as &$product)
                {
                    $product['title'] = $productInfos[$product['pid']]['sku']['title'];
                    $product['unit'] = $productInfos[$product['pid']]['sku']['unit'];
                }
            }
            $customer = Crm2_Api::getCustomerInfo($order['cid']);
            $this->smarty->assign('customer', $customer['customer']);
            $this->smarty->assign('activity_products', $activityProducts);
            $this->smarty->assign('order_privileges', $orderPrivileges);
        }
        $this->smarty->assign('order', $order);
		$this->html = $this->genHtml();
	}

	protected function genHtml()
	{
		$html = $this->smarty->fetch('order/block_order_activity_product.html');

		return $html;
	}

	protected function outputBody()
	{
		$result = array('html' => $this->html);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pub');
$app->run();