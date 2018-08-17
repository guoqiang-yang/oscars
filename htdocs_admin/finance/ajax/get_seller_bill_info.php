<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $bid;
    private $billInfo = array();

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/finance/ajax/seller_bill');
    }

    protected function getPara()
	{
		$this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
	}

	protected function checkPara()
    {
        if (empty($this->bid)) {
            throw new Exception('结算ID为空');
        }
    }

	protected function main()
	{
	    $sellerBillDao = new Finance_Seller_Bill();
		$this->billInfo = $sellerBillDao->getSellerBillInfo($this->bid);
	}

	protected function outputPage()
	{
	    $this->smarty->assign('bill_info', $this->billInfo);
        $this->smarty->assign('payment_list', Conf_Base::$PAYMENT_TYPES);
        $html = $this->smarty->fetch('finance/get_seller_bill_info.html');
		$result = array('html' =>$html );

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();