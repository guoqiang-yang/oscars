<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $orderInfo;
    private $orderProducts = array();
	
    private $wiopHandler = null;
    
	protected function getPara ()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		
        $this->wiopHandler = new Warehouse_In_Order_Product();
	}

	protected function checkAuth ()
	{
		parent::checkAuth();
	}
	
	protected function main ()
	{
		$this->orderInfo = Warehouse_Api::getOrderBase($this->oid);
        
        // 获取采购单的入库单
        $wsi = new Warehouse_Stock_In();
        $stockInInfo = $wsi->getListOfOrder($this->oid);
        
        if (!empty($stockInInfo))
        {
            throw new Exception('该采购单已经有入库商品，请与库房人员协调删除该采购单！');
        }
        
		//权限判断
		if ( $this->_uid != $this->orderInfo['buyer_uid'] && 
			!Admin_Role_Api::isAdmin($this->_uid, $this->_user))
		{
			throw new Exception('common: delete order only by it\'s owner or administrators');
		}
		
        // 采购商品列表
        $this->orderProducts = $this->wiopHandler->getProductsOfOrder($this->oid);
        
        // 删除
        $this->_delete();
	}
	
    private function _delete()
    {
        // 删除采购单
        Warehouse_Api::deleteOrder($this->oid);
        
        // 删除采购单商品
        $this->wiopHandler->deleteProductsByOrder($this->oid);
        
        if ($this->orderInfo['source'] == Conf_In_Order::SRC_OUTSOURCER)
        {
            $queue = new Data_Queue();
            $queue->enqueue(Queue_Base::Queue_Type_OutSourcer, array('type'=>'delete','oid' => $this->oid));
        }
        else if ($this->orderInfo['source']==Conf_In_Order::SRC_TEMPORARY||$this->orderInfo['source']==Conf_In_Order::SRC_COMPOSITIVE)
        {
            $op = new Data_Dao('t_order_product');
            $op->updateWhere(array('tmp_inorder_id'=>$this->oid), array('tmp_inorder_id'=>0, 'tmp_inorder_num'=>0));
        }
        
        //更新在途，获取采购单的全部商品，并计算在途
        if ($this->orderInfo['source']==Conf_In_Order::SRC_COMMON || $this->orderInfo['source']==Conf_In_Order::SRC_COMPOSITIVE)
        {
            $deslSids = Tool_Array::getFields($this->orderProducts, 'sid');
            Warehouse_Security_Stock_Api::updateWaitNumByWidSid($this->orderInfo['wid'], $deslSids);
        }
        
        //删除采购单日志
        $info = array(
            'admin_id' => $this->_uid,
            'obj_id' => $this->oid,
            'obj_type' => Conf_Admin_Log::OBJTYPE_IN_ORDER,
            'action_type' => 2,
            'params' => json_encode(array('oid' => $this->oid)),
            'wid' => $this->orderInfo['wid'],
        );
        Admin_Common_Api::addAminLog($info);
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