<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    
	protected function getPara()
	{
	}

	protected function main()
	{
        $this->setHeadTmpl('head/head_login.html');
        
		if ($this->_uid)
		{
			$firstPage = Conf_Admin_Page::getFirstPage($this->_uid, $this->_user);
            header('Location: ' . $firstPage);
			exit;
		}
        
		$this->addFootJs(array('js/apps/login.js'));
	}

	protected function outputBody()
	{
		$this->smarty->display('user/login.html');
	}
}

$app = new App('pub');
$app->run();

