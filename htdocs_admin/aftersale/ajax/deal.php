<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/7/13
 * Time: 16:35
 */
include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $id;
	private $execResult;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
		$this->execResult = Tool_Input::clean('r', 'exec_result', TYPE_STR);
	}

	protected function checkAuth()
    {
        parent::checkAuth('/aftersale/deal');
    }

	protected function main()
	{
		if (!empty($this->id))
		{
			$afterSale = Aftersale_Api::getDetail($this->id);
			$info = array(
				'exec_suid' => $afterSale['create_suid'],
				'exec_result' => $this->execResult,
				'exec_status' => Conf_Aftersale::STATUS_DEAL,
                'join_suids' => $afterSale['join_suids']
			);

			Aftersale_Api::update($this->id, $info);
		}
	}

	protected function outputPage()
	{
		$result = array('id' => $this->id);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();