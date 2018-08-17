<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $oldSuid;
    private $newSuid;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->oldSuid = Tool_Input::clean('r', 'old_saler', TYPE_UINT);
        $this->newSuid = Tool_Input::clean('r', 'new_saler', TYPE_UINT);
    }
    
    protected function checkPara() 
    {
        if (empty($this->oid) || !is_numeric(intval($this->oid)))
        {
            throw new  Exception('异常错误，请联系管理员！');
        }
        
        if ($this->oldSuid == $this->newSuid)
        {
            throw new Exception('销售没有改变，不做处理！');
        }
    }
    
    protected function main()
    {
        $oo = new Order_Order();
        $orderInfo = $oo->get($this->oid);
        $cc = new Crm2_Customer();
        $customer = $cc->get($orderInfo['cid']);

        if (empty($orderInfo) || $orderInfo['saler_suid']!=$this->oldSuid)
        {
            throw new Exception('订单状态异常！请联系管理员！');
        }

        if (!Admin_Role_Api::isAdmin($this->_uid, $this->_user)  && $this->_user['city_id'] != $customer['city_id'])
        {
            throw new Exception('只能修改本城市的订单!');
        }

        $upData = array('saler_suid' => $this->newSuid);
        $oo->update($this->oid, $upData);
        
        // 变更客户的销售
        $upDataCrm = array(
            'sales_suid'=>$this->newSuid, 
            'chg_sstatus_time'=>date('Y-m-d H:i:s'), 
            'sale_status'=>Conf_User::CRM_SALE_ST_PRIVATE,
        );
        $cc->update($orderInfo['cid'], $upDataCrm);
        $trackingInfo = array(
            'cid' => $orderInfo['cid'],
            'edit_suid' => $this->newSuid,    // 自动更新账号
            'content' => '修订订单销售：From：'. $this->oldSuid. ' To：'. $this->newSuid. ' By：'. $this->_uid,
            'type' => Conf_User::CT_CHG_SALE_ST,
        );
        Crm2_Api::saveCustomerTracking(0, $trackingInfo);
                
        $param = array(
            'fromSalerId' => $this->oldSuid,
            'toSalerId' => $this->newSuid,
        );
        Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_CHG_SALER, $param);
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

$app = new App();
$app->run();