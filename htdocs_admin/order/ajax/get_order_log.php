<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $list;
	private $html;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

    protected function checkPara()
    {
        if (empty($this->oid))
        {
            throw new Exception('对不起，订单id为空');
        }
    }


    protected function main()
	{
		$searchConf = array('oid' => $this->oid);

		$res = Admin_Api::getOrderActionLogList($searchConf, 0, 0);
		$this->list = $res['list'];
		$this->html = $this->genHtml();
	}

	protected function genHtml()
	{
		$this->smarty->assign('list', $this->list);

		$html = $this->smarty->fetch('admin/block_order_action_log.html');

		return $html;
	}

	protected function outputBody()
	{
		$result = array('html' => $this->html);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pub');
$app->run();