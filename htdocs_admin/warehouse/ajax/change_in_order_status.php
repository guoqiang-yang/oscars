<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $order;

	protected function getPara()
	{
	    $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	    $this->order['status'] = Tool_Input::clean('r', 'status', TYPE_UINT);
	}

	protected function checkAuth()
    {
        if ($_REQUEST['status'] == Conf_Base::STATUS_WAIT_AUDIT)
        {
            parent::checkAuth('/warehouse/ajax/create_inorder_4_supplier');
        }
        else if (in_array($_REQUEST['status'], array(Conf_Base::STATUS_NORMAL , Conf_Base::STATUS_UN_AUDIT)))
        {
            parent::checkAuth('hc_in_order_audit');
        }

    }

    protected function main()
	{
        $wio = new Warehouse_In_Order();
        $order = $wio->get($this->oid);

        $info = array(
            'admin_id' => $this->_uid,
            'obj_id' => $this->oid,
            'obj_type' => Conf_Admin_Log::OBJTYPE_IN_ORDER,
            'action_type' => 11,
            'wid' => $order['wid'],
        );
        if ($this->order['status'] == Conf_Base::STATUS_WAIT_AUDIT)     //提交审核
        {
            $wiop = new Warehouse_In_Order_Product();
            $sids = Tool_Array::getFields($wiop->getProductsOfOrder($this->oid, array('sid')), 'sid');
            $stockInfo = Warehouse_Security_Stock_Api::getSecurityStock($order['wid'], $sids);
            $flag = true;
            foreach ($stockInfo as $_stock)
            {
                if ($_stock['turn_day'] > 7 || $_stock['turn_day'] == '--')
                {
                    $flag =false;
                }
            }
            if ($order['in_order_type'] == Conf_In_Order::IN_ORDER_TYPE_GIFT || $flag)
            {
                $this->order['step'] = Conf_In_Order::ORDER_STEP_SURE;
                $this->order['status'] = Conf_Base::STATUS_NORMAL;
            }
            $info['params'] = json_encode(array('desc' => '提交审核'));
        }
        else if ($this->order['status'] == Conf_Base::STATUS_NORMAL)     //审核通过
        {
            $this->order['step'] = Conf_In_Order::ORDER_STEP_SURE;
            $info['params'] = json_encode(array('desc' => '审核通过'));
        }
        else if ($this->order['status'] == Conf_Base::STATUS_UN_AUDIT)     //驳回
        {
            $info['params'] = json_encode(array('desc' => '驳回'));
        }

        Admin_Common_Api::addAminLog($info);
        Warehouse_Api::updateorder($this->_uid, $this->oid, $this->order);
        
        // 审核通过，更新在途
        if ($this->order['status'] == Conf_Base::STATUS_NORMAL)
        {
            Warehouse_Security_Stock_Api::updateWaitNumByInorderId($this->oid, $order['wid']);
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

$app = new App('pub');
$app->run();

