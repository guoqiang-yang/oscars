<?php

include_once('../../../global.php');
/**
 * 更新企业高级编辑信息
 */
class App extends App_Admin_Ajax
{
    private $bid;
    private $businessInfo;
    
    protected function checkAuth()
    {
        parent::checkAuth('/crm2/edit_business');
    }
    
    protected function getPara()
    {
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
        
        $this->businessInfo = array(
            'contract_btime' => Tool_Input::clean('r', 'contract_btime', TYPE_STR),
            'contract_etime' => Tool_Input::clean('r', 'contract_etime', TYPE_STR),
            'discount_ratio' => Tool_Input::clean('r', 'discount_ratio', TYPE_INT),
            'level_for_sys' => Tool_Input::clean('r', 'level_for_sys', TYPE_UINT),
            'payment_days' => Tool_Input::clean('r', 'payment_days', TYPE_INT),
            'payment_amount' => Tool_Input::clean('r', 'payment_amount', TYPE_STR),
            'has_duty' => Tool_Input::clean('r', 'has_duty', TYPE_INT),
        );
    }
    
    protected function checkPara()
    {
        if (empty($this->businessInfo['level_for_sys'])){
            throw new Exception('请选择企业类别！');
        }
    }
    
    protected function main()
    {
        $this->bid = Business_Api::saveBusinessInfo($this->bid, $this->businessInfo);
        if ($this->bid){
            Business_Api::updateBusinessData($this->bid, $this->businessInfo);
        }
    }
    
    protected function outputBody()
    {
        $res = array('bid' => $this->bid);
        $response = new Response_Ajax();
        $response->setContent($res);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();