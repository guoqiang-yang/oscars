<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $orderProductList;
    private $html = '';
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
    }
    
    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/detail_in_order');
    }
    
    protected function checkPara()
    {
        if (empty($this->oid) || empty($this->sid))
        {
            throw new Exception('参数错误！');
        }
    }
    
    protected function main()
    {
        //
        $op = new Data_Dao('t_order_product');
        $this->orderProductList = $op->limit(0,0)->getListWhere(array('status'=>Conf_Base::STATUS_NORMAL,'tmp_inorder_id'=>$this->oid,'sid'=>$this->sid));
        if(!empty($this->orderProductList))
        {
            $oids = Tool_Array::getFields($this->orderProductList, 'oid');
            $orderList = Order_Api::getBulk(array_unique($oids));
            $rids = Tool_Array::getFields($this->orderProductList, 'rid');
            $rids = array_filter(array_unique($rids));
            if(!empty($rids))
            {
                $refundList = Refund_Api::getRefundByRids($rids);
            }else{
                $refundList = array();
            }
            foreach ($this->orderProductList as &$item)
            {
                $item['stockin_time'] = isset($refundList[$item['rid']]) ? $refundList[$item['rid']]['stockin_time'] : '';
                $item['ship_time'] = $orderList[$item['oid']]['ship_time'];
            }
        }
        $this->smarty->assign('order_product', $this->orderProductList);

        $this->html = $this->smarty->fetch('warehouse/aj_get_sku_inorder_detail.html');
    }
    
    protected function outputBody()
    {
        $st = !empty($this->html)? 1 : 0;
        $result = array('st'=>$st, 'html'=>  $this->html);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();