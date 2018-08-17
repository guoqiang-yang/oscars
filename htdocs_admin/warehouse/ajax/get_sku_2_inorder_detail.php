<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $skuId;
    private $search;
    private $orderProductList;
    private $html = '';
    
    protected function getPara()
    {
        $this->skuId = Tool_Input::clean('r', 'sku_id', TYPE_UINT);
        $this->search = array(
            'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
            'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
            'bdate' => Tool_Input::clean('r', 'bdate', TYPE_STR),
            'edate' => Tool_Input::clean('r', 'edate', TYPE_STR),
        );
    }
    
    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/tmp_outsourcer_purchase');
    }
    
    protected function checkPara()
    {
        if (empty($this->skuId) || empty($this->search['wid']) || empty($this->search['bdate']) || empty($this->search['edate']))
        {
            throw new Exception('参数错误！');
        }
    }
    
    protected function main()
    {
        $oo = new Order_Order();
        $kind = 't_order_product force index (primary)';
        $where = sprintf('sid=%d and status=0 and rid=0 and tmp_inorder_id=0 and vnum>tmp_inorder_num+refund_vnum', $this->skuId);
        if(empty($this->search['sid']))
        {
            $where .= ' and outsourcer_id>0';
        }else{
            $where .= ' and outsourcer_id='.$this->search['sid'];
        }

        $where .= sprintf(' and oid in(select oid from t_order where status=0 and wid=%d and ship_time>="%s 00:00:00" and ship_time<="%s 23:59:59")',
            $this->search['wid'], $this->search['bdate'], $this->search['edate']);

        $filed = array('oid','rid','cost','vnum','tmp_inorder_num', 'refund_vnum','picked','damaged_num');
        $_productList = $oo->getByRawWhere($kind, $where, $filed);
        if(!empty($_productList))
        {
            $oids = Tool_Array::getFields($_productList, 'oid');
            $_orderList = Order_Api::getBulk(array_unique($oids));
            foreach ($_productList as $item) {
                $item['ship_time'] = $_orderList[$item['oid']]['ship_time'];
                $this->orderProductList[] = $item;
            }
        }
        $or = new Order_Refund();
        $kind = 't_order_product force index (rid)';
        $where = sprintf('sid=%d and status=0 and tmp_inorder_id=0', $this->skuId);
        if(empty($this->search['sid'])) {
            $where .= ' and outsourcer_id>0';
        }else{
            $where .= ' and outsourcer_id='.$this->search['sid'];
        }
        $where .= sprintf(' and rid>0 and rid in (select rid from t_refund where  wid=%d and status=0 and stockin_time>="%s 00:00:00" and stockin_time<="%s 23:59:59")',
            $this->search['wid'], $this->search['bdate'], $this->search['edate']);
        $_productList = $oo->getByRawWhere($kind, $where, $filed);
        if(!empty($_productList))
        {
            $rids = Tool_Array::getFields($_productList, 'rid');
            $_refundList = $or->getBulk(array_unique($rids));
            foreach ($_productList as $item)
            {
                $item['stockin_time'] = $_refundList[$item['rid']]['stockin_time'];
                $this->orderProductList[] = $item;
            }
        }

        $this->smarty->assign('order_product', $this->orderProductList);

        $this->html = $this->smarty->fetch('warehouse/aj_get_sku_2_inorder_detail.html');
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