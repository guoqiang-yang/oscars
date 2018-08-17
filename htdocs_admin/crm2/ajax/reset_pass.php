<?php

/**
 * 重置密码-客服人员使用.
 */

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $uid;
	
	protected function getPara()
	{
		$this->uid = Tool_Input::clean('r', 'uid', TYPE_UINT);
	}
	
	protected function checkPara()
	{
		if (empty($this->uid))
		{
			throw new Exception('apply_coupon: customer id is empty');
		}
	}

	protected function checkAuth()
	{
		parent::checkAuth();
	}

	protected function main()
	{
        Crm2_Auth_Api::resetPassword($this->uid, '123456');

        $params = array(
            'uid' => $this->uid,
        );
        Admin_Api::addActionLog($this->_uid, Conf_Admin_Log::$ACTION_RESET_PASS, $params);
	}
	
	protected function outputBody()
	{
		$result = array('uid'=>$this->uid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App();
$app->run();