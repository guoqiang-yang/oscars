<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $address;
	private $communityId;
	private $productStr;
	private $city;
	private $district;
	private $area;
	private $cid;
	private $order = array();
	private $products = array();
    private $gift_products;
    private $discount_products;
    private $activityProducts = array();
    private $orderStep;

    protected function checkAuth()
    {
        parent::checkAuth('/order/edit_order');
    }

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
		$this->productStr = Tool_Input::clean('r', 'product_str', TYPE_STR);
		$this->city = Tool_Input::clean('r', 'city', TYPE_UINT);
		$this->district = Tool_Input::clean('r', 'district', TYPE_UINT);
		$this->area = Tool_Input::clean('r', 'area', TYPE_UINT);
        $this->gift_products = Tool_Input::clean('r', 'gift_products', TYPE_STR);
        $this->discount_products = Tool_Input::clean('r', 'discount_products', TYPE_STR);
        $this->orderStep = Tool_Input::clean('r', 'order_step', TYPE_UINT);

		// address 由小区和具体楼号门牌号两部分组成
		$addrDetail = Tool_Input::clean('r', 'addr_detail', TYPE_STR);
		$this->communityId = Tool_Input::clean('r', 'community_id', TYPE_UINT);

		$this->address = '';
		$deliveryType = Tool_Input::clean('r', 'delivery_type', TYPE_UINT) ? : Conf_Order::DELIVERY_EXPRESS;
		if ($deliveryType != Conf_Order::DELIVERY_BY_YOURSELF)
		{
			if (!empty($this->communityId))
			{
				$communtiyInfo = Order_Community_Api::get($this->communityId);

				if (empty($communtiyInfo))
				{
					throw new Exception('小区不存在请重新选择');
				}
				$communtiyName = $communtiyInfo['name'];
			}else{
			    $communtiyName = Tool_Input::clean('r', 'community_name', TYPE_STR);
            }
            if ($this->orderStep >= Conf_Order::ORDER_STEP_SURE && empty($communtiyName))
            {
                throw new Exception('非自提订单，小区信息不能为空！');
            }
			$this->address = $communtiyName . Conf_Area::Separator_Construction . $addrDetail;
		}

		$this->order = array(
			'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
			'uid' => Tool_Input::clean('r', 'uid', TYPE_UINT),
			'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
			'contact_name' => Tool_Input::clean('r', 'contact_name', TYPE_STR),
			'contact_phone' => Tool_Input::clean('r', 'contact_phone', TYPE_STR),
			'contact_phone2' => Tool_Input::clean('r', 'contact_phone2', TYPE_STR),
			'delivery_date' => Tool_Input::clean('r', 'delivery_date', TYPE_STR),
			'delivery_time' => Tool_Input::clean('r', 'delivery_time', TYPE_INT),
			'delivery_time_end' => Tool_Input::clean('r', 'delivery_time_end', TYPE_INT),
			'freight' => 100 * Tool_Input::clean('r', 'freight', TYPE_UINT),
			'note' => Tool_Input::clean('r', 'note', TYPE_STR),
			'customer_note' => Tool_Input::clean('r', 'customer_note', TYPE_STR),
			'payment_type' => Tool_Input::clean('r', 'payment_type', TYPE_UINT),
			'service' => Tool_Input::clean('r', 'service', TYPE_INT),
			'floor_num' => Tool_Input::clean('r', 'floor_num', TYPE_UINT),
			'customer_carriage' => 100 * Tool_Input::clean('r', 'customer_carriage', TYPE_NUM),
			'address' => $this->address,
			'community_id' => $this->communityId,
			'city' => Tool_Input::clean('r', 'city', TYPE_UINT),
			'city_id' => Tool_Input::clean('r', 'city', TYPE_UINT),
			'district' => Tool_Input::clean('r', 'district', TYPE_UINT),
			'area' => Tool_Input::clean('r', 'area', TYPE_UINT),
			'step' => $this->orderStep,
			'privilege_special' => Tool_Input::clean('r', 'privilege_special', TYPE_UINT),
			'privilege_special_desc' => Tool_Input::clean('r', 'privilege_special_desc', TYPE_STR),
			'pre_order_privilege' => Tool_Input::clean('r', 'pre_order_privilege', TYPE_NUM),
			'delivery_type' => $deliveryType,
            'customer_payment_type' => Conf_Base::CUSTOMER_PT_ONLINE_PAY,
		);
        
        if ($this->order['city'] == Conf_City::OTHER)
        {
            $this->order['city_id'] = Conf_City::BEIJING;
        }

		$opNote = array(
            'nopprice' => Tool_Input::clean('r', 'is_print_price', TYPE_UINT),
        );

		$this->order['op_note'] = Order_Api::generateOpNote($opNote);
		$source = Tool_Input::clean('r', 'source', TYPE_UINT);

		// 只要抢工长 特殊处理；请来源不变
		if ($source == Conf_Order::SOURCE_JINGDONG)
		{
			$this->order['source'] = $source;
		}
	}

	protected function checkPara()
	{
        $isCs = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_CS_NEW);
        $isSalerAssis = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ASSIS_SALER_NEW);
        $isAdmin = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ADMIN_NEW);
        
        if ($this->order['step'] >=Conf_Order::ORDER_STEP_SURE && !($isCs||$isSalerAssis||$isAdmin))
        {
            throw new Exception('order:order has been confirmed');
        }
        $msg = Order_Api::canEditOrderInfo($this->oid, $this->_uid);
        if($msg['error'] > 0)
        {
            throw new Exception($msg['errormsg']);
        }

		if (empty($this->order['cid']))
		{
			throw new Exception('customer:invalid customer id');
		}

		if (empty($this->order['contact_name']))
		{
			throw new Exception('customer:contact person name empty');
		}
		if (empty($this->order['contact_phone']))
		{
			throw new Exception('order:empty phone');
		}
		if ($this->order['delivery_type'] != Conf_Order::DELIVERY_BY_YOURSELF)
		{
//			if (empty($this->order['city']))
//			{
//				throw new Exception('order:empty city');
//			}
//			if (empty($this->order['district']))
//			{
//				throw new Exception('order:empty district');
//			}
			if (empty($this->order['address']))
			{
				throw new Exception('order:empty address');
			}
		}
		if (empty($this->order['wid']))
		{
			throw new Exception('order:empty wid');
		}
		if (empty($this->order['delivery_date']))
		{
			throw new Exception('order:delivery date empty');
		}
		if (empty($this->order['delivery_time']))
		{
			throw new Exception('order:delivery time empty');
		}
		if (empty($this->order['delivery_time_end']))
		{
			throw new Exception('order:delivery time empty');
		}
		if ($this->order['service'] == -1)
		{
			throw new Exception('order:service empty');
		}
        if (empty($this->order['city_id']))
        {
            throw new Exception('订单城市属性为空，请反馈给技术人员，查询原因！');
        }
        if(!empty($this->gift_products))
        {
            $this->gift_products = array_filter(explode(';', $this->gift_products));
            foreach ($this->gift_products as $item)
            {
                list($pid, $num) = explode(':', $item);
                if(empty($pid) || empty($num))
                {
                    throw new Exception('赠品数据不合法');
                }
                $this->activityProducts['gift_products'][] = array('pid' => $pid, 'num' => $num);
            }
        }else{
            $this->activityProducts['gift_products'] = 1;
        }
        if(!empty($this->discount_products))
        {
            $this->discount_products = array_filter(explode(';', $this->discount_products));
            foreach ($this->discount_products as $item)
            {
                list($pid, $num) = explode(':', $item);
                if(empty($pid) || empty($num))
                {
                    throw new Exception('特价商品数据不合法');
                }
                $this->activityProducts['discount_products'][] = array('pid' => $pid, 'num' => $num);
            }
        }else{
            $this->activityProducts['discount_products'] = 1;
        }
	}

	private function parseProducts($str)
	{
		if (empty($str))
		{
			return array();
		}

		// 解析字符串
		$products = $pids = array();
		$items = array_filter(explode(',', $str));
		foreach ($items as $item)
		{
			list($pid, $num, $note) = explode(":", $item);

			$products[] = array('pid' => $pid, 'num' => $num, 'oid' => $this->oid, 'note' => $note);
			$pids[] = $pid;
		}

		if (empty($products))
		{
			return array();
		}

		// 补充price
		$productInfos = Shop_Api::getProductInfos($pids);
		$productInfos = Tool_Array::list2Map($productInfos, 'pid');
		foreach ($products as $idx => &$product)
		{
			$pid = $product['pid'];
			$productInfo = $productInfos[$pid];
			$product['price'] += $productInfo['price'];
		}

		return $products;
	}

	protected function main()
	{
		$order = Order_Api::getOrderInfo($this->oid);
        $orderProducts = Order_Api::getOrderProducts($this->oid);
        
        if ($order['wid']!=$this->order['wid'] && $order['step']>=Conf_Order::ORDER_STEP_SURE)
        {
            throw new Exception('修改订单仓库，请回退到客服未确认状态！！');
        }
         
        if ($order['step'] >= Conf_Order::ORDER_STEP_PICKED)
        {
            throw new Exception('订单已出库，不能修改！！');
        }
        $this->order['aftersale_type'] = $order['aftersale_type'];
        $chkCity = Order_Helper::checkOrderAndProductCity($this->order, $orderProducts['products']);
        if ($chkCity['errno'] != 0)
        {
            throw new Exception($chkCity['errmsg']);
        }
        
        if (!in_array($order['saler_suid'], $this->_user['team_member']) && Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW))
        {
            if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_CS_NEW) )
            {
                //nothing
            }
            else
            {
                throw new Exception('order:order not belong to you');
            }
        }

		$this->cid = $order['cid'];

		if ($order['status'] != Conf_Base::STATUS_NORMAL)
		{
			throw new Exception('订单状态异常，不能编辑！');
		}

		//如果要把订单配送方式由配送变成自提，就要检测订单状态
		//已出库的，和已安排司机的订单，不能改成自提
		if ($order['delivery_type'] != Conf_Order::DELIVERY_BY_YOURSELF && $this->order['delivery_type'] == Conf_Order::DELIVERY_BY_YOURSELF)
		{
			if ($order['step'] >= Conf_Order::ORDER_STEP_PICKED)
			{
				throw new Exception('order:delivery type error');
			}
			$drivers = Logistics_Coopworker_Api::getOrderOfWorkers($this->oid, 1);
			if (!empty($drivers))
			{
				throw new Exception('order:delivery type error');
			}
		}

		$this->products = $this->parseProducts($this->productStr);
        
		// 保存工地
		if (!empty($this->address))
		{
			$info = array(
				'cid' => $this->cid,
				'uid' => $this->order['uid'],
				'city' => $this->city,
				'district' => $this->district,
				'area' => $this->area,
				'address' => $this->address,
				'community_id' => $this->communityId,
				'contact_name' => $this->order['contact_name'],
				'contact_phone' => $this->order['contact_phone'],
			);

            if (!empty($order['construction']))
            {
                $info['id'] = $order['construction'];
            }
                
			$siteId = Crm2_Api::saveConstructionSite($info);
            
            if ($siteId <= 0)
            {
                throw new Exception ('客户工地信息错误！！请联系技术查询！');
            }
                
			$this->order['construction'] = $siteId;
			//$this->order['city_id'] = $this->city;
		}

		//送货时间
		$startTime = strtotime($this->order['delivery_date']) + $this->order['delivery_time'] * 3600;
		$endTime = strtotime($this->order['delivery_date']) + $this->order['delivery_time_end'] * 3600;
		$this->order['delivery_date'] = date('Y-m-d H:00:00', $startTime);
		$this->order['delivery_date_end'] = date('Y-m-d H:00:00', $endTime);
		unset($this->order['delivery_time']);
		unset($this->order['delivery_time_end']);

		//安排司机后不再修改 '仓库'
		if ($order['step'] >= Conf_Order::ORDER_STEP_HAS_DRIVER)
		{
			unset($this->order['wid']);
		}

		//订单确认后只有财务可以修改运费搬运费
		if ($order['step'] >= Conf_Order::ORDER_STEP_SURE)
		{
			if (!Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_FINANCE))
			{
				unset($this->order['freight']);
				unset($this->order['customer_carriage']);
			}
		}

		//占用优惠券
		if ($order['step'] < Conf_Order::ORDER_STEP_SURE)
		{
			//优惠
			$info = array_merge($order, $this->order);
            $realOrderProducts = Privilege_Api::getRealBuyProducts($orderProducts['products'], $this->oid);
			$privilegeInfo = Privilege_2_Api::savePromotionPrivilege($info['cid'], $realOrderProducts, $info, false, $this->activityProducts);
			$this->order['privilege_note'] = $privilegeInfo['privilege_note'];
			$this->order['privilege'] = $privilegeInfo['total_privilege'];
		}

		// 配送变成自提，如果订单已确认，直接将步骤更新为已经安排司机，到‘未出库’状态
		if ($order['delivery_type'] != Conf_Order::DELIVERY_BY_YOURSELF && $this->order['delivery_type'] == Conf_Order::DELIVERY_BY_YOURSELF)
		{
			if ($order['step'] >= Conf_Order::ORDER_STEP_SURE)
			{
				$this->order['step'] = Conf_Order::ORDER_STEP_HAS_DRIVER;
			}
		}

		//订单操作日志
		if ($order['step'] != Conf_Order::ORDER_STEP_EMPTY)
		{
			$amountChange = array();
			if (isset($this->order['freight']) && $this->order['freight'] != $order['freight'])
			{
				$amountChange['运费'] = $this->order['freight'] / 100;
			}
			if (isset($this->order['customer_carriage']) && $this->order['customer_carriage'] != $order['customer_carriage'])
			{
				$amountChange['搬运费'] = $this->order['customer_carriage'] / 100;
			}
			if (isset($this->order['privilege']) && $this->order['privilege'] != $order['privilege'])
			{
				$amountChange['优惠'] = $this->order['privilege'] / 100;
			}
			if (!empty($amountChange))
			{
				Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_FEE, $amountChange);
			}

			//修改其他信息
			$fieldsArr = array('address' => '地址', 'service' => '上楼(0不上楼1电梯2楼梯)', 'floor_num' => '楼层', 'wid' => '仓库', 'delivery_date' => '配送日期');
			$orderChange = array();
			foreach ($fieldsArr as $field => $desc)
			{
				if (isset($this->order[$field]) && $this->order[$field] != $order[$field])
				{
					$orderChange[$desc] = $this->order[$field];
				}
			}

			if (!empty($orderChange))
			{
				Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_INFO, $orderChange);
			}
		}

		unset($this->order['privilege_special']);
		unset($this->order['privilege_special_desc']);
		unset($this->order['pre_order_privilege']);

		Order_Api::updateOrderInfo($this->oid, $this->order);

		if ($this->order['step'] == Conf_Order::ORDER_STEP_EMPTY)
		{
			$this->order['step'] = Conf_Order::ORDER_STEP_NEW;
		}
		Order_Api::forwardOrderStep($this->oid, $this->order['step'], $this->_user);

		//自提变成配送， 重新安排司机
		if ($order['delivery_type'] == Conf_Order::DELIVERY_BY_YOURSELF && $this->order['delivery_type'] != Conf_Order::DELIVERY_BY_YOURSELF)
		{
			if ($order['step'] >= Conf_Order::ORDER_STEP_SURE)
			{
				Order_Api::pickupToExpress($this->oid);
			}
		}

		//订单操作日志
		if ($order['step'] == Conf_Order::ORDER_STEP_EMPTY && $this->order['step'] == Conf_Order::ORDER_STEP_NEW)
		{
			$info = Order_Api::getOrderInfo($this->oid);
			//金额{price}，搬运费{carryFee}，运费{freight}，优惠{privilege}
			$param = array('price' => $info['price'] / 100, 'carryFee' => $info['customer_carriage'] / 100, 'freight' => $info['freight'] / 100, 'privilege' => $info['privilege'] / 100, 'delivery_date' => $info['delivery_date']);
			Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_NEW_ORDER, $param);
		}

		// 调整客户财务流水：客服确认后，如果运费，搬运费，优惠修改了，修改客户财务流水
		if ($order['step'] >= Conf_Order::ORDER_STEP_PICKED && isset($this->order['freight']) && isset($this->order['customer_carriage']) && ($this->order['freight'] != $order['freight'] || $this->order['privilege'] != $order['privilege'] || $this->order['customer_carriage'] != $order['customer_carriage']))
		{
			Order_Api::updateOrderByFinanceModify($this->oid, $this->order['freight'], $this->order['privilege'], $this->order['customer_carriage']);
		}
	}

	protected function outputPage()
	{
		$result = array('oid' => $this->oid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();

