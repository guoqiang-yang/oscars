<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $wid;
    private $sid;
    private $bdate;
    private $edate;
    private $plist;
    private $inorderProductList;

    protected function getPara()
    {
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->bdate = Tool_Input::clean('r', 'bdate', TYPE_STR);
        $this->edate = Tool_Input::clean('r', 'edate', TYPE_STR);
        $this->plist = json_decode(Tool_Input::clean('r', 'product_list', TYPE_STR), true);
    }
    
    protected function checkPara()
    {
        if (empty($this->wid))
        {
            throw new Exception('请选择仓库');
        }
        if (!empty($this->_user['wid']) && $this->_user['wid']!=$this->wid)
        {
            throw new Exception('只能创建自己仓库的临采单！');
        }
        if(empty($this->sid))
        {
            throw new Exception('请选择供应商');
        }
        if(empty($this->bdate) || empty($this->edate))
        {
            throw new Exception('请选择配送时间段');
        }
        if($this->bdate >= date('Y-m-d 00:00:00') || $this->edate >= date('Y-m-d 00:00:00'))
        {
            throw new Exception('配送时间只能选择今天之前的日期');
        }
        if (empty($this->plist))
        {
            throw new Exception('商品列表不能为空！');
        }
    }
    
    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/ajax/create_tmp_outsourcer_inorder');
    }
    
    protected function main()
    {
        $supplierInfo = Warehouse_Api::getSupplier($this->sid);
        if (empty($supplierInfo))
        {
            throw new Exception('该临采供应商账号不存在或者已下线，请联系技术人员！');
        }
        $orderInfo = array(
            'sid' => $this->sid,
            'contact_name' => $supplierInfo['contact_name'],
            'contact_phone' => $supplierInfo['phone'],
            'delivery_date' => $this->edate,
            'freight' => 0,
            'privilege' => 0,
            'privilege_note' => '',
            'note' => '',
            'payment_type' => Conf_Stock::PAYMENT_CASH,
            'wid' => $this->wid,
            'buyer_uid' => $this->_uid,
            'step' => Conf_In_Order::ORDER_STEP_SURE,
            'source' => Conf_In_Order::SRC_OUTSOURCER,
        );

        if (Conf_Base::switchForManagingMode())
        {
            $orderInfo['managing_mode'] = $supplierInfo['managing_mode'];
        }
        $_num = 0;
        foreach ($this->plist as $_p)
        {
            if($_p['amount'] <0)
            {
                throw new Exception('sid:'.$_p['sid'].'的金额为负');
            }
            if($_p['num'] < 0)
            {
                throw new  Exception('sid:'.$_p['sid'].' 的数量为负数');
            }elseif($_p['num'] > 0)
            {
                $_num++;
            }
            $this->inorderProductList[] = array(
                'sid'=> $_p['sid'],
                'source' => Conf_In_Order::SRC_OUTSOURCER,
                'price' => $_p['cost'],
                'num' => $_p['num'],
            );
        }
        if($_num == 0)
        {
            throw new Exception('请选择商品，或者最少含有一个数量大于0的商品');
        }

        $this->oid = Warehouse_Api::addOrder($this->sid, $orderInfo, $this->inorderProductList);
        if($this->oid)
        {
            $queue = new Data_Queue();
            $queue->enqueue(Queue_Base::Queue_Type_OutSourcer, array('type'=>'add','sid' => $this->sid, 'oid' => $this->oid, 'wid' => $this->wid, 'bdate' => $this->bdate, 'edate' => $this->edate, 'sids' => Tool_Array::getFields($this->inorderProductList,'sid')));
        }
    }
    
    protected function outputBody()
    {
        $result = array('oid' => $this->oid);
        
		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();