<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $rid;
	private $refund;
	protected $headTmpl = 'head/head_none.html';
	protected $tailTmpl = 'tail/tail_none.html';
    private $productPrivilege = 0;

	protected function getPara()
	{
		$this->rid = Tool_Input::clean('r', 'rid', TYPE_UINT);
	}

	protected function main()
	{
		$this->refund = Order_Api::getRefund($this->rid);
        foreach ($this->refund['products'] as $item)
        {
            $this->productPrivilege += ($item['ori_price'] - $item['price']) * $item['num'];
        }
		$this->removeJs();
		$this->addFootJs(array());
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$price = Refund_Api::calRefundPrice($this->refund['info']);
		$chineseTotal = Str_Chinese::getChineseNum($price/100);

		$this->smarty->assign('refund', $this->refund);
		$this->smarty->assign('chineseTotal', $chineseTotal);
		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
        $this->smarty->assign('price', $price);
        $this->smarty->assign('product_privilege', $this->productPrivilege);

		$this->smarty->display('order/refund_print.html');
	}
}

$app = new App('pri');
$app->run();
