<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $rid;
    
    protected function getPara()
    {
        $this->rid = Tool_Input::clean('r', 'rid', TYPE_UINT);
    }
    
    protected function checkAuth()
    {
        parent::checkAuth('hc_complate_virtual_refund');
    }
    
    protected function checkPara()
    {
        if (empty($this->rid))
        {
            throw new Exception('退单id为空');
        }
    }

    protected function main()
    {
        $or = new Order_Refund();
        
        $refundInfo = $or->get($this->rid);
        
        if (empty($refundInfo) || $refundInfo['status']!=Conf_Base::STATUS_NORMAL)
        {
            throw new Exception('退单异常，请刷新重试');
        }
        if ($refundInfo['type'] != Conf_Refund::REFUND_TYPE_VIRTUAL)
        {
            throw new Exception('单据类型异常，非空退单，不能进行该操作');
        }
        
        // 更新退单状态
        $info = array(
            'step' => Conf_Refund::REFUND_STEP_SHELVED, 
            'received_suid' => $this->_uid,
        );
        $or->update($this->rid, $info);
        
        // 更新退货商品
        $refundProducts = $or->getProductsOfRefund($this->rid);
        foreach($refundProducts as $rpinfo)
        {
            $where = sprintf('status=0 and oid=%d and rid=%d and pid=%d', $refundInfo['oid'], $this->rid, $rpinfo['pid']);
            $upData = array('picked' => $rpinfo['num']);
            $or->updateRefundProductByWhere($where, $upData);
        }
        
        Admin_Api::addOrderActionLog($this->_uid, $refundInfo['oid'], Conf_Order_Action_Log::ACTION_PUT_IN_REFUND_ORDER);
    }
    
    protected function outputBody()
    {
		$response = new Response_Ajax();
		$response->setContent(array('st'=>1));
		$response->send();

		exit;
    }
    
}

$app = new App();
$app->run();