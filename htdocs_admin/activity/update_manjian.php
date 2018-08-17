<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $id;
	private $info;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
	}

	protected function main()
	{
		if (0 != $this->id)
		{
			$this->info = Activity_Api::getManjianItem($this->id);
		}

		$this->addFootJs(array('js/apps/manjian.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$this->smarty->assign('info', $this->info);

		$this->smarty->display('activity/update_manjian.html');
	}
}

$app = new App('pri');
$app->run();
