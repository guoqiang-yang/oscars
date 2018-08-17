<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $id;
	private $statementInfo;
    private $supplierInfo;
    private $bankInfo;
	protected $headTmpl = 'head/head_none.html';
	protected $tailTmpl = 'tail/tail_none.html';

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
	}

	protected function checkPara()
    {
        if(empty($this->id))
        {
            throw new Exception('common:params error');
        }
    }

    protected function main()
	{
		$this->statementInfo = Finance_StockIn_Statements_Api::getStatementDetail($this->id);
        if(empty($this->statementInfo) || $this->statementInfo['status'] == Conf_Base::STATUS_DELETED)
        {
            throw new Exception('该结算单已被删除');
        }
        $this->supplierInfo = Warehouse_Api::getSupplier($this->statementInfo['supplier_id']);
        $this->statementInfo['_amount'] = Str_Chinese::getChineseNum($this->statementInfo['amount'] / 100);
        $this->statementInfo['amount'] = number_format($this->statementInfo['amount']/100,2);
        $this->bank_info = explode('-', $this->supplierInfo['bank_info']);
		$this->removeJs();
		$this->addFootJs(array());
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$this->smarty->assign('statement_info', $this->statementInfo);
        $this->smarty->assign('supplier_info', $this->supplierInfo);
        $this->smarty->assign('bank_info', $this->bank_info);
        $this->smarty->assign('today_date', date('Y年m月d日'));
		$this->smarty->display('finance/stockin_statement_print.html');
	}
}

$app = new App('pri');
$app->run();
