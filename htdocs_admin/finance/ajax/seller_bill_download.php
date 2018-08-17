<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $bid;

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/finance/ajax/seller_bill_download');
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
		$sellerBillDao->exportSellerBillInfo($this->bid);
        exit;
	}
}

$app = new App('pri');
$app->run();