<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $wid;
	private $sid;
    private $source;
    private $otype;
    
    private $chgType;
    private $delNum;
    private $salesOid;
    
    private $inorderProducts;
    private $delProduct = array();
    
    private $response=array();
    
	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
		$this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->source = Tool_Input::clean('r', 'source', TYPE_UINT);
        $this->otype = Tool_Input::clean('r', 'otype', TYPE_STR);
        
        // 临采删除使用
        $this->chgType = Tool_Input::clean('r', 'chg_type', TYPE_UINT);
        $this->delNum = Tool_Input::clean('r', 'del_num', TYPE_UINT);
        $this->salesOid = Tool_Input::clean('r', 'sale_oid', TYPE_UINT); 
	}
    
    protected function checkPara()
    {
        if (empty($this->oid) || empty($this->sid))
        {
            throw new Exception('基础参数错误，请联系管理员');
        }
        
        if(empty($this->source)||!array_key_exists($this->source, Conf_In_Order::$In_Order_Source))
        {
            throw new Exception('订单来源异常，请联系技术人员处理！！');
        }
        
        if (!empty($this->otype) && $this->source!=Conf_In_Order::SRC_TEMPORARY)
        {
            throw new Exception('非临采，不能进行该操作');
        }
    }

    protected function checkAuth()
	{
		parent::checkAuth();
	}

	protected function main()
	{
        if (!empty($this->otype))
        {
            $this->_oMain();
            return;
        }
        
        // 普采订单 删除商品
        if ($this->source == Conf_In_Order::SRC_COMMON)
        {
            Warehouse_Api::deleteProduct($this->oid, $this->sid, $this->source);
        }
        // 临采订单 删除商品
        else if ($this->source == Conf_In_Order::SRC_TEMPORARY)
        {
            // 获取采购单商品列表
            $inorderInfo = Warehouse_Api::getOrderProducts($this->oid);
            
            if (!array_key_exists(Conf_In_Order::SRC_TEMPORARY, $inorderInfo)
                || !array_key_exists($this->sid, $inorderInfo[Conf_In_Order::SRC_TEMPORARY]))
            {
                throw new Exception('删除商品不存在，请刷新页面查看！');
            }
            
            // 删除采购单商品
            Warehouse_Api::deleteProduct($this->oid, $this->sid, $this->source);
            
            // 删除的商品是临采商品，需要恢复t_order_product表中tmp_inorder_num的数量
            $op = new Data_Dao('t_order_product');
            $where = sprintf('tmp_inorder_id=%d and sid=%d', $this->oid, $this->sid);
            $op->updateWhere($where, array('tmp_inorder_id' => 0, 'tmp_inorder_num' => 0));
        }
        
        // 如果采购单没有商品，删除采购单
//        $newInorderProduct = Warehouse_Api::getOrderProducts($this->oid);
//        
//        if (empty($newInorderProduct))
//        {
//            Warehouse_Api::deleteOrder($this->oid);
//        }
        
        //刷新在途数量
        Warehouse_Security_Stock_Api::updateWaitNumByWidSid($this->wid, array($this->sid));
        
        $this->response = array('oid' => $this->oid, 'sid' => $this->sid);

        //删除采购商品日志
        $info = array(
            'admin_id' => $this->_uid,
            'obj_id' => $this->oid,
            'obj_type' => Conf_Admin_Log::OBJTYPE_IN_ORDER,
            'action_type' => 9,
            'params' => json_encode(array('sid' => $this->sid)),
            'wid' => $this->wid,
        );
        Admin_Common_Api::addAminLog($info);
	}

	protected function outputPage()
	{
		$response = new Response_Ajax();
		$response->setContent($this->response);
		$response->send();
		exit;
	}
    
    // 新逻辑入口
    private function _oMain()
    {
        // 检查商品是否入库
        if (Warehouse_Api::checkStockinOfInorderProduct($this->oid, $this->sid, $this->source))
        {
            throw new Exception('删除商品已经入库，不能在进行该操作！');
        }
        
        $this->inorderProducts = Warehouse_Api::getOrderProducts($this->oid);
        
        foreach($this->inorderProducts[$this->source] as $p)
        {
            if ($p['sid']==$this->sid)
            {
                $this->delProduct = $p; break;
            }
        }
        if(empty($this->delProduct))
        {
            throw new Exception('删除失败：商品不存在！！');
        }
       
        // 处理操作
        switch ($this->otype)
        {
            case 'show_tmp':
                $this->_showDelTmpProduct();
                break;
            case 'del_tmp_product':
                Warehouse_Api::changeTmpProduct($this->delProduct, $this->inorderProducts, $this->delNum, $this->chgType);
                
                //刷新在途数量
                Warehouse_Security_Stock_Api::updateWaitNumByWidSid($this->wid, array($this->sid));
                
                $this->response = array('st'=>1);
                break;
            default:
                throw new Exception('操作类型未定义！！');
        }
    }
    
    // 显示待删除的临采商品.
    private function _showDelTmpProduct()
    {
        $salesOrders = Warehouse_Api::parseTmpProductOrderInfo($this->delProduct['sales_oids'], $this->delProduct['num']);
        
        if(count($salesOrders) > 1)
        {
            $salesOrdersNum = $salesOrders;
            $salesOrdersNum[0] = $this->delProduct['num'];
        }
        else
        {
            $salesOrdersNum[0] = $this->delProduct['num'];
        }
        
        $this->smarty->assign('product', $this->delProduct);
        $this->smarty->assign('sales_orders', $salesOrders);
        $this->smarty->assign('sales_orders_num', $salesOrdersNum);
        $this->smarty->assign('otype', $this->otype);
        $this->smarty->assign('oid', $this->oid);
        $this->smarty->assign('sid', $this->sid);
        
        $html = $this->smarty->fetch('warehouse/aj_delete_product.html');
        
        $this->response = array('html'=>$html);
    }
    
    
}

$app = new App('pri');
$app->run();

