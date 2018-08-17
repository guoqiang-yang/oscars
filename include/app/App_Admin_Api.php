<?php

/**
 * htdocs_admin_api 使用.
 *
 */
class App_Admin_Api extends Base_App
{
	private $responseData;

	function __construct()
	{
		// 设置cityid 给默认值北京
		City_Api::setCity(Conf_City::BEIJING);

        //暂且关闭日志 addby guoqiang/2018-02-24
		//$this->printLog();
	}

	private function printLog()
	{
		$info = sprintf("\n-------------api: %s--------------\n", $_SERVER['REQUEST_URI']);
		$info .= "params = " . var_export($_REQUEST, TRUE) . "\n";
		Tool_Log::addFileLog('admin_api_call', $info);
	}

	protected function outputHttp()
	{
		header("Content-Type: application/json; charset=" . SYS_CHARSET);
		header("Cache-Control: no-cache");
		header("Pragma: no-cache");
	}

    protected function checkAuth()
    {
        if ($_SERVER['SCRIPT_URL'] != '/common/login.php' && isset($_REQUEST['version']) && $_REQUEST['version'] >= '1.2.0')
        {
            $verify = Tool_Input::clean('r', 'token', TYPE_STR);

            $this->_uid = Admin_Auth_Api::checkVerify($verify, Conf_Base::APP_TOKEN_EXPIRED, false);

            if (empty($this->_uid))
            {
                $response = new stdClass();
                $response->errno = 7;
                $response->errmsg = '登录信息已过期';
                $response->data = $this->responseData;

                $hResponse = new Response_Ajax();

                echo $hResponse->safeJSONEncode($response);

                exit;
            }
        }

    }

	protected function outputHead()
	{

	}

	protected function setResult($data)
	{
		$this->responseData = $data;
	}

	protected function outputPage()
	{
		// response
		$response = new stdClass();
		$response->errno = 0;
		$response->errmsg = '成功';
		$response->data = $this->responseData;

		$hResponse = new Response_Ajax();

		echo $hResponse->safeJSONEncode($response);

		exit;
	}

	protected function showError($ex)
	{
		$response = new stdClass();

		$msg = $ex->getMessage();
		if (array_key_exists($msg, Conf_Exception::$exceptions))
		{
			list($response->errno, $response->errmsg) = Conf_Exception::$exceptions[$msg];
		}
		else
		{
			$response->errno = Conf_Exception::DEFAULT_ERRNO;
			$response->errmsg = $msg;
		}

		$hResponse = new Response_Ajax();

		echo $hResponse->safeJSONEncode($response);
//		$content = "code:".$ex->getCode()."\nerror:" . $ex->getMessage() . "\n" . var_export($ex->getTrace(),true);
//		Tool_Log::addFileLog("exception_appapi" , $content, true);
		exit;
	}
}