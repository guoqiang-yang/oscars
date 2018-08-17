<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $pid;
	private $sid;
	private $location;
	private $num;
	private $note;

	protected function getPara()
	{
		$this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
		$this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
		$this->location = Tool_Input::clean('r', 'location', TYPE_STR);
		$this->note = Tool_Input::clean('r', 'note', TYPE_STR);
        $this->num = Tool_Input::clean('r', 'num', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->note))
        {
            throw new Exception('参数错误！');
        }
    }

    protected function main()
	{
        Warehouse_Api::updateDiffProductNum($this->pid, $this->sid, $this->location, $this->num, $this->note);
	}

	protected function outputPage()
	{
		$result = array('pid' => $this->pid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();

