<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $id;
    private $num;
	private $invoiceInfo;

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/finance/edit_input_invoice');
    }

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->num = Tool_Input::clean('r', 'invoice_num', TYPE_UINT);
		$this->invoiceInfo = array(
		    'supplier_id' => Tool_Input::clean('r', 'supplier_id', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
		    'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'amount' => Tool_Input::clean('r', 'amount', TYPE_UINT),
            'invoice_day' => Tool_Input::clean('r', 'invoice_day', TYPE_STR),
            'batch' => Tool_Input::clean('r', 'batch', TYPE_STR),
            'number' => Tool_Input::clean('r', 'number', TYPE_STR),
            'bill_ids' => Tool_Input::clean('r', 'bill_ids', TYPE_ARRAY),
            'invoice_type' => Tool_Input::clean('r', 'invoice_type', TYPE_UINT),
        );
	}

	protected function checkPara()
	{
        if (empty($this->invoiceInfo['supplier_id']))
        {
            throw new Exception('供应商ID不能为空');
        }
        if ($this->invoiceInfo['name'] == '')
        {
            throw new Exception('开票供应商不能为空');
        }

        if (empty($this->invoiceInfo['city_id']))
        {
            throw new Exception('请选择城市');
        }

        if (empty($this->invoiceInfo['invoice_type']))
        {
            throw new Exception('请选择开票类型');
        }

        if ($this->invoiceInfo['title'] == '')
        {
            throw new Exception('开票名称不能为空');
        }

        if (empty($this->invoiceInfo['amount']))
        {
            throw new Exception('开票金额不能为空');
        }

        if ($this->invoiceInfo['invoice_day'] == '')
        {
            throw new Exception('开票日期不能为空');
        }

        if ($this->invoiceInfo['batch'] == '')
        {
            throw new Exception('批次不能为空');
        }

        if ($this->invoiceInfo['number'] == '')
        {
            throw new Exception('票号不能为空');
        }

        $this->invoiceInfo['bill_ids'] = implode(',', $this->invoiceInfo['bill_ids']);
	}

	protected function main()
	{
        
		if (empty($this->id))   //新建商品
		{
		    for($i=0;$i<$this->num;$i++)
            {
                $this->invoiceInfo['create_suid'] = $this->_uid;
                $this->id = Invoice_Api::addInputInvoice($this->invoiceInfo['supplier_id'], $this->invoiceInfo);
            }
		}
		else    //编辑商品
		{
            $isBuyer = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_BUYER_NEW);
			Invoice_Api::updateInputInvoice($this->id, $this->invoiceInfo, $isBuyer);
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

