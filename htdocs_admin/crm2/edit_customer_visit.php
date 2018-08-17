<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $id;
    private $cid;
    private $scheduleId;
    private $info;
    private $visit_time;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->scheduleId = Tool_Input::clean('r', 'schedule_id', TYPE_UINT);
    }

	protected function checkPara()
    {
        if (empty($this->id) && empty($this->cid))
        {
            throw new Exception('common: param error');
        }
    }

    protected function main()
	{
	    if($this->id){
            $this->info = Crm2_Customer_Visit_Api::get($this->id);
        }
        if($this->info)
        {
            $this->cid = $this->info['cid'];
            if($this->info['visit_time'] < date('Y-m-d H:i', strtotime('-7 day')))
            {
                header('Location: /crm2/customer_visit_list.php');
                exit;
            }
            $this->visit_time = $this->info['visit_time'];
        }else{
            $this->visit_time = date("Y-m-d H:i:s", time());
        }
		$this->addFootJs(array(
//            'js/core/jquery.gridly.js',
		    'js/apps/customer_visit.js',
            'js/core/FileUploader.js',
            'js/core/imgareaselect.min.js',
            'js/apps/upload_visit_pic.js',));
        $this->addCss(array(
            'css/imgareaselect-default.css',
//            'css/jquery.gridly.css',
        ));
	}

	protected function outputBody()
	{
	    $this->smarty->assign('cid', $this->cid);
        $this->smarty->assign('info', $this->info);
        $visit_types = Conf_Crm::getTypeList();
        if(empty($this->id) || $this->info['visit_type'] != Conf_Crm::VISIT_TYPE_SCENE){
            unset($visit_types[Conf_Crm::VISIT_TYPE_SCENE]);
        }
        $this->smarty->assign('visit_types', $visit_types);
        $this->smarty->assign('schedule_id', $this->scheduleId);
        $this->smarty->assign('visit_time', $this->visit_time);
        $this->smarty->display('crm2/edit_customer_visit.html');
	}
}

$app = new App('pri');
$app->run();
