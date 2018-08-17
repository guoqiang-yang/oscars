<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $execType;
    private $orderType;
    private $ret;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->execType = Tool_Input::clean('r', 'exec_type', TYPE_STR);
		$this->orderType = Tool_Input::clean('r', 'order_type', TYPE_STR);
	}

	protected function checkAuth()
    {
        if ($_REQUEST['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_OUT)
        {
            if ($_REQUEST['exec_type'] == 'del')
            {
                parent::checkAuth('hc_del_other_stock_out_order');
            }
            elseif ($_REQUEST['exec_type'] == 'wait_audit')
            {
                parent::checkAuth('/warehouse/ajax/save_other_stock_out_order');
            }
            elseif ($_REQUEST['exec_type'] == 'un_audit')
            {
                parent::checkAuth('hc_un_audit_other_stock_out_order');
            }
            elseif ($_REQUEST['exec_type'] == 'audit')
            {
                parent::checkAuth('hc_audit_other_stock_out_order');
            }
            elseif ($_REQUEST['exec_type'] == 'finish')
            {
                parent::checkAuth('hc_finish_other_stock_out_order');
            }
        }
        elseif ($_REQUEST['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_IN)
        {
            if ($_REQUEST['exec_type'] == 'del')
            {
                parent::checkAuth('hc_del_other_stock_in_order');
            }
            elseif ($_REQUEST['exec_type'] == 'wait_audit')
            {
                parent::checkAuth('/warehouse/ajax/save_other_stock_in_order');
            }
            elseif ($_REQUEST['exec_type'] == 'un_audit')
            {
                parent::checkAuth('hc_un_audit_other_stock_in_order');
            }
            elseif ($_REQUEST['exec_type'] == 'audit')
            {
                parent::checkAuth('hc_audit_other_stock_in_order');
            }
            elseif ($_REQUEST['exec_type'] == 'finish')
            {
                parent::checkAuth('hc_finish_other_stock_in_order');
            }
        }
    }

    protected function main()
	{
        $this->ret = Warehouse_Api::changeOtherStockOutOrder($this->oid, $this->execType, $this->_uid);
	}
	
	protected function outputBody()
	{
		$result = array('ret' => $this->ret['ret'], 'msg' => $this->ret['msg']);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App();
$app->run();