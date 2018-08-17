<?php
include_once ('../../global.php');

class App extends App_Cli
{

	protected function main()
	{
		$ret = $this->_resetAdminPwd();
		var_dump($ret);
		exit;
	}

	private function _resetPwd()
	{
		$uid = 10074;
		$password = '123456';
		$ret = Crm_Auth_Api::resetPassword($uid, $password);
		return $ret;
	}

	private function _resetAdminPwd()
	{
		$uid = 1004;
		$password = '123456';
		$ret = Admin_Auth_Api::resetPassword($uid, $password);
		return $ret;
	}
}

$app = new App();
$app->run();
