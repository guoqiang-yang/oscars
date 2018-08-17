<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $oid;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }
	protected function main()
	{
		//获取源订单信息
		$sourceOrder = Order_Api::getOrderInfo($this->oid);
  
        if($sourceOrder['step'] >= Conf_Order::ORDER_STEP_PICKED)
        {
            throw new Exception('order:can not supplement order');
        }
        
		//源字段
		$fields = array(
			'cid','uid','wid','contact_name','contact_phone','contact_phone2',
			'delivery_date','delivery_time','note','service','floor_num',
			'address','city','district','area','saler_suid', 'uid','community_id','op_note'
		);
		$order = Tool_Array::checkCopyFields($sourceOrder, $fields);
        
		//变化字段
		$order['note'] .= sprintf(" 订单%d的补单", $this->oid);
		$order['source_oid'] = $this->oid;
        $order['address'] = empty($sourceOrder['_community_name'])? $sourceOrder['address']:
                    ($sourceOrder['_community_name'].Conf_Area::Separator_Construction.$sourceOrder['_address']);
        
		//新建订单
		$newOid = Order_Api::addOrder($sourceOrder['cid'], $order, array(), $this->_uid);
        header('Location: /order/edit_order.php?oid=' . $newOid );
	}
}

$app = new App('pri');
$app->run();
