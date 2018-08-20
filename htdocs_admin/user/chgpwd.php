<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
	protected function getPara()
	{
	}

	protected function main()
	{
		$this->addFootJs(array('js/apps/user.js'));
	}

	protected function outputBody()
	{
	    $this->smarty->assign('pinyin', $this->_user['pinyin']);
		$this->smarty->display('user/chgpwd.html');
	}
}

$app = new App('pri');
$app->run();

