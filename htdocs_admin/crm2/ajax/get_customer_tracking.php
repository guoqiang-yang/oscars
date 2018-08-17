<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $pageNum = 20;
    
    private $cid;
    private $start;
    
    private $trackingList = array();
    private $trackingHtml = '';
    
    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        
    }
    
    protected function main()
    {
        $searchConf = array('cid'=>$this->cid);
        $this->trackingList = Crm2_Api::getCustomerTrackingList($searchConf, $this->start, $this->pageNum);
        
        
		$pageHtml = Str_Html::getJsPagehtml2($this->start, $this->pageNum, 
                        $this->trackingList['total'], '_j_pagetruning_tracking');
        
		$this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->trackingList['total']);
        $this->smarty->assign('tracking_list', $this->trackingList['data']);
        $this->smarty->assign('tracking_types', Conf_User::$Customer_Tracking_Types);
        
        $this->trackingHtml = $this->smarty->fetch('crm2/block/customer_tracking_list.html');
    }
    
    protected function outputBody()
    {
        $result = array('html' => $this->trackingHtml);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
    }
}

$app = new App();
$app->run();