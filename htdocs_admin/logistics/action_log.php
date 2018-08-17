<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $start;
	private $searchConf;
	private $num = 20;
	private $total;
	private $list;
	
	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->searchConf = array(
			'action_type' => Tool_Input::clean('r', 'action_type', TYPE_UINT),
			'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'cuid' => Tool_Input::clean('r', 'cuid', TYPE_UINT),
            'line_id' => Tool_Input::clean('r', 'line_id', TYPE_UINT),
		);
	}

	protected function main()
	{
        $paramEmpty = true;
        foreach($this->searchConf as $p)
        {
            if (!empty($p))
            {
                $paramEmpty = false;
                break;
            }
        }
        
        if ($paramEmpty)
        {
            $this->list = array();
            return;
        }
        
		$res = Logistics_Api::getActionLogList($this->searchConf, $this->start, $this->num);
		$this->list = $res['list'];
		$this->total = $res['total'];
        
	}

	protected function outputBody()
	{
		$app = '/logistics/action_log.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
        
		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('searchConf', $this->searchConf);
		$this->smarty->assign('list', $this->list);
		$this->smarty->assign('action_type_list', Conf_Logistics_Action_Log::$ACTION_TYPE);
		$this->smarty->assign('total', $this->total);

		$this->smarty->display('logistics/action_log.html');
	}
}

$app = new App('pri');
$app->run();

