<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $bid;
	private $business;

	protected function getPara()
	{
		$this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
		$this->business = array(
			'name' => Tool_Input::clean('r', 'name', TYPE_STR),
			'contract_name' => Tool_Input::clean('r', 'contract_name', TYPE_STR),
			'contract_phone' => Tool_Input::clean('r', 'contract_phone', TYPE_STR),
			'contract_phone2' => Tool_Input::clean('r', 'contract_phone2', TYPE_STR),
			'address' => Tool_Input::clean('r', 'address', TYPE_STR),
			'note' => Tool_Input::clean('r', 'note', TYPE_STR),
			'join_date' => Tool_Input::clean('r', 'join_date', TYPE_STR),
			'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),
			'sales_suid2' => Tool_Input::clean('r', 'sales_suid2', TYPE_UINT),
			'payment_days' => Tool_Input::clean('r', 'payment_days', TYPE_UINT),
		);
	}

	protected function checkPara()
	{
		if (empty($this->business['name']))
		{
			throw new Exception('business: name empty');
		}
		if (empty($this->business['contract_name']))
		{
			throw new Exception('business: contract name empty');
		}
		if (empty($this->business['contract_phone']))
		{
			throw new Exception('business: contract phone empty');
		}
	}

	protected function main()
	{
		if ($this->bid)
		{
			$updateSt = Crm_Api::updateBusiness($this->bid, $this->business);
            if (!$updateSt)
            {
                throw new Exception('添加失败');
            }
		}
		else
		{
			if (empty($this->business['sales_suid']) && Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW))
			{
				$this->business['sales_suid'] = $this->_uid;
			}

			$bid = Crm_Api::addBusiness($this->business);
			$this->bid = $bid;
		}
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

