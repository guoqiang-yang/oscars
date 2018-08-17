<?php
include_once ('../../global.php');

class App extends App_Cli
{
	protected function main()
	{
		//$this->_testMc();
		$this->_tmp();
	}

	private function _tmp()
	{
		$sid = 10879;
		$ret = Shop_Api::isVirtual($sid);
		var_dump($ret);
	}

	private function _testMc()
	{
		$_mc = new Memcache();
		$ret = $_mc->connect(MEMCACHE_HOST, MEMCACHE_PORT);
		var_dump($ret);

		$mobile = '13810403104';
		$verifyCode = 9300;
		$ret = $_mc->set('_find_p_vc_' . $mobile, $verifyCode, 0, 300);
		var_dump($ret);
	}

	private function _testDataMc()
	{
		$mobile = '13810403104';
		$verifyCode = 9300;
		$ret = Data_Memcache::getInstance()->set('_find_p_vc_' . $mobile, $verifyCode, 300);
		var_dump($ret);
	}
}

$app = new App();
$app->run();
