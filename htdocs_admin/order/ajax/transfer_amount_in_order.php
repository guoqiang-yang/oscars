<?php

/**
 * 订单中金额转余额.
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $oid;
    private $otype;
    private $note;
    
    private $financeDetail;
    private $canTransFee = 0;
    
    private $response;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->otype = Tool_Input::clean('r', 'otype', TYPE_STR);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
        
        $this->response = array(
            'errno' => 0,
            'errmsg' => '',
            'data' => array(),
        );
    }
    
    
    protected function main()
    {
        $this->financeDetail = Finance_Api::calFinanceDetailByOrder($this->oid);
        
        $this->_setStatus();
        
        switch ($this->otype)
        {
            case 'show':
                $this->_show();
                break;
            case 'trans':
                $this->_trans();
                break;
            default:
                break;
        }
    }
    
    protected function outputBody()
    {
		$response = new Response_Ajax();
		$response->setContent($this->response);
		$response->send();

		exit;
    }
    
    private function _show()
    {
        $this->smarty->assign('details', $this->financeDetail['detail']);
        $this->smarty->assign('res_msg', $this->response['errmsg']);
        $this->smarty->assign('can_trans_fee', $this->canTransFee);
        
        $this->smarty->assign('notice', '');
        
        $this->response['data']['html'] = $this->smarty->fetch('order/aj_transfer_amount_in_order.html');
    }
    
    private function _trans()
    {
        if ($this->response['errno']!=0) return;
        
        Finance_Api::trans2AmountFromOrder($this->oid, abs($this->canTransFee), $this->note, $this->_uid);
    }


    private function _setStatus()
    {
        $this->canTransFee = array_sum($this->financeDetail['detail']);
        
//        $this->canTransFee = $this->financeDetail['detail']['can_trans_fee'];
//        
//        unset($this->financeDetail['detail']['can_trans_fee']);
        
        if (Order_Helper::isFranchiseeOrder($this->financeDetail['bill_step']['order']['wid'], $this->financeDetail['bill_step']['order']['city_id']))
        {
            $this->response['errno'] = 15;
            $this->response['errmsg'] = '加盟商订单，不能转余额';
            return;
        }
        
        $beginData = '2017-01-01';
        $deliveryData = substr($this->financeDetail['bill_step']['order']['delivery_date'], 0, 10);
        if ($deliveryData < $beginData)
        {
            $this->response['errno'] = 10;
            $this->response['errmsg'] = $beginData.' 之前的订单不能再转余额！';
            return;
        }
        
        if ($this->financeDetail['bill_step']['order']['step'] < Conf_Order::ORDER_STEP_PICKED)
        {
            $this->response['errno'] = 11;
            $this->response['errmsg'] = '订单未出库，不能转余额！';
            return;
        }
        
        if ($this->financeDetail['bill_step']['order']['paid'] != Conf_Order::HAD_PAID)
        {
            $this->response['errno'] = 14;
            $this->response['errmsg'] = '订单未完全收款，不能转余额！';
            return;
        }
        
        foreach($this->financeDetail['bill_step']['refund'] as $_rid => $_paid)
        {
            if ($_paid != Conf_Refund::HAD_PAID)
            {
                $this->response['errno'] = 12;
                $this->response['errmsg'] = '退货单'.$_rid.'没有财务结款，不能转余额！';
                return;
            }
        }
        
        if ($this->canTransFee >= 0)
        {
            $this->response['errno'] = 13;
            $this->response['errmsg'] = '订单累计金额为：'.($this->canTransFee/100).'元，不可转余额！';
            return;
        }
    }
}

$app = new App();
$app->run();