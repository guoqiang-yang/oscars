<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $num = 10;
    
    private $oid;
    private $cuid;
    private $price;
    private $role;
    private $userType;
    private $wid;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->cuid = Tool_Input::clean('r', 'cuid', TYPE_UINT);
        $this->price = intval(Tool_Input::clean('r', 'price', TYPE_UINT)) * 100;
        $this->role = Tool_Input::clean('r', 'role', TYPE_UINT);
        $this->userType = Tool_Input::clean('r', 'user_type', TYPE_STR);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        
        $this->userType = empty($this->userType)? $this->role: $this->userType;
        
    }
    
    protected function checkPara()
    {
        if (empty($this->oid) || empty($this->cuid)
            || !array_key_exists($this->role, Conf_Base::getCoopworkerTypes()) )
        {
            throw new Exception('coopworker: param error');
        }
        
        if (Order_Helper::isFranchiseeOrder($this->wid))
        {
            throw new Exception('加盟商的订单，有其自行处理');
        }
    }
    
    protected function checkAuth()
    {
        if ($_REQUEST['role'] == Conf_Base::COOPWORKER_DRIVER)
        {
            parent::checkAuth('hc_order_add_driver');
        }
        else if ($_REQUEST['role'] == Conf_Base::COOPWORKER_CARRIER)
        {
            parent::checkAuth('hc_order_add_del_carrier');
        }
        else
        {
            throw new Exception('common:permission denied');
        }
    }


    protected function main()
    {
        Order_Api::selectDriverCarrier($this->oid, $this->cuid, $this->price, $this->role, $this->userType, $this->wid, $this->_user);

	    $param = array('action' => '添加', 'price' => $this->price / 100, 'reason'=>'手动加司机');
	    if ($this->userType == Conf_Base::COOPWORKER_DRIVER) {
		    $param['role'] = '司机';
		    $driver = Logistics_Api::getDriver($this->cuid);
		    $param['name'] = $driver['name'];

	    } else if ($this->userType == Conf_Base::COOPWORKER_CARRIER) {
		    $param['role'] = '搬运工';
		    $carrier = Logistics_Api::getCarrier($this->cuid);
		    $param['name'] = $carrier['name'];
	    }
        
	    Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_EDIT_COOPWORKER, $param);
    }
    
    protected function outputBody()
    {
        $result = array('st'=>1);
		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
    }
}

$app = new App();
$app->run();