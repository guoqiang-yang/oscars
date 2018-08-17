<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $id;
	private $invoiceInfo;

    protected function checkAuth($permission = '')
    {
        $id = isset($_REQUEST['id'])? $_REQUEST['id']: '';
        if($id>0){
            $info = Invoice_Api::getOutputInvoiceInfo($id);
            if($info['step'] > Conf_Invoice::INVOICE_OUTPUT_STEP_SALES_AUDIT)
            {
                parent::checkAuth('/finance/edit_input_invoice');
            }else{
                parent::checkAuth('/crm2/edit_invoice');
            }
        }else{
            parent::checkAuth('/crm2/edit_invoice');
        }
    }

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
		$this->invoiceInfo = array(
		    'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'contract_number' => Tool_Input::clean('r', 'contract_number', TYPE_STR),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'invoice_type' => Tool_Input::clean('r', 'invoice_type', TYPE_UINT),
		    'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'pay_company' => Tool_Input::clean('r', 'pay_company', TYPE_STR),
            'invoice_amount' => Tool_Input::clean('r', 'invoice_amount', TYPE_UINT),
            'service_type' => Tool_Input::clean('r', 'service_type', TYPE_UINT),
            'service_amount' => Tool_Input::clean('r', 'service_amount', TYPE_UINT),
            'invoice_day' => Tool_Input::clean('r', 'invoice_day', TYPE_STR),
            'batch' => Tool_Input::clean('r', 'batch', TYPE_STR),
            'number' => Tool_Input::clean('r', 'number', TYPE_STR),
            'bill_ids' => Tool_Input::clean('r', 'bill_ids', TYPE_ARRAY),
        );
	}

	protected function checkPara()
	{
        if (empty($this->invoiceInfo['cid']))
        {
            throw new Exception('客户ID不能为空');
        }
        if ($this->invoiceInfo['contract_number'] == '')
        {
            throw new Exception('合同编号不能为空');
        }

        if (empty($this->invoiceInfo['city_id']))
        {
            throw new Exception('请选择城市');
        }

        if (empty($this->invoiceInfo['invoice_type']))
        {
            throw new Exception('请选择发票类型');
        }

        if ($this->invoiceInfo['title'] == '')
        {
            throw new Exception('开票名称不能为空');
        }

        if ($this->invoiceInfo['pay_company'] == '')
        {
            throw new Exception('付款单位不能为空');
        }

        if (empty($this->invoiceInfo['invoice_amount']))
        {
            throw new Exception('开票订单金额不能为空');
        }

        if (empty($this->invoiceInfo['service_type']))
        {
            throw new Exception('请选择服务费类型');
        }

        if (empty($this->invoiceInfo['service_amount']))
        {
            throw new Exception('服务费金额不能为空');
        }

        if($this->id > 0)
        {
            $info = Invoice_Api::getOutputInvoiceInfo($this->id);
            if(empty($info))
            {
                throw new Exception('该发票不存在');
            }
            if(empty($info['products']))
            {
                throw new Exception('请添加商品清单');
            }
            if($info['step'] > Conf_Invoice::INVOICE_OUTPUT_STEP_FINANCE_CONFIRM)
            {
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
            }
        }

        if(empty($this->invoiceInfo['bill_ids']))
        {
            throw new Exception('请选择开票单据');
        }

        $this->invoiceInfo['bill_ids'] = implode(',', $this->invoiceInfo['bill_ids']);
	}

	protected function main()
	{
        
		if (empty($this->id))   //新建
		{
            $this->invoiceInfo['create_suid'] = $this->_uid;
            $this->id = Invoice_Api::addOutputInvoice($this->invoiceInfo['cid'], $this->invoiceInfo);
		}
		else    //编辑
        {
            $isFinance = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_FINANCE_NEW);
            $isAdmin = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ADMIN_NEW);
            Invoice_Api::updateOutputInvoice($this->id, $this->invoiceInfo, $isFinance || $isAdmin);
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

