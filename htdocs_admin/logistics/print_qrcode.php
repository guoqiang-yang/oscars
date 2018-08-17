<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $checkInUrl;
	protected $headTmpl = 'head/head_none.html';
	protected $tailTmpl = 'tail/tail_none.html';

	protected function main()
	{
		$wid = $this->_user['wid'];
		$this->checkInUrl = 'http://' . COOPWORDER_H5_HOST . '/user/check_in.php?rand=' . strtotime('today') . '&wid=' . $wid;
		$this->checkInUrl = urlencode($this->checkInUrl);
	}

	protected function outputBody()
	{
		$this->smarty->assign('url', $this->checkInUrl);

		$this->smarty->display('logistics/print_qrcode.html');
	}
}

$app = new App('pri');
$app->run();