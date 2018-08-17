<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $ids;
    private $statementId;
    private $statementInfo = array();
    private $payment;
	protected $headTmpl = 'head/head_none.html';
	protected $tailTmpl = 'tail/tail_none.html';
    
    private $totalPrice = 0;
    private $orderList;
    private $workerInfo;
    private $staffList;

    protected function getPara()
    {
        $ids = Tool_Input::clean('r', 'ids', TYPE_STR);
        $this->ids = explode(',', $ids);
        $this->payment = Tool_Input::clean('r', 'payment', TYPE_INT);
        $this->statementId = Tool_Input::clean('r', 'statement_id', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (empty($this->ids))
        {
            throw  new Exception('没有可打印内容！');
        }
    }
    
    protected function main()
    {
        if ($this->statementId)
        {
            $statementInfo = Logistics_Coopworker_Api::getStatementDetail($this->statementId);
            $this->ids = array_unique(Tool_Array::getFields($statementInfo, 'id'));
            
            $lcs = new Logistics_Coopworker_Statement();
            $this->statementInfo = $lcs->getById($this->statementId);
        }

        $lc = new Logistics_Coopworker();
        
        $this->orderList = $lc->getByIds($this->ids);
        
        $cuids = array_unique(Tool_Array::getFields($this->orderList, 'cuid'));
        
        if (count($cuids) != 1)
        {
            throw new Exception(count($cuids)==0?'打印内容为空！': '订单不属于同一个客户，不能打印！');
        }
        
        // 工人信息
        if ($this->orderList[0]['user_type'] == Conf_Base::COOPWORKER_DRIVER)   //司机
        {
            $ld = new Logistics_Driver();
            $this->workerInfo = $ld->get($this->orderList[0]['cuid']);
        } else  // 搬运工
        {
            $lc = new Logistics_Carrier();
            $this->workerInfo = $lc->get($this->orderList[0]['cuid']);
        }
        
        // 订单信息
        $oo = new Order_Order();
        $oo->appendInfos($this->orderList, 'oid');
        
        foreach($this->orderList as $order)
        {
            $this->totalPrice += $order['price'];
            
            //$order['_order']['delivery_date'] = date('Y-m-d', strtotime($order['_order']['delivery_date']));
        }
        $staffs = Admin_Api::getStaffList();
        $this->staffList = Tool_Array::list2Map($staffs['list'], 'suid', 'name');
    }
    
    protected function outputBody()
    {
        if ($this->statementId)
        {
            $this->smarty->assign('statement_id', $this->statementId);
            $this->smarty->assign('statement_info', $this->statementInfo);
        }
        $this->smarty->assign('order_list', $this->orderList);
        $this->smarty->assign('worker_info', $this->workerInfo);
        $this->smarty->assign('fee_types', Conf_Base::getCoopworkerFeeTypes());
        
		$chineseTotal = Str_Chinese::getChineseNum($this->totalPrice / 100);
        $this->smarty->assign('chinese_total', $chineseTotal);
        $this->smarty->assign('total_price', $this->totalPrice);
        $this->smarty->assign('pay_data', date('Y年m月d日'));
        $this->smarty->assign('staff_list', $this->staffList);
        $this->smarty->assign('payment_name', Conf_Base::$PAYMENT_TYPES[$this->payment]?:'暂无');
        
        $this->smarty->display('order/coopworker_order_print.html');
    }
}

$app = new App();
$app->run();