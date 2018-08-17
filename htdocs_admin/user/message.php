<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $total;
	private $list;
	private $start;
	private $num = 20;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
	}

	protected function main()
	{
		$data = Admin_Message_Api::getList(array('receive_suid'=>$this->_uid), $this->start, $this->num);
		$this->total = $data['total'];
		$this->list = $data['list'];
        $mids = Tool_Array::getFields($this->list,'id');
        if(!empty($mids))
        {
            Admin_Message_Api::updateWhere('id in('.implode(',',$mids).')',array('has_read'=>1));
        }
	}

	protected function outputBody()
	{
		$app = '/user/message.php?';
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('list', $this->list);

		$this->smarty->display('user/message.html');
	}
}

$app = new App('pri');
$app->run();