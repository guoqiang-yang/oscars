<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cid;
    private $communityId;
    private $response;

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->communityId = Tool_Input::clean('r', 'community_id', TYPE_UINT);
    }

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/logistics/ajax/save_order_line');
    }

    protected function main()
    {
        $rids = Refund_Api::getRefundForSameCustomer($this->cid, $this->communityId);
        $rids = implode(',', $rids);
        $oids = Exchanged_Api::getExchangedOrdersForSameCustomer($this->cid, $this->communityId);
        $oids = implode(',', $oids);
        $oids2 = Traps_Api::getTrapsOrdersForSameCustomer($this->cid, $this->communityId);
        $oids2 = implode(',', $oids2);
        $this->response = array(
            'st' => 1,
            'rids' => $rids,
            'oids' => $oids,
            'oids2' => $oids2,
        );
    }

    protected function outputBody()
    {
		$response = new Response_Ajax();
		$response->setContent($this->response);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();