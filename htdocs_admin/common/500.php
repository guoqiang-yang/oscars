<?php
include_once ('../../global.php');

class App500 extends App_Admin_Page
{
	protected function main()
	{
		$this->setTitle("系统错误");
	}

	protected function outputBody()
	{
		$errmsg = Conf_Exception::DEFAULT_ERRMSG;
		if ($GLOBALS['t_exception'])
		{
			$ex = $GLOBALS['t_exception'];
			$rawmsg = $ex->getMessage();
			if (isset(Conf_Exception::$exceptions[$rawmsg]))
			{
				list($errno,$errmsg) = Conf_Exception::$exceptions[$rawmsg];
			}
			else
			{
				//$errmsg = Conf_Exception::DEFAULT_ERRMSG;
				//$errno = $ex->getCode();
                $errmsg = $rawmsg;
			}
		}
		$this->smarty->assign('errmsg', $errmsg);
		$this->smarty->display("common/500.html");
	}
}

$app = new App500('pub');
$app->run();

