<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $group;
    
    private $orderInfo;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->group = Tool_Input::clean('r', 'group', TYPE_STR);
        
        $this->orderInfo = Order_Api::getOrderInfo($this->oid);
        
    }
    
    protected function checkPara()
    {
        if (empty($this->oid)||empty($this->group)||empty($this->orderInfo))
        {
            throw new Exception('参数错误');
        }
        
        if ($this->group == $this->orderInfo['_picking_group']['group'])
        {
            throw new Exception('分组没有修改！');
        }
        
        if ( !array_key_exists($this->orderInfo['wid'], Conf_Admin::$PICKING_GROUPS)
             || !array_key_exists($this->group, Conf_Admin::$PICKING_GROUPS[$this->orderInfo['wid']]))
        {
            throw  new Exception('分组没有定义！');
        }
        
        if ($this->orderInfo['step'] <= Conf_Order::ORDER_STEP_SURE)
        {
            throw new Exception('订单客服为确认，请联系客服确认！');
        }
    }
    
    protected function main()
    {
        $groupInfo = Conf_Admin::$PICKING_GROUPS[$this->orderInfo['wid']][$this->group];
        $pickingGroupInfo = array(
            'group' => $this->group,
            'suid' => $groupInfo['suid'],
            'sname' => $groupInfo['sname'],
        );
        $upInfo = array('picking_group' => json_encode($pickingGroupInfo));
        Order_Api::updateOrderInfo($this->oid, $upInfo);
        
        // 记录操作日志
        $param = array(
            'wid' => $this->orderInfo['wid'],
            'from' => !empty($this->orderInfo['picking_group'])?
                    $this->orderInfo['_picking_group']['group'].':'.$this->orderInfo['_picking_group']['sname']: '无',
            'to' => $this->group.':'.$groupInfo['sname'],
        );
        Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_SET_PICKING_GROUP, $param);
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

$app = new App();
$app->run();