<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $id;
    private $scheduleId;
    private $num;
	private $visitInfo;

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/crm2/edit_customer_visit');
    }

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->scheduleId = Tool_Input::clean('r', 'schedule_id', TYPE_UINT);
        $this->num = Tool_Input::clean('r', 'invoice_num', TYPE_UINT);
		$this->visitInfo = array(
		    'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
		    'visit_time' => Tool_Input::clean('r', 'visit_time', TYPE_STR),
            'visit_type' => Tool_Input::clean('r', 'visit_type', TYPE_UINT),
            'address' => Tool_Input::clean('r', 'address', TYPE_STR),
            'content' => Tool_Input::clean('r', 'content', TYPE_STR),
            'pic_ids' => Tool_Input::clean('r', 'pic_ids', TYPE_STR),
        );
	}

	protected function checkPara()
	{
        if (empty($this->visitInfo['cid']))
        {
            throw new Exception('CID不能为空');
        }

        if ($this->visitInfo['visit_time'] == '')
        {
            throw new Exception('拜访时间不能为空');
        }

        if (empty($this->visitInfo['visit_type']))
        {
            throw new Exception('请选择拜访类型');
        }

        if ($this->visitInfo['address'] == '')
        {
            throw new Exception('拜访地址不能为空');
        }
	}

	protected function main()
	{
        
		if (empty($this->id))   //新建拜访
		{
            $this->visitInfo['suid'] = $this->_uid;
            $this->id = Crm2_Customer_Visit_Api::addCustomerVisit($this->visitInfo['cid'], $this->visitInfo);
            if(!empty($this->scheduleId))
            {
                Crm2_Sale_Schedule_Api::update($this->scheduleId, array('vid' => $this->id));
            }
		}
		else    //编辑拜访
		{
		    unset($this->visitInfo['visit_time']);
			Crm2_Customer_Visit_Api::update($this->id, $this->visitInfo);
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

