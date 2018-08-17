<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
    private $suid;
    private $price;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->suid = Tool_Input::clean('r', 'suid', TYPE_UINT);
		$this->price = Tool_Input::clean('r', 'price', TYPE_NUM) * 100;
	}

	protected function checkPara()
	{
		if (empty($this->oid))
		{
			throw new Exception('order:empty order id');
		}

		if (empty($this->suid))
		{
			throw new Exception('发放人不能为空');
		}
		if( !Admin_Role_Api::isAdmin($this->_uid) && $this->suid != $this->_uid)
        {
            throw new Exception('非本人不能发销售优惠');
        }
	}

	protected function main()
	{
        $orderInfo = Order_Api::getOrderInfo($this->oid);
        $sale_preferential = Admin_Api::getSaleLeaderBySuid($this->_user, $orderInfo);
        if(!Admin_Role_Api::isAdmin($this->_uid) && !in_array($this->_uid, array_keys($sale_preferential)))
        {
            throw new Exception('该发放人没有权限给优惠');
        }
        Order_Helper::canDealOrder($orderInfo, $this->_user);
        
        Privilege_Api::saveOrderAvailableSalePrivlegeAmount($this->oid, $this->suid, $this->price, $this->_uid);
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

