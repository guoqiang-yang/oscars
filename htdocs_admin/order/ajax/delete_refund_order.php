<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $rid;
    private $oid;
    private $refundInfo;
    private $delRet;

    protected function getPara()
    {
        $this->rid = Tool_Input::clean('r', 'rid', TYPE_UINT);
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (empty($this->rid) || empty($this->oid))
        {
            throw new Exception('delete refund: param error!');
        }
    }
    
    protected function main()
    {
//        $this->refundInfo = Order_Api::getRefund($this->rid);
//        if (empty($this->refundInfo['info']) || $this->refundInfo['info']['oid']!=$this->oid )
//        {
//            throw new Exception('delete refund: refund order Detail Error!');
//        }
//        if ($this->refundInfo['info']['step'] > Conf_Refund::REFUND_STEP_SURE)
//        {
//            throw new Exception('delete refund: step Error!');
//        }
//        
//        $this->delRet = Order_Api::deleteRefund($this->rid);

        Order_Api::deleteRefundNew($this->rid, $this->oid);
        
        //订单操作日志
        Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_DELETE_REFUND_ORDER, array('rid'=>$this->rid));
    }
    
    protected function outputBody()
    {
        $result = array('st' => $this->delRet);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();