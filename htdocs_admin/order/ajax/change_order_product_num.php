<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $pid;
	private $num;
    private $note;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
		$this->num = Tool_Input::clean('r', 'num', TYPE_UINT);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
	}

	protected function checkPara()
	{
		if (empty($this->oid) || empty($this->pid))
		{
			throw new Exception('common:params error');
		}
        $msg = Order_Api::canEditOrderInfo($this->oid);
        if($msg['error'] > 0)
        {
            throw new Exception($msg['errormsg']);
        }
	}

	protected function checkAuth()
	{
		parent::checkAuth('/order/edit_order');
	}

	protected function main()
	{
        $productsOfOrder = Order_Api::getOrderProduct($this->oid);
        $orderActivityProductDao = new Data_Dao('t_order_activity_product');
        $orderActivityProducts = $orderActivityProductDao->getListWhere(array('oid' => $this->oid, 'pid' => $this->pid));
        if(!empty($orderActivityProducts))
        {
            foreach ($orderActivityProducts as $item)
            {
                $this->num += $item['num'];
            }
        }
        $order = Order_Api::getOrderInfo($this->oid);
        if($order['payment_type'] == Conf_Base::PT_CREDIT_PAY)
        {
            if($this->num >0 && !array_key_exists($this->pid, $productsOfOrder['order']))
            {
                throw new Exception('信用付款订单不能添加商品');
            }
            if($this->num > 0 && $this->num > $productsOfOrder['order'][$this->pid]['num'])
            {
                throw new Exception('信用付款订单不能增加商品数量');
            }
        }
        // 判断是否临采已采购
        if (array_key_exists($this->pid, $productsOfOrder['order'])
            && $productsOfOrder['order'][$this->pid]['tmp_inorder_num']!=0)
        {
            throw new Exception('临采商品已经采购，不能再修改数量！增加数量走补单，减少数量请通知库房退货！！');
        }
        
		$price = Order_Api::updateOrderProductNum($this->oid, $this->pid, $this->num);
        //更新商品备注
        $oo = new Order_Order();
        $oo->updateOrderProductInfo($this->oid, $this->pid, 0, array('note'=>$this->note));

		Order_Api::updateOrderModify($this->oid, $price - $order['price']);
		if ($order['step'] > Conf_Order::ORDER_STEP_EMPTY)
		{
			$param = array('总价' => $price / 100);
			Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_PRODUCTS, $param);
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

$app = new App('pri');
$app->run();

