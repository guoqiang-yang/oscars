<?php
include_once('../../../global.php');
/*
 * 获取应收统计某天或者某月销售欠收款统计
 */

class App extends App_Admin_Ajax
{
	private $viewMode;
	private $timeStr;

	private $response = array('errno'=>1);
	

	protected function getPara()
	{
		$this->viewMode = Tool_Input::clean('r', 'view_mode', TYPE_STR);
		$this->timeStr = Tool_Input::clean('r', 'time_str', TYPE_STR);
	}

	protected function checkPara()
	{
		if (empty($this->viewMode) || empty($this->timeStr))
		{
			$this->response['errno'] = 0;
			$this->response['errmsg'] = '参数输入有误';
		}
		if($this->viewMode == 'date' && $this->timeStr == '总计')
        {
            $this->timeStr = array('start_date' => Tool_Input::clean('r', 'start_date', TYPE_STR), 'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR));
        }
	}

	protected function main()
    {
		if ($this->response['errno'])
		{
		    $oc = new Statistics_Receiverables();
            $this->response['list'] = $oc->getSalesAmountByTime($this->viewMode, $this->timeStr);
		}
	}

	protected function outputPage()
	{
		echo json_encode($this->response);
		exit;
	}
}

$app = new App('pri');
$app->run();
