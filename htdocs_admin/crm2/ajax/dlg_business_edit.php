<?php
include_once('../../../global.php');
class App extends App_Admin_Ajax
{
    private $businessInfo;
    
    protected function checkAuth()
    {
        parent::checkAuth('/crm2/edit_business');
    }
    
    protected function getPara()
    {
        $this->businessInfo = array(
            'bid' => Tool_Input::clean('r', 'bid', TYPE_UINT),
            'bname' => Tool_Input::clean('r', 'bname', TYPE_STR),
            'contract_btime' => Tool_Input::clean('r', 'contract_btime', TYPE_STR),
            'contract_etime' => Tool_Input::clean('r', 'contract_etime', TYPE_STR),
            'discount_ratio' => Tool_Input::clean('r', 'discount_ratio', TYPE_STR),
            'level_for_sys' => Tool_Input::clean('r', 'level_for_sys', TYPE_UINT),
        );
    }
    
    protected function main()
    {
        
    }
    
    protected function outputPage()
    {
        $this->smarty->assign('businessInfo', $this->businessInfo);
        $this->smarty->assign('sys_levels', Conf_User::$Business_Sys_Level_Descs);
        $html = $this->smarty->fetch('crm2/dlg_business_edit.html');
        $result = array('html' => $html);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();