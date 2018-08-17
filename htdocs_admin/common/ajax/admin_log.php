<?php

/**
 * 在页面自动加载日志并显示.
 * 
 * @uses 
 *      1 需要显示的页面添加标签
 *          <div id="show_hccommon_admin_log" data-objid="{{$sid}}" data-objtype="5" data-actiontype=""></div>
 *      2 标签中添加数据属性：data-objid, data-objtype
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $pageNum = 20;
    private $start;
    
    private $searchConf;
    
    private $logHtml;
    
    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        
        $this->searchConf = array(
            'obj_id' => Tool_Input::clean('r', 'obj_id', TYPE_UINT),
            'obj_type' => Tool_Input::clean('r', 'obj_type', TYPE_UINT),
            'action_type' => Tool_Input::clean('r', 'action_type', TYPE_UINT),
            'city_id' => Tool_Input::clean('r', 'ctiy_id', TYPE_UINT),
            'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
        );
    }
    
    protected function main()
    {
        $logDatas = Admin_Common_Api::fetchAdminLog($this->searchConf, $this->start, $this->pageNum);
        $total = $logDatas['total'];
        $logs = $logDatas['data'];
            
        $pageHtml = Str_Html::getJsPagehtml2($this->start, $this->pageNum, 
                        $total, 'hccommon_admin_log_pagetruning');
        
		$this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $total);
        $this->smarty->assign('logs', $logs);
        
        $this->logHtml = $this->smarty->fetch('common/aj_admin_log.html');
    }
    
    protected function outputBody()
    {
        $result = array('html' => $this->logHtml);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
    }
    
}

$app = new App('pub');
$app->run();