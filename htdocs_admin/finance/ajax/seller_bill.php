<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $bid;
    private $realAmount;
    private $paymentType;
    private $note;

	protected function getPara()
	{
		$this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
        $this->realAmount = Tool_Input::clean('r', 'real_amount', TYPE_UINT);
        $this->paymentType = Tool_Input::clean('r', 'payment_type', TYPE_UINT);
		$this->note = Tool_Input::clean('r', 'note', TYPE_STR);
	}

	protected function checkPara()
    {
        if (empty($this->bid))
        {
            throw new Exception('结算ID为空');
        }
        if(!in_array($this->paymentType, array_keys(Conf_Finance::$MONEY_OUT_PAID_TYPES)))
        {
            throw new Exception('支付方式错误');
        }
    }

	protected function main()
	{
		$sellerBillDao = new Finance_Seller_Bill();
        $info = array(
            'real_amount' => $this->realAmount,
            'payment_type' => $this->paymentType,
            'pay_time' => date('Y-m-d H:i:s'),
            'step' => 2,
            'suid' => $this->_uid,
            'note' => $this->note,
        );
        $ret = $sellerBillDao->updateSellerBill($this->bid,$info);
	}

	protected function outputPage()
	{
		$result = array('bid' => $this->bid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();