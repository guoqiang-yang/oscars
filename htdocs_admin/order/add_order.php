<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $step;  //第1步: 保存基本信息； 第2步: 保存商品列表
	private $oid;
	private $cid;
    private $uid;
	private $order;
	private $customer;
	private $deliverTime = array();
	private $area;
	private $distinct;
	private $city;
    private $orderProducts;

	protected function getPara()
	{
		$this->step = Tool_Input::clean('r', 'step', TYPE_UINT);
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->uid = Tool_Input::clean('r', 'uid', TYPE_UINT);
	}

	protected function checkPara()
	{
		$this->step = empty($this->step) ? 1 : $this->step;

		if (2 == $this->step)
		{
			if (empty($this->oid))
			{
				throw new Exception('order:empty order id');
			}
		}
        
        if (Conf_Order::ORDER_STEP_NEW==$this->step && (empty($this->cid)||empty($this->uid)))
        {
            throw new Exception('order:下单异常，请检查客户信息，或联系管理员！');
        }
	}

	protected function main()
	{
		if ($this->cid)
		{
            $deliverDateTime = Order_Api::getDeliveryDateTime();
			$this->deliverTime = $deliverDateTime['time'];
            $this->customer = Crm2_Api::getCustomerInfoByCidUid($this->cid, $this->uid);
            if (!is_array($this->customer))
            {
                throw new Exception('order:下单异常，请检查客户信息，或联系管理员！');
            }
		}
        
		if (2 == $this->step)
		{
			$this->order = Order_Api::getOrderInfo($this->oid);
            $orderProducts = Order_Api::getOrderProducts($this->oid);
            $this->orderProducts = $orderProducts['products'];
		}
        
		$this->city = Conf_Area::$CITY;
		$this->distinct = Conf_Area::$DISTRICT;
		$this->area = Conf_Area::$AREA;

		$this->addFootJs(array('js/core/autosuggest.js', 'js/core/area.js', 'js/apps/order.js'));
		$this->addCss(array('css/autosuggest.css'));
	}

	protected function outputBody()
	{
		if (1 == $this->step)
		{
			$this->smarty->assign('delivery_time', $this->deliverTime);
			$this->smarty->assign('customer', $this->customer);
			$this->smarty->assign('city', Tool_Array::jsonEncode($this->city));
			$this->smarty->assign('distinct', Tool_Array::jsonEncode($this->distinct));
			$this->smarty->assign('area', Tool_Array::jsonEncode($this->area));
            $cityInfo = City_Api::getCity();
            $this->smarty->assign('cur_city', $cityInfo['city_id']);

			$html = 'order/add_order_step1.html';
		}
		elseif (2 == $this->step)
		{
            $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
            $this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
			$this->smarty->assign('order', $this->order);
            $this->smarty->assign('order_products', $this->orderProducts);
			$html = 'order/add_order_step2.html';
		}
		$this->smarty->assign('warehouses', Conf_Warehouse::$WAREHOUSES);
		$this->smarty->assign('payment_types', Conf_Base::getPaymentTypes());

		$this->smarty->display($html);
	}
}

$app = new App('pri');
$app->run();
