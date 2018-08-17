<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $cid;
	private $uid;
	private $oid;
	
    private $order;
    private $customer;
    
	protected function getPara()
	{
		$this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
		$this->uid = Tool_Input::clean('r', 'uid', TYPE_UINT);
	}

	protected function main()
	{
        
		$this->customer = Crm2_Api::getCustomerInfoByCidUid($this->cid, $this->uid);
        
        // 判断一下是否有权限创建订单
        $this->_chkCreateOrder4Sales(); 
        
		$this->order['cid'] = $this->cid;
		$this->order['uid'] = $this->uid;
		$this->order['step'] = Conf_Order::ORDER_STEP_EMPTY;
		$this->order['contact_name'] = $this->customer['_user']['name'];
		$this->order['contact_phone'] = $this->customer['_user']['mobile'];
		$this->order['delivery_date'] = '0000-00-00 00:00:00';
		$this->order['saler_suid'] = intval($this->customer['sales_suid']);
		$this->order['bid'] = intval($this->customer['_user']['bid']);
        
		$cityInfo = City_Api::getCity();
		$this->order['city'] = $cityInfo['city_id'];     //城市不能换
        
        if (array_key_exists($this->_uid, Conf_Aftersale::$AFTER_SALE_PLACE_ORDER))
        {
            $this->order['source'] = Conf_Order::SOURCE_AFTER_SALE;
        }

		$this->oid = Order_Api::addOrder($this->cid, $this->order, array(), $this->_uid);

		header('Location: /order/edit_order.php?oid=' . $this->oid . '&new=1');
	}
    
    /**
     *  校验：销售创建订单：只能为自己的客户 or 自己团队的客户创建订单
     */
    private function _chkCreateOrder4Sales()
    {
        $isSaler = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW);
        $isCS = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_CS_NEW);
        
        if ( !$isCS && $isSaler && !in_array($this->customer['sales_suid'], $this->_user['team_member']) )
        {
            throw new Exception('order:customer not belong you un-create');
        } 
    }
}

$app = new App('pri');
$app->run();
