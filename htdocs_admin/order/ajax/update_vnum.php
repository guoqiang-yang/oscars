<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $sid;
	private $vnum;
	private $newVnum;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
		$this->vnum = Tool_Input::clean('r', 'vnum', TYPE_UINT);
		$this->newVnum = Tool_Input::clean('r', 'new_vnum', TYPE_UINT);
	}

	protected function main()
	{
        // 如果更新缺货：必须重新拣货（by：guoqiang 20170224）
        
//		$vnum = $picked = $num = 0;
//		$orderProducts = Order_Api::getOrderProduct($this->oid);
//		foreach ($orderProducts['order'] as $p) {
//			if ($p['sid'] == $this->sid) {
//				$vnum = $p['vnum'];
//				$picked = $p['picked'];
//				$num = $p['num'];
//                break;
//			}
// 		}
//
//		if ($picked > 0 && $picked <= $num - $this->newVnum) {
//			$picked = $num - $this->newVnum;
//		}
        
        $oorder = new Order_Order();
        $orderInfo = $oorder->get($this->oid);
        
        if ($orderInfo['step'] < Conf_Order::ORDER_STEP_SURE)
        {
            throw new Exception('订单未确认，不能更新缺货！！');
        }
        if ($orderInfo['step'] >= Conf_Order::ORDER_STEP_PICKED)
        {
            throw new Exception('订单已经出库，不能再更新缺货！！');
        }

        $oo = new Order_Occupied();
        $oo->updateOrderVnum($this->oid, $this->sid, $this->newVnum);
        
//		$update = array(
//			'vnum' => $this->newVnum,
//			'picked' => $picked,
//		);
//		Order_Api::updateOrderProduct($this->oid, $this->sid, $update);

            $param = array('sid' => $this->sid, 'from_num' => $this->vnum, 'to_num' => $this->newVnum);
            Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_UPDATE_SHORTAGE, $param);
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

