<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $sid;
	private $supplier;
	private $refer;
    private $retSt;

	protected function getPara()
	{
		$this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
		$this->supplier = array(
			'name' => Tool_Input::clean('r', 'name', TYPE_STR),
			'alias_name' => Tool_Input::clean('r', 'alias_name', TYPE_STR),
			'contact_name' => Tool_Input::clean('r', 'contact_name', TYPE_STR),
			'phone' => Tool_Input::clean('r', 'phone', TYPE_STR),
			'address' => Tool_Input::clean('r', 'address', TYPE_STR),
			'wid' => $this->getWarehouseId(),
			'products' => Tool_Input::clean('r', 'products', TYPE_STR),
			'cate1' => Tool_Input::clean('r', 'cate1', TYPE_STR),
			'city' => Tool_Input::clean('r', 'city', TYPE_STR),
			'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
			'note' => Tool_Input::clean('r', 'note', TYPE_STR),
            'book_note' => Tool_Input::clean('r', 'book_note', TYPE_STR),
			'delivery_hours' => Tool_Input::clean('r', 'delivery_hours', TYPE_UINT),
			'managing_mode' => Tool_Input::clean('r', 'managing_mode', TYPE_UINT),
		);
        
        $status = Tool_Input::clean('r', 'status', TYPE_STR);
        if ($status == Conf_Base::STATUS_WAIT_AUDIT)
        {
            $this->supplier['status'] = $status;
        }
        
		$this->refer = Tool_Input::clean('r', 'refer', TYPE_STR);
        if (strpos($this->refer, 'warehouse/supplier_list')!==false)
        {
            $this->refer = '';
        }
        
        $supplierInfo = array();
        if (!empty($this->sid))
        {
            $supplierInfo = Warehouse_Api::getSupplier($this->sid);
        }

        // 财务相关数据的保存 (新建供应商||驳回||有修改权限)
        if ( !empty($this->sid) || (isset($supplierInfo['status']) && $supplierInfo['status']==Conf_Base::STATUS_UN_AUDIT) 
            || !empty($this->permissions['hc_supplier_finance_data']))
        {
            $this->supplier['bank_info'] = Tool_Input::clean('r', 'bank_info', TYPE_STR);
            $bankInfo = Tool_Input::clean('r', 'public_bank', TYPE_STR);

            if (!empty($bankInfo))
            {
                $realBankInfo = array_unique(explode(',', $bankInfo));
                $str = '';
                foreach ($realBankInfo as $item)
                {
                    if ($item != 'undefined' && $item != '')
                    {
                        $str .= $item . ',';
                    }
                }
                $this->supplier['public_bank'] = rtrim($str, ',');
            } else {
                $this->supplier['public_bank'] = $bankInfo;
            }

            $this->supplier['payment_days'] = Tool_Input::clean('r', 'payment_days', TYPE_UINT);
            $this->supplier['invoice'] = Tool_Input::clean('r', 'invoice', TYPE_UINT);
            $this->supplier['duty'] = Tool_Input::clean('r', 'duty', TYPE_UINT);
            $this->supplier['special_duty'] = Tool_Input::clean('r', 'special_duty', TYPE_UINT);
            
            if ($this->supplier['invoice']!=2)
            {
                $this->supplier['duty'] = 0;
                $this->supplier['special_duty'] = 0;
            }
        }
        
	}

	protected function checkPara()
	{
	    if (empty($this->supplier['city']))
        {
            throw new Exception('至少选择一个经营城市！');
        }
		if (empty($this->supplier['name']))
		{
			throw new Exception('customer:name empty');
		}
		if (empty($this->supplier['contact_name']))
		{
			throw new Exception('customer:contact person name empty');
		}
		if (empty($this->supplier['phone']))
		{
			throw new Exception('customer:contact mobile');
		}
		if (empty($this->supplier['managing_mode']))
        {
            throw new Exception('请选择经营模式！');
        }
	}

	protected function checkAuth()
	{
		parent::checkAuth('/warehouse/edit_supplier');
	}

	protected function main()
	{
		if ($this->sid)
		{
            $supplier = Warehouse_Api::getSupplier($this->sid);
			if ($supplier['status'] == Conf_Base::STATUS_WAIT_AUDIT)
            {
                throw new Exception('待审核状态不能修改！');
            }
            
            // 驳回，保存，同时提交审核
            if ($supplier['status'] == Conf_Base::STATUS_UN_AUDIT)
            {
                $this->supplier['status'] = Conf_Base::STATUS_WAIT_AUDIT;
            }
            
			$ret = Warehouse_Api::updateSupplier($this->sid, $this->supplier);

			if (array_key_exists('status', $this->supplier) && $this->supplier['status'] == Conf_Base::STATUS_UN_AUDIT)
            {
                $messageData = array(
                'm_type' => 1,
                'typeid' => 0,
                'content' => '修改被驳回的供货商【供货商ID:'.$this->sid.'，供货商名称:'.$this->supplier['name'].'】；需要审核处理。',
                'send_suid' => $this->_uid,
                'receive_suid' => Conf_Stock::SUPPLIER_CHECK_USER,
                'url' => '/warehouse/edit_supplier.php?sid='.$this->sid.'&exec_type=check',
            );
                Admin_Message_Api::create($messageData);
            }

            $this->retSt = $ret['sid'];
		}
		else
		{
            $this->supplier['create_suid'] = $this->_uid;
			$ret = Warehouse_Api::addSupplier($this->supplier);
			$this->sid = $ret['sid'];
            $this->retSt = $ret['sid'];

            //添加供货商完成后，给制定的审核人发消息
            if (isset($ret['sid']) && $ret['sid'] > 0)
            {
                $messageData = array(
                    'm_type' => 1,
                    'typeid' => 0,
                    'content' => '新增供货商【供货商ID:'.$this->sid.'，供货商名称:'.$this->supplier['name'].'】；需要审核处理。',
                    'send_suid' => $this->_uid,
                    'receive_suid' => Conf_Stock::SUPPLIER_CHECK_USER,
                    'url' => '/warehouse/edit_supplier.php?sid='.$this->sid.'&exec_type=check',
                );
                Admin_Message_Api::create($messageData);
            }
		}
	}

	protected function outputPage()
	{
		$result = array('sid' => $this->sid, 'url' => $this->refer, 'errno'=> $this->retSt);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();

