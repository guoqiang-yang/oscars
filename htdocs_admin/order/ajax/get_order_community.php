<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
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
        $order = Order_Api::getOrderInfo($this->oid);
        $cs = new Crm2_Construction();
        $where = sprintf("cid=%d and city=%d and community_id>0", $order['cid'], $order['city_id']);
        $communityList = $cs->getListByWhere($where,array('*'),0,20);
        foreach ($communityList as $key => $item)
        {
            $addresses = explode(Conf_Area::Separator_Construction, $item['address'], 2);
            if (count($addresses) == 2)
            {
                $communityList[$key]['community_name'] = $addresses[0];
                $communityList[$key]['address'] = $addresses[1];
            }
        }
        $this->smarty->assign('community_list', $communityList);
		$this->html = $this->genHtml();
	}

	protected function genHtml()
	{
		$html = $this->smarty->fetch('order/block_show_community.html');

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