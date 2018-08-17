<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cid;
    private $backUrl;
    private $customer;
    private $ajResponse;


    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->backUrl = Tool_Input::clean('r', 'url', TYPE_STR);
        $this->customer = array(
            'payment_days' => Tool_Input::clean('r', 'payment_days', TYPE_UINT),
            'payment_amount' => Tool_Input::clean('r', 'payment_amount', TYPE_UINT),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'level_for_sys' => Tool_Input::clean('r', 'level_for_sys', TYPE_UINT),
            'level_for_saler' => Tool_Input::clean('r', 'level_for_saler', TYPE_UINT),
            'contract_btime' => Tool_Input::clean('r', 'contract_btime', TYPE_STR),
            'contract_etime' => Tool_Input::clean('r', 'contract_etime', TYPE_STR),
            'has_duty' => Tool_Input::clean('r', 'has_duty', TYPE_UINT),
            'discount_ratio' => Tool_Input::clean('r', 'discount_ratio', TYPE_UINT),
        );
        
        $this->customer['contract_btime'] = !empty($this->customer['contract_btime'])? $this->customer['contract_btime']: '0000-00-00';
        $this->customer['contract_etime'] = !empty($this->customer['contract_etime'])? $this->customer['contract_etime']: '0000-00-00';
        if($this->customer['level_for_saler'] < Conf_User::SALER_LEVEL_CASH_CONTRACT)
        {
            $this->customer['contract_btime'] = '0000-00-00';
            $this->customer['contract_etime'] = '0000-00-00';
        }
        
        $this->ajResponse = array(
            'st' => 0,
            'msg' => '',
        );
    }

    protected function checkPara()
    {
        if (empty($this->cid))
        {
            $this->ajResponse['st'] = 1;
            $this->ajResponse['msg'] = '无效用户CID';
            return;
        }
        if(!empty($this->customer['discount_ratio']) && ($this->customer['discount_ratio'] < 90 || $this->customer['discount_ratio'] >= 100))
        {
            $this->ajResponse['st'] = 15;
            $this->ajResponse['msg'] = '折扣比例只能在0,90<=x<100之间';
            return;
        }
    }

    protected function main()
    {
        if ($this->ajResponse['st'] != 0)
        {
            return;
        }

        $customerInfo = Crm2_Api::getCustomerInfo($this->cid, FALSE, FALSE);

        // 修改了客户状态，记录日志
        if ($this->customer['status'] != $customerInfo['customer']['status'])
        {
            $allSt = Conf_Base::getCustomerStatus();
            $actlog['customer_info'] = $customerInfo['customer']['name'] . '#' . $customerInfo['customer']['cid'];
            $actlog['fr_status'] = array_key_exists($customerInfo['customer']['status'], $allSt) ? $allSt[$customerInfo['customer']['status']] : '暂无';
            $actlog['fr_status'] .= '#' . $customerInfo['customer']['status'];
            $actlog['to_status'] .= array_key_exists($this->customer['status'], $allSt) ? $allSt[$this->customer['status']] : '暂无';
            $actlog['to_status'] .= '#' . $this->customer['status'];

            Admin_Api::addActionLog($this->_uid, Conf_Admin_Log::$ACTION_CHG_CUSTOMER_STATUS, $actlog);
        }

        Crm2_Api::updateCustomerInfo($this->cid, $this->customer);
    }

    protected function outputPage()
    {
        $this->ajResponse['cid'] = $this->cid;
        $this->ajResponse['url'] = $this->backUrl;

        $response = new Response_Ajax();
        $response->setContent($this->ajResponse);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();

