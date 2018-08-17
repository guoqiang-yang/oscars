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
		if (!empty($this->id))
		{
			$this->info = Admin_Task_Api::getDetail($this->id);
		}

		$this->addFootJs(array('js/apps/aftersale.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$this->smarty->assign('info', $this->info);
		$this->smarty->assign('admins', Conf_Aftersale::$RELATION_ADMINS);
		$this->smarty->assign('department_list', Conf_Aftersale::$DEPARTMENT);
		$this->smarty->assign('types', Conf_Admin_Task::$AFTERSALE_TYPE);

		$this->smarty->display('aftersale/edit.html');
	}
}

$app = new App('pri');
$app->run();
