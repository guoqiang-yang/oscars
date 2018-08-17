<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $id;
	private $scheduleInfo;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
		$this->scheduleInfo = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
		    'schedule_time' => Tool_Input::clean('r', 'schedule_time', TYPE_STR),
            'remind_tag' => Tool_Input::clean('r', 'remind_tag', TYPE_UINT),
            'content' => Tool_Input::clean('r', 'content', TYPE_STR)
        );
	}

	protected function checkPara()
	{
        if(!empty($this->scheduleInfo['cid']))
        {
            $customer = Crm2_Api::getCustomerInfo($this->scheduleInfo['cid']);
            if(empty($customer['customer']))
            {
                throw new Exception('客户不存在');
            }
            if($customer['customer']['sales_suid'] != $this->_uid)
            {
                throw new Exception('该客户不是您的客户，不能添加、编辑日程');
            }
        }
        if (empty($this->scheduleInfo['schedule_time']) || $this->scheduleInfo['schedule_time'] == '')
        {
            throw new Exception('开始时间不能为空');
        }else{
            $this->scheduleInfo['schedule_time'] = date('Y-m-d H:i:s', strtotime($this->scheduleInfo['schedule_time']));
        }

        if($this->scheduleInfo['schedule_time'] < date('Y-m-d H:i:s',time()))
        {
            throw new Exception('开始时间不能小于当前时间');
        }

        $remind_types = Conf_Crm::getRemindList();

        $this->scheduleInfo['remind_time'] = date('Y-m-d H:i:s', (strtotime($this->scheduleInfo['schedule_time'])-$remind_types[$this->scheduleInfo['remind_tag']]['interval']));

        if (empty($this->scheduleInfo['content']) || $this->scheduleInfo['content'] == '')
        {
            throw new Exception('日程内容不能为空');
        }


        $this->scheduleInfo['suid'] = $this->_uid;
	}

	protected function main()
	{
        
		if (empty($this->id))   //新建日程
		{
            $this->id = Crm2_Sale_Schedule_Api::addSaleSchedule($this->_uid, $this->scheduleInfo);
		}
		else    //编辑日程
		{
		    unset($this->scheduleInfo['cid']);
            unset($this->scheduleInfo['schedule_time']);
            unset($this->scheduleInfo['remind_tag']);

            Crm2_Sale_Schedule_Api::update($this->id, $this->scheduleInfo);
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

