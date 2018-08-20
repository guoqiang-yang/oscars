<?php
class App_Admin_Ajax extends App_Admin_Web
{
	protected $allowedMethods = array('GET', 'POST');

	protected function checkAuth($permission = '')
	{
		if (!in_array($_SERVER["REQUEST_METHOD"], $this->allowedMethods))
		{
			header("HTTP/1.1 405 Method Not Allowed");
			header("Allow: ".implode($this->allowedMethods, ", "));
			exit;
		}

		parent::checkAuth();

        if ($this->lgmode == 'pri')
        {
            $forbidden = parent::checkPermission($permission);
            if ($forbidden)
            {
                throw new Exception('您无权执行该操作！');
            }
        }
	}

	protected function outputPage()
	{
		$this->setCommonPara();
		$this->outputBody();
	}

	protected function showError($ex)
	{
		$rawmsg = $ex->getMessage();
		if (isset(Conf_Exception::$exceptions[$rawmsg]))
		{
			list($errno,$errmsg) = Conf_Exception::$exceptions[$rawmsg];
		}
		else
		{
			$errno = $ex->getCode();
			$errmsg = $rawmsg;
		}

		$response = new Response_Ajax();
		$errorArr = array(
			"errmsg" => $errmsg,
			"errno" => $errno,
		);
		if (defined('DEBUG_MODE') && DEBUG_MODE)
		{
			$errorArr["trace"] = $rawmsg . "\n" . var_export($ex->getTrace(), true);
		}
		$response->setError($errorArr);
		$response->send();
		Tool_Log::debug('@admin_ajax', Tool_Log::genSimpleLog4Exception($ex));
	}

	public function goToErrorFast()
	{
		$response = new Response_Ajax();
		$response->seeOther("/common/error_fast.php");
		$response->send();
		exit;
	}
}
