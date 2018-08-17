<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $oid;
    private $guaranteed;
    private $note;
    
    private $orderInfo;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->guaranteed = Tool_Input::clean('r', 'guaranteed', TYPE_UINT);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
        
        $this->guaranteed = empty($this->guaranteed)? 2: $this->guaranteed;
    }
    
    protected function checkPara()
    {
        if (empty($this->oid) || empty($this->note))
        {
            throw new Exception('common:params error');
        }
        
//        $roles = explode(',', $this->_user['roles']);
//        $pr = new Permission_Role();
//        $roleInfos = $pr->getBulk($roles);
//        $rkeysArr = Tool_Array::getFields($roleInfos, 'rkey');
        
        $roleIds = explode(',', $this->_user['roles']);
        $rkeysArr = Permission_Api::getRolesRkey($roleIds);
        
        if ((array_key_exists(Conf_City::CHONGQING, $this->_user['city_wid_map']) 
             ||array_key_exists(Conf_City::CHENGDU, $this->_user['city_wid_map']) )
            && in_array(Conf_Admin::ROLE_SALES_NEW, $rkeysArr) && count($this->_user['team_member'])==1)
        {
            throw new Exception('重庆销售不能担保或取消担保！');
        }
    }

    protected function checkAuth()
    {
        return parent::checkAuth(array('/order/ajax/guaranteed_order', '/order/ajax/guaranteed_order_4_sales'));
    }
    
    protected function main()
    {
        $this->orderInfo = Order_Api::getOrderInfo($this->oid);
        
        if($this->orderInfo['is_guaranteed'] != $this->guaranteed)
        {
            throw new Exception('担保状态错误，请刷新后查看！');
        }
        
        if ($this->orderInfo['paid'] == Conf_Order::HAD_PAID)
        {
            throw new Exception('已经支付，无需担保！');
        }

        if ($this->guaranteed == 1 && $this->orderInfo['city_id'] == Conf_City::CHONGQING 
            && $this->orderInfo['step'] >= Conf_Order::ORDER_STEP_SURE && $this->_uid!=1029)
        {
            throw new Exception('重庆订单不能取消担保');
        }

            $upData = array(
            'is_guaranteed' => $this->guaranteed==1? 2: 1,
        );
        
        Order_Api::updateOrderInfo($this->oid, $upData);  
        
        // 订单操作日志
        $param = array(
            'guaranteed_type' => $this->guaranteed==1? '取消担保': '担保',
            'note' => $this->note,
        );
        
        Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_GUARANTEED_ORDER, $param);
    }
    
    protected function outputBody()
    {
        $ret = array('st'=>0);
        $response = new Response_Ajax();
		$response->setContent($ret);
		$response->send();

		exit;
    }
    
}

$app =  new App();
$app->run();